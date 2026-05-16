<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeItem;
use App\Models\KnowledgeReviewLog;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KnowledgeItemReviewLogController extends Controller
{
    public function store(Request $request, KnowledgeItem $knowledgeItem): RedirectResponse
    {
        $validated = $request->validate([
            'reviewdate' => ['required', 'date'],
            'reviewtype' => ['required', 'string', Rule::in(KnowledgeReviewLog::typeValues())],
            'outcome' => ['nullable', 'string', 'max:50'],
            'summary' => ['nullable', 'string'],
            'nextreviewdate' => ['nullable', 'date'],
        ]);

        $knowledgeItem->reviewLogs()->create($validated);

        if (!empty($validated['nextreviewdate'])) {
            $knowledgeItem->update([
                'nextreviewdate' => $validated['nextreviewdate'],
            ]);
        }

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItem,
                'tab' => 'review-log',
            ]) ->with('success', 'Review log added successfully.');
    }

    public function update(Request $request, KnowledgeItem $knowledgeItem, KnowledgeReviewLog $knowledgeReviewLog): RedirectResponse
    {
        $validated = $request->validate([
            'reviewdate' => ['required', 'date'],
            'reviewtype' => ['required', 'string', Rule::in(KnowledgeReviewLog::typeValues())],
            'outcome' => ['nullable', 'string', 'max:50'],
            'summary' => ['nullable', 'string'],
            'nextreviewdate' => ['nullable', 'date'],
        ]);

        $knowledgeReviewLog->update($validated);

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItem,
                'tab' => 'review-log',])
            ->with('success', 'Review log updated successfully.');
    }

    public function destroy(KnowledgeItem $knowledgeItem, KnowledgeReviewLog $knowledgeReviewLog): RedirectResponse
    {
        $knowledgeReviewLog->delete();

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItem,
                'tab' => 'review-log',])
            ->with('success', 'Review log deleted successfully.');
    }
}