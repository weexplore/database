<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class KnowledgeTopicSearchService
{
    public function search(string $query): array
    {
        $key = config('knowledge_research.api_key');
        if (!filled($key)) {
            throw ValidationException::withMessages([
                'query' => 'Topic search is not configured. Add PERPLEXITY_API_KEY to the server environment. Manual import still works.',
            ]);
        }

        try {
            $response = Http::withToken($key)->acceptJson()
                ->connectTimeout(10)->timeout(config('knowledge_research.timeout', 30))
                ->post('https://api.perplexity.ai/search', [
                    'query' => $query,
                    'max_results' => (int) config('knowledge_research.max_results', 8),
                ]);
        } catch (\Illuminate\Http\Client\ConnectionException $exception) {
            throw ValidationException::withMessages([
                'query' => 'The search provider could not be reached. Please try again later.',
            ]);
        }

        if (!$response->successful()) {
            $message = match ($response->status()) {
                401, 403 => 'The search API key was rejected. Check the server configuration and API account.',
                429 => 'The search provider has reached a usage or rate limit. Please try again later.',
                default => 'The search provider returned an error. Please try again later.',
            };
            throw ValidationException::withMessages(['query' => $message]);
        }

        $results = $response->json('results');
        if (!is_array($results)) {
            throw ValidationException::withMessages(['query' => 'The search provider returned an unexpected response.']);
        }

        return collect($results)->filter(function ($row) {
            if (!is_array($row)) return false;
            $url = $row['url'] ?? '';
            return is_string($url) && filter_var($url, FILTER_VALIDATE_URL)
                && in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true);
        })->map(fn ($row) => [
            'title' => Str::limit((string) ($row['title'] ?? 'Untitled result'), 255, ''),
            'url' => $row['url'],
            'publisher' => Str::limit((string) parse_url($row['url'], PHP_URL_HOST), 150, ''),
            'snippet' => Str::limit((string) ($row['snippet'] ?? ''), 50000, '\n[Search excerpt shortened.]'),
            'date' => $row['date'] ?? null,
        ])->values()->all();
    }
}
