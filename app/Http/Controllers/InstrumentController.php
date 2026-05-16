<?php

namespace App\Http\Controllers;

use App\Models\Instrument;
use App\Models\KnowledgeItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InstrumentController extends Controller
{
    public function storeForKnowledgeItem(Request $request, KnowledgeItem $knowledgeItem): RedirectResponse
    {
        $data = $this->validateInstrument($request);

        if ($knowledgeItem->instrument()->exists()) {
            return redirect()
                ->route('knowledge.items.edit', [
                    'knowledgeItem' => $knowledgeItem,
                    'tab' => 'investments',
                ])
                ->with('error', 'This knowledge item already has an instrument profile.');
        }

        $knowledgeItem->instrument()->create($data);

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItem,
                'tab' => 'investments',
            ])
            ->with('success', 'Instrument profile created.');
    }

    public function updateForKnowledgeItem(Request $request, KnowledgeItem $knowledgeItem, Instrument $instrument): RedirectResponse
    {
        if ((int) $instrument->knowledgeitemid !== (int) $knowledgeItem->id) {
            abort(404);
        }

        $data = $this->validateInstrument($request, $instrument);

        $instrument->update($data);

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItem,
                'tab' => 'investments',
            ])
            ->with('success', 'Instrument profile updated.');
    }

    protected function validateInstrument(Request $request, ?Instrument $instrument = null): array
    {
        return $request->validate([
            'instrumenttypeid' => ['nullable', 'integer', 'exists:instrumenttypes,id'],
            'exchangeid' => ['nullable', 'integer', 'exists:exchanges,id'],
            'symbol' => [
                'required',
                'string',
                'max:30',
                Rule::unique('instruments', 'symbol')->ignore($instrument?->id),
            ],
            'instrumentname' => ['required', 'string', 'max:255'],
            'isin' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('instruments', 'isin')->ignore($instrument?->id),
            ],
            'apiric' => ['nullable', 'string', 'max:50'],
            'currencycode' => ['nullable', 'string', 'size:3'],
            'fundmanager' => ['nullable', 'string', 'max:150'],
            'sector' => ['nullable', 'string', 'max:100'],
            'industry' => ['nullable', 'string', 'max:100'],
            'domicilecountrycode' => ['nullable', 'string', 'size:2'],
            'status' => ['nullable', 'string', 'max:30'],
            'website' => ['nullable', 'url', 'max:255'],
            'notes' => ['nullable', 'string'],
            'isactive' => ['required', 'boolean'],
        ]);
    }
}