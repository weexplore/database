<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeItem;
use App\Models\KnowledgeNote;
use Illuminate\Http\Request;
use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KnowledgeItemNoteController extends Controller
{
    public function store(Request $request, KnowledgeItem $knowledgeItem)
    {
        $validated = $this->validatedData($request);

        $note = KnowledgeNote::create([
            'knowledgeitemid' => $knowledgeItem->getKey(),
            ...$this->noteAttributes($validated),
        ]);

        return response()->json([
            'note' => $this->notePayload($note->fresh()),
        ], 201);
    }

    public function update(
        Request $request,
        KnowledgeItem $knowledgeItem,
        KnowledgeNote $knowledgeNote
    ) {
        $this->ensureOwnership($knowledgeItem, $knowledgeNote);

        $validated = $this->validatedData($request);

        $knowledgeNote->update(
            $this->noteAttributes($validated)
        );

        return response()->json([
            'note' => $this->notePayload($knowledgeNote->fresh()),
        ]);
    }

    public function destroy(
        KnowledgeItem $knowledgeItem,
        KnowledgeNote $knowledgeNote
    ) {
        $this->ensureOwnership($knowledgeItem, $knowledgeNote);

        $knowledgeNote->delete();

        return response()->noContent();
    }

    public function reorder(Request $request, KnowledgeItem $knowledgeItem)
    {
        $validated = $request->validate([
            'note_order' => ['required', 'array'],
            'note_order.*' => ['required', 'integer', 'min:1'],
        ]);

        $noteOrder = $validated['note_order'];

        $noteIds = $knowledgeItem->notes()
            ->whereIn('id', array_keys($noteOrder))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        DB::transaction(function () use ($knowledgeItem, $noteOrder, $noteIds) {
            foreach ($noteOrder as $noteId => $sortOrder) {
                $noteId = (int) $noteId;

                if (!in_array($noteId, $noteIds, true)) {
                    continue;
                }

                $knowledgeItem->notes()
                    ->where('id', $noteId)
                    ->update([
                        'sortorder' => (int) $sortOrder,
                    ]);
            }
        });

        return response()->noContent();
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'notetype' => [
                'required',
                'string',
                Rule::in(KnowledgeNote::typeValues()),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'notecontent' => ['required', 'string'],
            'stance' => ['nullable', 'string', 'max:30'],
            'convictionlevel' => ['nullable', 'integer', 'min:1', 'max:5'],
            'reviewdate' => ['nullable', 'date'],
            'isprivate' => ['nullable', 'boolean'],
            'sortorder' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function noteAttributes(array $validated): array
    {
        return [
            'notetype' => $validated['notetype'],
            'title' => $validated['title'] ?? null,
            'notecontent' => $validated['notecontent'],
            'stance' => $validated['stance'] ?? null,
            'convictionlevel' => $validated['convictionlevel'] ?? null,
            'reviewdate' => $validated['reviewdate'] ?? null,
            'isprivate' => (bool) ($validated['isprivate'] ?? false),
            'sortorder' => $validated['sortorder'] ?? 0,
        ];
    }

    private function ensureOwnership(
        KnowledgeItem $knowledgeItem,
        KnowledgeNote $knowledgeNote
    ): void {
        abort_unless(
            (int) $knowledgeNote->knowledgeitemid === (int) $knowledgeItem->id,
            404
        );
    }

    private function notePayload(KnowledgeNote $note): array
    {
        $noteTypeOptions = KnowledgeNote::typeOptions();

        return [
            'id' => $note->id,
            'notetype' => $note->notetype,
            'notetype_label' => $noteTypeOptions[$note->notetype]
                ?? $note->notetype
                ?? 'Note',
            'title' => $note->title ?? '',
            'notecontent' => $note->notecontent ?? '',
            'notecontent_html' => app(Markdown::class)
                ->parse($note->notecontent ?? '')
                ->toHtml(),
            'stance' => $note->stance ?? '',
            'convictionlevel' => $note->convictionlevel,
            'reviewdate' => $note->reviewdate?->format('Y-m-d'),
            'reviewdate_display' => $note->reviewdate?->format('d M Y'),
            'isprivate' => (bool) $note->isprivate,
            'sortorder' => (int) ($note->sortorder ?? 0),
        ];
    }
}