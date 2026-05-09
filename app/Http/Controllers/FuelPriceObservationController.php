<?php

namespace App\Http\Controllers;

use App\Models\FuelPriceObservation;
use App\Models\FuelStop;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FuelPriceObservationController extends Controller
{
    public function index(Request $request)
    {
        $fuelTypes = config('fuel.fuel_types');

        $priceSources = [
            'actual_purchase' => 'Actual Purchase',
            'signboard' => 'Signboard',
            'website' => 'Website',
            'imported' => 'Imported',
            'estimate' => 'Estimate',
        ];

        $fuelStops = FuelStop::orderBy('stopname')->get();

        $fuelPriceObservations = FuelPriceObservation::with('fuelStop.place')
            ->when($request->filled('fuelstopid'), fn ($query) => $query->where('fuelstopid', $request->fuelstopid))
            ->when($request->filled('fueltype'), fn ($query) => $query->where('fueltype', $request->fueltype))
            ->when($request->filled('pricesource'), fn ($query) => $query->where('pricesource', $request->pricesource))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('observedon', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('observedon', '<=', $request->date_to))
            ->orderByDesc('observedon')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('fuelpriceobservations.index', compact(
            'fuelPriceObservations',
            'fuelStops',
            'fuelTypes',
            'priceSources'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateObservation($request);

        FuelPriceObservation::create($validated);

        return redirect()
            ->route('fuel-price-observations.index')
            ->with('success', 'Fuel price observation added.');
    }

    public function edit(FuelPriceObservation $fuelPriceObservation)
    {
        $fuelTypes = config('fuel.fuel_types');

        $priceSources = [
            'actual_purchase' => 'Actual Purchase',
            'signboard' => 'Signboard',
            'website' => 'Website',
            'imported' => 'Imported',
            'estimate' => 'Estimate',
        ];

        $fuelStops = FuelStop::orderBy('stopname')->get();

        return view('fuelpriceobservations.edit', compact(
            'fuelPriceObservation',
            'fuelStops',
            'fuelTypes',
            'priceSources'
        ));
    }

    public function update(Request $request, FuelPriceObservation $fuelPriceObservation)
    {
        $validated = $this->validateObservation($request);

        $fuelPriceObservation->update($validated);

        if ($request->input('save_action') === 'index') {
            return redirect()
                ->route('fuel-price-observations.index')
                ->with('success', 'Fuel price observation updated.');
        }

        return redirect()
            ->route('fuel-price-observations.edit', $fuelPriceObservation)
            ->with('success', 'Fuel price observation updated.');
    }

    public function destroy(FuelPriceObservation $fuelPriceObservation)
    {
        $fuelPriceObservation->delete();

        return redirect()
            ->route('fuel-price-observations.index')
            ->with('success', 'Fuel price observation deleted.');
    }

    protected function validateObservation(Request $request): array
    {
        $priceSources = ['actual_purchase', 'signboard', 'website', 'imported', 'estimate'];

        return $request->validate([
            'fuelstopid' => ['required', 'integer', 'exists:fuelstops,id'],
            'observedon' => ['required', 'date'],
            'fueltype' => ['required', 'string', Rule::in(array_keys(config('fuel.fuel_types')))],
            'priceperlitre' => ['required', 'numeric', 'min:0', 'max:99.9999'],
            'pricesource' => ['nullable', 'string', Rule::in($priceSources)],
            'observationnotes' => ['nullable', 'string'],
        ]);
    }
}