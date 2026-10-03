<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeItem;
use App\Models\KnowledgeReviewLog;
use Illuminate\Http\Request;
use Illuminate\Mail\Markdown;
use Illuminate\Validation\Rule;

class KnowledgeItemReviewLogController extends Controller
{
    public function store(
        Request $request,
        KnowledgeItem $knowledgeItem
    ) {
        $validated = $this->validatedData($request);

        $reviewLog = $knowledgeItem
            ->reviewLogs()
            ->create($validated);

        return response()->json([
            'reviewLog' => $this->reviewLogPayload(
                $reviewLog->fresh()
            ),
            'knowledgeItemNextReviewDate' => $knowledgeItem
                ->fresh()
                ->nextreviewdate
                ?->format('Y-m-d'),
        ], 201);
    }

    public function update(
        Request $request,
        KnowledgeItem $knowledgeItem,
        KnowledgeReviewLog $knowledgeReviewLog
    ) {
        $this->ensureOwnership(
            $knowledgeItem,
            $knowledgeReviewLog
        );

        $validated = $this->validatedData($request);

        $knowledgeReviewLog->update($validated);

        return response()->json([
            'reviewLog' => $this->reviewLogPayload(
                $knowledgeReviewLog->fresh()
            ),
            'knowledgeItemNextReviewDate' => $knowledgeItem
                ->fresh()
                ->nextreviewdate
                ?->format('Y-m-d'),
        ]);
    }

    public function complete(
        KnowledgeItem $knowledgeItem,
        KnowledgeReviewLog $knowledgeReviewLog
    ) {
        $this->ensureOwnership(
            $knowledgeItem,
            $knowledgeReviewLog
        );

        $knowledgeReviewLog->update([
            'nextreviewdate' => null,
        ]);

        return response()->json([
            'reviewLog' => $this->reviewLogPayload(
                $knowledgeReviewLog->fresh()
            ),
            'knowledgeItemNextReviewDate' => $knowledgeItem
                ->fresh()
                ->nextreviewdate
                ?->format('Y-m-d'),
        ]);
    }

    public function destroy(
        KnowledgeItem $knowledgeItem,
        KnowledgeReviewLog $knowledgeReviewLog
    ) {
        $this->ensureOwnership(
            $knowledgeItem,
            $knowledgeReviewLog
        );

        $knowledgeReviewLog->delete();

        return response()->noContent();
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'reviewdate' => [
                'required',
                'date',
            ],
            'reviewtype' => [
                'required',
                'string',
                Rule::in(KnowledgeReviewLog::typeValues()),
            ],
            'outcome' => [
                'nullable',
                'string',
                'max:50',
            ],
            'summary' => [
                'nullable',
                'string',
            ],
            'nextreviewdate' => [
                'nullable',
                'date',
            ],
            'isactive' => [
                'sometimes',
                'boolean',
            ],
        ]);
    }

    private function ensureOwnership(
        KnowledgeItem $knowledgeItem,
        KnowledgeReviewLog $knowledgeReviewLog
    ): void {
        abort_unless(
            (int) $knowledgeReviewLog->knowledgeitemid
                === (int) $knowledgeItem->id,
            404
        );
    }

    private function reviewLogPayload(
        KnowledgeReviewLog $reviewLog
    ): array {
        $reviewTypeOptions =
            KnowledgeReviewLog::typeOptions();

        return [
            'id' => $reviewLog->id,
            'reviewdate' => $reviewLog
                ->reviewdate
                ?->format('Y-m-d'),
            'reviewdate_display' => $reviewLog
                ->reviewdate
                ?->format('d M Y'),
            'reviewtype' => $reviewLog->reviewtype,
            'reviewtype_label' =>
                $reviewTypeOptions[$reviewLog->reviewtype]
                ?? $reviewLog->reviewtype
                ?? 'Review',
            'outcome' => $reviewLog->outcome,
            'summary' => $reviewLog->summary ?? '',
            'summary_html' => app(Markdown::class)
                ->parse($reviewLog->summary ?? '')
                ->toHtml(),
            'nextreviewdate' => $reviewLog
                ->nextreviewdate
                ?->format('Y-m-d'),
            'nextreviewdate_display' => $reviewLog
                ->nextreviewdate
                ?->format('d M Y'),
            'isactive' => (bool) $reviewLog->isactive,
        ];
    }
}