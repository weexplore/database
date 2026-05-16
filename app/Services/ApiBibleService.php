<?php

namespace App\Services;

use App\Models\BibleReference;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ApiBibleService
{
    public function fetchAndStorePassage(BibleReference $reference): BibleReference
    {
        $reference->loadMissing(['book', 'version']);

        $apiBibleId = $reference->version?->apibibleid;

        if (!$apiBibleId) {
            throw new RuntimeException('No API.Bible mapping is set for the selected Bible version.');
        }

        $referenceText = $reference->buildReferenceText();
        $apiPassageKey = $apiBibleId . ':' . $referenceText;

        $passageId = $this->buildPassageId($reference);

        $response = Http::baseUrl(config('services.apibible.base_url'))
            ->acceptJson()
            ->withHeaders([
                'api-key' => config('services.apibible.key'),
            ])
            ->timeout(15)
            ->retry(2, 300)
            ->get("/bibles/{$apiBibleId}/passages/{$passageId}", [
                'content-type' => 'text',
                'include-notes' => 'false',
                'include-titles' => 'false',
                'include-chapter-numbers' => 'false',
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Unable to fetch Bible passage from API.Bible.');
        }

        $payload = $response->json();
        $passageText = trim((string) data_get($payload, 'data.content', ''));

        if ($passageText === '') {
            throw new RuntimeException('API.Bible returned an empty passage.');
        }

        $reference->update([
            'cachedpassagetext' => $passageText,
            'cachedreferencetext' => $referenceText,
            'apipassagekey' => $apiPassageKey,
            'passagefetchedat' => Carbon::now(),
        ]);

        return $reference->fresh(['book', 'version']);
    }

    public function isPassageCacheFresh(BibleReference $reference): bool
    {
        if (!$reference->passagefetchedat || blank($reference->cachedpassagetext)) {
            return false;
        }

        $days = max(1, (int) config('services.apibible.cache_days', 7));

        return $reference->passagefetchedat->greaterThanOrEqualTo(now()->subDays($days));
    }

    private function encodeReference(string $referenceText): string
    {
        return str_replace('%20', ' ', rawurlencode($referenceText));
    }

    private function buildPassageId(BibleReference $reference): string
{
    $bookCode = $reference->book?->apibookcode;

    if (!$bookCode) {
        throw new RuntimeException('No API.Bible book code is set for the selected Bible book.');
    }

    $from = $bookCode . '.' . $reference->chapterfrom;

    if ($reference->versefrom) {
        $from .= '.' . $reference->versefrom;
    }

    if ($reference->chapterto || $reference->verseto) {
        $toBookCode = $bookCode;
        $toChapter = $reference->chapterto ?: $reference->chapterfrom;
        $toVerse = $reference->verseto;

        $to = $toBookCode . '.' . $toChapter;

        if ($toVerse) {
            $to .= '.' . $toVerse;
        }

        return $from . '-' . $to;
    }

    return $from;
}
}