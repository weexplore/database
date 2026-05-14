<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeDomain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KnowledgeDomainController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'active' => (string) $request->query('active', ''),
        ];

        $query = KnowledgeDomain::query();

        if ($filters['search'] !== '') {
            $query->where(function ($q) use ($filters) {
                $q->where('domaincode', 'like', "%{$filters['search']}%")
                  ->orWhere('domainname', 'like', "%{$filters['search']}%");
            });
        }

        if ($filters['active'] !== '') {
            $query->where('isactive', (int) $filters['active']);
        }

        return view('knowledge-domains.index', [
            'pageTitle' => 'Knowledge Domains',
            'rows' => $query->orderByRaw('COALESCE(sortorder, 999999), domainname')->get(),
            'filters' => $filters,
        ]);
    }

    public function bulkSave(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'existing' => ['nullable', 'array'],
            'existing.*.domaincode' => ['required', 'string', 'max:50'],
            'existing.*.domainname' => ['required', 'string', 'max:150'],
            'existing.*.description' => ['nullable', 'string'],
            'existing.*.sortorder' => ['nullable', 'integer', 'min:0'],
            'existing.*.isactive' => ['nullable', 'boolean'],

            'new' => ['nullable', 'array'],
            'new.domaincode' => ['nullable', 'string', 'max:50'],
            'new.domainname' => ['nullable', 'string', 'max:150'],
            'new.description' => ['nullable', 'string'],
            'new.sortorder' => ['nullable', 'integer', 'min:0'],
            'new.isactive' => ['nullable', 'boolean'],

            'search' => ['nullable', 'string'],
            'active' => ['nullable', 'in:0,1'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['existing'] ?? [] as $domainId => $row) {
                $domaincode = strtolower(trim((string) ($row['domaincode'] ?? '')));
                $domainname = trim((string) ($row['domainname'] ?? ''));
                $description = isset($row['description']) ? trim((string) $row['description']) : null;

                if ($domaincode === '') {
                    throw ValidationException::withMessages([
                        "existing.$domainId.domaincode" => 'Domain code is required.',
                    ]);
                }

                if ($domainname === '') {
                    throw ValidationException::withMessages([
                        "existing.$domainId.domainname" => 'Domain name is required.',
                    ]);
                }

                $duplicateCode = KnowledgeDomain::query()
                    ->where('domaincode', $domaincode)
                    ->where('id', '!=', $domainId)
                    ->exists();

                if ($duplicateCode) {
                    throw ValidationException::withMessages([
                        "existing.$domainId.domaincode" => 'Domain code must be unique.',
                    ]);
                }

                $duplicateName = KnowledgeDomain::query()
                    ->where('domainname', $domainname)
                    ->where('id', '!=', $domainId)
                    ->exists();

                if ($duplicateName) {
                    throw ValidationException::withMessages([
                        "existing.$domainId.domainname" => 'Domain name must be unique.',
                    ]);
                }

                $knowledgeDomain = KnowledgeDomain::findOrFail($domainId);

                $knowledgeDomain->update([
                    'domaincode' => $domaincode,
                    'domainname' => $domainname,
                    'description' => $description !== '' ? $description : null,
                    'sortorder' => $row['sortorder'] ?? null,
                    'isactive' => (bool) ($row['isactive'] ?? false),
                ]);
            }

            $new = $validated['new'] ?? [];
            $newCode = strtolower(trim((string) ($new['domaincode'] ?? '')));
            $newName = trim((string) ($new['domainname'] ?? ''));
            $newDescription = isset($new['description']) ? trim((string) $new['description']) : null;

            $hasNewDomain = $newCode !== '' || $newName !== '' || ($newDescription !== null && $newDescription !== '');

            if ($hasNewDomain) {
                if ($newCode === '' || $newName === '') {
                    throw ValidationException::withMessages([
                        'new.domaincode' => 'Domain code is required for a new knowledge domain.',
                        'new.domainname' => 'Domain name is required for a new knowledge domain.',
                    ]);
                }

                $duplicateCode = KnowledgeDomain::query()
                    ->where('domaincode', $newCode)
                    ->exists();

                if ($duplicateCode) {
                    throw ValidationException::withMessages([
                        'new.domaincode' => 'Domain code must be unique.',
                    ]);
                }

                $duplicateName = KnowledgeDomain::query()
                    ->where('domainname', $newName)
                    ->exists();

                if ($duplicateName) {
                    throw ValidationException::withMessages([
                        'new.domainname' => 'Domain name must be unique.',
                    ]);
                }

                KnowledgeDomain::create([
                    'domaincode' => $newCode,
                    'domainname' => $newName,
                    'description' => $newDescription !== '' ? $newDescription : null,
                    'sortorder' => $new['sortorder'] ?? null,
                    'isactive' => (bool) ($new['isactive'] ?? false),
                ]);
            }
        });

        return redirect()
            ->route('knowledge-domains.index', [
                'search' => $request->input('search'),
                'active' => $request->input('active'),
            ])
            ->with('success', 'Knowledge domains saved successfully.');
    }

    public function destroy(KnowledgeDomain $knowledgeDomain): RedirectResponse
    {
        $knowledgeDomain->delete();

        return redirect()
            ->route('knowledge-domains.index', [
                'search' => request('search'),
                'active' => request('active'),
            ])
            ->with('success', 'Knowledge domain deleted.');
    }
}