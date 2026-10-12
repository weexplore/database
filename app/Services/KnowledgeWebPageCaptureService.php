<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Support\Str;
use RuntimeException;
use League\HTMLToMarkdown\HtmlConverter;
use League\HTMLToMarkdown\Converter\TableConverter;

class KnowledgeWebPageCaptureService
{
    private const MAX_DOWNLOAD_BYTES = 4_000_000;
    private const MAX_CONTENT_CHARACTERS = 500_000;
    private const MAX_REDIRECTS = 5;

    public function capture(string $url): array
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException(
                'Website fetching requires the PHP cURL extension.'
            );
        }

        if (!class_exists(DOMDocument::class)) {
            throw new RuntimeException(
                'Website fetching requires the PHP DOM extension.'
            );
        }

        $url = trim($url);

        for ($redirect = 0; $redirect <= self::MAX_REDIRECTS; $redirect++) {
            $destination = $this->validatedDestination($url);

            $page = $this->download(
                $url,
                $destination['host'],
                $destination['port'],
                $destination['ip']
            );

            if (in_array($page['status'], [301, 302, 303, 307, 308], true)) {
                if ($redirect === self::MAX_REDIRECTS) {
                    throw new RuntimeException(
                        'The website redirected too many times.'
                    );
                }

                if ($page['location'] === '') {
                    throw new RuntimeException(
                        'The website returned a redirect without a destination.'
                    );
                }

                $url = (string) UriResolver::resolve(
                    new Uri($url),
                    new Uri($page['location'])
                );

                continue;
            }

            if ($page['status'] < 200 || $page['status'] >= 300) {
                throw new RuntimeException(
                    'The website returned HTTP '.$page['status'].
                    '. It may block automated retrieval or require a login.'
                );
            }

            $contentType = strtolower($page['contenttype']);

            if (
                $contentType !== ''
                && !str_contains($contentType, 'text/html')
                && !str_contains($contentType, 'application/xhtml+xml')
            ) {
                throw new RuntimeException(
                    'This fetch action supports HTML webpages, not PDFs or other file types.'
                );
            }

            if (trim($page['body']) === '') {
                throw new RuntimeException(
                    'The website returned an empty response.'
                );
            }

            return $this->extract($page['body'], $url);
        }

        throw new RuntimeException('Unable to complete the webpage request.');
    }

    private function validatedDestination(string $url): array
    {
        if (
            !filter_var($url, FILTER_VALIDATE_URL)
            || preg_match('/[\x00-\x20\x7f\\\\]/', $url)
        ) {
            throw new RuntimeException('Enter a valid webpage URL.');
        }

        $parts = parse_url($url);

        if (!is_array($parts)) {
            throw new RuntimeException('Unable to read that URL.');
        }

        $scheme = strtolower($parts['scheme'] ?? '');
        $host = strtolower($parts['host'] ?? '');
        $port = (int) ($parts['port'] ?? ($scheme === 'https' ? 443 : 80));

        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new RuntimeException(
                'Only HTTP and HTTPS webpages can be fetched.'
            );
        }

        if (
            $host === ''
            || isset($parts['user'])
            || isset($parts['pass'])
            || !in_array($port, [80, 443], true)
        ) {
            throw new RuntimeException(
                'Use a public webpage URL without credentials and on port 80 or 443.'
            );
        }

        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $addresses = [$host];
        } else {
            if (
                !str_contains($host, '.')
                || !preg_match('/^[a-z0-9.-]+$/i', $host)
            ) {
                throw new RuntimeException(
                    'This importer requires a public IPv4 address or a conventional public hostname.'
                );
            }

            $records = dns_get_record($host, DNS_A);

            $addresses = collect($records ?: [])
                ->pluck('ip')
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        if ($addresses === []) {
            throw new RuntimeException(
                'The website hostname did not resolve to an IPv4 address.'
            );
        }

        foreach ($addresses as $address) {
            if (!$this->isPublicIpv4($address)) {
                throw new RuntimeException(
                    'Fetching private, local or reserved network addresses is not permitted.'
                );
            }
        }

        return [
            'host' => $host,
            'port' => $port,
            'ip' => $addresses[0],
        ];
    }

    private function isPublicIpv4(string $ip): bool
    {
        if (!filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        )) {
            return false;
        }

        $blockedRanges = [
            ['0.0.0.0', 8],
            ['10.0.0.0', 8],
            ['100.64.0.0', 10],
            ['127.0.0.0', 8],
            ['169.254.0.0', 16],
            ['172.16.0.0', 12],
            ['192.0.0.0', 24],
            ['192.0.2.0', 24],
            ['192.168.0.0', 16],
            ['198.18.0.0', 15],
            ['198.51.100.0', 24],
            ['203.0.113.0', 24],
            ['224.0.0.0', 4],
            ['240.0.0.0', 4],
        ];

        $number = ip2long($ip);

        foreach ($blockedRanges as [$network, $prefix]) {
            $mask = -1 << (32 - $prefix);

            if (($number & $mask) === (ip2long($network) & $mask)) {
                return false;
            }
        }

        return true;
    }

    private function download(
        string $url,
        string $host,
        int $port,
        string $ip
    ): array {
        $body = '';
        $location = '';
        $tooLarge = false;

        $handle = curl_init($url);

        if ($handle === false) {
            throw new RuntimeException('Unable to initialise webpage fetching.');
        }

        try {
            curl_setopt_array($handle, [
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 25,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_PROXY => '',
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_RESOLVE => [
                    $host.':'.$port.':'.$ip,
                ],
                CURLOPT_ENCODING => '',
                CURLOPT_USERAGENT =>
                    config('app.name', 'Laravel').' Knowledge Research Importer',
                CURLOPT_HTTPHEADER => [
                    'Accept: text/html,application/xhtml+xml',
                    'Accept-Language: en-AU,en;q=0.9',
                ],

                CURLOPT_HEADERFUNCTION => function ($curl, string $line) use (&$location) {
                    if (str_starts_with(strtolower($line), 'location:')) {
                        $location = trim(substr($line, 9));
                    }

                    return strlen($line);
                },

                CURLOPT_WRITEFUNCTION => function ($curl, string $chunk) use (
                    &$body,
                    &$tooLarge
                ) {
                    if (
                        strlen($body) + strlen($chunk)
                        > self::MAX_DOWNLOAD_BYTES
                    ) {
                        $tooLarge = true;
                        return 0;
                    }

                    $body .= $chunk;

                    return strlen($chunk);
                },
            ]);

            $completed = curl_exec($handle);

            if ($tooLarge) {
                throw new RuntimeException(
                    'The webpage exceeds the 4 MB download limit. Copy the relevant text manually instead.'
                );
            }

            if ($completed === false) {
                throw new RuntimeException(
                    'Unable to retrieve the webpage: '.curl_error($handle)
                );
            }

            return [
                'status' => (int) curl_getinfo($handle, CURLINFO_HTTP_CODE),
                'contenttype' => (string) curl_getinfo(
                    $handle,
                    CURLINFO_CONTENT_TYPE
                ),
                'location' => $location,
                'body' => $body,
            ];
        } finally {
            curl_close($handle);
        }
    }

    private function extract(string $html, string $url): array
{
    $previousLibxmlSetting = libxml_use_internal_errors(true);

    try {
        $encoding = mb_detect_encoding(
            $html,
            ['UTF-8', 'Windows-1252', 'ISO-8859-1'],
            true
        ) ?: 'UTF-8';

        if ($encoding !== 'UTF-8') {
            $html = mb_convert_encoding(
                $html,
                'UTF-8',
                $encoding
            );
        }

        $dom = new DOMDocument();

        $loaded = $dom->loadHTML(
            '<?xml encoding="UTF-8">'.$html,
            LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING
        );

        if (!$loaded) {
            throw new RuntimeException(
                'Unable to parse the webpage HTML.'
            );
        }

        $xpath = new DOMXPath($dom);

        $meta = function (
            string $attribute,
            string $value
        ) use ($xpath): string {
            $nodes = $xpath->query(
                "//meta[@{$attribute}='{$value}']"
            );

            return $nodes && $nodes->length
                ? trim($nodes->item(0)->getAttribute('content'))
                : '';
        };

        $title = $meta('property', 'og:title')
            ?: $meta('name', 'twitter:title')
            ?: trim(
                $dom->getElementsByTagName('title')
                    ->item(0)
                    ?->textContent
                ?? ''
            );

        $publisher = $meta('property', 'og:site_name')
            ?: (string) parse_url($url, PHP_URL_HOST);

        /*
         * Remove site controls, not the article's formatting.
         * Images are omitted from this text-research import.
         */
        $junkQueries = [
            '//script|//style|//noscript|//svg|//form|//iframe|//img',
            '//nav|//footer|//aside',
            "//*[@id='mw-navigation' or @id='mw-panel']",
            "//*[contains(concat(' ', normalize-space(@class), ' '), ' mw-editsection ')]",
            "//*[contains(concat(' ', normalize-space(@class), ' '), ' navbox ')]",
            "//*[contains(concat(' ', normalize-space(@class), ' '), ' vertical-navbox ')]",
        ];

        foreach ($junkQueries as $query) {
            $nodes = $xpath->query($query);

            if (!$nodes) {
                continue;
            }

            foreach (iterator_to_array($nodes) as $node) {
                $node->parentNode?->removeChild($node);
            }
        }

        $contentNode = null;

        foreach ([
            "//*[@id='mw-content-text']",
            '//article',
            '//main',
            "//*[@role='main']",
            '//body',
        ] as $query) {
            $nodes = $xpath->query($query);

            if ($nodes && $nodes->length) {
                $contentNode = $nodes->item(0);
                break;
            }
        }

        if (!$contentNode) {
            throw new RuntimeException(
                'No readable webpage content was found.'
            );
        }

        /*
         * Convert relative links to absolute links.
         * Unsafe or unsupported link schemes become plain text.
         */
        $links = $xpath->query('.//a[@href]', $contentNode);

        foreach ($links ?: [] as $link) {
            $href = trim($link->getAttribute('href'));

            if ($href === '') {
                $link->removeAttribute('href');
                continue;
            }

            try {
                $absoluteUrl = (string) UriResolver::resolve(
                    new Uri($url),
                    new Uri($href)
                );

                $scheme = strtolower(
                    (string) parse_url(
                        $absoluteUrl,
                        PHP_URL_SCHEME
                    )
                );

                if (
                    in_array($scheme, ['http', 'https'], true)
                    && filter_var(
                        $absoluteUrl,
                        FILTER_VALIDATE_URL
                    )
                ) {
                    $link->setAttribute('href', $absoluteUrl);
                } else {
                    $link->removeAttribute('href');
                }
            } catch (\Throwable $exception) {
                $link->removeAttribute('href');
            }
        }

        /*
         * Convert the complete cleaned content fragment.
         * Do not flatten its nodes through textContent.
         */
        $contentHtml = $dom->saveHTML($contentNode);

        if ($contentHtml === false) {
            throw new RuntimeException(
                'Unable to read the cleaned webpage content.'
            );
        }

        $converter = new HtmlConverter([
            'strip_tags' => true,
            'header_style' => 'atx',
            'remove_nodes' => 'script style noscript iframe svg form img',
        ]);

        $converter->getEnvironment()->addConverter(
            new TableConverter()
        );

        $content = trim(
            $converter->convert($contentHtml)
        );

        if ($content === '') {
            throw new RuntimeException(
                'The webpage contained no readable content. It may require JavaScript or a login.'
            );
        }

        if (
            mb_strlen($content)
            > self::MAX_CONTENT_CHARACTERS
        ) {
            throw new RuntimeException(
                'The extracted content exceeds the import limit. Copy the relevant section manually instead.'
            );
        }

        if ($title === '') {
            $title = (string) parse_url(
                $url,
                PHP_URL_HOST
            );
        }

        return [
            'sourcetype' => 'website',
            'sourcetitle' => Str::limit($title, 255, ''),
            'sourceurl' => $url,
            'sourcepublisher' => Str::limit($publisher, 150, ''),
            'rawcontent' => $content,
            'contentformat' => 'markdown',
        ];
    } finally {
        libxml_clear_errors();

        libxml_use_internal_errors(
            $previousLibxmlSetting
        );
    }
}

    private function cleanText(string $text): string
    {
        $text = str_replace("\u{00A0}", ' ', $text);

        return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    }
}