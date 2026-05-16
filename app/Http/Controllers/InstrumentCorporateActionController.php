<?php

namespace App\Http\Controllers;

use App\Models\Instrument;
use App\Models\InstrumentCorporateAction;
use App\Models\KnowledgeItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class InstrumentCorporateActionController extends Controller
{
    public function storeForInstrument(Request $request, KnowledgeItem $knowledgeItem, Instrument $instrument): RedirectResponse
    {
        $this->ensureInstrumentBelongsToKnowledgeItem($knowledgeItem, $instrument);

        $data = $this->validateCorporateAction($request);

        $instrument->corporateActions()->create($data);

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItem,
                'tab' => 'investments',
            ])
            ->with('success', 'Corporate action added.');
    }

    public function updateForInstrument(
        Request $request,
        KnowledgeItem $knowledgeItem,
        Instrument $instrument,
        InstrumentCorporateAction $corporateAction
    ): RedirectResponse {
        $this->ensureInstrumentBelongsToKnowledgeItem($knowledgeItem, $instrument);
        $this->ensureCorporateActionBelongsToInstrument($instrument, $corporateAction);

        $data = $this->validateCorporateAction($request);

        $corporateAction->update($data);

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItem,
                'tab' => 'investments',
            ])
            ->with('success', 'Corporate action updated.');
    }

    public function destroyForInstrument(
        KnowledgeItem $knowledgeItem,
        Instrument $instrument,
        InstrumentCorporateAction $corporateAction
    ): RedirectResponse {
        $this->ensureInstrumentBelongsToKnowledgeItem($knowledgeItem, $instrument);
        $this->ensureCorporateActionBelongsToInstrument($instrument, $corporateAction);

        $corporateAction->delete();

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItem,
                'tab' => 'investments',
            ])
            ->with('success', 'Corporate action deleted.');
    }



    protected function ensureInstrumentBelongsToKnowledgeItem(KnowledgeItem $knowledgeItem, Instrument $instrument): void
    {
        if ((int) $instrument->knowledgeitemid !== (int) $knowledgeItem->id) {
            abort(404);
        }
    }

    protected function ensureCorporateActionBelongsToInstrument(
        Instrument $instrument,
        InstrumentCorporateAction $corporateAction
    ): void {
        if ((int) $corporateAction->instrumentid !== (int) $instrument->id) {
            abort(404);
        }
    }

        public static function actionTypeOptions(): array
    {
        return [
            'split' => 'Split',
            'consolidation' => 'Consolidation',
            'rename' => 'Rename',
            'ticker-change' => 'Ticker Change',
            'isin-change' => 'ISIN Change',
            'merger' => 'Merger',
            'demerger' => 'Demerger',
            'scheme-of-arrangement' => 'Scheme of Arrangement',
            'capital-return' => 'Capital Return',
            'spin-off' => 'Spin-off',
            'delisting' => 'Delisting',
            'reinstatement' => 'Reinstatement',
            'other' => 'Other',
        ];
    }

    protected function validateCorporateAction(Request $request): array
    {
        return $request->validate([
            'actiondate' => ['required', 'date'],
            'actiontype' => ['required', 'string', Rule::in(array_keys(self::actionTypeOptions()))],
            'ratiofrom' => ['nullable', 'numeric', 'min:0'],
            'ratioto' => ['nullable', 'numeric', 'min:0'],
            'oldvalue' => ['nullable', 'string', 'max:255'],
            'newvalue' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'sourceid' => ['nullable', 'integer', 'exists:knowledgesources,id'],
        ]);
    }
}