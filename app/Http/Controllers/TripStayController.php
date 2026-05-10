<?php

namespace App\Http\Controllers;

use App\Models\DestinationItem;
use App\Models\Place;
use App\Models\Trip;
use App\Models\TripLeg;
use App\Models\TripStay;
use Illuminate\Http\Request;

class TripStayController extends Controller
{
    private function stayTypeOptions(): array
    {
        return [
            'caravanpark' => 'Caravan Park',
            'freecamp' => 'Free Camp',
            'showgrounds' => 'Showgrounds',
            'stationstay' => 'Station Stay',
            'campground' => 'Campground',
            'motel' => 'Motel',
            'farmstay' => 'Farm Stay',
            'other' => 'Other',
        ];
    }

    public function index(Request $request, Trip $trip)
{
    $query = TripStay::with(['place', 'tripLeg', 'travelledFromPlace'])
        ->where('tripid', $trip->id);

    if ($request->filled('tripleg_id')) {
        $query->where('triplegid', $request->integer('tripleg_id'));
    }

    if ($request->filled('place_id')) {
        $query->where('placeid', $request->integer('place_id'));
    }

    if ($request->filled('travelledfromplace_id')) {
        $query->where('travelledfromplaceid', $request->integer('travelledfromplace_id'));
    }

    if ($request->filled('staytype')) {
        $query->where('staytype', $request->string('staytype'));
    }

    $stays = $query->orderBy('checkindate')
        ->orderBy('id')
        ->get();

    $places = Place::orderBy('placename')->get();

    $tripLegs = TripLeg::where('tripid', $trip->id)
        ->orderBy('legnumber')
        ->orderBy('sortorder')
        ->get();

    $destinationItems = DestinationItem::orderBy('itemname')->get();

    $stayTypes = [
        'caravan_park',
        'free_camp',
        'showgrounds',
        'station_stay',
        'campground',
        'motel',
        'farm_stay',
        'friends_family',
        'roadside_stop',
        'other',
    ];

    $showCreate = $request->boolean('show_create');
    $selectedTripLegId = $request->integer('tripleg_id');
    $selectedPlaceId = $request->integer('place_id');
    $selectedTravelledFromPlaceId = $request->integer('travelledfromplace_id');

    return view('trip-stays.index', compact(
        'trip',
        'stays',
        'places',
        'tripLegs',
        'destinationItems',
        'stayTypes',
        'showCreate',
        'selectedTripLegId',
        'selectedPlaceId',
        'selectedTravelledFromPlaceId'
    ));
}

    public function create(Request $request, Trip $trip)
    {
        $returnTo = $request->input('return_to');

        if ($returnTo) {
            return redirect($returnTo);
        }

        return redirect()->route('trips.stays.index', [
            'trip' => $trip->id,
            'show_create' => 1,
        ]);
    }

    public function store(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'triplegid' => ['nullable', 'integer', 'exists:triplegs,id'],
            'placeid' => ['nullable', 'integer', 'exists:places,id'],
            'stayname' => ['required', 'string', 'max:200'],
            'staytype' => ['nullable', 'string', 'max:50'],
            'checkindate' => ['nullable', 'date'],
            'checkoutdate' => ['nullable', 'date', 'after_or_equal:checkindate'],
            'nights' => ['nullable', 'integer', 'min:0'],
            'isaccommodationpaid' => ['nullable', 'boolean'],
            'costpernight' => ['nullable', 'numeric', 'min:0'],
            'estimatedtotalcost' => ['nullable', 'numeric', 'min:0'],
            'actualtotalcost' => ['nullable', 'numeric', 'min:0'],
            'travelledfromplaceid' => ['nullable', 'integer', 'exists:places,id'],
            'distancetravelledkm' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'woulduseagain' => ['nullable', 'boolean'],
            'reviewnotes' => ['nullable', 'string'],
            'destinationitemid' => ['nullable', 'integer', 'exists:destinationitems,id'],
        ]);

        $validated['tripid'] = $trip->id;
        $validated['isaccommodationpaid'] = $request->boolean('isaccommodationpaid');
        $validated['woulduseagain'] = $request->boolean('woulduseagain');

        $tripStay = TripStay::create($validated);

        $returnTo = $request->input('return_to');

        if ($returnTo) {
            return redirect($returnTo)->with('success', 'Trip stay created successfully.');
        }

        return redirect()
            ->route('trips.stays.edit', ['trip' => $trip, 'tripStay' => $tripStay])
            ->with('success', 'Trip stay created successfully.');
    }

    public function edit(Request $request, Trip $trip, TripStay $tripStay)
{
    abort_unless((int) $tripStay->tripid === (int) $trip->id, 404);

    $places = Place::orderBy('placename')->get();

    $tripLegs = TripLeg::where('tripid', $trip->id)
        ->orderBy('legnumber')
        ->orderBy('sortorder')
        ->get();

    $selectedPlaceId = old('placeid', $tripStay->placeid);
    $selectedTripLegId = old('triplegid', $tripStay->triplegid);

    $selectedTripLeg = $tripLegs->firstWhere('id', (int) $selectedTripLegId);

    $destinationItemsQuery = DestinationItem::query()
        ->with(['place', 'destination'])
        ->where('isactive', 1);

    $destinationItemsQuery->where(function ($query) use ($selectedPlaceId, $selectedTripLeg, $tripStay) {
        $hasCondition = false;

        if ($selectedPlaceId) {
            $query->where('placeid', $selectedPlaceId);
            $hasCondition = true;
        }

        $destinationId = $selectedTripLeg?->destinationid;

        if ($destinationId) {
            if ($hasCondition) {
                $query->orWhere('destinationid', $destinationId);
            } else {
                $query->where('destinationid', $destinationId);
                $hasCondition = true;
            }
        }

        if ($tripStay->destinationitemid) {
            if ($hasCondition) {
                $query->orWhere('id', $tripStay->destinationitemid);
            } else {
                $query->where('id', $tripStay->destinationitemid);
            }
        }
    });

    $destinationItems = $destinationItemsQuery
        ->orderBy('itemname')
        ->get();

    $stayTypes = [
        'caravan_park',
        'free_camp',
        'showgrounds',
        'station_stay',
        'campground',
        'motel',
        'farm_stay',
        'friends_family',
        'roadside_stop',
        'other',
    ];

    return view('trip-stays.edit', compact(
        'trip',
        'tripStay',
        'places',
        'tripLegs',
        'destinationItems',
        'stayTypes'
    ));
}

    public function update(Request $request, Trip $trip, TripStay $tripStay)
    {
        abort_unless((int) $tripStay->tripid === (int) $trip->id, 404);

        $validated = $request->validate([
            'triplegid' => ['nullable', 'integer', 'exists:triplegs,id'],
            'placeid' => ['nullable', 'integer', 'exists:places,id'],
            'stayname' => ['required', 'string', 'max:200'],
            'staytype' => ['nullable', 'string', 'max:50'],
            'checkindate' => ['nullable', 'date'],
            'checkoutdate' => ['nullable', 'date', 'after_or_equal:checkindate'],
            'nights' => ['nullable', 'integer', 'min:0'],
            'isaccommodationpaid' => ['nullable', 'boolean'],
            'costpernight' => ['nullable', 'numeric', 'min:0'],
            'estimatedtotalcost' => ['nullable', 'numeric', 'min:0'],
            'actualtotalcost' => ['nullable', 'numeric', 'min:0'],
            'travelledfromplaceid' => ['nullable', 'integer', 'exists:places,id'],
            'distancetravelledkm' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'woulduseagain' => ['nullable', 'boolean'],
            'reviewnotes' => ['nullable', 'string'],
        ]);

        $validated['isaccommodationpaid'] = $request->boolean('isaccommodationpaid');
        $validated['woulduseagain'] = $request->boolean('woulduseagain');

        $tripStay->update($validated);

        $returnTo = $request->input('return_to');

        if ($returnTo) {
            return redirect($returnTo)->with('success', 'Trip stay updated successfully.');
        }

        return redirect()
            ->route('trips.stays.edit', ['trip' => $trip, 'tripStay' => $tripStay])
            ->with('success', 'Trip stay updated successfully.');
    }

    public function destroy(Request $request, Trip $trip, TripStay $tripStay)
    {
        abort_unless((int) $tripStay->tripid === (int) $trip->id, 404);

        $returnTo = $request->input('return_to');

        try {
            $tripStay->delete();

            if ($returnTo) {
                return redirect($returnTo)->with('success', 'Trip stay deleted successfully.');
            }

            return redirect()
                ->route('trips.stays.index', $trip)
                ->with('success', 'Trip stay deleted successfully.');
        } catch (\Throwable $e) {
            if ($returnTo) {
                return redirect($returnTo)->with('error', 'This trip stay could not be deleted.');
            }

            return redirect()
                ->route('trips.stays.index', $trip)
                ->with('error', 'This trip stay could not be deleted.');
        }
    }

    public function prefillFromPlace(Trip $trip, Request $request)
    {
        $validated = $request->validate([
            'placeid' => ['required', 'integer', 'exists:places,id'],
        ]);

        $place = Place::findOrFail($validated['placeid']);

        return response()->json([
            'fields' => [
                'stayname' => $place->placename,
                'description' => $place->generalnotes,
            ],
            'place' => [
                'id' => $place->id,
                'placename' => $place->placename,
                'placetype' => $place->placetype,
                'locality' => $place->locality,
                'accessnotes' => $place->accessnotes,
                'generalnotes' => $place->generalnotes,
            ],
        ]);
    }

    public function prefillFromPreviousStay(Trip $trip, Request $request)
    {
        $validated = $request->validate([
            'placeid' => ['required', 'integer', 'exists:places,id'],
        ]);

        $previousStay = TripStay::query()
            ->where('placeid', $validated['placeid'])
            ->where('tripid', '!=', $trip->id)
            ->orderByDesc('checkindate')
            ->orderByDesc('id')
            ->first();

        if (!$previousStay) {
            return response()->json([
                'found' => false,
                'message' => 'No previous stay found for this place.',
            ]);
        }

        return response()->json([
            'found' => true,
            'fields' => [
                'stayname' => $previousStay->stayname,
                'staytype' => $previousStay->staytype,
                'isaccommodationpaid' => (bool) $previousStay->isaccommodationpaid,
                'costpernight' => $previousStay->costpernight,
                'estimatedtotalcost' => $previousStay->estimatedtotalcost,
                'travelledfromplaceid' => $previousStay->travelledfromplaceid,
                'distancetravelledkm' => $previousStay->distancetravelledkm,
                'description' => $previousStay->description,
            ],
            'previousStay' => [
                'id' => $previousStay->id,
                'stayname' => $previousStay->stayname,
                'checkindate' => optional($previousStay->checkindate)?->format('Y-m-d'),
            ],
        ]);
    }
}