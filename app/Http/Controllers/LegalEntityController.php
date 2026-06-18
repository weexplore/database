<?php

namespace App\Http\Controllers;

use App\Models\LegalEntity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LegalEntityController extends Controller
{
    public function index(Request $request): View
    {
        $legalEntities = LegalEntity::query()
            ->when($request->filled('isactive'), fn ($query) => $query->where('isactive', $request->boolean('isactive')))
            ->orderBy('sortorder')
            ->orderBy('entityname')
            ->paginate(25)
            ->withQueryString();

        return view('legalentities.index', compact('legalEntities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateLegalEntity($request);

        LegalEntity::create($validated);

        return redirect()
            ->route('legal-entities.index')
            ->with('success', 'Legal entity added successfully.');
    }

    public function edit(LegalEntity $legalEntity): View
    {
        return view('legalentities.edit', compact('legalEntity'));
    }

    public function update(Request $request, LegalEntity $legalEntity): RedirectResponse
    {
        $validated = $this->validateLegalEntity($request, $legalEntity);

        $legalEntity->update($validated);

        return redirect()
            ->route('legal-entities.edit', $legalEntity)
            ->with('success', 'Legal entity updated successfully.');
    }

    public function destroy(LegalEntity $legalEntity): RedirectResponse
    {
        $legalEntity->delete();

        return redirect()
            ->route('legal-entities.index')
            ->with('success', 'Legal entity deleted successfully.');
    }

    private function validateLegalEntity(Request $request, ?LegalEntity $legalEntity = null): array
    {
        return $request->validate([
            'entitycode' => [
                'required',
                'string',
                'max:30',
                Rule::unique('legal_entities', 'entitycode')->ignore($legalEntity),
            ],
            'entityname' => [
                'required',
                'string',
                'max:150',
                Rule::unique('legal_entities', 'entityname')->ignore($legalEntity),
            ],
            'entitytype' => ['required', 'string', 'max:50'],
            'abn' => ['nullable', 'string', 'max:20'],
            'acn' => ['nullable', 'string', 'max:20'],
            'sortorder' => ['nullable', 'integer', 'min:0'],
            'isactive' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
