<?php

namespace App\Http\Controllers;

use App\Models\Exchange;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExchangeController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'active' => (string) $request->query('active', ''),
            'countrycode' => strtoupper(trim((string) $request->query('countrycode', ''))),
        ];

        $query = Exchange::query();

        if ($filters['search'] !== '') {
            $query->where(function ($q) use ($filters) {
                $q->where('exchangecode', 'like', "%{$filters['search']}%")
                    ->orWhere('exchangename', 'like', "%{$filters['search']}%")
                    ->orWhere('countrycode', 'like', "%{$filters['search']}%")
                    ->orWhere('defaultcurrencycode', 'like', "%{$filters['search']}%");
            });
        }

        if ($filters['active'] !== '') {
            $query->where('isactive', (int) $filters['active']);
        }

        if ($filters['countrycode'] !== '') {
            $query->where('countrycode', $filters['countrycode']);
        }

        return view('exchanges.index', [
            'pageTitle' => 'Exchanges',
            'rows' => $query->orderBy('exchangename')->get(),
            'filters' => $filters,
        ]);
    }

    public function bulkSave(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'existing' => ['nullable', 'array'],
            'existing.*.exchangecode' => ['required', 'string', 'max:20'],
            'existing.*.exchangename' => ['required', 'string', 'max:150'],
            'existing.*.countrycode' => ['nullable', 'string', 'size:2'],
            'existing.*.defaultcurrencycode' => ['nullable', 'string', 'size:3'],
            'existing.*.marketwebsite' => ['nullable', 'url', 'max:255'],
            'existing.*.timezone' => ['nullable', 'string', 'max:100'],
            'existing.*.isactive' => ['nullable', 'boolean'],

            'new' => ['nullable', 'array'],
            'new.exchangecode' => ['nullable', 'string', 'max:20'],
            'new.exchangename' => ['nullable', 'string', 'max:150'],
            'new.countrycode' => ['nullable', 'string', 'size:2'],
            'new.defaultcurrencycode' => ['nullable', 'string', 'size:3'],
            'new.marketwebsite' => ['nullable', 'url', 'max:255'],
            'new.timezone' => ['nullable', 'string', 'max:100'],
            'new.isactive' => ['nullable', 'boolean'],

            'search' => ['nullable', 'string'],
            'active' => ['nullable', 'in:0,1'],
            'countrycode' => ['nullable', 'string', 'size:2'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['existing'] ?? [] as $id => $row) {
                $exchangeCode = strtoupper(trim((string) ($row['exchangecode'] ?? '')));
                $exchangeName = trim((string) ($row['exchangename'] ?? ''));
                $countryCode = strtoupper(trim((string) ($row['countrycode'] ?? '')));
                $currencyCode = strtoupper(trim((string) ($row['defaultcurrencycode'] ?? '')));
                $marketWebsite = trim((string) ($row['marketwebsite'] ?? ''));
                $timezone = trim((string) ($row['timezone'] ?? ''));

                if ($exchangeCode === '') {
                    throw ValidationException::withMessages([
                        "existing.$id.exchangecode" => 'Exchange code is required.',
                    ]);
                }

                if ($exchangeName === '') {
                    throw ValidationException::withMessages([
                        "existing.$id.exchangename" => 'Exchange name is required.',
                    ]);
                }

                $duplicateCode = Exchange::query()
                    ->whereRaw('UPPER(exchangecode) = ?', [$exchangeCode])
                    ->where('id', '!=', $id)
                    ->exists();

                if ($duplicateCode) {
                    throw ValidationException::withMessages([
                        "existing.$id.exchangecode" => 'Exchange code must be unique.',
                    ]);
                }

                $exchange = Exchange::findOrFail($id);

                $exchange->update([
                    'exchangecode' => $exchangeCode,
                    'exchangename' => $exchangeName,
                    'countrycode' => $countryCode !== '' ? $countryCode : null,
                    'defaultcurrencycode' => $currencyCode !== '' ? $currencyCode : null,
                    'marketwebsite' => $marketWebsite !== '' ? $marketWebsite : null,
                    'timezone' => $timezone !== '' ? $timezone : null,
                    'isactive' => (bool) ($row['isactive'] ?? false),
                ]);
            }

            $new = $validated['new'] ?? [];

            $newCode = strtoupper(trim((string) ($new['exchangecode'] ?? '')));
            $newName = trim((string) ($new['exchangename'] ?? ''));
            $newCountryCode = strtoupper(trim((string) ($new['countrycode'] ?? '')));
            $newCurrencyCode = strtoupper(trim((string) ($new['defaultcurrencycode'] ?? '')));
            $newWebsite = trim((string) ($new['marketwebsite'] ?? ''));
            $newTimezone = trim((string) ($new['timezone'] ?? ''));

            $hasNewRow =
                $newCode !== '' ||
                $newName !== '' ||
                $newCountryCode !== '' ||
                $newCurrencyCode !== '' ||
                $newWebsite !== '' ||
                $newTimezone !== '';

            if ($hasNewRow) {
                if ($newCode === '') {
                    throw ValidationException::withMessages([
                        'new.exchangecode' => 'Exchange code is required for a new exchange.',
                    ]);
                }

                if ($newName === '') {
                    throw ValidationException::withMessages([
                        'new.exchangename' => 'Exchange name is required for a new exchange.',
                    ]);
                }

                $duplicateCode = Exchange::query()
                    ->whereRaw('UPPER(exchangecode) = ?', [$newCode])
                    ->exists();

                if ($duplicateCode) {
                    throw ValidationException::withMessages([
                        'new.exchangecode' => 'Exchange code must be unique.',
                    ]);
                }

                Exchange::create([
                    'exchangecode' => $newCode,
                    'exchangename' => $newName,
                    'countrycode' => $newCountryCode !== '' ? $newCountryCode : null,
                    'defaultcurrencycode' => $newCurrencyCode !== '' ? $newCurrencyCode : null,
                    'marketwebsite' => $newWebsite !== '' ? $newWebsite : null,
                    'timezone' => $newTimezone !== '' ? $newTimezone : null,
                    'isactive' => (bool) ($new['isactive'] ?? false),
                ]);
            }
        });

        return redirect()
            ->route('exchanges.index', [
                'search' => $request->input('search'),
                'active' => $request->input('active'),
                'countrycode' => $request->input('countrycode'),
            ])
            ->with('success', 'Exchanges saved successfully.');
    }

    public function destroy(Exchange $exchange): RedirectResponse
    {
        $exchange->delete();

        return redirect()
            ->route('exchanges.index', [
                'search' => request('search'),
                'active' => request('active'),
                'countrycode' => request('countrycode'),
            ])
            ->with('success', 'Exchange deleted.');
    }
}