<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeItem;
use App\Models\KnowledgeSource;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KnowledgeItemSourceController extends Controller
{
    public function store(Request $request, KnowledgeItem $knowledgeItem): RedirectResponse
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
}