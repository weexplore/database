<?php

namespace App\Http\Controllers;

use App\Models\BibleBook;
use App\Models\BibleReference;
use App\Models\BibleVersion;
use App\Models\KnowledgeItem;
use App\Services\ApiBibleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BibleReferenceController extends Controller
{
    public function store(Request $request, KnowledgeItem $knowledgeItem): RedirectResponse
    {
        $data = $this->validateData($request);

        BibleReference::create([
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
                $data['referencelabel'] ?? null
            ),
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItem,
                'tab' => 'bible-references',
            ])
            ->with('success', 'Bible reference added.');
    }

    public function edit(BibleReference $bibleReference): View
    {
        $bibleReference->load(['item.primaryCategory.domain', 'book', 'version']);

        return view('knowledge.items.bible-references.edit', [
            'bibleReference' => $bibleReference,
            'knowledgeItem' => $bibleReference->item,
            'books' => BibleBook::query()
                ->orderBy('sortorder')
                ->orderBy('bookname')
                ->get(),
            'versions' => BibleVersion::query()
                ->where('isactive', 1)
                ->orderBy('versionname')
                ->get(),
            'returnTo' => route('knowledge.items.edit', [
                'knowledgeItem' => $bibleReference->knowledgeitemid,
                'tab' => 'bible-references',
            ]),
        ]);
    }

    public function update(Request $request, BibleReference $bibleReference): RedirectResponse
    {
        $data = $this->validateData($request);

        $newReferenceLabel = $this->buildReferenceLabel(
            $data['bookid'],
            $data['chapterfrom'],
            $data['versefrom'] ?? null,
            $data['chapterto'] ?? null,
            $data['verseto'] ?? null,
            $data['referencelabel'] ?? null
        );

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
            'referencelabel' => $newReferenceLabel,
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

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $bibleReference->knowledgeitemid,
                'tab' => 'bible-references',
            ])
            ->with(
                'success',
                $referenceChanged
                    ? 'Bible reference updated. Cached passage cleared because the reference changed.'
                    : 'Bible reference updated.'
            );
    }

    public function destroy(BibleReference $bibleReference): RedirectResponse
    {
        $knowledgeItemId = $bibleReference->knowledgeitemid;

        $bibleReference->delete();

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItemId,
                'tab' => 'bible-references',
            ])
            ->with('success', 'Bible reference deleted.');
    }

    public function fetchPassage(BibleReference $bibleReference, ApiBibleService $apiBibleService): RedirectResponse
    {
        try {
            $apiBibleService->fetchAndStorePassage($bibleReference);

            return redirect()
                ->route('knowledge.items.edit', [
                    'knowledgeItem' => $bibleReference->knowledgeitemid,
                    'tab' => 'bible-references',
                ])
                ->with('success', 'Bible passage fetched successfully.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('knowledge.items.edit', [
                    'knowledgeItem' => $bibleReference->knowledgeitemid,
                    'tab' => 'bible-references',
                ])
                ->with('error', $e->getMessage());
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

        if (!empty($data['chapterto']) && (int) $data['chapterto'] < (int) $data['chapterfrom']) {
            throw ValidationException::withMessages([
                'chapterto' => 'The ending chapter must be greater than or equal to the starting chapter.',
            ]);
        }

        if (
            !empty($data['chapterto']) &&
            (int) $data['chapterto'] === (int) $data['chapterfrom'] &&
            !empty($data['versefrom']) &&
            !empty($data['verseto']) &&
            (int) $data['verseto'] < (int) $data['versefrom']
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
        ?string $manualLabel
    ): string {
        if (!empty(trim((string) $manualLabel))) {
            return trim((string) $manualLabel);
        }

        $book = BibleBook::find($bookId);
        $bookName = $book?->bookname ?: 'Book';

        $from = $chapterFrom . ($verseFrom ? ':' . $verseFrom : '');
        $to = null;

        if ($chapterTo) {
            $to = $chapterTo . ($verseTo ? ':' . $verseTo : '');
        } elseif ($verseTo && $verseFrom) {
            $to = $chapterFrom . ':' . $verseTo;
        }

        return $to ? "{$bookName} {$from}-{$to}" : "{$bookName} {$from}";
    }
}