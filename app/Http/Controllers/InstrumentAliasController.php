<?php

namespace App\Http\Controllers;

use App\Models\Instrument;
use App\Models\InstrumentAlias;
use App\Models\KnowledgeItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InstrumentAliasController extends Controller
{
    public function storeForInstrument(Request $request, KnowledgeItem $knowledgeItem, Instrument $instrument): RedirectResponse
    {
        $this->ensureInstrumentBelongsToKnowledgeItem($knowledgeItem, $instrument);

        $data = $this->validateAlias($request, $instrument);

        $instrument->aliases()->create($data);

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItem,
                'tab' => 'investments',
            ])
            ->with('success', 'Instrument alias added.');
    }

    public function updateForInstrument(Request $request, KnowledgeItem $knowledgeItem, Instrument $instrument, InstrumentAlias $alias): RedirectResponse
    {
        $this->ensureInstrumentBelongsToKnowledgeItem($knowledgeItem, $instrument);
        $this->ensureAliasBelongsToInstrument($instrument, $alias);

        $data = $this->validateAlias($request, $instrument, $alias);

        $alias->update($data);

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItem,
                'tab' => 'investments',
            ])
            ->with('success', 'Instrument alias updated.');
    }

    public function destroyForInstrument(KnowledgeItem $knowledgeItem, Instrument $instrument, InstrumentAlias $alias): RedirectResponse
    {
        $this->ensureInstrumentBelongsToKnowledgeItem($knowledgeItem, $instrument);
        $this->ensureAliasBelongsToInstrument($instrument, $alias);

        $alias->delete();

        return redirect()
            ->route('knowledge.items.edit', [
                'knowledgeItem' => $knowledgeItem,
                'tab' => 'investments',
            ])
            ->with('success', 'Instrument alias deleted.');
    }

    protected function validateAlias(Request $request, Instrument $instrument, ?InstrumentAlias $alias = null): array
    {
        return $request->validate([
            'aliasvalue' => [
                'required',
                'string',
                'max:100',
                Rule::unique('instrumentaliases', 'aliasvalue')
                    ->where(fn ($query) => $query->where('instrumentid', $instrument->id))
                    ->ignore($alias?->id),
            ],
            'aliastype' => ['nullable', 'string', 'max:50'],
        ]);
    }

    protected function ensureInstrumentBelongsToKnowledgeItem(KnowledgeItem $knowledgeItem, Instrument $instrument): void
    {
        if ((int) $instrument->knowledgeitemid !== (int) $knowledgeItem->id) {
            abort(404);
        }
    }

    protected function ensureAliasBelongsToInstrument(Instrument $instrument, InstrumentAlias $alias): void
    {
        if ((int) $alias->instrumentid !== (int) $instrument->id) {
            abort(404);
        }
    }
}