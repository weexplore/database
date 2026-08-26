<?php

namespace App\Http\Controllers;

use App\Models\BibleBook;
use App\Models\BibleReference;
use App\Models\KnowledgeItem;
use App\Services\ApiBibleService;
use Illuminate\Http\Request;
use Illuminate\Mail\Markdown;
use Illuminate\Validation\ValidationException;

class BibleReferenceController extends Controller
{
    public function store(Request $request, KnowledgeItem $knowledgeItem)
    {
        $data = $this->validateData($request);

        $bibleReference = BibleReference::create([
            'knowledgeitemid' => $knowledgeItem->id,
            'versionid' => $data['versionid'] ?? null,
            'bookid' => $data['bookid'],
            'chapterfrom' => $data['chapterfrom'],
            'versefrom' => $data['versefrom'] ?? null,
            'chapterto' => $data['chapterto'] ?? null,
            'verseto' => $data['verseto'] ?? null,
            'referencelabel' => $this->buildReferenceLabel(
                $data['bookid'],
                $data['chapterfrom'],
                $data['versefrom'] ?? null,
                $data['chapterto'] ?? null,
                $data['verseto'] ?? null,
                $data['referencelabel'] ?? null,
            ),
            'notes' => $data['notes'] ?? null,
        ]);

        $bibleReference->load(['book', 'version']);

        return response()->json([
            'reference' => $this->referencePayload($bibleReference),
        ], 201);
    }

    public function update(Request $request, BibleReference $bibleReference)
    {
        $data = $this->validateData($request);

        $referenceChanged =
            (int) $bibleReference->versionid !== (int) ($data['versionid'] ?? null) ||
            (int) $bibleReference->bookid !== (int) $data['bookid'] ||
            (int) $bibleReference->chapterfrom !== (int) $data['chapterfrom'] ||
            (int) ($bibleReference->versefrom ?? 0) !== (int) ($data['versefrom'] ?? 0) ||
            (int) ($bibleReference->chapterto ?? 0) !== (int) ($data['chapterto'] ?? 0) ||
            (int) ($bibleReference->verseto ?? 0) !== (int) ($data['verseto'] ?? 0);

        $updateData = [
            'versionid' => $data['versionid'] ?? null,
            'bookid' => $data['bookid'],
            'chapterfrom' => $data['chapterfrom'],
            'versefrom' => $data['versefrom'] ?? null,
            'chapterto' => $data['chapterto'] ?? null,
            'verseto' => $data['verseto'] ?? null,
            'referencelabel' => $this->buildReferenceLabel(
                $data['bookid'],
                $data['chapterfrom'],
                $data['versefrom'] ?? null,
                $data['chapterto'] ?? null,
                $data['verseto'] ?? null,
                $data['referencelabel'] ?? null,
            ),
            'notes' => $data['notes'] ?? null,
        ];

        if ($referenceChanged) {
            $updateData = array_merge($updateData, [
                'cachedpassagetext' => null,
                'cachedreferencetext' => null,
                'apipassagekey' => null,
                'passagefetchedat' => null,
            ]);
        }

        $bibleReference->update($updateData);
        $bibleReference->load(['book', 'version']);

        return response()->json([
            'reference' => $this->referencePayload($bibleReference),
            'message' => $referenceChanged
                ? 'Bible reference updated. Cached passage cleared because the reference changed.'
                : 'Bible reference updated.',
        ]);
    }

    public function destroy(BibleReference $bibleReference)
    {
        $bibleReference->delete();

        return response()->noContent();
    }

    public function fetchPassage(
        BibleReference $bibleReference,
        ApiBibleService $apiBibleService
    ) {
        try {
            $apiBibleService->fetchAndStorePassage($bibleReference);

            return redirect()
                ->route('knowledge.items.edit', [
                    'knowledgeItem' => $bibleReference->knowledgeitemid,
                    'tab' => 'bible-references',
                ])
                ->with('success', 'Bible passage fetched successfully.');
        } catch (\Throwable $exception) {
            return redirect()
                ->route('knowledge.items.edit', [
                    'knowledgeItem' => $bibleReference->knowledgeitemid,
                    'tab' => 'bible-references',
                ])
                ->with('error', $exception->getMessage());
        }
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'versionid' => ['nullable', 'integer', 'exists:bibleversions,id'],
            'bookid' => ['required', 'integer', 'exists:biblebooks,id'],
            'chapterfrom' => ['required', 'integer', 'min:1'],
            'versefrom' => ['nullable', 'integer', 'min:1'],
            'chapterto' => ['nullable', 'integer', 'min:1'],
            'verseto' => ['nullable', 'integer', 'min:1'],
            'referencelabel' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        if (
            !empty($data['chapterto'])
            && (int) $data['chapterto'] < (int) $data['chapterfrom']
        ) {
            throw ValidationException::withMessages([
                'chapterto' => 'The ending chapter must be greater than or equal to the starting chapter.',
            ]);
        }

        if (
            !empty($data['chapterto'])
            && (int) $data['chapterto'] === (int) $data['chapterfrom']
            && !empty($data['versefrom'])
            && !empty($data['verseto'])
            && (int) $data['verseto'] < (int) $data['versefrom']
        ) {
            throw ValidationException::withMessages([
                'verseto' => 'The ending verse must be greater than or equal to the starting verse when the chapter is the same.',
            ]);
        }

        return $data;
    }

    private function buildReferenceLabel(
        int $bookId,
        int $chapterFrom,
        ?int $verseFrom,
        ?int $chapterTo,
        ?int $verseTo,
        ?string $manualLabel,
    ): string {
        if (filled(trim((string) $manualLabel))) {
            return trim((string) $manualLabel);
        }

        $bookName = BibleBook::find($bookId)?->bookname ?? 'Book';

        $from = $chapterFrom . ($verseFrom ? ':' . $verseFrom : '');
        $to = null;

        if ($chapterTo) {
            $to = $chapterTo . ($verseTo ? ':' . $verseTo : '');
        } elseif ($verseTo && $verseFrom) {
            $to = $chapterFrom . ':' . $verseTo;
        }

        return $to ? "{$bookName} {$from}-{$to}" : "{$bookName} {$from}";
    }

    private function referencePayload(BibleReference $reference): array
    {
        return [
            'id' => $reference->id,
            'versionid' => $reference->versionid,
            'versionname' => $reference->version?->versionname ?? '—',
            'bookid' => $reference->bookid,
            'bookname' => $reference->book?->bookname ?? 'Book',
            'chapterfrom' => (int) $reference->chapterfrom,
            'versefrom' => $reference->versefrom ? (int) $reference->versefrom : null,
            'chapterto' => $reference->chapterto ? (int) $reference->chapterto : null,
            'verseto' => $reference->verseto ? (int) $reference->verseto : null,
            'referencelabel' => $reference->referencelabel,
            'notes' => $reference->notes ?? '',
            'notes_html' => filled($reference->notes)
                ? app(Markdown::class)->parse($reference->notes)->toHtml()
                : '',
            'cachedpassagetext' => $reference->cachedpassagetext ?? '',
            'cachedreferencetext' => $reference->cachedreferencetext ?? '',
            'passagefetchedat' => $reference->passagefetchedat?->format('d M Y H:i'),
        ];
    }
}