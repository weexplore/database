<?php

namespace App\Http\Controllers;

use App\Models\FuelPriceObservation;
use App\Models\FuelStop;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FuelPriceObservationController extends Controller
{
    public function index(Request $request)
    {
        $fuelTypes = FuelPriceObservation::fuelTypeOptions();
        $priceSources = FuelPriceObservation::priceSourceOptions();

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

    public function edit(Request $request, FuelPriceObservation $fuelPriceObservation)
{
    $fuelPriceObservation->load([
        'fuelStop.place',
        'trip',
    ]);

    $fuelStop = $fuelPriceObservation->fuelStop;
    $fuelStops = FuelStop::orderBy('stopname')->get();
    $fuelTypes = FuelPriceObservation::fuelTypeOptions();
    $priceSources = FuelPriceObservation::priceSourceOptions();
    $trips = Trip::orderBy('tripname')->get();
    $returnTo = $request->input('return_to', route('fuel-price-observations.index'));

    return view('fuelpriceobservations.edit', compact(
        'fuelPriceObservation',
        'fuelStop',
        'fuelStops',
        'fuelTypes',
        'priceSources',
        'trips',
        'returnTo'
    ));
}

    public function update(Request $request, FuelPriceObservation $fuelPriceObservation)
{
    $validated = $this->validateObservation($request);

    $fuelPriceObservation->update($validated);

    $saveAction = $request->input('save_action');
    $returnTo = $request->input('return_to');

    if ($saveAction === 'stay') {
        return redirect()
            ->route('fuel-price-observations.edit', [
                'fuel_price_observation' => $fuelPriceObservation,
                'return_to' => $returnTo,
            ])
            ->with('success', 'Fuel price observation updated successfully.');
    }

    if ($saveAction === 'index') {
        if ($returnTo) {
            return redirect($returnTo)
                ->with('success', 'Fuel price observation updated successfully.');
        }

        return redirect()
            ->route('fuel-price-observations.index')
            ->with('success', 'Fuel price observation updated successfully.');
    }

    if ($returnTo) {
        return redirect($returnTo)
            ->with('success', 'Fuel price observation updated successfully.');
    }

    return redirect()
        ->route('fuel-price-observations.index')
        ->with('success', 'Fuel price observation updated successfully.');
}

    public function destroy(Request $request, FuelPriceObservation $fuelPriceObservation)
    {
        $returnTo = $request->input('return_to');

        $fuelPriceObservation->delete();

        if ($returnTo) {
            return redirect($returnTo)
                ->with('success', 'Fuel price observation deleted.');
        }

        return redirect()
            ->route('fuel-price-observations.index')
            ->with('success', 'Fuel price observation deleted.');
    }

private function validateObservation(Request $request): array
{
    $fuelTypeKeys = array_keys(FuelPriceObservation::fuelTypeOptions());
    $priceSourceKeys = array_keys(FuelPriceObservation::priceSourceOptions());

    return $request->validate([
        'fuelstopid' => ['required', 'integer', 'exists:fuelstops,id'],
        'observedon' => ['required', 'date'],
        'fueltype' => ['required', 'string', Rule::in($fuelTypeKeys)],
        'priceperlitre' => ['required', 'numeric', 'min:0'],
        'pricesource' => ['nullable', 'string', Rule::in($priceSourceKeys)],
        'observationnotes' => ['nullable', 'string'],
        'tripid' => ['nullable', 'integer', 'exists:trips,id'],
    ]);
}
public function create(Request $request)
{
    $fuelStopId = $request->filled('fuel_stop_id')
        ? $request->integer('fuel_stop_id')
        : null;

    $fuelStop = null;

    if ($fuelStopId) {
        $fuelStop = FuelStop::findOrFail($fuelStopId);
    }

    $observation = FuelPriceObservation::create([
        'fuelstopid' => $fuelStopId,
        'observedon' => now()->toDateString(),
        'fueltype' => array_key_first(FuelPriceObservation::fuelTypeOptions()),
        'priceperlitre' => 0,
        'pricesource' => array_key_first(FuelPriceObservation::priceSourceOptions()),
    ]);

    $returnTo = $request->input('return_to');

    return redirect()->route('fuel-price-observations.edit', [
        'fuel_price_observation' => $observation,
        'return_to' => $returnTo,
    ])->with('success', 'Fuel price observation created. Update the details below.');
}
}