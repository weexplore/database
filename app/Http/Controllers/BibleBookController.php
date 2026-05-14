<?php

namespace App\Http\Controllers;

use App\Models\BibleBook;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BibleBookController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'testament' => trim((string) $request->query('testament', '')),
        ];

        $query = BibleBook::query();

        if ($filters['search'] !== '') {
            $query->where(function ($q) use ($filters) {
                $q->where('bookcode', 'like', "%{$filters['search']}%")
                    ->orWhere('bookname', 'like', "%{$filters['search']}%");
            });
        }

        if ($filters['testament'] !== '') {
            $query->where('testament', $filters['testament']);
        }

        return view('bible-books.index', [
            'pageTitle' => 'Bible Books',
            'rows' => $query->orderBy('sortorder')->orderBy('bookname')->get(),
            'filters' => $filters,
            'testamentOptions' => [
                'old' => 'Old Testament',
                'new' => 'New Testament',
            ],
        ]);
    }

    public function bulkSave(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'existing' => ['nullable', 'array'],
            'existing.*.bookcode' => ['required', 'string', 'max:20'],
            'existing.*.bookname' => ['required', 'string', 'max:100'],
            'existing.*.testament' => ['required', 'string', Rule::in(['old', 'new'])],
            'existing.*.sortorder' => ['required', 'integer', 'min:1'],
            'existing.*.chaptercount' => ['nullable', 'integer', 'min:1'],


            'new' => ['nullable', 'array'],
            'new.bookcode' => ['nullable', 'string', 'max:20'],
            'new.bookname' => ['nullable', 'string', 'max:100'],
            'new.testament' => ['nullable', 'string', Rule::in(['old', 'new'])],
            'new.sortorder' => ['nullable', 'integer', 'min:1'],
            'new.chaptercount' => ['nullable', 'integer', 'min:1'],
            'search' => ['nullable', 'string'],
            'testament' => ['nullable', Rule::in(['old', 'new'])],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['existing'] ?? [] as $id => $row) {
                $bookCode = strtoupper(trim((string) ($row['bookcode'] ?? '')));
                $bookName = trim((string) ($row['bookname'] ?? ''));
                $testament = strtolower(trim((string) ($row['testament'] ?? '')));
                $sortOrder = $row['sortorder'] ?? null;
                $chapterCount = $row['chaptercount'] ?? null;

                if ($bookCode === '') {
                    throw ValidationException::withMessages([
                        "existing.$id.bookcode" => 'Book code is required.',
                    ]);
                }

                if ($bookName === '') {
                    throw ValidationException::withMessages([
                        "existing.$id.bookname" => 'Book name is required.',
                    ]);
                }

                $duplicateCode = BibleBook::query()
                    ->whereRaw('UPPER(bookcode) = ?', [$bookCode])
                    ->where('id', '!=', $id)
                    ->exists();

                if ($duplicateCode) {
                    throw ValidationException::withMessages([
                        "existing.$id.bookcode" => 'Book code must be unique.',
                    ]);
                }

                $duplicateSort = BibleBook::query()
                    ->where('sortorder', $sortOrder)
                    ->where('id', '!=', $id)
                    ->exists();

                if ($duplicateSort) {
                    throw ValidationException::withMessages([
                        "existing.$id.sortorder" => 'Sort order must be unique.',
                    ]);
                }

                $book = BibleBook::findOrFail($id);

                $book->update([
                    'bookcode' => $bookCode,
                    'bookname' => $bookName,
                    'testament' => $testament,
                    'sortorder' => $sortOrder,
                    'chaptercount' => $chapterCount !== null && $chapterCount !== '' ? (int) $chapterCount : null,
                ]);
            }

            $new = $validated['new'] ?? [];

            $newCode = strtoupper(trim((string) ($new['bookcode'] ?? '')));
            $newName = trim((string) ($new['bookname'] ?? ''));
            $newTestament = strtolower(trim((string) ($new['testament'] ?? '')));
            $newSortOrder = $new['sortorder'] ?? null;
            $newChapterCount = $new['chaptercount'] ?? null;

            $hasNewRow =
                $newCode !== '' ||
                $newName !== '' ||
                $newTestament !== '' ||
                $newSortOrder !== null ||
                $newChapterCount !== null;

            if ($hasNewRow) {
                if ($newCode === '') {
                    throw ValidationException::withMessages([
                        'new.bookcode' => 'Book code is required for a new Bible book.',
                    ]);
                }

                if ($newName === '') {
                    throw ValidationException::withMessages([
                        'new.bookname' => 'Book name is required for a new Bible book.',
                    ]);
                }

                if (!in_array($newTestament, ['old', 'new'], true)) {
                    throw ValidationException::withMessages([
                        'new.testament' => 'Testament must be old or new.',
                    ]);
                }

                if ($newSortOrder === null || $newSortOrder === '') {
                    throw ValidationException::withMessages([
                        'new.sortorder' => 'Sort order is required for a new Bible book.',
                    ]);
                }

                $duplicateCode = BibleBook::query()
                    ->whereRaw('UPPER(bookcode) = ?', [$newCode])
                    ->exists();

                if ($duplicateCode) {
                    throw ValidationException::withMessages([
                        'new.bookcode' => 'Book code must be unique.',
                    ]);
                }

                $duplicateSort = BibleBook::query()
                    ->where('sortorder', $newSortOrder)
                    ->exists();

                if ($duplicateSort) {
                    throw ValidationException::withMessages([
                        'new.sortorder' => 'Sort order must be unique.',
                    ]);
                }

                BibleBook::create([
                    'bookcode' => $newCode,
                    'bookname' => $newName,
                    'testament' => $newTestament,
                    'sortorder' => (int) $newSortOrder,
                    'chaptercount' => $newChapterCount !== null && $newChapterCount !== '' ? (int) $newChapterCount : null,
                ]);
            }
        });

        return redirect()
            ->route('bible-books.index', [
                'search' => $request->input('search'),
                'testament' => $request->input('testament'),
            ])
            ->with('success', 'Bible books saved successfully.');
    }

    public function destroy(BibleBook $bibleBook): RedirectResponse
    {
        $bibleBook->delete();

        return redirect()
            ->route('bible-books.index', [
                'search' => request('search'),
                'testament' => request('testament'),
            ])
            ->with('success', 'Bible book deleted.');
    }
}