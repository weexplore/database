<?php

namespace App\Http\Controllers;

use App\Models\FuelPriceEstimate;
use App\Models\FuelPriceObservation;
use App\Models\FuelStop;
use App\Models\Place;
use App\Models\Trip;
use App\Models\TripLeg;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TripFuelEstimateController extends Controller
{
    public function index(Request $request, Trip $trip)
    {
        $fuelTypes = config('fuel.fuel_types');

        $tripLegs = TripLeg::where('tripid', $trip->id)
            ->orderBy('sortorder')
            ->orderBy('legnumber')
            ->orderBy('id')
            ->get();

        $places = Place::orderBy('placename')->get();
        $fuelStops = FuelStop::with('place')->orderBy('stopname')->get();

        $sourceObservations = FuelPriceObservation::with('fuelStop')
            ->orderByDesc('observedon')
            ->orderByDesc('id')
            ->get();

        $tripFuelEstimates = FuelPriceEstimate::with(['tripLeg', 'fuelStop.place', 'place', 'sourceObservation'])
            ->where('tripid', $trip->id)
            ->when($request->filled('triplegid'), fn ($query) => $query->where('triplegid', $request->triplegid))
            ->when($request->filled('fueltype'), fn ($query) => $query->where('fueltype', $request->fueltype))
            ->when($request->filled('fuelstopid'), fn ($query) => $query->where('fuelstopid', $request->fuelstopid))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('estimatedate', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('estimatedate', '<=', $request->date_to))
            ->orderBy('estimatedate')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('tripfuelestimates.index', compact(
            'trip',
            'tripFuelEstimates',
            'tripLegs',
            'places',
            'fuelStops',
            'sourceObservations',
            'fuelTypes'
        ));
    }

    public function store(Request $request, Trip $trip)
    {
        $validated = $this->validateEstimate($request, $trip);

        $validated['tripid'] = $trip->id;
        $validated = $this->applyDerivedTotals($validated);

        FuelPriceEstimate::create($validated);

        return redirect()
            ->route('trips.fuel-estimates.index', $trip)
            ->with('success', 'Trip fuel estimate added.');
    }

    public function edit(Trip $trip, FuelPriceEstimate $fuelEstimate)
    {
        abort_if((int) $fuelEstimate->tripid !== (int) $trip->id, 404);

        $fuelTypes = config('fuel.fuel_types');

        $tripLegs = TripLeg::where('tripid', $trip->id)
                    ->orderBy('sortorder')
                    ->orderBy('legnumber')
                    ->orderBy('id')
                    ->get();

        $places = Place::orderBy('placename')->get();
        $fuelStops = FuelStop::with('place')->orderBy('stopname')->get();

        $sourceObservations = FuelPriceObservation::with('fuelStop')
            ->orderByDesc('observedon')
            ->orderByDesc('id')
            ->get();

        return view('tripfuelestimates.edit', compact(
            'trip',
            'fuelEstimate',
            'tripLegs',
            'places',
            'fuelStops',
            'sourceObservations',
            'fuelTypes'
        ));
    }

    public function update(Request $request, Trip $trip, FuelPriceEstimate $fuelEstimate)
    {
        abort_if((int) $fuelEstimate->tripid !== (int) $trip->id, 404);

        $validated = $this->validateEstimate($request, $trip);

        $validated['tripid'] = $trip->id;
        $validated = $this->applyDerivedTotals($validated);

        $fuelEstimate->update($validated);

        if ($request->input('save_action') === 'index') {
            return redirect()
                ->route('trips.fuel-estimates.index', $trip)
                ->with('success', 'Trip fuel estimate updated.');
        }

        return redirect()
            ->route('trips.fuel-estimates.edit', [$trip, $fuelEstimate])
            ->with('success', 'Trip fuel estimate updated.');
    }

    public function destroy(Trip $trip, FuelPriceEstimate $fuelEstimate)
    {
        abort_if((int) $fuelEstimate->tripid !== (int) $trip->id, 404);

        $fuelEstimate->delete();

        return redirect()
            ->route('trips.fuel-estimates.index', $trip)
            ->with('success', 'Trip fuel estimate deleted.');
    }

    protected function validateEstimate(Request $request, Trip $trip): array
    {
        return $request->validate([
            'triplegid' => [
                'nullable',
                'integer',
                Rule::exists('triplegs', 'id')->where(fn ($query) => $query->where('tripid', $trip->id)),
            ],
            'fuelstopid' => ['nullable', 'integer', 'exists:fuelstops,id'],
            'placeid' => ['nullable', 'integer', 'exists:places,id'],
            'estimatedate' => ['required', 'date'],
            'fueltype' => ['required', 'string', Rule::in(array_keys(config('fuel.fuel_types')))],
            'expectedpriceperlitre' => ['nullable', 'numeric', 'min:0', 'max:99.9999'],
            'estimateddistancekm' => ['nullable', 'numeric', 'min:0', 'max:999999.9'],
            'estimatedlitres' => ['nullable', 'numeric', 'min:0', 'max:99999.999'],
            'estimatedtotalcost' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'sourceobservationid' => ['nullable', 'integer', 'exists:fuelpriceobservations,id'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    protected function applyDerivedTotals(array $validated): array
    {
        $price = isset($validated['expectedpriceperlitre']) && $validated['expectedpriceperlitre'] !== null
            ? (float) $validated['expectedpriceperlitre']
            : null;

        $litres = isset($validated['estimatedlitres']) && $validated['estimatedlitres'] !== null
            ? (float) $validated['estimatedlitres']
            : null;

        if (($validated['estimatedtotalcost'] ?? null) === null && $price !== null && $litres !== null) {
            $validated['estimatedtotalcost'] = round($price * $litres, 2);
        }

        return $validated;
    }
}