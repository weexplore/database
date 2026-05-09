<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StateController extends Controller
{
    public function index(Request $request)
    {
        $countries = Country::orderBy('countryname')->get();

        $query = State::query()
            ->with('country');

        if ($request->filled('countryid')) {
            $query->where('countryid', (int) $request->countryid);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('statecode', 'like', '%' . $search . '%')
                    ->orWhere('statename', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('isactive', (int) $request->status);
        }

        $states = $query
            ->orderByRaw('COALESCE(sortorder, 999999)')
            ->orderBy('statename')
            ->get();

        return view('states.index', compact('states', 'countries'));
    }

    public function bulkSave(Request $request)
    {
        $validated = $request->validate([
            'existing' => ['nullable', 'array'],
            'existing.*.countryid' => ['required', 'integer', 'exists:countries,id'],
            'existing.*.statecode' => ['required', 'string', 'max:10'],
            'existing.*.statename' => ['required', 'string', 'max:100'],
            'existing.*.sortorder' => ['nullable', 'integer', 'min:0'],
            'existing.*.isactive' => ['nullable', 'boolean'],

            'new' => ['nullable', 'array'],
            'new.countryid' => ['nullable', 'integer', 'exists:countries,id'],
            'new.statecode' => ['nullable', 'string', 'max:10'],
            'new.statename' => ['nullable', 'string', 'max:100'],
            'new.sortorder' => ['nullable', 'integer', 'min:0'],
            'new.isactive' => ['nullable', 'boolean'],

            'countryid' => ['nullable', 'integer', 'exists:countries,id'],
            'search' => ['nullable', 'string'],
            'status' => ['nullable', 'in:0,1'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['existing'] ?? [] as $stateId => $row) {
                $statecode = strtoupper(trim((string) ($row['statecode'] ?? '')));
                $statename = trim((string) ($row['statename'] ?? ''));

                if ($statecode === '') {
                    throw ValidationException::withMessages([
                        "existing.$stateId.statecode" => 'State code is required.',
                    ]);
                }

                $state = State::findOrFail($stateId);

                $state->update([
                    'countryid' => (int) $row['countryid'],
                    'statecode' => $statecode,
                    'statename' => $statename,
                    'sortorder' => $row['sortorder'] ?? null,
                    'isactive' => (bool) ($row['isactive'] ?? false),
                ]);
            }

            $new = $validated['new'] ?? [];
            $newCountryId = $new['countryid'] ?? null;
            $newCode = strtoupper(trim((string) ($new['statecode'] ?? '')));
            $newName = trim((string) ($new['statename'] ?? ''));
            $hasNewState = $newCountryId || $newCode !== '' || $newName !== '';

            if ($hasNewState) {
                if (!$newCountryId || $newCode === '' || $newName === '') {
                    throw ValidationException::withMessages([
                        'new.countryid' => 'Country, code, and name are required for a new state.',
                    ]);
                }

                State::create([
                    'countryid' => (int) $newCountryId,
                    'statecode' => $newCode,
                    'statename' => $newName,
                    'sortorder' => $new['sortorder'] ?? null,
                    'isactive' => (bool) ($new['isactive'] ?? false),
                ]);
            }
        });

        return redirect()
            ->route('states.index', [
                'countryid' => $request->input('countryid'),
                'search' => $request->input('search'),
                'status' => $request->input('status'),
            ])
            ->with('success', 'States saved successfully.');
    }

    public function destroy(Request $request, State $state)
    {
        try {
            $state->delete();

            return redirect()
                ->route('states.index', [
                    'countryid' => $request->input('countryid'),
                    'search' => $request->input('search'),
                    'status' => $request->input('status'),
                ])
                ->with('success', 'State deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('states.index', [
                    'countryid' => $request->input('countryid'),
                    'search' => $request->input('search'),
                    'status' => $request->input('status'),
                ])
                ->with('error', 'This state is in use and cannot be deleted.');
        }
    }
}