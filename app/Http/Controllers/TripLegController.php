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
        $query = TripLeg::with([
            'fromPlace',
            'fromDestination',
            'fromDestinationItem',
            'toPlace',
            'toDestination',
            'toDestinationItem',
        ])->where('tripid', $trip->id);

        if ($request->filled('fromdestination_id')) {
            $query->where('fromdestinationid', $request->integer('fromdestination_id'));
        }

        if ($request->filled('todestination_id')) {
            $query->where('todestinationid', $request->integer('todestination_id'));
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
        $selectedFromDestinationId = $request->integer('fromdestination_id');
        $selectedFromPlaceId = $request->integer('fromplace_id');
        $selectedToDestinationId = $request->integer('todestination_id');
        $selectedToPlaceId = $request->integer('toplace_id');

        return view('trip-legs.index', compact(
            'trip',
            'legs',
            'places',
            'destinations',
            'destinationItems',
            'vehicles',
            'showCreate',
            'selectedFromDestinationId',
            'selectedFromPlaceId',
            'selectedToDestinationId',
            'selectedToPlaceId'
        ));
    }

    public function create(Request $request, Trip $trip)
    {
        $query = [
            'show_create' => 1,
        ];

        if ($request->filled('fromdestination_id')) {
            $query['fromdestination_id'] = $request->integer('fromdestination_id');
        }

        if ($request->filled('fromplace_id')) {
            $query['fromplace_id'] = $request->integer('fromplace_id');
        }

        if ($request->filled('todestination_id')) {
            $query['todestination_id'] = $request->integer('todestination_id');
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
            'fromdestinationid' => ['nullable', 'integer', 'exists:destinations,id'],
            'fromdestinationitemid' => ['nullable', 'integer', 'exists:destinationitems,id'],
            'toplaceid' => ['nullable', 'integer', 'exists:places,id'],
            'todestinationid' => ['nullable', 'integer', 'exists:destinations,id'],
            'todestinationitemid' => ['nullable', 'integer', 'exists:destinationitems,id'],
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

        $trip->load([
            'tripVehicles.vehicle',
        ]);

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

        $tripLeg->load([
            'fromPlace:id,placename,latitude,longitude',
            'toPlace:id,placename,latitude,longitude',
            'fromDestination:id,destinationname,placeid',
            'toDestination:id,destinationname,placeid',
            'fromDestinationItem:id,itemname,latitude,longitude,destinationid,placeid',
            'toDestinationItem:id,itemname,latitude,longitude,destinationid,placeid',
            'vehicles',
            'legPoints',
        ]);

        $fromPlace = $tripLeg->fromPlace;
        $toPlace = $tripLeg->toPlace;
        $fromDestinationItem = $tripLeg->fromDestinationItem;
        $toDestinationItem = $tripLeg->toDestinationItem;

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
        if ($toDestinationItem && $toDestinationItem->latitude !== null && $toDestinationItem->longitude !== null) {
            $to = [
                'id' => $toDestinationItem->id,
                'name' => $toDestinationItem->itemname,
                'lat' => (float) $toDestinationItem->latitude,
                'lng' => (float) $toDestinationItem->longitude,
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

        $selectedFromDestinationId = $tripLeg->fromdestinationid;
        $selectedFromPlaceId = $tripLeg->fromplaceid;
        $selectedToDestinationId = $tripLeg->todestinationid;
        $selectedToPlaceId = $tripLeg->toplaceid;

        return view('trip-legs.edit', compact(
            'trip',
            'tripLeg',
            'places',
            'destinations',
            'destinationItems',
            'vehicles',
            'tripLegMap',
            'selectedFromDestinationId',
            'selectedFromPlaceId',
            'selectedToDestinationId',
            'selectedToPlaceId'
        ));
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
            'fromdestinationid' => ['nullable', 'integer', 'exists:destinations,id'],
            'fromdestinationitemid' => ['nullable', 'integer', 'exists:destinationitems,id'],
            'toplaceid' => ['nullable', 'integer', 'exists:places,id'],
            'todestinationid' => ['nullable', 'integer', 'exists:destinations,id'],
            'todestinationitemid' => ['nullable', 'integer', 'exists:destinationitems,id'],
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
            'leg_points' => ['nullable', 'array'],
            'leg_points.*.id' => ['nullable', 'integer'],
            'leg_points.*.sequence_no' => ['nullable', 'integer', 'min:1'],
            'leg_points.*.pointtype' => ['nullable', 'string', 'max:50'],
            'leg_points.*.title' => ['nullable', 'string', 'max:255'],
            'leg_points.*.placeid' => ['nullable', 'integer', 'exists:places,id'],
            'leg_points.*.destinationid' => ['nullable', 'integer', 'exists:destinations,id'],
            'leg_points.*.destinationitemid' => ['nullable', 'integer', 'exists:destinationitems,id'],
            'leg_points.*.notes' => ['nullable', 'string'],
        ]);

        $tripLeg->update(collect($validated)->except('vehicles', 'leg_points')->toArray());

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

        $legPointRows = collect($validated['leg_points'] ?? [])
            ->filter(function ($row) {
                return !empty($row['placeid'])
                    || !empty($row['destinationitemid'])
                    || !empty($row['title'])
                    || !empty($row['notes']);
            })
            ->values();

        $existingIds = $tripLeg->legPoints()->pluck('id')->all();
        $submittedIds = $legPointRows->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();

        $idsToDelete = array_diff($existingIds, $submittedIds);

        if (!empty($idsToDelete)) {
            $tripLeg->legPoints()->whereIn('id', $idsToDelete)->delete();
        }

        foreach ($legPointRows as $index => $row) {
            $payload = [
                'sequence_no' => $row['sequence_no'] ?? ($index + 1),
                'pointtype' => $row['pointtype'] ?? 'route_anchor',
                'title' => $row['title'] ?? null,
                'placeid' => $row['placeid'] ?: null,
                'destinationid' => $row['destinationid'] ?: null,
                'destinationitemid' => $row['destinationitemid'] ?: null,
                'notes' => $row['notes'] ?? null,
            ];

            if (!empty($row['id'])) {
                $tripLeg->legPoints()->where('id', $row['id'])->update($payload);
            } else {
                $tripLeg->legPoints()->create($payload);
            }
        }

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