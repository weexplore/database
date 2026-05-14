<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KnowledgeTagController extends Controller
{
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
            'rows' => $query->orderByRaw('COALESCE(sortorder, 999999), tagname')->get(),
            'filters' => $filters,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tagname' => ['required', 'string', 'max:100', 'unique:knowledgetags,tagname'],
            'tagtype' => ['nullable', 'string', 'max:50'],
            'sortorder' => ['nullable', 'integer', 'min:0'],
            'isactive' => ['required', 'boolean'],
        ]);

        KnowledgeTag::create($data);

        return redirect()->route('knowledge-tags.index')->with('success', 'Knowledge tag created.');
    }

    public function update(Request $request, KnowledgeTag $knowledgeTag): RedirectResponse
    {
        $data = $request->validate([
            'tagname' => ['required', 'string', 'max:100', 'unique:knowledgetags,tagname,' . $knowledgeTag->id],
            'tagtype' => ['nullable', 'string', 'max:50'],
            'sortorder' => ['nullable', 'integer', 'min:0'],
            'isactive' => ['required', 'boolean'],
        ]);

        $knowledgeTag->update($data);

        return redirect()->route('knowledge-tags.index')->with('success', 'Knowledge tag updated.');
    }

    public function destroy(KnowledgeTag $knowledgeTag): RedirectResponse
    {
        $knowledgeTag->delete();

        return redirect()->route('knowledge-tags.index')->with('success', 'Knowledge tag deleted.');
    }
}
