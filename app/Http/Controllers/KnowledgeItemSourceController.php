<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeItem;
use App\Models\KnowledgeSource;
use Illuminate\Http\Request;
use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class KnowledgeItemSourceController extends Controller
{
    public function store(Request $request, KnowledgeItem $knowledgeItem)
    {
        $validated = $this->validatedData($request);

        $source = $knowledgeItem->sources()->create($validated);

        return response()->json([
            'source' => $this->sourcePayload($source->fresh()),
        ], 201);
    }

    public function update(
        Request $request,
        KnowledgeItem $knowledgeItem,
        KnowledgeSource $knowledgeSource
    ) {
        $this->ensureOwnership($knowledgeItem, $knowledgeSource);

        $validated = $this->validatedData($request);

        $knowledgeSource->update($validated);

        return response()->json([
            'source' => $this->sourcePayload($knowledgeSource->fresh()),
        ]);
    }

    public function destroy(
        KnowledgeItem $knowledgeItem,
        KnowledgeSource $knowledgeSource
    ) {
        $this->ensureOwnership($knowledgeItem, $knowledgeSource);

        $knowledgeSource->delete();

        return response()->noContent();
    }

    public function fetchFromInternet(Request $request, KnowledgeItem $knowledgeItem)
    {
        $validated = $request->validate([
            'fetch_url' => ['required', 'url', 'max:255'],
        ]);

        $url = $validated['fetch_url'];

        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    'User-Agent' => config('app.name', 'Laravel').' Knowledge Source Importer',
                    'Accept-Language' => 'en-AU,en;q=0.9',
                ])
                ->get($url);

            if (! $response->successful()) {
                return redirect()
                    ->route('knowledge.items.edit', [
                        'knowledgeItem' => $knowledgeItem,
                        'tab' => 'sources',
                        'show_add_source' => 1,
                    ])
                    ->withErrors([
                        'fetch_url' => 'Unable to fetch the requested page.',
                    ]);
            }

            $html = $response->body();

            libxml_use_internal_errors(true);

            $dom = new \DOMDocument();
            @$dom->loadHTML($html);

            $xpath = new \DOMXPath($dom);

            $removeNodes = function (string $query) use ($xpath) {
                foreach ($xpath->query($query) as $node) {
                    $node->parentNode?->removeChild($node);
                }
            };

            $removeNodes('//script|//style|//noscript|//svg|//form|//iframe');
            $removeNodes('//nav|//header|//footer|//aside');

            $junkSelectors = [
                "//*[contains(translate(@class,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'cookie')]",
                "//*[contains(translate(@class,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'consent')]",
                "//*[contains(translate(@class,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'banner')]",
                "//*[contains(translate(@class,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'toolbar')]",
                "//*[contains(translate(@class,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'share')]",
                "//*[contains(translate(@class,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'social')]",
                "//*[contains(translate(@class,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'menu')]",
                "//*[contains(translate(@class,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'nav')]",
                "//*[contains(translate(@class,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'footer')]",
                "//*[contains(translate(@class,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'header')]",
                "//*[contains(translate(@id,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'cookie')]",
                "//*[contains(translate(@id,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'consent')]",
                "//*[contains(translate(@id,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'menu')]",
                "//*[contains(translate(@id,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'nav')]",
            ];

            foreach ($junkSelectors as $selector) {
                $removeNodes($selector);
            }

            $metaContent = function (string $attr, string $value) use ($xpath) {
                $nodes = $xpath->query("//meta[@{$attr}='{$value}']");

                if ($nodes && $nodes->length > 0) {
                    return trim((string) $nodes->item(0)->getAttribute('content'));
                }

                return null;
            };

            $titleNode = $dom->getElementsByTagName('title')->item(0);
            $htmlTitle = trim($titleNode?->textContent ?? '');

            $pageTitle = $metaContent('property', 'og:title')
                ?: $metaContent('name', 'twitter:title')
                ?: $htmlTitle;

            $pageDescription = $metaContent('property', 'og:description')
                ?: $metaContent('name', 'description')
                ?: $metaContent('name', 'twitter:description');

            $publisher = $metaContent('property', 'og:site_name')
                ?: parse_url($url, PHP_URL_HOST);

            $contentNode = null;

            foreach ([
                '//main',
                '//article',
                "//*[contains(@class,'content')]",
                "//*[contains(@class,'article')]",
                "//*[contains(@class,'entry')]",
                "//*[contains(@class,'post')]",
                '//body',
            ] as $query) {
                $nodes = $xpath->query($query);

                if ($nodes && $nodes->length > 0) {
                    $contentNode = $nodes->item(0);
                    break;
                }
            }

            $rawText = $contentNode?->textContent ?? '';
            $cleanText = html_entity_decode($rawText, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $cleanText = preg_replace('/\s+/u', ' ', $cleanText);
            $cleanText = preg_replace('/(Facebook|X|Pinterest|Email)\b/i', '', $cleanText);
            $cleanText = preg_replace('/\b(Light|Dark|Font Family|Font Size|Download the app)\b/i', '', $cleanText);
            $cleanText = trim($cleanText);

            if ($pageTitle && Str::startsWith(Str::lower($cleanText), Str::lower($pageTitle))) {
                $cleanText = trim(Str::after($cleanText, $pageTitle));
            }

            $cleanText = Str::limit($cleanText, 2500, '...');

            return redirect()
                ->route('knowledge.items.edit', [
                    'knowledgeItem' => $knowledgeItem,
                    'tab' => 'sources',
                    'show_add_source' => 1,
                ])
                ->withInput([
                    'sourcetype' => old('sourcetype', 'website'),
                    'sourcetitle' => $pageTitle ?: old('sourcetitle'),
                    'sourceurl' => $url,
                    'sourcepublisher' => $publisher,
                    'retrievedon' => now()->format('Y-m-d'),
                    'importstatus' => 'pendingreview',
                    'importedsummary' => $pageDescription ?: old('importedsummary'),
                    'importednotes' => $cleanText,
                ])
                ->with('success', 'Internet source fetched. Review and save below.');
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('knowledge.items.edit', [
                    'knowledgeItem' => $knowledgeItem,
                    'tab' => 'sources',
                    'show_add_source' => 1,
                ])
                ->withErrors([
                    'fetch_url' => 'Unable to fetch or parse that URL.',
                ]);
        }
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'sourcetype' => [
                'required',
                'string',
                Rule::in(KnowledgeSource::typeValues()),
            ],
            'sourceurl' => ['nullable', 'url', 'max:255'],
            'sourcetitle' => ['required', 'string', 'max:255'],
            'sourcepublisher' => ['nullable', 'string', 'max:150'],
            'retrievedon' => ['nullable', 'date'],
            'importedsummary' => ['nullable', 'string'],
            'importednotes' => ['nullable', 'string'],
            'importstatus' => ['required', 'string', 'max:30'],
            'reviewedon' => ['nullable', 'date'],
            'reviewedby' => ['nullable', 'string', 'max:100'],
            'internalnotes' => ['nullable', 'string'],
        ]);
    }

    private function ensureOwnership(
        KnowledgeItem $knowledgeItem,
        KnowledgeSource $knowledgeSource
    ): void {
        abort_unless(
            (int) $knowledgeSource->knowledgeitemid === (int) $knowledgeItem->id,
            404
        );
    }

    private function sourcePayload(KnowledgeSource $source): array
    {
        $sourceTypeOptions = KnowledgeSource::typeOptions();

        return [
            'id' => $source->id,
            'sourcetype' => $source->sourcetype,
            'sourcetype_label' => $sourceTypeOptions[$source->sourcetype]
                ?? $source->sourcetype
                ?? 'Source',
            'sourceurl' => $source->sourceurl ?? '',
            'sourcetitle' => $source->sourcetitle ?? '',
            'sourcepublisher' => $source->sourcepublisher ?? '',
            'retrievedon' => $source->retrievedon?->format('Y-m-d'),
            'retrievedon_display' => $source->retrievedon?->format('d M Y'),
            'importedsummary' => $source->importedsummary ?? '',
            'importedsummary_html' => app(Markdown::class)
                ->parse($source->importedsummary ?? '')
                ->toHtml(),
            'importednotes' => $source->importednotes ?? '',
            'importednotes_html' => app(Markdown::class)
                ->parse($source->importednotes ?? '')
                ->toHtml(),
            'importstatus' => $source->importstatus ?? '',
            'reviewedon' => $source->reviewedon?->format('Y-m-d'),
            'reviewedon_display' => $source->reviewedon?->format('d M Y'),
            'reviewedby' => $source->reviewedby ?? '',
            'internalnotes' => $source->internalnotes ?? '',
            'internalnotes_html' => app(Markdown::class)
                ->parse($source->internalnotes ?? '')
                ->toHtml(),
        ];
    }
}