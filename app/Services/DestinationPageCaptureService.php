<?php

namespace App\Services;

use DOMDocument;
use DOMNode;
use DOMXPath;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use RuntimeException;

class DestinationPageCaptureService
{
    private const MAX_BYTES = 2_000_000;
    private const MAX_TEXT_CHARACTERS = 150_000;
    private const MAX_REDIRECTS = 2;

    public function capture(string $url): array
    {
        if (!extension_loaded('curl')) {
            throw new RuntimeException(
                'The PHP cURL extension is required for webpage capture.'
            );
        }

        if (!class_exists(DOMDocument::class)) {
            throw new RuntimeException(
                'The PHP DOM extension is required for webpage capture.'
            );
        }

        $currentUrl = $url;

        for ($hop = 0; $hop <= self::MAX_REDIRECTS; $hop++) {
            $response = $this->request($currentUrl);

            if (in_array($response['status'], [301, 302, 303, 307, 308], true)) {
                if (
                    $hop === self::MAX_REDIRECTS
                    || empty($response['location'])
                ) {
                    throw new RuntimeException(
                        'The page redirected too many times or supplied no redirect destination.'
                    );
                }

                $currentUrl = (string) UriResolver::resolve(
                    new Uri($currentUrl),
                    new Uri($response['location'])
                );

                continue;
            }

            if ($response['status'] < 200 || $response['status'] >= 300) {
                throw new RuntimeException(
                    "The website returned HTTP {$response['status']}. No content was saved."
                );
            }

            $contentType = strtolower(
                trim(explode(';', $response['content_type'])[0])
            );

            if (!in_array(
                $contentType,
                ['text/html', 'application/xhtml+xml', 'text/plain'],
                true
            )) {
                throw new RuntimeException(
                    'This version supports HTML and plain-text pages only.'
                );
            }

            $text = $contentType === 'text/plain'
                ? $this->normaliseText($response['body'])
                : $this->extractReadableText($response['body']);

            if (mb_strlen($text) < 80) {
                throw new RuntimeException(
                    'The page contained too little readable text. It may require JavaScript, a login, or manual copying.'
                );
            }

            $truncated = mb_strlen($text) > self::MAX_TEXT_CHARACTERS;

            if ($truncated) {
                $text = mb_substr($text, 0, self::MAX_TEXT_CHARACTERS);
            }

            return [
                'text' => $text,
                'final_url' => $currentUrl,
                'characters' => mb_strlen($text),
                'truncated' => $truncated,
            ];
        }

        throw new RuntimeException('The page could not be captured.');
    }

    private function request(string $url): array
    {
        [$host, $port, $address] = $this->resolvePublicDestination($url);

        $body = '';
        $headers = [];
        $tooLarge = false;

        $handle = curl_init($url);

        if ($handle === false) {
            throw new RuntimeException('The webpage request could not be initialised.');
        }

        curl_setopt_array($handle, [
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_PROXY => '',
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_RESOLVE => ["{$host}:{$port}:{$address}"],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_ENCODING => '',
            CURLOPT_USERAGENT => 'CaravanTravelSourceCapture/1.0',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html, application/xhtml+xml, text/plain;q=0.9',
            ],
            CURLOPT_HEADERFUNCTION => function ($curl, string $line) use (&$headers): int {
                if (str_starts_with($line, 'HTTP/')) {
                    $headers = [];
                } elseif (str_contains($line, ':')) {
                    [$name, $value] = explode(':', $line, 2);
                    $headers[strtolower(trim($name))] = trim($value);
                }

                return strlen($line);
            },
            CURLOPT_WRITEFUNCTION => function ($curl, string $chunk) use (
                &$body,
                &$tooLarge
            ): int {
                if (strlen($body) + strlen($chunk) > self::MAX_BYTES) {
                    $tooLarge = true;
                    return 0;
                }

                $body .= $chunk;

                return strlen($chunk);
            },
        ]);

        try {
            $success = curl_exec($handle);

            if ($tooLarge) {
                throw new RuntimeException(
                    'The page exceeded the 2 MB capture limit. No content was saved.'
                );
            }

            if ($success === false) {
                throw new RuntimeException(
                    'The page request failed or timed out. No content was saved.'
                );
            }

            return [
                'status' => (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE),
                'content_type' => (string) curl_getinfo($handle, CURLINFO_CONTENT_TYPE),
                'location' => $headers['location'] ?? null,
                'body' => $body,
            ];
        } finally {
            curl_close($handle);
        }
    }

    private function resolvePublicDestination(string $url): array
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('The source URL is invalid.');
        }

        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = strtolower($parts['host'] ?? '');

        if (
            !in_array($scheme, ['http', 'https'], true)
            || isset($parts['user'])
            || isset($parts['pass'])
        ) {
            throw new RuntimeException(
                'Only HTTP/HTTPS URLs without embedded credentials are supported.'
            );
        }

        $port = (int) ($parts['port'] ?? ($scheme === 'https' ? 443 : 80));

        if ($port !== ($scheme === 'https' ? 443 : 80)) {
            throw new RuntimeException(
                'Only standard HTTP and HTTPS ports are supported.'
            );
        }

        /*
         * Accept ordinary public DNS hostnames only.
         * Direct IP addresses and local/single-label hosts are refused.
         */
        if (
            !str_contains($host, '.')
            || filter_var($host, FILTER_VALIDATE_IP)
            || !filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)
            || preg_match('/\.(localhost|local|internal|test|invalid)$/i', $host)
        ) {
            throw new RuntimeException(
                'This URL is not an accepted public website hostname.'
            );
        }

        $addresses = gethostbynamel($host);

        if (!$addresses) {
            throw new RuntimeException(
                'The hostname could not be resolved to a supported public IPv4 address.'
            );
        }

        foreach ($addresses as $address) {
            if (!$this->isPublicIpv4($address)) {
                throw new RuntimeException(
                    'The hostname resolves to a local, private or reserved address. Capture was blocked.'
                );
            }
        }

        return [$host, $port, $addresses[0]];
    }

    private function isPublicIpv4(string $address): bool
    {
        if (!filter_var(
            $address,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        )) {
            return false;
        }

        /*
         * Additional non-public/special-purpose ranges.
         */
        foreach ([
            '0.0.0.0/8',
            '100.64.0.0/10',
            '192.0.0.0/24',
            '192.0.2.0/24',
            '192.88.99.0/24',
            '198.18.0.0/15',
            '198.51.100.0/24',
            '203.0.113.0/24',
            '224.0.0.0/4',
            '240.0.0.0/4',
        ] as $range) {
            [$network, $bits] = explode('/', $range);
            $mask = -1 << (32 - (int) $bits);

            if ((ip2long($address) & $mask) === (ip2long($network) & $mask)) {
                return false;
            }
        }

        return true;
    }

    private function extractReadableText(string $html): string
    {
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);

        try {
            $loaded = $document->loadHTML(
                '<?xml encoding="UTF-8">' . $html,
                LIBXML_NONET
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        if (!$loaded) {
            throw new RuntimeException('The webpage HTML could not be read.');
        }

        $xpath = new DOMXPath($document);

        $unwanted = $xpath->query(
            '//script | //style | //noscript | //nav | //header | //footer'
            . ' | //aside | //form | //svg | //iframe | //template'
            . ' | //*[@hidden] | //*[@aria-hidden="true"]'
        );

        foreach (iterator_to_array($unwanted) as $node) {
            $node->parentNode?->removeChild($node);
        }

        /*
         * Prefer main/article content; otherwise use the body.
         */
        $candidates = $xpath->query('//main | //article | //*[@role="main"]');

        $selected = null;
        $longest = 0;

        foreach ($candidates as $candidate) {
            $length = mb_strlen(trim($candidate->textContent));

            if ($length > $longest) {
                $selected = $candidate;
                $longest = $length;
            }
        }

        if (!$selected || $longest < 80) {
            $selected = $document->getElementsByTagName('body')->item(0);
        }

        if (!$selected) {
            throw new RuntimeException('No readable page body was found.');
        }

        return $this->normaliseText($this->nodeText($selected));
    }

    private function nodeText(DOMNode $node): string
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return preg_replace('/\s+/u', ' ', $node->nodeValue ?? '') ?? '';
        }

        $name = strtolower($node->nodeName);

        if ($name === 'br') {
            return "\n";
        }

        $text = '';

        foreach ($node->childNodes as $child) {
            $text .= $this->nodeText($child);
        }

        $blocks = [
            'p', 'div', 'section', 'article', 'main', 'li',
            'blockquote', 'pre', 'h1', 'h2', 'h3', 'h4',
            'h5', 'h6', 'tr', 'ul', 'ol',
        ];

        return in_array($name, $blocks, true)
            ? "\n\n" . $text . "\n\n"
            : $text . ' ';
    }

    private function normaliseText(string $text): string
    {
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        $text = str_replace(["\r\n", "\r", "\u{00A0}"], ["\n", "\n", ' '], $text);
        $text = preg_replace('/[ \t]+/u', ' ', $text) ?? '';
        $text = preg_replace('/ *\n */u', "\n", $text) ?? '';
        $text = preg_replace('/\n{3,}/u', "\n\n", $text) ?? '';

        return trim($text);
    }
}