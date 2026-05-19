<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeItem;
use App\Models\KnowledgeSource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;


class KnowledgeItemSourceController extends Controller
{
    public function store(Request $request, KnowledgeItem $knowledgeItem): RedirectResponse
    {
        $validated = $request->validate([
            'sourcetype' => ['required', 'string', 'max:50'],
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

        $knowledgeItem->sources()->create($validated);

        return redirect()
            ->route('knowledge.items.edit', [
                    'knowledgeItem' => $knowledgeItem,
                    'tab' => 'sources',
                ])
            ->with('success', 'Source added successfully.');
    }

    public function update(Request $request, KnowledgeItem $knowledgeItem, KnowledgeSource $knowledgeSource): RedirectResponse
    {
        $validated = $request->validate([
            'sourcetype' => ['required', 'string', Rule::in(KnowledgeSource::typeValues())],
            'sourceurl' => ['nullable', 'url', 'max:1000'],
            'sourcetitle' => ['required', 'string', 'max:255'],
            'sourcepublisher' => ['nullable', 'string', 'max:255'],
            'retrievedon' => ['nullable', 'date'],
            'importedsummary' => ['nullable', 'string'],
            'importednotes' => ['nullable', 'string'],
            'importstatus' => ['nullable', 'string', 'max:30'],
            'reviewedon' => ['nullable', 'date'],
            'reviewedby' => ['nullable', 'string', 'max:255'],
            'internalnotes' => ['nullable', 'string'],
        ]);

        $knowledgeSource->update($validated);

        return redirect()
            ->route('knowledge.items.edit', [
                    'knowledgeItem' => $knowledgeItem,
                    'tab' => 'sources',
                ])
            ->with('success', 'Source updated successfully.');
    }

    public function destroy(KnowledgeItem $knowledgeItem, KnowledgeSource $knowledgeSource): RedirectResponse
    {
        $knowledgeSource->delete();

        return redirect()
            ->route('knowledge.items.edit', [
                    'knowledgeItem' => $knowledgeItem,
                    'tab' => 'sources',
                ])
            ->with('success', 'Source deleted successfully.');
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
                'User-Agent' => config('app.name', 'Laravel') . ' Knowledge Source Importer',
                'Accept-Language' => 'en-AU,en;q=0.9',
            ])
            ->get($url);

        if (!$response->successful()) {
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

        // Remove obvious junk
        $removeNodes('//script|//style|//noscript|//svg|//form|//iframe');
        $removeNodes('//nav|//header|//footer|//aside');

        // Remove common boilerplate by class/id hints
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

        // Prefer article/main content if available
        $contentNode = null;
        $contentQueries = [
            '//main',
            '//article',
            "//*[contains(@class,'content')]",
            "//*[contains(@class,'article')]",
            "//*[contains(@class,'entry')]",
            "//*[contains(@class,'post')]",
            '//body',
        ];

        foreach ($contentQueries as $query) {
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

        // Remove repeated title from the front
        if ($pageTitle && Str::startsWith(Str::lower($cleanText), Str::lower($pageTitle))) {
            $cleanText = trim(Str::after($cleanText, $pageTitle));
        }

        // Cap to manageable review size
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
    } catch (\Throwable $e) {
        report($e);

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
}