<?php

namespace App\Services;

use App\Models\Destination;
use App\Models\DestinationSource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DestinationImportService
{
    public function suggestForDestination(Destination $destination): array
    {
        $query = $this->buildSearchQuery($destination);
        $results = $this->searchWeb($query);

        $draftSources = $this->mapSearchResultsToSources($results, $destination);
        $savedSources = $this->persistValidSources($destination, $draftSources);
        $fields = $this->buildSuggestedFields($destination, $savedSources);

        return [
            'fields' => $fields,
            'sources' => collect($savedSources)->map(function (DestinationSource $source) {
                return [
                    'id' => $source->id,
                    'sourceurl' => $source->sourceurl,
                    'sourcetitle' => $source->sourcetitle,
                    'sourcepublisher' => $source->sourcepublisher,
                    'retrievedon' => optional($source->retrievedon)->toDateString(),
                    'importstatus' => $source->importstatus,
                    'importedsummary' => $source->importedsummary,
                    'importednotes' => $source->importednotes,
                ];
            })->values()->all(),
            'query' => $query,
        ];
    }

    protected function buildSearchQuery(Destination $destination): string
    {
        $parts = array_filter([
            $destination->destinationname,
            optional($destination->place)->locality,
            optional($destination->place)->placename,
            'Victoria',
            'Australia',
            'tourism',
        ]);

        return implode(' ', array_unique($parts));
    }

    protected function searchWeb(string $query): array
    {
        $apiKey = config('services.serpapi.api_key');

        if (blank($apiKey)) {
            return [];
        }

        $response = Http::timeout(20)
            ->acceptJson()
            ->get('https://serpapi.com/search.json', [
                'engine' => 'google',
                'q' => $query,
                'api_key' => $apiKey,
                'num' => 5,
            ]);

        if (! $response->successful()) {
            return [];
        }

        return $response->json('organic_results', []);
    }

    protected function mapSearchResultsToSources(array $results, Destination $destination): array
    {
        return collect($results)
            ->take(5)
            ->map(function (array $result) use ($destination) {
                $url = $result['link'] ?? null;
                $title = $result['title'] ?? null;
                $snippet = $result['snippet'] ?? null;
                $displayedLink = $result['displayed_link'] ?? null;

                return [
                    'destinationid' => $destination->id,
                    'destinationitemid' => null,
                    'sourcetype' => $this->detectSourceType($url, $title, $displayedLink),
                    'sourceurl' => $url,
                    'sourcetitle' => $title,
                    'sourcepublisher' => $this->extractPublisher($url, $displayedLink),
                    'retrievedon' => Carbon::now()->toDateString(),
                    'importedsummary' => $this->cleanText($snippet),
                    'importednotes' => null,
                    'importstatus' => 'pendingreview',
                    'reviewedon' => null,
                    'reviewedby' => null,
                    'internalnotes' => 'Imported from web search results.',
                ];
            })
            ->filter(fn (array $source) => $this->shouldPersistSource($source['sourceurl'] ?? null))
            ->values()
            ->all();
    }

    protected function persistValidSources(Destination $destination, array $sources): array
    {
        $saved = [];

        foreach ($sources as $sourceData) {
            $existing = DestinationSource::query()
                ->where('destinationid', $destination->id)
                ->where('sourceurl', $sourceData['sourceurl'])
                ->first();

            if ($existing) {
                $saved[] = $existing;
                continue;
            }

            $saved[] = DestinationSource::create($sourceData);
        }

        return $saved;
    }

    protected function buildSuggestedFields(Destination $destination, array $sources): array
    {
        $summaries = collect($sources)
            ->pluck('importedsummary')
            ->filter()
            ->map(fn ($text) => trim($text))
            ->values();

        $overview = $summaries->take(2)->implode(' ');
        $overview = $overview ? Str::limit($overview, 900) : null;

        return [
            'overview' => $overview,
            'travelnotes' => null,
            'bestseason' => null,
            'suitability' => null,
            'accessnotes' => null,
        ];
    }

    protected function shouldPersistSource(?string $url): bool
    {
        if (blank($url) || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);

        if (blank($host)) {
            return false;
        }

        $blockedHosts = [
            'example.com',
            'example.org',
            'localhost',
            '127.0.0.1',
        ];

        return ! in_array(Str::lower($host), $blockedHosts, true);
    }

    protected function detectSourceType(?string $url, ?string $title, ?string $displayedLink): string
    {
        $haystack = Str::lower(trim(($url ?? '') . ' ' . ($title ?? '') . ' ' . ($displayedLink ?? '')));

        if (Str::contains($haystack, ['visit', 'tourism', 'australia.com', '.gov', '.vic.gov'])) {
            return 'tourismboard';
        }

        if (Str::contains($haystack, ['map', 'google maps'])) {
            return 'map';
        }

        if (Str::contains($haystack, ['blog'])) {
            return 'blog';
        }

        return 'website';
    }

    protected function extractPublisher(?string $url, ?string $displayedLink): ?string
    {
        $host = parse_url($url ?? '', PHP_URL_HOST);

        if (blank($host) && ! blank($displayedLink)) {
            $host = $displayedLink;
        }

        if (blank($host)) {
            return null;
        }

        $host = Str::lower($host);
        $host = preg_replace('/^www\./', '', $host);

        return $host;
    }

    protected function cleanText(?string $text): ?string
    {
        if (blank($text)) {
            return null;
        }

        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }
}