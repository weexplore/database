<?php

namespace App\Http\Controllers;

use App\Models\Instrument;
use App\Models\InstrumentTransaction;
use App\Models\KnowledgeItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InstrumentTransactionController extends Controller
{
    public static function transactionTypeOptions(): array
    {
        return [
            'buy' => 'Buy',
            'sell' => 'Sell',
            'dividend' => 'Dividend',
            'drp' => 'DRP Reinvestment',
            'transfer-in' => 'Transfer In',
            'transfer-out' => 'Transfer Out',
            'switch-in' => 'Switch In',
            'switch-out' => 'Switch Out',
            'bonus-issue' => 'Bonus Issue',
            'return-of-capital' => 'Return of Capital',
            'other' => 'Other',
        ];
    }

    public function storeForInstrument(Request $request, KnowledgeItem $knowledgeItem, Instrument $instrument): RedirectResponse
    {
        $this->ensureInstrumentBelongsToKnowledgeItem($knowledgeItem, $instrument);

        $data = $this->validateTransaction($request);

        $instrument->transactions()->create($data);

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItem,
                'tab' => 'investments',
            ])
            ->with('success', 'Transaction added.');
    }

    public function updateForInstrument(
        Request $request,
        KnowledgeItem $knowledgeItem,
        Instrument $instrument,
        InstrumentTransaction $transaction
    ): RedirectResponse {
        $this->ensureInstrumentBelongsToKnowledgeItem($knowledgeItem, $instrument);
        $this->ensureTransactionBelongsToInstrument($instrument, $transaction);

        $data = $this->validateTransaction($request);

        $transaction->update($data);

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItem,
                'tab' => 'investments',
            ])
            ->with('success', 'Transaction updated.');
    }

    public function destroyForInstrument(
        KnowledgeItem $knowledgeItem,
        Instrument $instrument,
        InstrumentTransaction $transaction
    ): RedirectResponse {
        $this->ensureInstrumentBelongsToKnowledgeItem($knowledgeItem, $instrument);
        $this->ensureTransactionBelongsToInstrument($instrument, $transaction);

        $transaction->delete();

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItem,
                'tab' => 'investments',
            ])
            ->with('success', 'Transaction deleted.');
    }

    protected function validateTransaction(Request $request): array
    {
        return $request->validate([
            'portfolioid' => ['required', 'integer', 'exists:portfolios,id'],
            'transactiondate' => ['required', 'date'],
            'settlementdate' => ['nullable', 'date'],
            'transactiontype' => ['required', 'string', Rule::in(array_keys(self::transactionTypeOptions()))],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'priceperunit' => ['nullable', 'numeric', 'min:0'],
            'grossamount' => ['nullable', 'numeric'],
            'brokerage' => ['nullable', 'numeric'],
            'taxesandfees' => ['nullable', 'numeric'],
            'netcashamount' => ['nullable', 'numeric'],
            'currencycode' => ['nullable', 'string', 'size:3'],
            'fxrateaud' => ['nullable', 'numeric', 'min:0'],
            'externalreference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    protected function ensureInstrumentBelongsToKnowledgeItem(KnowledgeItem $knowledgeItem, Instrument $instrument): void
    {
        if ((int) $instrument->knowledgeitemid !== (int) $knowledgeItem->id) {
            abort(404);
        }
    }

    protected function ensureTransactionBelongsToInstrument(Instrument $instrument, InstrumentTransaction $transaction): void
    {
        if ((int) $transaction->instrumentid !== (int) $instrument->id) {
            abort(404);
        }
    }
}