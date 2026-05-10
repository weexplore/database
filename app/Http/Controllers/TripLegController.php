<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\DestinationItem;
use App\Models\Place;
use App\Models\Trip;
use App\Models\TripLeg;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class TripLegController extends Controller
{
    public function index(Request $request, Trip $trip)
    {
        $query = TripLeg::with(['fromPlace', 'toPlace', 'destination'])
            ->where('tripid', $trip->id);

        if ($request->filled('destination_id')) {
            $query->where('destinationid', $request->integer('destination_id'));
        }

        if ($request->filled('fromplace_id')) {
            $query->where('fromplaceid', $request->integer('fromplace_id'));
        }

        if ($request->filled('toplace_id')) {
            $query->where('toplaceid', $request->integer('toplace_id'));
        }

        $legs = $query->orderBy('legnumber')
            ->orderBy('sortorder')
            ->get();

        $places = Place::orderBy('placename')->get();
        $destinations = Destination::orderBy('destinationname')->get();
        $destinationItems = DestinationItem::query()
            ->with([
                'destination.place',
                'place',
            ])
            ->where('isactive', 1)
            ->orderBy('itemname')
            ->get();
        $vehicles = Vehicle::query()
            ->where('isactive', 1)
            ->orderBy('vehiclename')
            ->orderBy('id')
            ->get();

        $showCreate = $request->boolean('show_create');
        $selectedDestinationId = $request->integer('destination_id');
        $selectedFromPlaceId = $request->integer('fromplace_id');
        $selectedToPlaceId = $request->integer('toplace_id');

        return view('trip-legs.index', compact(
            'trip',
            'legs',
            'places',
            'destinations',
            'destinationItems',
            'vehicles',
            'showCreate',
            'selectedDestinationId',
            'selectedFromPlaceId',
            'selectedToPlaceId'
        ));
    }

    public function create(Request $request, Trip $trip)
    {
        $query = [
            'show_create' => 1,
        ];

        if ($request->filled('destination_id')) {
            $query['destination_id'] = $request->integer('destination_id');
        }

        if ($request->filled('fromplace_id')) {
            $query['fromplace_id'] = $request->integer('fromplace_id');
        }

        if ($request->filled('toplace_id')) {
            $query['toplace_id'] = $request->integer('toplace_id');
        }

        return redirect()->route('trips.legs.index', array_merge(['trip' => $trip->id], $query));
    }

    public function store(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'legnumber' => ['required', 'integer', 'min:1'],
            'startdate' => ['nullable', 'date'],
            'enddate' => ['nullable', 'date', 'after_or_equal:startdate'],
            'nightsplanned' => ['nullable', 'integer', 'min:0'],
            'fromplaceid' => ['nullable', 'integer', 'exists:places,id'],
            'toplaceid' => ['nullable', 'integer', 'exists:places,id'],
            'destinationid' => ['nullable', 'integer', 'exists:destinations,id'],
            'fromdestinationitemid' => ['nullable', 'integer', 'exists:destinationitems,id'],
            'destinationitemid' => ['nullable', 'integer', 'exists:destinationitems,id'],
            'title' => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'distancekm' => ['nullable', 'numeric', 'min:0'],
            'elevationgainm' => ['nullable', 'numeric', 'min:0'],
            'elevationlossm' => ['nullable', 'numeric', 'min:0'],
            'drivingnotes' => ['nullable', 'string'],
            'planningnotes' => ['nullable', 'string'],
            'actualnotes' => ['nullable', 'string'],
            'sortorder' => ['nullable', 'integer', 'min:0'],
            'vehicles' => ['nullable', 'array'],
            'vehicles.*.vehicleid' => ['nullable', 'integer', 'exists:vehicles,id'],
            'vehicles.*.vehiclerole' => ['nullable', 'string', 'max:50'],
            'vehicles.*.sortorder' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['tripid'] = $trip->id;

        $tripLeg = TripLeg::create(collect($validated)->except('vehicles')->toArray());

        $vehicleSync = [];

        foreach (($validated['vehicles'] ?? []) as $row) {
            $vehicleId = $row['vehicleid'] ?? null;

            if (! $vehicleId) {
                continue;
            }

            $vehicleSync[$vehicleId] = [
                'vehiclerole' => $row['vehiclerole'] ?? null,
                'sortorder' => $row['sortorder'] ?? null,
            ];
        }

        $tripLeg->vehicles()->sync($vehicleSync);

        return redirect()
            ->route('trips.legs.index', $trip)
            ->with('success', 'Trip leg created successfully.');
    }

    public function edit(Trip $trip, TripLeg $tripLeg)
    {
        abort_unless((int) $tripLeg->tripid === (int) $trip->id, 404);

        $places = Place::orderBy('placename')->get();
        $destinations = Destination::orderBy('destinationname')->get();
        $destinationItems = DestinationItem::query()
            ->with([
                'destination.place',
                'place',
            ])
            ->where('isactive', 1)
            ->orderBy('itemname')
            ->get();
        $vehicles = Vehicle::query()
            ->where('isactive', 1)
            ->orderBy('vehiclename')
            ->orderBy('id')
            ->get();

        // Eager-load including destinationItem with coords
        $tripLeg->load([
            'fromPlace:id,placename,latitude,longitude',
            'toPlace:id,placename,latitude,longitude',
            'destination:id,destinationname,placeid',
            'fromDestinationItem:id,itemname,latitude,longitude,destinationid,placeid',
            'destinationItem:id,itemname,latitude,longitude,destinationid,placeid',
            'vehicles',
        ]);

        $fromPlace = $tripLeg->fromPlace;
        $toPlace = $tripLeg->toPlace;
        $fromDestinationItem = $tripLeg->fromDestinationItem;
        $destinationItem = $tripLeg->destinationItem;

        $from = null;
        if ($fromDestinationItem && $fromDestinationItem->latitude !== null && $fromDestinationItem->longitude !== null) {
            $from = [
                'id' => $fromDestinationItem->id,
                'name' => $fromDestinationItem->itemname,
                'lat' => (float) $fromDestinationItem->latitude,
                'lng' => (float) $fromDestinationItem->longitude,
            ];
        } elseif ($fromPlace && $fromPlace->latitude !== null && $fromPlace->longitude !== null) {
            $from = [
                'id' => $fromPlace->id,
                'name' => $fromPlace->placename,
                'lat' => (float) $fromPlace->latitude,
                'lng' => (float) $fromPlace->longitude,
            ];
        }

        $to = null;
        if ($destinationItem && $destinationItem->latitude !== null && $destinationItem->longitude !== null) {
            $to = [
                'id' => $destinationItem->id,
                'name' => $destinationItem->itemname,
                'lat' => (float) $destinationItem->latitude,
                'lng' => (float) $destinationItem->longitude,
            ];
        } elseif ($toPlace && $toPlace->latitude !== null && $toPlace->longitude !== null) {
            $to = [
                'id' => $toPlace->id,
                'name' => $toPlace->placename,
                'lat' => (float) $toPlace->latitude,
                'lng' => (float) $toPlace->longitude,
            ];
        }

        $tripLegMap = [
            'from' => $from,
            'to' => $to,
        ];

        return view('trip-legs.edit', compact('trip', 'tripLeg', 'places', 'destinations', 'destinationItems', 'vehicles', 'tripLegMap'));
    }

    public function update(Request $request, Trip $trip, TripLeg $tripLeg)
    {
        abort_unless((int) $tripLeg->tripid === (int) $trip->id, 404);

        $validated = $request->validate([
            'legnumber' => ['required', 'integer', 'min:1'],
            'startdate' => ['nullable', 'date'],
            'enddate' => ['nullable', 'date', 'after_or_equal:startdate'],
            'nightsplanned' => ['nullable', 'integer', 'min:0'],
            'fromplaceid' => ['nullable', 'integer', 'exists:places,id'],
            'toplaceid' => ['nullable', 'integer', 'exists:places,id'],
            'destinationid' => ['nullable', 'integer', 'exists:destinations,id'],
            'destinationitemid' => ['nullable', 'integer', 'exists:destinationitems,id'], // NEW
            'fromdestinationitemid' => ['nullable', 'integer', 'exists:destinationitems,id'],
            'title' => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'distancekm' => ['nullable', 'numeric', 'min:0'],
            'elevationgainm' => ['nullable', 'numeric', 'min:0'],
            'elevationlossm' => ['nullable', 'numeric', 'min:0'],
            'drivingnotes' => ['nullable', 'string'],
            'planningnotes' => ['nullable', 'string'],
            'actualnotes' => ['nullable', 'string'],
            'sortorder' => ['nullable', 'integer', 'min:0'],
            'vehicles' => ['nullable', 'array'],
            'vehicles.*.vehicleid' => ['nullable', 'integer', 'exists:vehicles,id'],
            'vehicles.*.vehiclerole' => ['nullable', 'string', 'max:50'],
            'vehicles.*.sortorder' => ['nullable', 'integer', 'min:0'],
        ]);

        $tripLeg->update(collect($validated)->except('vehicles')->toArray());

        $vehicleSync = [];

        foreach (($validated['vehicles'] ?? []) as $row) {
            $vehicleId = $row['vehicleid'] ?? null;

            if (! $vehicleId) {
                continue;
            }

            $vehicleSync[$vehicleId] = [
                'vehiclerole' => $row['vehiclerole'] ?? null,
                'sortorder' => $row['sortorder'] ?? null,
            ];
        }

        $tripLeg->vehicles()->sync($vehicleSync);

        return redirect()
            ->route('trips.legs.index', $trip)
            ->with('success', 'Trip leg updated successfully.');
    }

    public function destroy(Trip $trip, TripLeg $tripLeg)
    {
        abort_unless((int) $tripLeg->tripid === (int) $trip->id, 404);

        try {
            $tripLeg->delete();

            return redirect()
                ->route('trips.legs.index', $trip)
                ->with('success', 'Trip leg deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('trips.legs.index', $trip)
                ->with('error', 'This trip leg could not be deleted.');
        }
    }
}