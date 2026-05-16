<?php

namespace App\Http\Controllers;

use App\Models\Instrument;
use App\Models\InstrumentPriceObservation;
use App\Models\KnowledgeItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InstrumentPriceObservationController extends Controller
{
    public function storeForInstrument(Request $request, KnowledgeItem $knowledgeItem, Instrument $instrument): RedirectResponse
    {
        $this->ensureInstrumentBelongsToKnowledgeItem($knowledgeItem, $instrument);

        $data = $this->validateObservation($request, $instrument);

        $instrument->priceObservations()->create($data);

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItem,
                'tab' => 'investments',
            ])
            ->with('success', 'Price observation added.');
    }

    public function updateForInstrument(
        Request $request,
        KnowledgeItem $knowledgeItem,
        Instrument $instrument,
        InstrumentPriceObservation $priceObservation
    ): RedirectResponse {
        $this->ensureInstrumentBelongsToKnowledgeItem($knowledgeItem, $instrument);
        $this->ensureObservationBelongsToInstrument($instrument, $priceObservation);

        $data = $this->validateObservation($request, $instrument, $priceObservation);

        $priceObservation->update($data);

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItem,
                'tab' => 'investments',
            ])
            ->with('success', 'Price observation updated.');
    }

    public function destroyForInstrument(
        KnowledgeItem $knowledgeItem,
        Instrument $instrument,
        InstrumentPriceObservation $priceObservation
    ): RedirectResponse {
        $this->ensureInstrumentBelongsToKnowledgeItem($knowledgeItem, $instrument);
        $this->ensureObservationBelongsToInstrument($instrument, $priceObservation);

        $priceObservation->delete();

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItem,
                'tab' => 'investments',
            ])
            ->with('success', 'Price observation deleted.');
    }

    protected function validateObservation(
        Request $request,
        Instrument $instrument,
        ?InstrumentPriceObservation $priceObservation = null
    ): array {
        return $request->validate([
            'observedon' => [
                'required',
                'date',
                'before_or_equal:today',
                Rule::unique('instrumentpriceobservations', 'observedon')
                    ->where(fn ($query) => $query->where('instrumentid', $instrument->id))
                    ->ignore($priceObservation?->id),
            ],
            'priceopen' => ['nullable', 'numeric', 'min:0'],
            'pricehigh' => ['nullable', 'numeric', 'min:0'],
            'pricelow' => ['nullable', 'numeric', 'min:0'],
            'priceclose' => ['nullable', 'numeric', 'min:0'],
            'adjustedclose' => ['nullable', 'numeric', 'min:0'],
            'volume' => ['nullable', 'integer', 'min:0'],
            'currencycode' => ['nullable', 'string', 'size:3'],
            'pricesource' => ['nullable', 'string', 'max:100'],
            'observationnotes' => ['nullable', 'string'],
        ]);
    }

    protected function ensureInstrumentBelongsToKnowledgeItem(KnowledgeItem $knowledgeItem, Instrument $instrument): void
    {
        if ((int) $instrument->knowledgeitemid !== (int) $knowledgeItem->id) {
            abort(404);
        }
    }

    protected function ensureObservationBelongsToInstrument(Instrument $instrument, InstrumentPriceObservation $priceObservation): void
    {
        if ((int) $priceObservation->instrumentid !== (int) $instrument->id) {
            abort(404);
        }
    }
}