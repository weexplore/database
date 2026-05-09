<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CountryController extends Controller
{
    public function index(Request $request)
    {
        $countries = Country::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $countries->where(function ($query) use ($search) {
                $query->where('countrycode', 'like', '%' . $search . '%')
                    ->orWhere('countryname', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status')) {
            $countries->where('isactive', (int) $request->status);
        }

        $countries = $countries
            ->orderByRaw('COALESCE(sortorder, 999999)')
            ->orderBy('countryname')
            ->get();

        return view('countries.index', compact('countries'));
    }

    public function bulkSave(Request $request)
    {
        $validated = $request->validate([
            'existing' => ['nullable', 'array'],
            'existing.*.countrycode' => ['required', 'string', 'max:3'],
            'existing.*.countryname' => ['required', 'string', 'max:100'],
            'existing.*.sortorder' => ['nullable', 'integer', 'min:0'],
            'existing.*.isactive' => ['nullable', 'boolean'],

            'new' => ['nullable', 'array'],
            'new.countrycode' => ['nullable', 'string', 'max:3'],
            'new.countryname' => ['nullable', 'string', 'max:100'],
            'new.sortorder' => ['nullable', 'integer', 'min:0'],
            'new.isactive' => ['nullable', 'boolean'],

            'search' => ['nullable', 'string'],
            'status' => ['nullable', 'in:0,1'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['existing'] ?? [] as $countryId => $row) {
                $countrycode = strtoupper(trim((string) ($row['countrycode'] ?? '')));
                $countryname = trim((string) ($row['countryname'] ?? ''));

                if ($countrycode === '' || strlen($countrycode) > 3) {
                    throw ValidationException::withMessages([
                        "existing.$countryId.countrycode" => 'Country code must be between 1 and 3 characters.',
                    ]);
                }

                $country = Country::findOrFail($countryId);

                $country->update([
                    'countrycode' => $countrycode,
                    'countryname' => $countryname,
                    'sortorder' => $row['sortorder'] ?? null,
                    'isactive' => (bool) ($row['isactive'] ?? false),
                ]);
            }

            $new = $validated['new'] ?? [];
            $newCode = strtoupper(trim((string) ($new['countrycode'] ?? '')));
            $newName = trim((string) ($new['countryname'] ?? ''));
            $hasNewCountry = $newCode !== '' || $newName !== '';

            if ($hasNewCountry) {
                if ($newCode === '' || $newName === '') {
                    throw ValidationException::withMessages([
                        'new.countrycode' => 'Country code is required for a new country.',
                        'new.countryname' => 'Country name is required for a new country.',
                    ]);
                }

                if (strlen($newCode) > 3) {
                    throw ValidationException::withMessages([
                        'new.countrycode' => 'Country code must be between 1 and 3 characters.',
                    ]);
                }

                Country::create([
                    'countrycode' => $newCode,
                    'countryname' => $newName,
                    'sortorder' => $new['sortorder'] ?? null,
                    'isactive' => (bool) ($new['isactive'] ?? false),
                ]);
            }
        });

        return redirect()
            ->route('countries.index', [
                'search' => $request->input('search'),
                'status' => $request->input('status'),
            ])
            ->with('success', 'Countries saved successfully.');
    }

    public function destroy(Request $request, Country $country)
    {
        try {
            $country->delete();

            return redirect()
                ->route('countries.index', [
                    'search' => $request->input('search'),
                    'status' => $request->input('status'),
                ])
                ->with('success', 'Country deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('countries.index', [
                    'search' => $request->input('search'),
                    'status' => $request->input('status'),
                ])
                ->with('error', 'This country is in use and cannot be deleted.');
        }
    }
}