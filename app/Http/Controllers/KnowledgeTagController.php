<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KnowledgeTagController extends Controller
{
    private const TAG_TYPE_OPTIONS = [
        'finance' => 'Finance',
        'risk' => 'Risk',
        'theme' => 'Theme',
        'topic' => 'Topic',
        'doctrine' => 'Doctrine',
        'person' => 'Person',
        'place' => 'Place',
        'organisation' => 'Organisation',
        'status' => 'Status',
        'custom' => 'Custom',
    ];

    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'active' => (string) $request->query('active', ''),
        ];

        $query = KnowledgeTag::query();

        if ($filters['search'] !== '') {
            $query->where(function ($q) use ($filters) {
                $q->where('tagname', 'like', "%{$filters['search']}%")
                  ->orWhere('tagtype', 'like', "%{$filters['search']}%");
            });
        }

        if ($filters['active'] !== '') {
            $query->where('isactive', (int) $filters['active']);
        }

        return view('knowledge-tags.index', [
            'pageTitle' => 'Knowledge Tags',
            'rows' => $query->orderByRaw('COALESCE(sortorder, 999999), tagname')->get(),
            'filters' => $filters,
            'tagTypeOptions' => self::TAG_TYPE_OPTIONS,
        ]);
    }

    public function bulkSave(Request $request): RedirectResponse
    {
        $tagTypeKeys = array_keys(self::TAG_TYPE_OPTIONS);

        $validated = $request->validate([
            'existing' => ['nullable', 'array'],
            'existing.*.tagname' => ['required', 'string', 'max:100'],
            'existing.*.tagtype' => ['nullable', 'string', Rule::in($tagTypeKeys)],
            'existing.*.description' => ['nullable', 'string'],
            'existing.*.sortorder' => ['nullable', 'integer', 'min:0'],
            'existing.*.isactive' => ['nullable', 'boolean'],

            'new' => ['nullable', 'array'],
            'new.tagname' => ['nullable', 'string', 'max:100'],
            'new.tagtype' => ['nullable', 'string', Rule::in($tagTypeKeys)],
            'new.description' => ['nullable', 'string'],
            'new.sortorder' => ['nullable', 'integer', 'min:0'],
            'new.isactive' => ['nullable', 'boolean'],

            'search' => ['nullable', 'string'],
            'active' => ['nullable', 'in:0,1'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['existing'] ?? [] as $tagId => $row) {
                $tagname = trim((string) ($row['tagname'] ?? ''));
                $tagtype = trim((string) ($row['tagtype'] ?? ''));
                $description = isset($row['description']) ? trim((string) $row['description']) : null;

                if ($tagname === '') {
                    throw ValidationException::withMessages([
                        "existing.$tagId.tagname" => 'Tag name is required.',
                    ]);
                }

                $duplicateName = KnowledgeTag::query()
                    ->whereRaw('LOWER(tagname) = ?', [mb_strtolower($tagname)])
                    ->where('id', '!=', $tagId)
                    ->exists();

                if ($duplicateName) {
                    throw ValidationException::withMessages([
                        "existing.$tagId.tagname" => 'Tag name must be unique.',
                    ]);
                }

                $tag = KnowledgeTag::findOrFail($tagId);

                $tag->update([
                    'tagname' => $tagname,
                    'tagtype' => $tagtype !== '' ? $tagtype : null,
                    'description' => $description !== '' ? $description : null,
                    'sortorder' => $row['sortorder'] ?? null,
                    'isactive' => (bool) ($row['isactive'] ?? false),
                ]);
            }

            $new = $validated['new'] ?? [];
            $newName = trim((string) ($new['tagname'] ?? ''));
            $newType = trim((string) ($new['tagtype'] ?? ''));
            $newDescription = isset($new['description']) ? trim((string) $new['description']) : null;

            $hasNewTag = $newName !== '' || $newType !== '' || ($newDescription !== null && $newDescription !== '');

            if ($hasNewTag) {
                if ($newName === '') {
                    throw ValidationException::withMessages([
                        'new.tagname' => 'Tag name is required for a new knowledge tag.',
                    ]);
                }

                $duplicateName = KnowledgeTag::query()
                    ->whereRaw('LOWER(tagname) = ?', [mb_strtolower($newName)])
                    ->exists();

                if ($duplicateName) {
                    throw ValidationException::withMessages([
                        'new.tagname' => 'Tag name must be unique.',
                    ]);
                }

                KnowledgeTag::create([
                    'tagname' => $newName,
                    'tagtype' => $newType !== '' ? $newType : null,
                    'description' => $newDescription !== '' ? $newDescription : null,
                    'sortorder' => $new['sortorder'] ?? null,
                    'isactive' => (bool) ($new['isactive'] ?? false),
                ]);
            }
        });

        return redirect()
            ->route('knowledge-tags.index', [
                'search' => $request->input('search'),
                'active' => $request->input('active'),
            ])
            ->with('success', 'Knowledge tags saved successfully.');
    }

    public function destroy(KnowledgeTag $knowledgeTag): RedirectResponse
    {
        $knowledgeTag->delete();

        return redirect()
            ->route('knowledge-tags.index', [
                'search' => request('search'),
                'active' => request('active'),
            ])
            ->with('success', 'Knowledge tag deleted.');
    }
}