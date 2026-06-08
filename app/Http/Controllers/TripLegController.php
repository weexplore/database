<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\DestinationItem;
use App\Models\Place;
use App\Models\Trip;
use App\Models\TripItem;
use App\Models\TripLeg;
use App\Models\Vehicle;
use App\Services\RouteDiscoveryProfileResolver;
use App\Services\RouteDiscoveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TripLegController extends Controller
{
    public function __construct(
        private RouteDiscoveryService $routeDiscoveryService,
        private RouteDiscoveryProfileResolver $routeDiscoveryProfileResolver
    ) {
    }

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

    public function show(Trip $trip, TripLeg $tripLeg)
    {
        abort_unless((int) $tripLeg->tripid === (int) $trip->id, 404);

        $tripLeg->load([
            'trip',
            'fromPlace',
            'fromDestination.place',
            'fromDestinationItem.destination.place',
            'fromDestinationItem.place',
            'toPlace',
            'toDestination.place',
            'toDestinationItem.destination.place',
            'toDestinationItem.place',
            'legPoints' => fn ($query) => $query
                ->with([
                    'place',
                    'destination.place',
                    'destinationItem.destination.place',
                    'destinationItem.place',
                ])
                ->orderBy('sequence_no'),
            'destination.place',
            'tripStays.place',
            'tripFuelEstimates.fuelStop.place',
            'tripFuelEstimates.place',
            'tripFuelEstimates.sourceObservation',
        ]);

        $routeSuggestions = $this->routeDiscoveryService->buildTripLegSuggestions($tripLeg);

        $alongRoute = $routeSuggestions;
        $alongRoutePlaces = $routeSuggestions['places'] ?? collect();
        $alongRouteMessage = $routeSuggestions['message'] ?? null;
        $alongRouteHasRoute = (bool) ($routeSuggestions['hasRoute'] ?? false);
        $alongRouteBufferKm = $routeSuggestions['bufferKm'] ?? null;
        $alongRouteRoutePoints = $routeSuggestions['routePoints'] ?? collect();
        $alongRouteRouteSegments = $routeSuggestions['routeSegments'] ?? collect();
        $alongRouteRoutePointCount = $routeSuggestions['routePointCount'] ?? 0;
        $alongRouteRouteSegmentCount = $routeSuggestions['routeSegmentCount'] ?? 0;
        $alongRouteRouteDistanceKm = $routeSuggestions['routeDistanceKm'] ?? 0.0;

        return view('trip-legs.show', compact(
            'trip',
            'tripLeg',
            'routeSuggestions',
            'alongRoute',
            'alongRoutePlaces',
            'alongRouteMessage',
            'alongRouteHasRoute',
            'alongRouteBufferKm',
            'alongRouteRoutePoints',
            'alongRouteRouteSegments',
            'alongRouteRoutePointCount',
            'alongRouteRouteSegmentCount',
            'alongRouteRouteDistanceKm'
        ));
    }

    public function addPlacePoint(Request $request, Trip $trip, TripLeg $tripLeg)
    {
        abort_unless((int) $tripLeg->tripid === (int) $trip->id, 404);

        $validated = $request->validate([
            'placeid' => ['required', 'integer', 'exists:places,id'],
            'pointtype' => ['nullable', 'string', 'max:50'],
        ]);

        $alreadyExists = $tripLeg->legPoints()
            ->where('placeid', $validated['placeid'])
            ->exists();

        if ($alreadyExists) {
            return redirect()
                ->route('trips.legs.edit', [$trip, $tripLeg])
                ->with('error', 'That place is already linked as a leg point.');
        }

        $place = Place::findOrFail($validated['placeid']);
        $nextSequence = ((int) $tripLeg->legPoints()->max('sequence_no')) + 1;

        $tripLeg->legPoints()->create([
            'sequence_no' => $nextSequence,
            'pointtype' => $validated['pointtype'] ?: 'plannedstop',
            'placeid' => $place->id,
            'title' => $place->placename,
        ]);

        return redirect()
            ->route('trips.legs.edit', [$trip, $tripLeg])
            ->with('success', 'Place added as a leg point.');
    }

    public function addDestinationItem(Request $request, Trip $trip, TripLeg $tripLeg)
    {
        abort_unless((int) $tripLeg->tripid === (int) $trip->id, 404);

        $validated = $request->validate([
            'destinationitemid' => ['required', 'integer', 'exists:destinationitems,id'],
        ]);

        $alreadyExists = TripItem::query()
            ->where('tripid', $trip->id)
            ->where('triplegid', $tripLeg->id)
            ->where('destinationitemid', $validated['destinationitemid'])
            ->exists();

        if ($alreadyExists) {
            return redirect()
                ->route('trips.legs.edit', [$trip, $tripLeg])
                ->with('error', 'That destination item is already linked to this leg.');
        }

        $item = DestinationItem::with(['destination.place', 'place'])->findOrFail($validated['destinationitemid']);

        $resolvedPlace = $item->place ?: optional($item->destination)->place;
        $resolvedDestination = $item->destination;
        $resolvedType = $item->itemtype ?: 'activity';

        $title = trim($item->itemname ?: 'Trip Item');

        $contextBits = collect([
            $resolvedDestination?->destinationname,
            $resolvedPlace?->placename,
        ])->filter()->unique()->values()->all();

        $descriptionParts = collect([
            $item->shortdescription ?: null,
            !empty($contextBits) ? 'Location: ' . implode(' · ', $contextBits) : null,
        ])->filter()->values()->all();

        TripItem::create([
            'tripid' => $trip->id,
            'triplegid' => $tripLeg->id,
            'placeid' => $resolvedPlace?->id,
            'destinationid' => $resolvedDestination?->id,
            'destinationitemid' => $item->id,
            'itemname' => $title,
            'title' => $title,
            'itemtype' => $resolvedType,
            'description' => implode("\n\n", $descriptionParts),
            'itemstatus' => 'planned',
            'sortorder' => ((int) TripItem::query()
                ->where('tripid', $trip->id)
                ->where('triplegid', $tripLeg->id)
                ->max('sortorder')) + 1,
        ]);

        return redirect()
            ->route('trips.legs.edit', [$trip, $tripLeg])
            ->with('success', 'Destination item added as a trip item.');
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
            'triplegsearchprofileid' => [
                'nullable',
                'integer',
                \Illuminate\Validation\Rule::exists('trip_leg_search_profiles', 'id')
                    ->where(function ($query) use ($trip) {
                        $query->where(function ($q) use ($trip) {
                            $q->whereNull('tripid')
                            ->orWhere('tripid', $trip->id);
                        });
                    }),
            ],
        ]);

        $validated['tripid'] = $trip->id;

        $tripLeg = TripLeg::create(collect($validated)->except('vehicles')->toArray());

        $vehicleSync = [];

        foreach (($validated['vehicles'] ?? []) as $row) {
            $vehicleId = $row['vehicleid'] ?? null;

            if (!$vehicleId) {
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

    public function edit(Request $request, Trip $trip, TripLeg $tripLeg)
    {
        abort_unless((int) $tripLeg->tripid === (int) $trip->id, 404);

        $trip->loadMissing([
            'tripLegSearchProfiles',
            'tripLegSearchProfile',
        ]);

        $tripLeg->load([
            'trip.searchProfiles',
            'trip.selectedSearchProfile',
            'searchProfile',
            'trip',
            'fromPlace',
            'toPlace',
            'destination',
            'fromDestination',
            'toDestination',
            'fromDestinationItem.destination.place',
            'fromDestinationItem.place',
            'toDestinationItem.destination.place',
            'toDestinationItem.place',
            'legPoints' => fn ($query) => $query
                ->with(['place', 'destination.place', 'destinationItem.destination.place', 'destinationItem.place'])
                ->orderBy('sequence_no'),
            'vehicles',
        ]);

        $places = Place::orderBy('placename')->get();
        $destinations = Destination::with('place')->orderBy('destinationname')->get();
        $destinationItems = DestinationItem::with(['destination.place', 'place'])
            ->where('isactive', 1)
            ->orderBy('itemname')
            ->get();
        $vehicles = Vehicle::query()->where('isactive', 1)->orderBy('vehiclename')->get();
        $searchProfiles = \App\Models\TripLegSearchProfile::query()
            ->where('isactive', 1)
            ->where(function ($query) use ($trip) {
                $query->whereNull('tripid')
                    ->orWhere('tripid', $trip->id);
            })
            ->orderByDesc('isdefault')
            ->orderBy('profiletype')
            ->orderBy('profilename')
            ->orderBy('id')
            ->get();

        $existingTripItems = TripItem::query()
            ->where('tripid', $trip->id)
            ->where('triplegid', $tripLeg->id)
            ->with(['place', 'booking', 'destination', 'destinationItem.destination'])
            ->orderByRaw('COALESCE(itemdate, startdatetime)')
            ->orderBy('id')
            ->get();

        $existingLegPointPlaceIds = $tripLeg->legPoints->pluck('placeid')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
        $existingLegPointDestinationItemIds = $tripLeg->legPoints->pluck('destinationitemid')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
        $existingTripItemPlaceIds = $existingTripItems->pluck('placeid')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
        $existingTripItemDestinationItemIds = $existingTripItems->pluck('destinationitemid')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();

        $resolvedSearchProfile = $this->routeDiscoveryProfileResolver->forTripLeg($tripLeg);
        $alongRoute = $this->routeDiscoveryService->buildTripLegSuggestions($tripLeg, $resolvedSearchProfile);

        $selectedFromDestinationId = old('fromdestinationid', $tripLeg->fromdestinationid);
        $selectedFromPlaceId = old('fromplaceid', $tripLeg->fromplaceid);
        $selectedToDestinationId = old('todestinationid', $tripLeg->todestinationid);
        $selectedToPlaceId = old('toplaceid', $tripLeg->toplaceid);
        $selectedFromDestinationItemId = old('fromdestinationitemid', $tripLeg->fromdestinationitemid);
        $selectedToDestinationItemId = old('todestinationitemid', $tripLeg->todestinationitemid);
        $selectedSearchProfileId = old('triplegsearchprofileid', $tripLeg->triplegsearchprofileid);
        $tripSelectedSearchProfileId = old('trip_triplegsearchprofileid', $trip->triplegsearchprofileid);

        $selectedLegPoints = old('legpoints');
        if ($selectedLegPoints === null) {
            $selectedLegPoints = old('leg_points');
        }
        if ($selectedLegPoints === null) {
            $selectedLegPoints = $tripLeg->legPoints
                ->sortBy('sequence_no')
                ->map(function ($point) {
                    return [
                        'id' => $point->id,
                        'sequenceno' => $point->sequence_no,
                        'sequence_no' => $point->sequence_no,
                        'pointtype' => $point->pointtype,
                        'placeid' => $point->placeid,
                        'destinationid' => $point->destinationid,
                        'destinationitemid' => $point->destinationitemid,
                        'title' => $point->title,
                        'notes' => $point->notes,
                    ];
                })
                ->values()
                ->all();
        }
        $selectedLegPoints = is_array($selectedLegPoints) ? array_values($selectedLegPoints) : [];

        $selectedVehicles = old('vehicles');
        if ($selectedVehicles === null && $tripLeg->relationLoaded('vehicles') && $tripLeg->vehicles->isNotEmpty()) {
            $selectedVehicles = $tripLeg->vehicles
                ->map(function ($vehicle) {
                    return [
                        'vehicleid' => $vehicle->id,
                        'vehiclerole' => $vehicle->pivot->vehiclerole,
                        'sortorder' => $vehicle->pivot->sortorder,
                    ];
                })
                ->values()
                ->all();
        }
        $selectedVehicles = is_array($selectedVehicles) ? array_values($selectedVehicles) : [];

        $alongRoutePlaces = $alongRoute['places'] ?? collect();
        $alongRouteMessage = $alongRoute['message'] ?? null;
        $alongRouteHasRoute = (bool) ($alongRoute['hasRoute'] ?? false);
        $alongRouteBufferKm = $alongRoute['bufferKm'] ?? null;
        $alongRouteRoutePoints = $alongRoute['routePoints'] ?? collect();
        $alongRouteRouteSegments = $alongRoute['routeSegments'] ?? collect();
        $alongRouteRoutePointCount = $alongRoute['routePointCount'] ?? 0;
        $alongRouteRouteSegmentCount = $alongRoute['routeSegmentCount'] ?? 0;
        $alongRouteRouteDistanceKm = $alongRoute['routeDistanceKm'] ?? 0.0;
        $alongRouteSearchPasses = $alongRoute['searchPasses'] ?? collect();
        $alongRouteFinalRadiusKm = $alongRoute['finalRadiusKm'] ?? null;
        $alongRouteMinimumResults = $alongRoute['minimumResults'] ?? null;

        return view('trip-legs.edit', compact(
            'trip',
            'tripLeg',
            'places',
            'destinations',
            'destinationItems',
            'vehicles',
            'searchProfiles',
            'resolvedSearchProfile',
            'selectedSearchProfileId',
            'tripSelectedSearchProfileId',
            'existingTripItems',
            'existingLegPointPlaceIds',
            'existingLegPointDestinationItemIds',
            'existingTripItemPlaceIds',
            'existingTripItemDestinationItemIds',
            'alongRoute',
            'alongRoutePlaces',
            'alongRouteMessage',
            'alongRouteHasRoute',
            'alongRouteBufferKm',
            'alongRouteRoutePoints',
            'alongRouteRouteSegments',
            'alongRouteRoutePointCount',
            'alongRouteRouteSegmentCount',
            'alongRouteRouteDistanceKm',
            'alongRouteSearchPasses',
            'alongRouteFinalRadiusKm',
            'alongRouteMinimumResults',
            'selectedFromDestinationId',
            'selectedFromPlaceId',
            'selectedToDestinationId',
            'selectedToPlaceId',
            'selectedFromDestinationItemId',
            'selectedToDestinationItemId',
            'selectedLegPoints',
            'selectedVehicles'
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
            'triplegsearchprofileid' => [
                'nullable',
                'integer',
                \Illuminate\Validation\Rule::exists('trip_leg_search_profiles', 'id')
                    ->where(function ($query) use ($trip) {
                        $query->where(function ($q) use ($trip) {
                            $q->whereNull('tripid')
                            ->orWhere('tripid', $trip->id);
                        });
                    }),
            ],
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
            'legpoints' => ['nullable', 'array'],
            'legpoints.*.id' => ['nullable', 'integer'],
            'legpoints.*.sequenceno' => ['nullable', 'integer', 'min:1'],
            'legpoints.*.pointtype' => ['nullable', 'string', 'max:50'],
            'legpoints.*.title' => ['nullable', 'string', 'max:255'],
            'legpoints.*.placeid' => ['nullable', 'integer', 'exists:places,id'],
            'legpoints.*.destinationid' => ['nullable', 'integer', 'exists:destinations,id'],
            'legpoints.*.destinationitemid' => ['nullable', 'integer', 'exists:destinationitems,id'],
            'legpoints.*.notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $tripLeg) {
            $tripLeg->update(collect($validated)->except('vehicles', 'leg_points', 'legpoints')->toArray());

            $vehicleSync = [];
            foreach (($validated['vehicles'] ?? []) as $row) {
                $vehicleId = $row['vehicleid'] ?? null;
                if (!$vehicleId) {
                    continue;
                }
                $vehicleSync[$vehicleId] = [
                    'vehiclerole' => $row['vehiclerole'] ?? null,
                    'sortorder' => $row['sortorder'] ?? null,
                ];
            }
            $tripLeg->vehicles()->sync($vehicleSync);

            $submittedLegPoints = $validated['leg_points'] ?? $validated['legpoints'] ?? [];
            $legPointRows = collect($submittedLegPoints)
                ->filter(fn ($row) => !empty($row['placeid']) || !empty($row['destinationid']) || !empty($row['destinationitemid']) || !empty($row['title']) || !empty($row['notes']))
                ->values()
                ->map(function ($row, $index) {
                    return [
                        'id' => $row['id'] ?? null,
                        'sequence_no' => $index + 1,
                        'pointtype' => $row['pointtype'] ?? 'routeanchor',
                        'title' => $row['title'] ?? null,
                        'placeid' => $row['placeid'] ?: null,
                        'destinationid' => $row['destinationid'] ?: null,
                        'destinationitemid' => $row['destinationitemid'] ?: null,
                        'notes' => $row['notes'] ?? null,
                    ];
                });

            $existingIds = $tripLeg->legPoints()->pluck('id')->map(fn ($id) => (int) $id)->all();
            $submittedExistingIds = $legPointRows->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();
            $idsToDelete = array_values(array_diff($existingIds, $submittedExistingIds));

            if (!empty($submittedExistingIds)) {
                foreach ($submittedExistingIds as $offset => $id) {
                    $tripLeg->legPoints()->where('id', $id)->update(['sequence_no' => 1000 + $offset]);
                }
            }

            foreach ($legPointRows as $row) {
                $payload = [
                    'sequence_no' => $row['sequence_no'],
                    'pointtype' => $row['pointtype'],
                    'title' => $row['title'],
                    'placeid' => $row['placeid'],
                    'destinationid' => $row['destinationid'],
                    'destinationitemid' => $row['destinationitemid'],
                    'notes' => $row['notes'],
                ];

                if (!empty($row['id'])) {
                    $tripLeg->legPoints()->where('id', $row['id'])->update($payload);
                } else {
                    $tripLeg->legPoints()->create($payload);
                }
            }

            if (!empty($idsToDelete)) {
                $tripLeg->legPoints()->whereIn('id', $idsToDelete)->delete();
            }
        });

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

    public function reorder(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'ordered_ids' => ['required', 'array', 'min:1'],
            'ordered_ids.*' => ['integer'],
        ]);

        $orderedIds = collect($validated['ordered_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $existingIds = TripLeg::query()
            ->where('tripid', $trip->id)
            ->whereIn('id', $orderedIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($existingIds->count() !== $orderedIds->count()) {
            return response()->json([
                'message' => 'One or more trip legs were invalid for this trip.',
            ], 422);
        }

        DB::transaction(function () use ($trip, $orderedIds) {
            $temporaryOffset = 100000;

            foreach ($orderedIds as $index => $id) {
                TripLeg::query()
                    ->where('tripid', $trip->id)
                    ->where('id', $id)
                    ->update([
                        'legnumber' => $temporaryOffset + $index + 1,
                        'sortorder' => $temporaryOffset + $index + 1,
                    ]);
            }

            foreach ($orderedIds as $index => $id) {
                TripLeg::query()
                    ->where('tripid', $trip->id)
                    ->where('id', $id)
                    ->update([
                        'legnumber' => $index + 1,
                        'sortorder' => $index + 1,
                    ]);
            }
        });

        return response()->json([
            'message' => 'Trip leg order updated successfully.',
        ]);
    }
}