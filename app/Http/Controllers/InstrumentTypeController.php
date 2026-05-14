<?php

namespace App\Http\Controllers;

use App\Models\InstrumentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InstrumentTypeController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'active' => (string) $request->query('active', ''),
            'income' => (string) $request->query('income', ''),
        ];

        $query = InstrumentType::query();

        if ($filters['search'] !== '') {
            $query->where(function ($q) use ($filters) {
                $q->where('typecode', 'like', "%{$filters['search']}%")
                    ->orWhere('typename', 'like', "%{$filters['search']}%")
                    ->orWhere('notes', 'like', "%{$filters['search']}%");
            });
        }

        if ($filters['active'] !== '') {
            $query->where('isactive', (int) $filters['active']);
        }

        if ($filters['income'] === 'dividends') {
            $query->where('hasdividends', 1);
        } elseif ($filters['income'] === 'distributions') {
            $query->where('hasdistributions', 1);
        } elseif ($filters['income'] === 'both') {
            $query->where('hasdividends', 1)
                ->where('hasdistributions', 1);
        } elseif ($filters['income'] === 'none') {
            $query->where('hasdividends', 0)
                ->where('hasdistributions', 0);
        }

        return view('instrument-types.index', [
            'pageTitle' => 'Instrument Types',
            'rows' => $query->orderBy('typename')->get(),
            'filters' => $filters,
        ]);
    }

    public function bulkSave(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'existing' => ['nullable', 'array'],
            'existing.*.typecode' => ['required', 'string', 'max:30'],
            'existing.*.typename' => ['required', 'string', 'max:100'],
            'existing.*.hasunits' => ['nullable', 'boolean'],
            'existing.*.hasdividends' => ['nullable', 'boolean'],
            'existing.*.hasdistributions' => ['nullable', 'boolean'],
            'existing.*.notes' => ['nullable', 'string'],
            'existing.*.isactive' => ['nullable', 'boolean'],

            'new' => ['nullable', 'array'],
            'new.typecode' => ['nullable', 'string', 'max:30'],
            'new.typename' => ['nullable', 'string', 'max:100'],
            'new.hasunits' => ['nullable', 'boolean'],
            'new.hasdividends' => ['nullable', 'boolean'],
            'new.hasdistributions' => ['nullable', 'boolean'],
            'new.notes' => ['nullable', 'string'],
            'new.isactive' => ['nullable', 'boolean'],

            'search' => ['nullable', 'string'],
            'active' => ['nullable', 'in:0,1'],
            'income' => ['nullable', 'in:dividends,distributions,both,none'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['existing'] ?? [] as $id => $row) {
                $typeCode = strtolower(trim((string) ($row['typecode'] ?? '')));
                $typeName = trim((string) ($row['typename'] ?? ''));
                $notes = trim((string) ($row['notes'] ?? ''));

                if ($typeCode === '') {
                    throw ValidationException::withMessages([
                        "existing.$id.typecode" => 'Type code is required.',
                    ]);
                }

                if ($typeName === '') {
                    throw ValidationException::withMessages([
                        "existing.$id.typename" => 'Type name is required.',
                    ]);
                }

                $duplicateCode = InstrumentType::query()
                    ->whereRaw('LOWER(typecode) = ?', [$typeCode])
                    ->where('id', '!=', $id)
                    ->exists();

                if ($duplicateCode) {
                    throw ValidationException::withMessages([
                        "existing.$id.typecode" => 'Type code must be unique.',
                    ]);
                }

                $instrumentType = InstrumentType::findOrFail($id);

                $instrumentType->update([
                    'typecode' => $typeCode,
                    'typename' => $typeName,
                    'hasunits' => (bool) ($row['hasunits'] ?? false),
                    'hasdividends' => (bool) ($row['hasdividends'] ?? false),
                    'hasdistributions' => (bool) ($row['hasdistributions'] ?? false),
                    'notes' => $notes !== '' ? $notes : null,
                    'isactive' => (bool) ($row['isactive'] ?? false),
                ]);
            }

            $new = $validated['new'] ?? [];

            $newCode = strtolower(trim((string) ($new['typecode'] ?? '')));
            $newName = trim((string) ($new['typename'] ?? ''));
            $newNotes = trim((string) ($new['notes'] ?? ''));

            $hasNewRow = $newCode !== '' || $newName !== '';

            if ($hasNewRow) {
                if ($newCode === '') {
                    throw ValidationException::withMessages([
                        'new.typecode' => 'Type code is required for a new instrument type.',
                    ]);
                }

                if ($newName === '') {
                    throw ValidationException::withMessages([
                        'new.typename' => 'Type name is required for a new instrument type.',
                    ]);
                }

                $duplicateCode = InstrumentType::query()
                    ->whereRaw('LOWER(typecode) = ?', [$newCode])
                    ->exists();

                if ($duplicateCode) {
                    throw ValidationException::withMessages([
                        'new.typecode' => 'Type code must be unique.',
                    ]);
                }

                InstrumentType::create([
                    'typecode' => $newCode,
                    'typename' => $newName,
                    'hasunits' => (bool) ($new['hasunits'] ?? false),
                    'hasdividends' => (bool) ($new['hasdividends'] ?? false),
                    'hasdistributions' => (bool) ($new['hasdistributions'] ?? false),
                    'notes' => $newNotes !== '' ? $newNotes : null,
                    'isactive' => (bool) ($new['isactive'] ?? false),
                ]);
            }
        });

        return redirect()
            ->route('instrument-types.index', [
                'search' => $request->input('search'),
                'active' => $request->input('active'),
                'income' => $request->input('income'),
            ])
            ->with('success', 'Instrument types saved successfully.');
    }

    public function destroy(InstrumentType $instrumentType): RedirectResponse
    {
        $instrumentType->delete();

        return redirect()
            ->route('instrument-types.index', [
                'search' => request('search'),
                'active' => request('active'),
                'income' => request('income'),
            ])
            ->with('success', 'Instrument type deleted.');
    }
}