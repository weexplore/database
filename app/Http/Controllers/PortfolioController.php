<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'type' => trim((string) $request->query('type', '')),
            'active' => (string) $request->query('active', ''),
        ];

        $query = Portfolio::query();

        if ($filters['search'] !== '') {
            $query->where(function ($q) use ($filters) {
                $q->where('portfolioname', 'like', "%{$filters['search']}%")
                    ->orWhere('portfoliotype', 'like', "%{$filters['search']}%")
                    ->orWhere('basecurrencycode', 'like', "%{$filters['search']}%")
                    ->orWhere('ownernotes', 'like', "%{$filters['search']}%");
            });
        }

        if ($filters['type'] !== '') {
            $query->where('portfoliotype', $filters['type']);
        }

        if ($filters['active'] !== '') {
            $query->where('isactive', (int) $filters['active']);
        }

        return view('portfolios.index', [
            'pageTitle' => 'Portfolios',
            'rows' => $query->orderBy('portfolioname')->get(),
            'filters' => $filters,
            'portfolioTypes' => [
                'personal' => 'Personal',
                'joint' => 'Joint',
                'smsf' => 'SMSF',
                'trust' => 'Trust',
                'watchlist' => 'Watchlist',
                'model' => 'Model',
            ],
        ]);
    }

    public function bulkSave(Request $request): RedirectResponse
    {
        $portfolioTypes = ['personal', 'joint', 'smsf', 'trust', 'watchlist', 'model'];

        $validated = $request->validate([
            'existing' => ['nullable', 'array'],
            'existing.*.portfolioname' => ['required', 'string', 'max:150'],
            'existing.*.portfoliotype' => ['required', Rule::in($portfolioTypes)],
            'existing.*.basecurrencycode' => ['required', 'string', 'size:3'],
            'existing.*.ownernotes' => ['nullable', 'string'],
            'existing.*.isactive' => ['nullable', 'in:0,1'],

            'new' => ['nullable', 'array'],
            'new.portfolioname' => ['nullable', 'string', 'max:150'],
            'new.portfoliotype' => ['nullable', Rule::in($portfolioTypes)],
            'new.basecurrencycode' => ['nullable', 'string', 'size:3'],
            'new.ownernotes' => ['nullable', 'string'],
            'new.isactive' => ['nullable', 'in:0,1'],

            'search' => ['nullable', 'string'],
            'type' => ['nullable', Rule::in($portfolioTypes)],
            'active' => ['nullable', 'in:0,1'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['existing'] ?? [] as $id => $row) {
                $portfolioName = trim((string) ($row['portfolioname'] ?? ''));
                $portfolioType = trim((string) ($row['portfoliotype'] ?? ''));
                $baseCurrencyCode = strtoupper(trim((string) ($row['basecurrencycode'] ?? '')));
                $ownerNotes = trim((string) ($row['ownernotes'] ?? ''));

                if ($portfolioName === '') {
                    throw ValidationException::withMessages([
                        "existing.$id.portfolioname" => 'Portfolio name is required.',
                    ]);
                }

                if ($portfolioType === '') {
                    throw ValidationException::withMessages([
                        "existing.$id.portfoliotype" => 'Portfolio type is required.',
                    ]);
                }

                if ($baseCurrencyCode === '') {
                    throw ValidationException::withMessages([
                        "existing.$id.basecurrencycode" => 'Base currency code is required.',
                    ]);
                }

                $duplicateName = Portfolio::query()
                    ->whereRaw('LOWER(portfolioname) = ?', [strtolower($portfolioName)])
                    ->where('id', '!=', $id)
                    ->exists();

                if ($duplicateName) {
                    throw ValidationException::withMessages([
                        "existing.$id.portfolioname" => 'Portfolio name must be unique.',
                    ]);
                }

                $portfolio = Portfolio::findOrFail($id);

                $portfolio->update([
                    'portfolioname' => $portfolioName,
                    'portfoliotype' => $portfolioType,
                    'basecurrencycode' => $baseCurrencyCode,
                    'ownernotes' => $ownerNotes !== '' ? $ownerNotes : null,
                    'isactive' => (bool) ($row['isactive'] ?? false),
                ]);
            }
            $new = $validated['new'] ?? [];

            $newName = trim((string) ($new['portfolioname'] ?? ''));
            $newType = trim((string) ($new['portfoliotype'] ?? ''));
            $newCurrency = strtoupper(trim((string) ($new['basecurrencycode'] ?? '')));
            $newNotes = trim((string) ($new['ownernotes'] ?? ''));

            $hasNewRow = $newName !== '' || $newType !== '' || $newNotes !== '';

            if ($hasNewRow) {
                if ($newName === '') {
                    throw ValidationException::withMessages([
                        'new.portfolioname' => 'Portfolio name is required for a new portfolio.',
                    ]);
                }

                if ($newType === '') {
                    throw ValidationException::withMessages([
                        'new.portfoliotype' => 'Portfolio type is required for a new portfolio.',
                    ]);
                }

                if ($newCurrency === '') {
                    throw ValidationException::withMessages([
                        'new.basecurrencycode' => 'Base currency code is required for a new portfolio.',
                    ]);
                }

                $duplicateName = Portfolio::query()
                    ->whereRaw('LOWER(portfolioname) = ?', [strtolower($newName)])
                    ->exists();

                if ($duplicateName) {
                    throw ValidationException::withMessages([
                        'new.portfolioname' => 'Portfolio name must be unique.',
                    ]);
                }

                Portfolio::create([
                    'portfolioname' => $newName,
                    'portfoliotype' => $newType,
                    'basecurrencycode' => $newCurrency,
                    'ownernotes' => $newNotes !== '' ? $newNotes : null,
                    'isactive' => (bool) ($new['isactive'] ?? true),
                ]);
            }
        });

        return redirect()
            ->route('portfolios.index', [
                'search' => $request->input('search'),
                'type' => $request->input('type'),
                'active' => $request->input('active'),
            ])
            ->with('success', 'Portfolios saved successfully.');
    }

    public function destroy(Portfolio $portfolio): RedirectResponse
    {
        $portfolio->delete();

        return redirect()
            ->route('portfolios.index', [
                'search' => request('search'),
                'type' => request('type'),
                'active' => request('active'),
            ])
            ->with('success', 'Portfolio deleted.');
    }
}