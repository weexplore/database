<?php

namespace App\Http\Controllers;

use App\Models\FuelStop;
use App\Models\Place;
use App\Models\Trip;
use App\Models\FuelPricePurchase;
use App\Models\TripLeg;
use Illuminate\Http\Request;

class TripFuelPurchaseController extends Controller
{
    public function index(Trip $trip, Request $request)
    {
        $tripLegs = TripLeg::where('tripid', $trip->id)
            ->orderBy('legnumber')
            ->orderBy('startdate')
            ->get();

        $places = Place::orderBy('placename')->get();
        $fuelStops = FuelStop::with('place')->orderBy('stopname')->get();
        $fuelTypes = config('fuel.fuel_types'); // <— make sure this line exists

        $query = FuelPricePurchase::with(['trip', 'leg', 'fuelStop', 'place'])
            ->where('tripid', $trip->id);

        if ($request->filled('trip_leg_id')) {
            $query->where('triplegid', (int) $request->trip_leg_id);
        }

        if ($request->filled('fuel_stop_id')) {
            $query->where('fuelstopid', (int) $request->fuel_stop_id);
        }

        if ($request->filled('fuel_type')) {
            $query->where('fueltype', trim((string) $request->fuel_type));
        }

        $purchases = $query
            ->orderByDesc('purchasedate')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('tripfuelpurchases.index', compact(
            'trip',
            'purchases',
            'tripLegs',
            'places',
            'fuelStops',
            'fuelTypes', // <— ensure this is included
        ));
    }

    public function create(Trip $trip)
    {
        return redirect()
            ->route('trips.fuel-purchases.index', $trip)
            ->with('show_create', true);
    }

    public function store(Trip $trip, Request $request)
    {
        $data = $this->validatedData($request);

        FuelPricePurchase::create($this->mappedData($data, $trip->id));

        return redirect()
            ->route('trips.fuel-purchases.index', $trip)
            ->with('success', 'Trip fuel purchase created successfully.');
    }

    public function edit(Trip $trip, FuelPricePurchase $fuelPurchase)
    {
        abort_unless((int) $fuelPurchase->tripid === (int) $trip->id, 404);
        

        $tripLegs = TripLeg::where('tripid', $trip->id)
            ->orderBy('legnumber')
            ->orderBy('startdate')
            ->get();

        $places = Place::orderBy('placename')->get();
        $fuelStops = FuelStop::with('place')->orderBy('stopname')->get();
        $fuelTypes = config('fuel.fuel_types');

        return view('tripfuelpurchases.edit', compact(
            'trip',
            'fuelPurchase',
            'tripLegs',
            'places',
            'fuelStops',
            'fuelTypes',
            ));
    }

    public function update(Trip $trip, Request $request, FuelPricePurchase $fuelPurchase)
    {
        abort_unless((int) $fuelPurchase->tripid === (int) $trip->id, 404);

        $data = $this->validatedData($request);

        $fuelPurchase->update($this->mappedData($data, $trip->id));

        return redirect()
            ->route('trips.fuel-purchases.index', $trip)
            ->with('success', 'Trip fuel purchase updated successfully.');
    }

    public function destroy(Trip $trip, FuelPricePurchase $fuelPurchase)
    {
        abort_unless((int) $fuelPurchase->tripid === (int) $trip->id, 404);

        $fuelPurchase->delete();

        return redirect()
            ->route('trips.fuel-purchases.index', $trip)
            ->with('success', 'Trip fuel purchase deleted successfully.');
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'triplegid' => ['nullable', 'integer', 'exists:triplegs,id'],
            'fuelstopid' => ['nullable', 'integer', 'exists:fuelstops,id'],
            'placeid' => ['nullable', 'integer', 'exists:places,id'],
            'purchasedate' => ['required', 'date'],
            'odometerkm' => ['nullable', 'numeric', 'min:0'],
            'distancesincelastfillkm' => ['nullable', 'numeric', 'min:0'],
            'fueltype' => ['required', 'string', 'max:30'],
            'litres' => ['required', 'numeric', 'min:0'],
            'priceperlitre' => ['required', 'numeric', 'min:0'],
            'fueltotal' => ['required', 'numeric', 'min:0'],
            'servicecosts' => ['nullable', 'numeric', 'min:0'],
            'repairscost' => ['nullable', 'numeric', 'min:0'],
            'receiptreference' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    protected function mappedData(array $data, int $tripId): array
    {
        return [
            'tripid' => $tripId,
            'triplegid' => $data['triplegid'] ?? null,
            'fuelstopid' => $data['fuelstopid'] ?? null,
            'placeid' => $data['placeid'] ?? null,
            'purchasedate' => $data['purchasedate'],
            'odometerkm' => $data['odometerkm'] ?? null,
            'distancesincelastfillkm' => $data['distancesincelastfillkm'] ?? null,
            'fueltype' => $data['fueltype'],
            'litres' => $data['litres'],
            'priceperlitre' => $data['priceperlitre'],
            'fueltotal' => $data['fueltotal'],
            'servicecosts' => $data['servicecosts'] ?? null,
            'repairscost' => $data['repairscost'] ?? null,
            'receiptreference' => $data['receiptreference'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];
    }
}