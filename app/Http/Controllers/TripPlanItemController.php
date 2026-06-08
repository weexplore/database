<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\DestinationItem;
use App\Models\Place;
use App\Models\Trip;
use App\Models\TripPlanItem;
use App\Models\TripLeg;
use App\Models\TripStay;
use App\Models\TripItem;
use App\Models\TripLegPoint;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class TripPlanItemController extends Controller
{
    private function planTypeOptions(): array
    {
        return [
            'place' => 'Place',
            'destination' => 'Destination',
            'destination_item' => 'Destination Item',
            'route_anchor' => 'Route Anchor',
            'overnight' => 'Overnight',
            'activity' => 'Activity',
            'fuel' => 'Fuel',
            'detour' => 'Detour',
            'note' => 'Note',
        ];
    }

    private function stayTypeOptions(): array
    {
        return [
            'caravan_park' => 'Caravan Park',
            'free_camp' => 'Free Camp',
            'showgrounds' => 'Showgrounds',
            'station_stay' => 'Station Stay',
            'campground' => 'Campground',
            'motel' => 'Motel',
            'farm_stay' => 'Farm Stay',
            'friends_family' => 'Friends / Family',
            'roadside_stop' => 'Roadside Stop',
            'other' => 'Other',
        ];
    }

    public function index(Request $request, Trip $trip)
{
    $planItems = TripPlanItem::query()
        ->with([
            'place',
            'destination.place',
            'destinationItem.place',
            'destinationItem.destination.place',
            'tripLeg',
            'tripStay',
        ])
        ->where('tripid', $trip->id)
        ->orderByRaw('planneddate IS NULL, planneddate ASC')
        // Within the same day: items with no end date first, then ones with an end date
        ->orderByRaw('plannedenddate IS NOT NULL, plannedenddate ASC')
        ->orderBy('sequence_no')
        ->orderBy('id')
        ->get();

    // For the Planning sequence table, hide route anchors that duplicate a stay at the same stop/date.
    $stayBoundaryKeys = $planItems
        ->filter(fn ($item) => (bool) $item->tripstayid)
        ->map(function ($item) {
            $resolvedPlaceId = $item->placeid ?: 0;

            if (! $resolvedPlaceId && $item->destinationitemid) {
                $destinationItem = $item->destinationItem;
                $resolvedPlaceId = (int) ($destinationItem?->placeid ?: $destinationItem?->destination?->placeid ?: 0);
            }

            if (! $resolvedPlaceId && $item->destinationid) {
                $destination = $item->destination;
                $resolvedPlaceId = (int) ($destination?->placeid ?: 0);
            }

            $dateKey = optional($item->planneddate)->format('Y-m-d') ?: '';

            return implode('|', [$dateKey, $resolvedPlaceId]);
        })
        ->filter()
        ->unique()
        ->values();

    $planningSequenceItems = $planItems->filter(function ($item) use ($stayBoundaryKeys) {
        if (! $item->isrouteanchor) {
            return true;
        }

        // Anchor: hide if its boundary matches a stay boundary (same place/date).
        $resolvedPlaceId = $item->placeid ?: 0;

        if (! $resolvedPlaceId && $item->destinationitemid) {
            $destinationItem = $item->destinationItem;
            $resolvedPlaceId = (int) ($destinationItem?->placeid ?: $destinationItem?->destination?->placeid ?: 0);
        }

        if (! $resolvedPlaceId && $item->destinationid) {
            $destination = $item->destination;
            $resolvedPlaceId = (int) ($destination?->placeid ?: 0);
        }

        $dateKey = optional($item->planneddate)->format('Y-m-d') ?: '';

        $key = implode('|', [$dateKey, $resolvedPlaceId]);

        return ! $stayBoundaryKeys->contains($key);
    })->values();

    $places = Place::query()
        ->orderBy('placename')
        ->get();

    $destinations = Destination::query()
        ->with('place')
        ->orderBy('destinationname')
        ->get();

    $destinationItems = DestinationItem::query()
        ->with(['place', 'destination.place'])
        ->where('isactive', 1)
        ->orderBy('itemname')
        ->get();

    $showCreate = $request->boolean('show_create');
    $planTypeOptions = $this->planTypeOptions();
    $stayTypeOptions = $this->stayTypeOptions();

    $selectedPlanType = $request->input('plantype');
    $selectedPlaceId = $request->integer('place_id');
    $selectedDestinationId = $request->integer('destination_id');
    $selectedDestinationItemId = $request->integer('destinationitem_id');

    $mapItems = $planItems->map(function ($item) {
        $resolvedPlace = $item->place
            ?? $item->destinationItem?->place
            ?? $item->destinationItem?->destination?->place
            ?? $item->destination?->place;

        $resolvedDestination = $item->destination
            ?? $item->destinationItem?->destination;

        $resolvedDestinationItem = $item->destinationItem;

        $latitude = $resolvedDestinationItem->latitude
            ?? $resolvedDestination?->latitude
            ?? $resolvedPlace?->latitude;

        $longitude = $resolvedDestinationItem->longitude
            ?? $resolvedDestination?->longitude
            ?? $resolvedPlace?->longitude;

        return [
            'id' => $item->id,
            'sequence_no' => $item->sequence_no,
            'title' => $item->display_title,
            'plantype' => $item->plantype,
            'planneddate' => optional($item->planneddate)->format('Y-m-d'),
            'place_name' => $resolvedPlace?->placename,
            'destination_name' => $resolvedDestination?->destinationname,
            'destination_item_name' => $resolvedDestinationItem?->itemname,
            'latitude' => $latitude !== null ? (float) $latitude : null,
            'longitude' => $longitude !== null ? (float) $longitude : null,
            'isrouteanchor' => (bool) $item->isrouteanchor,
            'isgovia' => (bool) $item->isgovia,
            'isovernight' => (bool) $item->isovernight,
            'isstaytarget' => (bool) $item->isstaytarget,
            'triplegid' => $item->triplegid,
            'tripstayid' => $item->tripstayid,
            
        ];
    })->values();



    $candidates = $this->buildGenerationCandidates($trip);

    $candidateLegs = $candidates['candidateLegs']->map(function ($leg) {
    $fromItem = $leg['from_item'];
    $toItem = $leg['to_item'];

    $fromCoordinates = $this->resolvePlanningItemCoordinates($fromItem);
    $toCoordinates = $this->resolvePlanningItemCoordinates($toItem);

    $straightLineKm = null;
    $estimatedRoadKm = null;
    $estimatedMinutes = null;
    $estimatedTimeLabel = null;

    if ($fromCoordinates['has_coordinates'] && $toCoordinates['has_coordinates']) {
        $straightLineKm = $this->haversineDistanceKm(
            $fromCoordinates['latitude'],
            $fromCoordinates['longitude'],
            $toCoordinates['latitude'],
            $toCoordinates['longitude']
        );

        $estimatedRoadKm = $this->estimateRoadDistanceKm($straightLineKm);
        $estimatedMinutes = $this->estimateCaravanDrivingMinutes($estimatedRoadKm);
        $estimatedTimeLabel = $this->formatDrivingTime($estimatedMinutes);
    }

return [
    'from_item_id' => $fromItem->id,
    'to_item_id' => $toItem->id,
    'from_label' => $leg['from_label'],
    'to_label' => $leg['to_label'],
    'from_sequence' => $leg['from_sequence'],
    'to_sequence' => $leg['to_sequence'],
    'start_date' => optional($leg['start_date'])->format('d M Y'),
    'end_date' => optional($leg['end_date'])->format('d M Y'),
    'from_has_coordinates' => $fromCoordinates['has_coordinates'],
    'to_has_coordinates' => $toCoordinates['has_coordinates'],
    'straight_line_km' => $straightLineKm !== null ? round($straightLineKm, 1) : null,
    'estimated_road_km' => $estimatedRoadKm !== null ? round($estimatedRoadKm, 1) : null,
    'estimated_minutes' => $estimatedMinutes,
    'estimated_time_label' => $estimatedTimeLabel,
];
})->values();

        $candidateLegBoundaries = $candidates['candidateLegBoundaries'];
        $estimatedDistanceKm = round(
            $candidateLegs->sum(fn ($leg) => (float) ($leg['estimated_road_km'] ?? 0)),
            1
        );

        $estimatedDriveMinutes = (int) $candidateLegs->sum(
            fn ($leg) => (int) ($leg['estimated_minutes'] ?? 0)
        );

    $summary = [
        'total_items' => $planItems->count(),
        'mapped_items' => $mapItems->filter(fn ($item) => !is_null($item['latitude']) && !is_null($item['longitude']))->count(),
        'missing_coordinates' => $mapItems->filter(fn ($item) => is_null($item['latitude']) || is_null($item['longitude']))->count(),
        'route_anchors' => $planItems->where('isrouteanchor', true)->count(),
        'go_via' => $planItems->where('isgovia', true)->count(),
        'overnights' => $planItems->where('isovernight', true)->count(),
        'stay_targets' => $planItems->where('isstaytarget', true)->count(),
        'generated_legs' => $planItems->whereNotNull('triplegid')->count(),
        'generated_stays' => $planItems->whereNotNull('tripstayid')->count(),
        'estimated_distance_km' => $estimatedDistanceKm,
        'estimated_drive_minutes' => $estimatedDriveMinutes,
        'estimated_drive_time_label' => $estimatedDriveMinutes > 0
            ? $this->formatDrivingTime($estimatedDriveMinutes)
            : '0 min',
    ];

    return view('trip-planner.index', compact(
        'trip',
        'planItems',
        'places',
        'destinations',
        'destinationItems',
        'showCreate',
        'planTypeOptions',
        'stayTypeOptions',
        'selectedPlanType',
        'selectedPlaceId',
        'selectedDestinationId',
        'selectedDestinationItemId',
        'mapItems',
        'summary',
        'candidateLegs',
        'candidateLegBoundaries'
    ));
}

    public function create(Request $request, Trip $trip)
    {
        $query = array_filter([
            'show_create' => 1,
            'plantype' => $request->input('plantype'),
            'place_id' => $request->input('place_id'),
            'destination_id' => $request->input('destination_id'),
            'destinationitem_id' => $request->input('destinationitem_id'),
        ], fn ($value) => !is_null($value) && $value !== '');

        return redirect()->route('trips.planner.index', [
            'trip' => $trip->id,
            ...$query,
        ]);
    }

    public function store(Request $request, Trip $trip)
{
    $validated = $this->validateData($request, $trip);

    $selectedDestinationItemIds = collect($request->input('selected_destinationitemids', []))
        ->filter(fn ($id) => filled($id))
        ->map(fn ($id) => (int) $id)
        ->values();

    $validated['tripid'] = $trip->id;
    $validated['nightsplanned'] = $this->calculateNightsPlanned(
        $validated['planneddate'] ?? null,
        $validated['plannedenddate'] ?? null,
        !empty($validated['isovernight']) || !empty($validated['isstaytarget'])
    );

    DB::transaction(function () use ($trip, &$validated, $selectedDestinationItemIds) {
        $currentMaxSequence = (int) TripPlanItem::query()
            ->where('tripid', $trip->id)
            ->max('sequence_no');

        $baseSequence = filled($validated['sequence_no'] ?? null)
            ? (int) $validated['sequence_no']
            : ($currentMaxSequence + 1);

        $validated['sequence_no'] = $baseSequence;

        TripPlanItem::create($validated);

        if ($selectedDestinationItemIds->isEmpty()) {
            return;
        }

        $destinationItems = DestinationItem::query()
            ->with(['destination.place', 'place'])
            ->whereIn('id', $selectedDestinationItemIds->all())
            ->get()
            ->keyBy('id');

        if ($destinationItems->isEmpty()) {
            return;
        }

        $orderedDestinationItems = $selectedDestinationItemIds
            ->map(fn ($id) => $destinationItems->get($id))
            ->filter();

        if (filled($validated['destinationitemid'] ?? null)) {
            $mainDestinationItemId = (int) $validated['destinationitemid'];

            $orderedDestinationItems = $orderedDestinationItems->reject(
                fn ($item) => (int) $item->id === $mainDestinationItemId
            )->values();
        }

        if ($orderedDestinationItems->isEmpty()) {
            return;
        }

        $nextSequence = $baseSequence;

        $startDate = $validated['planneddate'] ?? null;
        $endDate = $validated['plannedenddate'] ?? null;
        $startTime = $validated['starttime'] ?? null;
        $endTime = $validated['endtime'] ?? null;
        $sortGroup = $validated['sortgroup'] ?? null;
        $nightsPlanned = $this->calculateNightsPlanned(
            $startDate,
            $endDate,
            !empty($validated['isovernight']) || !empty($validated['isstaytarget'])
        );

        foreach ($orderedDestinationItems as $destinationItem) {
            $nextSequence++;

            $placeId = $destinationItem->placeid
                ?: $destinationItem->destination?->placeid;

            $destinationId = $destinationItem->destinationid;

            TripPlanItem::create([
                'tripid' => $trip->id,
                'sequence_no' => $nextSequence,
                'plantype' => 'destination_item',
                'title' => $destinationItem->itemname,
                'sortgroup' => $sortGroup,
                'placeid' => $placeId,
                'destinationid' => $destinationId,
                'destinationitemid' => $destinationItem->id,
                'planneddate' => $startDate,
                'plannedenddate' => $endDate,
                'starttime' => $startTime,
                'endtime' => $endTime,
                'notes' => null,
                'isrouteanchor' => 0,
                'isovernight' => 0,
                'isstaytarget' => 0,
                'staytype' => null,
                'nightsplanned' => $nightsPlanned,
                'triplegid' => null,
                'tripstayid' => null,
            ]);
        }
    });

    return redirect()
        ->route('trips.planner.index', $trip)
        ->with('success', 'Planning item created with any selected destination items.');
}

    public function edit(Request $request, Trip $trip, TripPlanItem $tripPlanItem)
{
    abort_unless((int) $tripPlanItem->tripid === (int) $trip->id, 404);

    $places = Place::query()
        ->orderBy('placename')
        ->get();

    $destinations = Destination::query()
        ->with('place')
        ->orderBy('destinationname')
        ->get();

    $destinationItems = DestinationItem::query()
        ->with(['place', 'destination'])
        ->where('isactive', 1)
        ->orderBy('itemname')
        ->get();

    $tripLegs = $trip->legs()
        ->orderBy('legnumber')
        ->orderBy('sortorder')
        ->get();

    $tripStays = $trip->stays()
        ->orderBy('checkindate')
        ->orderBy('id')
        ->get();

    $planTypeOptions = $this->planTypeOptions();
    $stayTypeOptions = $this->stayTypeOptions();
    $returnTo = $request->input('return_to', route('trips.planner.index', $trip));

    $existingSelectedDestinationItemIds = TripPlanItem::query()
        ->where('tripid', $trip->id)
        ->whereNotNull('destinationitemid')
        ->pluck('destinationitemid')
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values()
        ->all();

    return view('trip-planner.edit', compact(
        'trip',
        'tripPlanItem',
        'places',
        'destinations',
        'destinationItems',
        'tripLegs',
        'tripStays',
        'planTypeOptions',
        'stayTypeOptions',
        'returnTo',
        'existingSelectedDestinationItemIds'
    ));
}


public function update(Request $request, Trip $trip, TripPlanItem $tripPlanItem)
{
    abort_unless((int) $tripPlanItem->tripid === (int) $trip->id, 404);

    $request->merge([
        'selected_destinationitemids' => $request->input('selected_destinationitemids', []),
    ]);

    $validated = $this->validateData($request, $trip);

    $validated['nightsplanned'] = $this->calculateNightsPlanned(
        $validated['planneddate'] ?? null,
        $validated['plannedenddate'] ?? null,
        !empty($validated['isovernight']) || !empty($validated['isstaytarget'])
    );

    $selectedDestinationItemIds = collect($request->input('selected_destinationitemids', []))
        ->filter()
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values();

    $newSequence = (int) ($validated['sequence_no'] ?? $tripPlanItem->sequence_no);
    $oldSequence = (int) $tripPlanItem->sequence_no;

    DB::transaction(function () use (
        $trip,
        $tripPlanItem,
        $validated,
        $oldSequence,
        $newSequence,
        $selectedDestinationItemIds
    ) {
        $maxSequence = (int) TripPlanItem::query()
            ->where('tripid', $trip->id)
            ->max('sequence_no');

        $temporarySequence = $maxSequence + 1000;

        if ($newSequence !== $oldSequence) {
            $tripPlanItem->update([
                'sequence_no' => $temporarySequence,
            ]);

            if ($newSequence < $oldSequence) {
                TripPlanItem::query()
                    ->where('tripid', $trip->id)
                    ->where('id', '!=', $tripPlanItem->id)
                    ->where('sequence_no', '>=', $newSequence)
                    ->where('sequence_no', '<', $oldSequence)
                    ->orderBy('sequence_no', 'desc')
                    ->get()
                    ->each(function ($item) {
                        $item->update([
                            'sequence_no' => $item->sequence_no + 1,
                        ]);
                    });
            } elseif ($newSequence > $oldSequence) {
                TripPlanItem::query()
                    ->where('tripid', $trip->id)
                    ->where('id', '!=', $tripPlanItem->id)
                    ->where('sequence_no', '>', $oldSequence)
                    ->where('sequence_no', '<=', $newSequence)
                    ->orderBy('sequence_no')
                    ->get()
                    ->each(function ($item) {
                        $item->update([
                            'sequence_no' => $item->sequence_no - 1,
                        ]);
                    });
            }
        }

        $validated['sequence_no'] = $newSequence;

        $tripPlanItem->update($validated);

        if ($selectedDestinationItemIds->isEmpty()) {
            return;
        }

        $currentDestinationItemId = (int) ($validated['destinationitemid'] ?? $tripPlanItem->destinationitemid ?? 0);

        $existingTripDestinationItemIds = TripPlanItem::query()
            ->where('tripid', $trip->id)
            ->whereNotNull('destinationitemid')
            ->where('id', '!=', $tripPlanItem->id)
            ->pluck('destinationitemid')
            ->map(fn ($id) => (int) $id)
            ->unique();

        $missingIds = $selectedDestinationItemIds
            ->reject(function ($id) use ($existingTripDestinationItemIds, $currentDestinationItemId) {
                return $existingTripDestinationItemIds->contains((int) $id)
                    || (int) $id === $currentDestinationItemId;
            })
            ->values();

        if ($missingIds->isEmpty()) {
            return;
        }

        $destinationItems = DestinationItem::query()
            ->with(['destination', 'place'])
            ->whereIn('id', $missingIds)
            ->orderBy('itemname')
            ->get();

        if ($destinationItems->isEmpty()) {
            return;
        }

        $orderedDestinationItems = $selectedDestinationItemIds
            ->map(fn ($id) => $destinationItems->firstWhere('id', $id))
            ->filter()
            ->values();

        if ($orderedDestinationItems->isEmpty()) {
            return;
        }

        $insertStartSequence = (int) $tripPlanItem->fresh()->sequence_no + 1;
        $insertCount = $orderedDestinationItems->count();

        TripPlanItem::query()
            ->where('tripid', $trip->id)
            ->where('id', '!=', $tripPlanItem->id)
            ->where('sequence_no', '>=', $insertStartSequence)
            ->orderBy('sequence_no', 'desc')
            ->get()
            ->each(function ($item) use ($insertCount) {
                $item->update([
                    'sequence_no' => (int) $item->sequence_no + $insertCount,
                ]);
            });

        $nextSequence = $insertStartSequence - 1;
        $nightsPlanned = $this->calculateNightsPlanned(
            $validated['planneddate'] ?? null,
            $validated['plannedenddate'] ?? null,
            !empty($validated['isovernight']) || !empty($validated['isstaytarget'])
        );

        foreach ($orderedDestinationItems as $destinationItem) {
            $nextSequence++;

            $placeId = $destinationItem->placeid ?: $destinationItem->destination?->placeid;
            $destinationId = $destinationItem->destinationid;

            TripPlanItem::create([
                'tripid' => $trip->id,
                'sequence_no' => $nextSequence,
                'plantype' => 'destination_item',
                'title' => $destinationItem->itemname,
                'sortgroup' => $validated['sortgroup'] ?? null,
                'placeid' => $placeId,
                'destinationid' => $destinationId,
                'destinationitemid' => $destinationItem->id,
                'planneddate' => $validated['planneddate'] ?? null,
                'plannedenddate' => $validated['plannedenddate'] ?? null,
                'starttime' => $validated['starttime'] ?? null,
                'endtime' => $validated['endtime'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'isrouteanchor' => 0,
                'isovernight' => 0,
                'isstaytarget' => 0,
                'staytype' => null,
                'nightsplanned' => $nightsPlanned,
                'triplegid' => null,
                'tripstayid' => null,
            ]);
        }
    });

    return redirect()->route('trips.planner.edit', [
        'trip' => $trip->id,
        'tripPlanItem' => $tripPlanItem->id,
        'return_to' => $request->input('return_to'),
    ])->with('success', 'Planning item updated successfully.');
}

    public function destroy(Request $request, Trip $trip, TripPlanItem $tripPlanItem)
    {
        abort_unless((int) $tripPlanItem->tripid === (int) $trip->id, 404);

        try {
            $tripPlanItem->delete();

            return redirect($request->input('return_to', route('trips.planner.index', $trip)))
                ->with('success', 'Planning item deleted successfully.');
        } catch (\Throwable $e) {
            return redirect($request->input('return_to', route('trips.planner.index', $trip)))
                ->with('error', 'This planning item could not be deleted.');
        }
    }

    public function resequence(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', Rule::exists('tripplanitems', 'id')],
            'items.*.sequence_no' => ['required', 'integer', 'min:1'],
        ]);

        foreach ($validated['items'] as $itemData) {
            TripPlanItem::query()
                ->where('tripid', $trip->id)
                ->where('id', $itemData['id'])
                ->update(['sequence_no' => $itemData['sequence_no']]);
        }

        return response()->json(['success' => true]);
    }

    private function validateData(Request $request, Trip $trip): array
    {
$validated = $request->validate([
    'sequence_no' => ['nullable', 'integer', 'min:1'],
    'plantype' => ['required', Rule::in(array_keys($this->planTypeOptions()))],
    'placeid' => ['nullable', 'integer', 'exists:places,id'],
    'destinationid' => ['nullable', 'integer', 'exists:destinations,id'],
    'destinationitemid' => ['nullable', 'integer', 'exists:destinationitems,id'],
    'selected_destinationitemids' => ['nullable', 'array'],
    'selected_destinationitemids.*' => ['integer', 'exists:destinationitems,id'],
    'triplegid' => ['nullable', 'integer', Rule::exists('triplegs', 'id')],
    'tripstayid' => ['nullable', 'integer', Rule::exists('tripstays', 'id')],
    'planneddate' => ['nullable', 'date'],
    'plannedenddate' => ['nullable', 'date', 'after_or_equal:planneddate'],
    'starttime' => ['nullable', 'date_format:H:i'],
    'endtime' => ['nullable', 'date_format:H:i'],
    'title' => ['nullable', 'string', 'max:200'],
    'notes' => ['nullable', 'string'],
    'isovernight' => ['nullable', 'boolean'],
    'isstaytarget' => ['nullable', 'boolean'],
    'isrouteanchor' => ['nullable', 'boolean'],
    'isgovia' => ['nullable', 'boolean'],
    'staytype' => ['nullable', Rule::in(array_keys($this->stayTypeOptions()))],
    'nightsplanned' => ['nullable', 'integer', 'min:0'],
    'sortgroup' => ['nullable', 'string', 'max:30'],
    'mapcolor' => ['nullable', 'string', 'max:20'],
]);

        foreach (['isovernight', 'isstaytarget', 'isrouteanchor', 'isgovia'] as $field) {
            $validated[$field] = (bool) ($validated[$field] ?? false);
        }

        return $validated;
    }

public function bulkUpdate(Request $request, Trip $trip)
{
    $validated = $request->validate([
        'items' => ['required', 'array'],
        'items.*.id' => ['required', 'integer', 'exists:tripplanitems,id'],
        'items.*.sequence_no' => ['nullable', 'integer', 'min:1'],
        'items.*.planneddate' => ['nullable', 'date'],
        'items.*.plannedenddate' => ['nullable', 'date'],
        'items.*.isrouteanchor' => ['nullable', 'boolean'],
        'items.*.isgovia' => ['nullable', 'boolean'],
        'items.*.isovernight' => ['nullable', 'boolean'],
        'items.*.isstaytarget' => ['nullable', 'boolean'],
    ]);

    $items = collect($validated['items']);

    foreach ($items as $row) {
        $startDate = $row['planneddate'] ?? null;
        $endDate = $row['plannedenddate'] ?? null;

        if ($startDate && $endDate && $endDate < $startDate) {
            return redirect()
                ->route('trips.planner.index', $trip)
                ->with('error', 'One or more planning items have an end date before the planned date.');
        }
    }

    $existingItems = TripPlanItem::query()
        ->where('tripid', $trip->id)
        ->whereIn('id', $items->pluck('id'))
        ->get()
        ->keyBy('id');

    $maxSequence = (int) TripPlanItem::query()
        ->where('tripid', $trip->id)
        ->max('sequence_no');

    $temporaryOffset = $maxSequence + 1000;

    DB::transaction(function () use ($items, $existingItems, $temporaryOffset) {
        foreach ($items as $index => $row) {
            $item = $existingItems->get((int) $row['id']);

            if (! $item) {
                continue;
            }

            $item->update([
                'sequence_no' => $temporaryOffset + $index + 1,
            ]);
        }

        foreach ($items as $row) {
            $item = $existingItems->get((int) $row['id']);

            if (! $item) {
                continue;
            }

            $plannedDate = !empty($row['planneddate'])
                ? Carbon::parse($row['planneddate'])->startOfDay()
                : null;

            $plannedEndDate = !empty($row['plannedenddate'])
                ? Carbon::parse($row['plannedenddate'])->startOfDay()
                : null;

            $isOvernight = (bool) ($row['isovernight'] ?? false);
            $isStayTarget = (bool) ($row['isstaytarget'] ?? false);

            $nightsPlanned = null;

            if ($plannedDate && $plannedEndDate) {
                $nightsPlanned = $plannedDate->diffInDays($plannedEndDate);
            } elseif ($plannedDate && ($isOvernight || $isStayTarget)) {
                $nightsPlanned = 1;
            }

            $item->update([
                'sequence_no' => $row['sequence_no'] ?? $item->sequence_no,
                'planneddate' => $plannedDate,
                'plannedenddate' => $plannedEndDate,
                'nightsplanned' => $nightsPlanned,
                'isrouteanchor' => (bool) ($row['isrouteanchor'] ?? false),
                'isgovia' => (bool) ($row['isgovia'] ?? false),
                'isovernight' => $isOvernight,
                'isstaytarget' => $isStayTarget,
            ]);
        }
    });

    return redirect()
        ->route('trips.planner.index', $trip)
        ->with('success', 'Planning sequence updated successfully.');
}

public function renumber(Request $request, Trip $trip)
{
    $items = TripPlanItem::query()
        ->where('tripid', $trip->id)
        ->orderByRaw('planneddate IS NULL, planneddate ASC')
        ->orderByRaw('plannedenddate IS NOT NULL, plannedenddate ASC')
        ->orderBy('sequence_no')
        ->orderBy('id')
        ->get();

    if ($items->isEmpty()) {
        return redirect()
            ->route('trips.planner.index', $trip)
            ->with('success', 'No planning items to renumber.');
    }

    \DB::transaction(function () use ($items) {
        $temporaryOffset = 1000;

        foreach ($items as $index => $item) {
            $item->update([
                'sequence_no' => $temporaryOffset + $index + 1,
            ]);
        }

        foreach ($items as $index => $item) {
            $item->update([
                'sequence_no' => $index + 1,
            ]);
        }
    });

    return redirect()
        ->route('trips.planner.index', $trip)
        ->with('success', 'Planning items renumbered successfully.');
}


public function bulkAddDestinationItems(Request $request, Trip $trip)
{
    $validated = $request->validate([
        'bulk_placeid' => ['nullable', 'integer', 'exists:places,id'],
        'bulk_destinationid' => ['nullable', 'integer', 'exists:destinations,id'],
        'destinationitemids' => ['required', 'array', 'min:1'],
        'destinationitemids.*' => ['integer', 'exists:destinationitems,id'],
        'bulk_planneddate' => ['nullable', 'date'],
        'bulk_plannedenddate' => ['nullable', 'date'],
        'bulk_sortgroup' => ['nullable', 'string', 'max:30'],
        'bulk_notes' => ['nullable', 'string'],
    ]);

    $startDate = $validated['bulk_planneddate'] ?? null;
    $endDate = $validated['bulk_plannedenddate'] ?? null;

    if ($startDate && $endDate && $endDate < $startDate) {
        return redirect()
            ->route('trips.planner.index', $trip)
            ->withInput()
            ->with('error', 'End date cannot be before start date.');
    }

    $items = DestinationItem::query()
        ->with(['destination', 'place'])
        ->whereIn('id', $validated['destinationitemids'])
        ->orderBy('itemname')
        ->get();

    if ($items->isEmpty()) {
        return redirect()
            ->route('trips.planner.index', $trip)
            ->withInput()
            ->with('error', 'Please select at least one destination item.');
    }

    if (!empty($validated['bulk_destinationid'])) {
        $items = $items->where('destinationid', (int) $validated['bulk_destinationid']);
    }

    if (!empty($validated['bulk_placeid'])) {
        $items = $items->filter(function ($item) use ($validated) {
            return (int) ($item->placeid ?? $item->destination?->placeid) === (int) $validated['bulk_placeid'];
        });
    }

    if ($items->isEmpty()) {
        return redirect()
            ->route('trips.planner.index', $trip)
            ->withInput()
            ->with('error', 'The selected destination items do not match the chosen place or destination.');
    }

    DB::transaction(function () use ($trip, $items, $validated, $startDate, $endDate) {
        $nextSequence = (int) TripPlanItem::query()
            ->where('tripid', $trip->id)
            ->max('sequence_no');

        foreach ($items as $item) {
            $nextSequence++;

            $placeId = $item->placeid ?: $item->destination?->placeid;
            $destinationId = $item->destinationid;

            TripPlanItem::create([
                'tripid' => $trip->id,
                'sequence_no' => $nextSequence,
                'plantype' => 'destination_item',
                'title' => $item->itemname,
                'sortgroup' => $validated['bulk_sortgroup'] ?? null,
                'placeid' => $placeId,
                'destinationid' => $destinationId,
                'destinationitemid' => $item->id,
                'planneddate' => $startDate,
                'plannedenddate' => $endDate,
                'notes' => $validated['bulk_notes'] ?? null,
                'isrouteanchor' => 0,
                'isgovia' => 0,
                'isovernight' => 0,
                'isstaytarget' => 0,
            ]);
        }
    });

    return redirect()
        ->route('trips.planner.index', $trip)
        ->with('success', 'Selected destination items added to the trip plan.');
}

public function generatePreview(Request $request, Trip $trip)
{
    $candidates = $this->buildGenerationCandidates($trip);

    $existingLegs = $trip->legs()
        ->orderBy('legnumber')
        ->orderBy('id')
        ->get();

    $existingStays = $trip->stays()
        ->orderBy('checkindate')
        ->orderBy('id')
        ->get();

    return view('trip-planner.generate', [
        'trip' => $trip,
        'planItems' => $candidates['planItems'],
        'candidateLegAnchors' => $candidates['candidateLegBoundaries'],
        'candidateLegBoundaries' => $candidates['candidateLegBoundaries'],
        'candidateLegPoints' => $candidates['candidateLegPoints'],
        'candidateStayItems' => $candidates['candidateStayItems'],
        'candidateTripItems' => $candidates['candidateTripItems'],
        'candidateLegs' => $candidates['candidateLegs'],
        'existingLegs' => $existingLegs,
        'existingStays' => $existingStays,
        'returnTo' => $request->input('return_to', route('trips.planner.index', $trip)),
    ]);
}

public function generateApply(Request $request, Trip $trip)
{
    if (($trip->tripstatus ?? null) !== 'planned') {
        return redirect()
            ->route('trips.planner.generate', [
                'trip' => $trip->id,
                'return_to' => $request->input('return_to', route('trips.planner.index', $trip)),
            ])
            ->with('error', 'Generation is only available while the trip status is Planned.');
    }

    $trip->load([
        'tripVehicles' => function ($query) {
            $query->where('isdefaultforlegs', 1)
                ->orderByRaw('COALESCE(sortorder, 999999), id');
        },
    ]);

    $defaultVehicleSync = $trip->tripVehicles
        ->filter(fn ($tripVehicle) => !empty($tripVehicle->vehicleid))
        ->mapWithKeys(function ($tripVehicle) {
            return [
                $tripVehicle->vehicleid => [
                    'vehiclerole' => $tripVehicle->vehiclerole ?? null,
                    'sortorder' => $tripVehicle->sortorder ?? null,
                ],
            ];
        })
        ->all();

    $candidates = $this->buildGenerationCandidates($trip);

    $candidateLegs = $candidates['candidateLegs'];
    $candidateStayItems = $candidates['candidateStayItems'];
    $candidateTripItems = $candidates['candidateTripItems'];
    $candidateLegPoints = $candidates['candidateLegPoints'];

    $createdLegsCount = 0;
    $createdStaysCount = 0;
    $createdItemsCount = 0;
    $createdLegPointsCount = 0;
    $deletedLegsCount = 0;
    $deletedStaysCount = 0;
    $deletedItemsCount = 0;
    $deletedLegPointsCount = 0;

    DB::transaction(function () use (
        $trip,
        $candidateLegs,
        $candidateStayItems,
        $candidateTripItems,
        $candidateLegPoints,
        $defaultVehicleSync,
        &$createdLegsCount,
        &$createdStaysCount,
        &$createdItemsCount,
        &$createdLegPointsCount,
        &$deletedLegsCount,
        &$deletedStaysCount,
        &$deletedItemsCount,
        &$deletedLegPointsCount
    ) {
        // Clear planner output links before regenerating
        TripPlanItem::query()
            ->where('tripid', $trip->id)
            ->update([
                'triplegid' => null,
                'tripstayid' => null,
            ]);

        $existingLegIds = $trip->legs()->pluck('id');

        if ($existingLegIds->isNotEmpty()) {
            $deletedLegPointsCount = TripLegPoint::whereIn('triplegid', $existingLegIds)->count();
            TripLegPoint::whereIn('triplegid', $existingLegIds)->delete();
        }

        $deletedItemsCount = $trip->tripItems()->count();
        $trip->tripItems()->delete();

        $deletedStaysCount = $trip->stays()->count();
        $trip->stays()->delete();

        $deletedLegsCount = $trip->legs()->count();
        $trip->legs()->delete();

        $nextLegNumber = 1;
        $generatedLegs = collect();

        foreach ($candidateLegs as $legData) {
            $fromItem = $legData['from_item'];
            $toItem = $legData['to_item'];

            $leg = TripLeg::create([
                'tripid' => $trip->id,
                'legnumber' => $nextLegNumber++,
                'startdate' => $legData['start_date'],
                'enddate' => $legData['end_date'],
                'nightsplanned' => null,
                'fromplaceid' => $fromItem->placeid ?? null,
                'toplaceid' => $toItem->placeid ?? null,
                'destinationid' => $toItem->destinationid ?? null,
                'fromdestinationitemid' => $fromItem->destinationitemid ?? null,
                'destinationitemid' => $toItem->destinationitemid ?? null,
                'title' => trim($legData['from_label'] . ' → ' . $legData['to_label']),
                'description' => null,
                'distancekm' => null,
                'elevationgainm' => null,
                'elevationlossm' => null,
                'drivingnotes' => null,
                'planningnotes' => null,
                'actualnotes' => null,
                'sortorder' => $fromItem->sequence_no ?? null,
                'plannerstatus' => 'generated',
            ]);

            if (!empty($defaultVehicleSync)) {
                $leg->vehicles()->sync($defaultVehicleSync);
            }

            $generatedLegs->push([
                'model' => $leg,
                'from_item_id' => $fromItem->id,
                'to_item_id' => $toItem->id,
                'from_sequence' => (int) ($fromItem->sequence_no ?? 0),
                'to_sequence' => (int) ($toItem->sequence_no ?? 0),
                'start_date' => !empty($legData['start_date'])
                    ? Carbon::parse($legData['start_date'])->startOfDay()
                    : null,
                'end_date' => !empty($legData['end_date'])
                    ? Carbon::parse($legData['end_date'])->startOfDay()
                    : null,
            ]);

            // Attach the generated leg to the planner boundary rows
            $fromItem->update([
                'triplegid' => $leg->id,
            ]);

            $toItem->update([
                'triplegid' => $leg->id,
            ]);

            $createdLegsCount++;
        }

        $findLegForPlannerItem = function ($plannerItem) use ($generatedLegs) {
            $itemSequence = (int) ($plannerItem->sequence_no ?? 0);
            $itemDate = !empty($plannerItem->planneddate)
                ? Carbon::parse($plannerItem->planneddate)->startOfDay()
                : null;

            $sequenceMatchedLeg = $generatedLegs->first(function ($legRow) use ($itemSequence) {
                return $itemSequence > $legRow['from_sequence']
                    && $itemSequence < $legRow['to_sequence'];
            });

            if ($sequenceMatchedLeg) {
                return $sequenceMatchedLeg['model'];
            }

            $boundaryMatchedLeg = $generatedLegs->first(function ($legRow) use ($itemSequence) {
                return $itemSequence === $legRow['from_sequence']
                    || $itemSequence === $legRow['to_sequence'];
            });

            if ($boundaryMatchedLeg) {
                return $boundaryMatchedLeg['model'];
            }

            $dateMatchedLeg = $generatedLegs->first(function ($legRow) use ($itemDate) {
                return $itemDate
                    && $legRow['start_date']
                    && $legRow['end_date']
                    && $itemDate->betweenIncluded($legRow['start_date'], $legRow['end_date']);
            });

            if ($dateMatchedLeg) {
                return $dateMatchedLeg['model'];
            }

            $sameDayStartingLeg = $generatedLegs->first(function ($legRow) use ($itemDate) {
                return $itemDate
                    && $legRow['start_date']
                    && $legRow['start_date']->equalTo($itemDate);
            });

            if ($sameDayStartingLeg) {
                return $sameDayStartingLeg['model'];
            }

            $latestPreviousLeg = $generatedLegs
                ->filter(function ($legRow) use ($itemDate) {
                    return $itemDate
                        && $legRow['start_date']
                        && $legRow['start_date']->lte($itemDate);
                })
                ->sortByDesc(function ($legRow) {
                    return optional($legRow['start_date'])->timestamp ?? 0;
                })
                ->first();

            return $latestPreviousLeg['model'] ?? null;
        };

        foreach ($candidateStayItems as $stayItem) {
            $matchedLeg = $findLegForPlannerItem($stayItem);

            $checkIn = !empty($stayItem->planneddate)
                ? Carbon::parse($stayItem->planneddate)->startOfDay()
                : null;

            $nights = null;

            if (!is_null($stayItem->nightsplanned)) {
                $nights = max((int) $stayItem->nightsplanned, 0);
            } elseif (!empty($stayItem->planneddate) && !empty($stayItem->plannedenddate)) {
                $nights = Carbon::parse($stayItem->planneddate)
                    ->startOfDay()
                    ->diffInDays(Carbon::parse($stayItem->plannedenddate)->startOfDay());
            } elseif ((bool) $stayItem->isovernight || (bool) $stayItem->isstaytarget) {
                $nights = 1;
            }

            $checkout = null;
            if ($checkIn && !is_null($nights)) {
                $checkout = $checkIn->copy()->addDays($nights);
            }

            $stay = TripStay::create([
                'tripid' => $trip->id,
                'triplegid' => $matchedLeg?->id,
                'placeid' => $stayItem->placeid ?? null,
                'destinationitemid' => $stayItem->destinationitemid ?? null,
                'stayname' => $stayItem->display_title,
                'staytype' => $stayItem->staytype ?? null,
                'checkindate' => $checkIn,
                'checkoutdate' => $checkout,
                'nights' => $nights,
                'isaccommodationpaid' => false,
                'costpernight' => null,
                'estimatedtotalcost' => null,
                'actualtotalcost' => null,
                'travelledfromplaceid' => $matchedLeg?->fromplaceid,
                'distancetravelledkm' => null,
                'description' => null,
                'woulduseagain' => null,
                'reviewnotes' => null,
            ]);

            $stayItem->update([
                'triplegid' => $matchedLeg?->id,
                'tripstayid' => $stay->id,
            ]);

            $createdStaysCount++;
        }

        foreach ($candidateTripItems as $item) {
            $matchedLeg = $findLegForPlannerItem($item);

            TripItem::create([
                'tripid' => $trip->id,
                'triplegid' => $matchedLeg?->id,
                'tripstayid' => null,
                'destinationid' => $item->destinationid ?? null,
                'destinationitemid' => $item->destinationitemid ?? null,
                'placeid' => $item->placeid ?? null,
                'itemdate' => $item->planneddate ?? null,
                'startdatetime' => null,
                'enddatetime' => null,
                'itemtype' => $item->plantype,
                'status' => 'planned',
                'title' => $item->display_title,
                'description' => $item->notes ?? null,
                'priority' => null,
                'isfullday' => false,
                'peoplecount' => null,
                'estimatedcostperperson' => null,
                'estimatedtotalcost' => null,
                'actualcost' => null,
                'allocateasdailycost' => false,
                'bookingid' => null,
                'notesinternal' => null,
                'sortorder' => $item->sequence_no ?? null,
            ]);

            $item->update([
                'triplegid' => $matchedLeg?->id,
            ]);

            $createdItemsCount++;
        }

        foreach ($candidateLegPoints as $index => $pointItem) {
            $matchedLeg = $findLegForPlannerItem($pointItem);

            if (! $matchedLeg) {
                continue;
            }

            TripLegPoint::create([
                'triplegid' => $matchedLeg->id,
                'sequence_no' => $index + 1,
                'pointtype' => $pointItem->isgovia ? 'govia' : 'waypoint',
                'placeid' => $pointItem->placeid ?? null,
                'destinationid' => $pointItem->destinationid ?? null,
                'destinationitemid' => $pointItem->destinationitemid ?? null,
                'title' => $pointItem->display_title,
                'notes' => $pointItem->notes ?? null,
            ]);

            $pointItem->update([
                'triplegid' => $matchedLeg->id,
            ]);

            $createdLegPointsCount++;
        }
    });

    return redirect()
        ->route('trips.planner.generate', [
            'trip' => $trip->id,
            'return_to' => $request->input('return_to', route('trips.planner.index', $trip)),
        ])
        ->with('success', sprintf(
            'Deleted %d legs, %d stays, and %d trip items. Generated %d legs, %d stays, %d leg points, and %d trip items from planner.',
            $deletedLegsCount,
            $deletedStaysCount,
            $deletedItemsCount,
            $createdLegsCount,
            $createdStaysCount,
            $createdLegPointsCount,
            $createdItemsCount
        ));
}

private function calculateNightsPlanned($plannedDate, $plannedEndDate, bool $defaultOneNightForStay = false): ?int
{
    if (!empty($plannedDate) && !empty($plannedEndDate)) {
        return Carbon::parse($plannedDate)
            ->startOfDay()
            ->diffInDays(Carbon::parse($plannedEndDate)->startOfDay());
    }

    if ($defaultOneNightForStay && !empty($plannedDate)) {
        return 1;
    }

    return null;
}

public function rollbackGenerated(Request $request, Trip $trip)
{
    return redirect()
        ->route('trips.planner.generate', [
            'trip' => $trip->id,
            'return_to' => $request->input('return_to', route('trips.planner.index', $trip)),
        ])
        ->with('success', 'Rollback workflow scaffold is in place. Cleanup logic is the next step.');
}

private function buildGenerationCandidates(Trip $trip): array
{
    $trip->loadMissing([
        'planItems' => fn ($query) => $query
            ->with(['place', 'destination', 'destinationItem', 'tripLeg', 'tripStay'])
            ->orderByRaw('planneddate IS NULL, planneddate ASC')
            ->orderByRaw('plannedenddate IS NOT NULL, plannedenddate ASC')
            ->orderBy('sequence_no')
            ->orderBy('id')
    ]);

    $planItems = $trip->planItems;

    $hasPlanningLocation = function ($item): bool {
        return ! is_null($item->placeid)
            || ! is_null($item->destinationid)
            || ! is_null($item->destinationitemid)
            || ! is_null($item->place)
            || ! is_null($item->destination)
            || ! is_null($item->destinationItem);
    };

    $candidateStayItems = $planItems
        ->filter(function ($item) use ($hasPlanningLocation) {
            if (! $hasPlanningLocation($item)) {
                return false;
            }

            return (bool) $item->isovernight || (bool) $item->isstaytarget;
        })
        ->values();

    $candidateLegBoundaries = $planItems
        ->filter(function ($item) use ($hasPlanningLocation) {
            if (! $hasPlanningLocation($item)) {
                return false;
            }

            return (bool) $item->isrouteanchor;
        })
        ->values();

    $candidateLegPoints = $planItems
        ->filter(function ($item) use ($candidateStayItems, $candidateLegBoundaries, $hasPlanningLocation) {
            if (! $hasPlanningLocation($item)) {
                return false;
            }

            if ($candidateStayItems->contains('id', $item->id)) {
                return false;
            }

            if ($candidateLegBoundaries->contains('id', $item->id)) {
                return false;
            }

            return (bool) $item->isgovia;
        })
        ->values();

    $candidateTripItems = $planItems
        ->filter(function ($item) use ($candidateStayItems, $candidateLegBoundaries, $candidateLegPoints) {
            if ($candidateStayItems->contains('id', $item->id)) {
                return false;
            }

            if ($candidateLegBoundaries->contains('id', $item->id)) {
                return false;
            }

            if ($candidateLegPoints->contains('id', $item->id)) {
                return false;
            }

            return true;
        })
        ->values();

    $candidateLegs = collect();

    if ($candidateLegBoundaries->count() > 1) {
        for ($i = 1; $i < $candidateLegBoundaries->count(); $i++) {
            $fromItem = $candidateLegBoundaries[$i - 1];
            $toItem = $candidateLegBoundaries[$i];

            // Skip legs where we are at the same place (no towing between boundaries).
            $samePlaceId = ($fromItem->placeid && $toItem->placeid && $fromItem->placeid === $toItem->placeid);

            $sameDestinationItem = ($fromItem->destinationitemid && $toItem->destinationitemid
                && $fromItem->destinationitemid === $toItem->destinationitemid);

            if ($samePlaceId || $sameDestinationItem) {
                continue;
            }

            $candidateLegs->push([
                'from_item'      => $fromItem,
                'to_item'        => $toItem,
                'from_label'     => $fromItem->display_title,
                'to_label'       => $toItem->display_title,
                'from_sequence'  => $fromItem->sequence_no,
                'to_sequence'    => $toItem->sequence_no,
                'planned_start'  => $fromItem->planneddate,
                'planned_end'    => $toItem->planneddate,
                'start_date'     => $fromItem->planneddate,
                'end_date'       => $toItem->planneddate,
            ]);
        }
    }

    return [
        'planItems' => $planItems,
        'candidateLegs' => $candidateLegs,
        'candidateStayItems' => $candidateStayItems,
        'candidateTripItems' => $candidateTripItems,
        'candidateLegPoints' => $candidateLegPoints,
        'candidateLegBoundaries' => $candidateLegBoundaries,
    ];
}
protected function resolvePlanningItemCoordinates(TripPlanItem $item): array
{
    $resolvedPlace = $item->place
        ?? $item->destinationItem?->place
        ?? $item->destinationItem?->destination?->place
        ?? $item->destination?->place;

    $resolvedDestination = $item->destination
        ?? $item->destinationItem?->destination;

    $resolvedDestinationItem = $item->destinationItem;

    $latitude = $resolvedDestinationItem?->latitude
        ?? $resolvedDestination?->latitude
        ?? $resolvedPlace?->latitude;

    $longitude = $resolvedDestinationItem?->longitude
        ?? $resolvedDestination?->longitude
        ?? $resolvedPlace?->longitude;

    return [
        'latitude' => $latitude !== null ? (float) $latitude : null,
        'longitude' => $longitude !== null ? (float) $longitude : null,
        'has_coordinates' => $latitude !== null && $longitude !== null,
    ];
}

protected function estimateRoadDistanceKm(float $straightLineKm): float
{
    if ($straightLineKm < 50) {
        return $straightLineKm * 1.45;
    }

    if ($straightLineKm < 200) {
        return $straightLineKm * 1.30;
    }

    return $straightLineKm * 1.18;
}

protected function estimateCaravanDrivingMinutes(float $roadDistanceKm): int
{
    $averageSpeedKph = 72.0;

    if ($roadDistanceKm <= 0 || $averageSpeedKph <= 0) {
        return 0;
    }

    return (int) round(($roadDistanceKm / $averageSpeedKph) * 60);
}

protected function formatDrivingTime(int $minutes): string
{
    if ($minutes < 60) {
        return $minutes . ' min';
    }

    $hours = intdiv($minutes, 60);
    $remainingMinutes = $minutes % 60;

    if ($remainingMinutes === 0) {
        return $hours . ' hr';
    }

    return $hours . ' hr ' . $remainingMinutes . ' min';
}

protected function haversineDistanceKm(
    float $latitudeFrom,
    float $longitudeFrom,
    float $latitudeTo,
    float $longitudeTo
): float {
    $earthRadiusKm = 6371.0;

    $latFrom = deg2rad($latitudeFrom);
    $lngFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lngTo = deg2rad($longitudeTo);

    $latDelta = $latTo - $latFrom;
    $lngDelta = $lngTo - $lngFrom;

    $angle = 2 * asin(sqrt(
        pow(sin($latDelta / 2), 2) +
        cos($latFrom) * cos($latTo) * pow(sin($lngDelta / 2), 2)
    ));

    return $angle * $earthRadiusKm;
}

protected function estimateDrivingMinutesFromDistance(float $distanceKm, float $averageSpeedKph = 75.0): int
{
    if ($distanceKm <= 0 || $averageSpeedKph <= 0) {
        return 0;
    }

    return (int) round(($distanceKm / $averageSpeedKph) * 60);
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

    $existingIds = TripPlanItem::query()
        ->where('tripid', $trip->id)
        ->whereIn('id', $orderedIds)
        ->pluck('id')
        ->map(fn ($id) => (int) $id)
        ->values();

    if ($existingIds->count() !== $orderedIds->count()) {
        return response()->json([
            'message' => 'One or more planner items were invalid for this trip.',
        ], 422);
    }

    DB::transaction(function () use ($trip, $orderedIds) {
        $temporaryOffset = 100000;

        foreach ($orderedIds as $index => $id) {
            TripPlanItem::query()
                ->where('tripid', $trip->id)
                ->where('id', $id)
                ->update([
                    'sequence_no' => $temporaryOffset + $index + 1,
                ]);
        }

        foreach ($orderedIds as $index => $id) {
            TripPlanItem::query()
                ->where('tripid', $trip->id)
                ->where('id', $id)
                ->update([
                    'sequence_no' => $index + 1,
                ]);
        }
    });

    return response()->json([
        'message' => 'Planner order updated.',
    ]);
}

public function addNearbyPlace(Request $request, Trip $trip, TripPlanItem $tripPlanItem)
{
    abort_unless((int) $tripPlanItem->tripid === (int) $trip->id, 404);

    $validated = $request->validate([
        'placeid' => ['required', 'integer', 'exists:places,id'],
        'returnto' => ['nullable', 'string'],
    ]);

    $nearbyPlace = Place::query()->findOrFail((int) $validated['placeid']);

    $insertSequence = (int) $tripPlanItem->sequence_no + 1;

    DB::transaction(function () use ($trip, $tripPlanItem, $nearbyPlace, $insertSequence) {
        TripPlanItem::query()
            ->where('tripid', $trip->id)
            ->where('id', '!=', $tripPlanItem->id)
            ->where('sequence_no', '>=', $insertSequence)
            ->orderBy('sequence_no', 'desc')
            ->get()
            ->each(function ($item) {
                $item->update([
                    'sequence_no' => (int) $item->sequence_no + 1,
                ]);
            });

        TripPlanItem::create([
            'tripid' => $trip->id,
            'sequence_no' => $insertSequence,
            'plantype' => 'place',
            'title' => $nearbyPlace->placename,
            'placeid' => $nearbyPlace->id,
            'destinationid' => null,
            'destinationitemid' => null,
            'planneddate' => $tripPlanItem->planneddate,
            'plannedenddate' => $tripPlanItem->plannedenddate,
            'starttime' => $tripPlanItem->starttime,
            'endtime' => $tripPlanItem->endtime,
            'notes' => null,
            'sortgroup' => $tripPlanItem->sortgroup,
            'isrouteanchor' => 0,
            'isovernight' => 0,
            'isstaytarget' => 0,
            'staytype' => null,
            'nightsplanned' => null,
            'triplegid' => null,
            'tripstayid' => null,
        ]);
    });

    return redirect($validated['returnto'] ?: route('trips.planner.edit', [
        'trip' => $trip->id,
        'tripPlanItem' => $tripPlanItem->id,
    ]))->with('success', 'Nearby place added after this planning item.');
}

private function makePlannerStayKey(TripStay $stay): string
{
    return $this->makePlannerBoundaryKey(
        placeId: $stay->placeid,
        destinationId: null,
        destinationItemId: $stay->destinationitemid,
        plannedDate: $stay->checkindate
    );
}
public function rebuildFromOutputs(Request $request, Trip $trip)
{
    if (($trip->tripstatus ?? null) !== 'planned') {
        return redirect()
            ->route('trips.planner.index', $trip)
            ->withError('Planner rebuild is only available while the trip status is Planned.');
    }

    $trip->load([
        'legs' => fn ($query) => $query->orderBy('legnumber')->orderBy('id'),
        'legs.tripLegPoints' => fn ($query) => $query->orderBy('sequence_no')->orderBy('id'),
        'stays' => fn ($query) => $query->orderBy('checkindate')->orderBy('id'),
        'tripItems' => fn ($query) => $query->orderByRaw('itemdate IS NULL, itemdate ASC')->orderBy('sortorder')->orderBy('id'),
    ]);

    if (
        $trip->legs->isEmpty() &&
        $trip->stays->isEmpty() &&
        $trip->tripItems->isEmpty()
    ) {
        return redirect()
            ->route('trips.planner.index', $trip)
            ->withError('There are no trip legs, stays, or trip items to rebuild from.');
    }

    $deletedPlannerItemsCount = 0;
    $createdAnchorItemsCount = 0;
    $createdLegPointItemsCount = 0;
    $createdStayItemsCount = 0;
    $createdTripItemsCount = 0;
    $createdTotalCount = 0;

    DB::transaction(function () use (
        $trip,
        &$deletedPlannerItemsCount,
        &$createdAnchorItemsCount,
        &$createdLegPointItemsCount,
        &$createdStayItemsCount,
        &$createdTripItemsCount,
        &$createdTotalCount
    ) {
        $deletedPlannerItemsCount = TripPlanItem::query()
            ->where('tripid', $trip->id)
            ->count();

        TripPlanItem::query()
            ->where('tripid', $trip->id)
            ->delete();

        $sequence = 1;
        $usedBoundaryKeys = [];

        $staysByBoundaryKey = $trip->stays
            ->mapWithKeys(function ($stay) {
                $key = $this->makePlannerLocationDateKey(
                    placeId: $stay->placeid,
                    destinationId: null,
                    destinationItemId: $stay->destinationitemid,
                    plannedDate: $stay->checkindate
                );

                return [$key => $stay];
            });

        $createStayPlannerRow = function (TripStay $stay) use (
            $trip,
            &$sequence,
            &$createdStayItemsCount,
            &$createdTotalCount
        ) {
            $nights = $stay->nights;

            if (is_null($nights) && ! empty($stay->checkindate) && ! empty($stay->checkoutdate)) {
                $nights = Carbon::parse($stay->checkindate)
                    ->diffInDays(Carbon::parse($stay->checkoutdate));
            }

            $nights = max((int) ($nights ?? 0), 0);

            TripPlanItem::create([
                'tripid' => $trip->id,
                'sequence_no' => $sequence++,
                'plantype' => $stay->destinationitemid ? 'destination_item' : 'place',
                'title' => $stay->stayname,
                'placeid' => $stay->placeid,
                'destinationid' => null,
                'destinationitemid' => $stay->destinationitemid,
                'triplegid' => $stay->triplegid,
                'tripstayid' => $stay->id,
                'planneddate' => $stay->checkindate,
                'plannedenddate' => $stay->checkoutdate,
                'starttime' => null,
                'endtime' => null,
                'notes' => $stay->description,
                'sortgroup' => 'stay',
                'isrouteanchor' => 1,
                'isgovia' => 0,
                'isovernight' => $nights > 0,
                'isstaytarget' => 1,
                'staytype' => $stay->staytype,
                'nightsplanned' => $nights > 0 ? $nights : null,
                'mapcolor' => null,
            ]);

            $createdStayItemsCount++;
            $createdTotalCount++;
        };

        foreach ($trip->legs as $leg) {
            $startBoundaryKey = $this->makePlannerLocationDateKey(
                placeId: $leg->fromplaceid,
                destinationId: $leg->fromdestinationid ?? null,
                destinationItemId: $leg->fromdestinationitemid ?? null,
                plannedDate: $leg->startdate
            );

            if (! isset($usedBoundaryKeys[$startBoundaryKey])) {
                $startStay = $staysByBoundaryKey->get($startBoundaryKey);

                if ($startStay) {
                    $createStayPlannerRow($startStay);
                } else {
                    TripPlanItem::create(
                        $this->makePlannerRowFromLegAnchor(
                            trip: $trip,
                            sequence: $sequence++,
                            leg: $leg,
                            useStart: true
                        )
                    );

                    $createdAnchorItemsCount++;
                    $createdTotalCount++;
                }

                $usedBoundaryKeys[$startBoundaryKey] = true;
            }

            foreach ($leg->tripLegPoints as $point) {
                TripPlanItem::create(
                    $this->makePlannerRowFromLegPoint(
                        trip: $trip,
                        sequence: $sequence++,
                        leg: $leg,
                        point: $point
                    )
                );

                $createdLegPointItemsCount++;
                $createdTotalCount++;
            }

            $endBoundaryKey = $this->makePlannerLocationDateKey(
                placeId: $leg->toplaceid,
                destinationId: $leg->destinationid ?? null,
                destinationItemId: $leg->destinationitemid ?? null,
                plannedDate: $leg->enddate
            );

            if (! isset($usedBoundaryKeys[$endBoundaryKey])) {
                $endStay = $staysByBoundaryKey->get($endBoundaryKey);

                if ($endStay) {
                    $createStayPlannerRow($endStay);
                } else {
                    TripPlanItem::create(
                        $this->makePlannerRowFromLegAnchor(
                            trip: $trip,
                            sequence: $sequence++,
                            leg: $leg,
                            useStart: false
                        )
                    );

                    $createdAnchorItemsCount++;
                    $createdTotalCount++;
                }

                $usedBoundaryKeys[$endBoundaryKey] = true;
            }
        }

        foreach ($trip->tripItems as $item) {
            TripPlanItem::create([
                'tripid' => $trip->id,
                'sequence_no' => $sequence++,
                'plantype' => $this->mapTripItemTypeToPlannerType($item->itemtype),
                'title' => $item->title,
                'placeid' => $item->placeid,
                'destinationid' => $item->destinationid,
                'destinationitemid' => $item->destinationitemid,
                'triplegid' => $item->triplegid,
                'tripstayid' => $item->tripstayid,
                'planneddate' => $item->itemdate,
                'plannedenddate' => null,
                'starttime' => optional($item->startdatetime)?->format('H:i'),
                'endtime' => optional($item->enddatetime)?->format('H:i'),
                'notes' => $item->description,
                'sortgroup' => 'item',
                'isrouteanchor' => 0,
                'isgovia' => 0,
                'isovernight' => 0,
                'isstaytarget' => 0,
                'staytype' => null,
                'nightsplanned' => null,
                'mapcolor' => null,
            ]);

            $createdTripItemsCount++;
            $createdTotalCount++;
        }

        $ordered = TripPlanItem::query()
            ->where('tripid', $trip->id)
            ->orderByRaw('planneddate IS NULL, planneddate ASC')
            ->orderByRaw('plannedenddate IS NOT NULL, plannedenddate ASC')
            ->orderBy('sequence_no')
            ->orderBy('id')
            ->get();

        $temporarySequence = 100000;

        foreach ($ordered as $row) {
            $row->update([
                'sequence_no' => $temporarySequence++,
            ]);
        }

        $renumber = 1;

        foreach ($ordered as $row) {
            $row->update([
                'sequence_no' => $renumber++,
            ]);
        }
    });

    if ($createdTotalCount === 0) {
        return redirect()
            ->route('trips.planner.index', $trip)
            ->withError('Planner rebuild completed, but no planning items were created from trip outputs.');
    }

    return redirect()
        ->route('trips.planner.index', $trip)
        ->withSuccess(sprintf(
            'Deleted %d planning items. Rebuilt %d anchor items, %d leg points, %d stays, and %d trip items into %d planning items.',
            $deletedPlannerItemsCount,
            $createdAnchorItemsCount,
            $createdLegPointItemsCount,
            $createdStayItemsCount,
            $createdTripItemsCount,
            $createdTotalCount
        ));
}

private function makePlannerRowFromLegAnchor(Trip $trip, int $sequence, TripLeg $leg, bool $useStart): array
{
    $placeId = $useStart ? $leg->fromplaceid : $leg->toplaceid;
    $destinationId = $useStart
        ? ($leg->fromdestinationid ?? null)
        : ($leg->todestinationid ?? $leg->destinationid ?? null);

    $plannedDate = $useStart
        ? $leg->startdate
        : ($leg->enddate ?? $leg->startdate);

    return [
        'tripid' => $trip->id,
        'sequence_no' => $sequence,
        'plantype' => 'routeanchor',
        'title' => $this->resolveLegAnchorTitle($leg, $useStart),
        'placeid' => $placeId,
        'destinationid' => $destinationId,
        'destinationitemid' => null,
        'triplegid' => $leg->id,
        'tripstayid' => null,
        'planneddate' => $plannedDate,
        'plannedenddate' => null,
        'starttime' => null,
        'endtime' => null,
        'notes' => $useStart ? $leg->planningnotes : $leg->drivingnotes,
        'sortgroup' => 'leg',
        'isrouteanchor' => 1,
        'isgovia' => 0,
        'isovernight' => 0,
        'isstaytarget' => 0,
        'staytype' => null,
        'nightsplanned' => null,
        'mapcolor' => null,
    ];
}

private function makePlannerRowFromLegPoint(Trip $trip, int $sequence, TripLeg $leg, TripLegPoint $point): array
{
    return [
        'tripid' => $trip->id,
        'sequence_no' => $sequence,
        'plantype' => 'place',
        'title' => $point->title,
        'placeid' => $point->placeid,
        'destinationid' => $point->destinationid,
        'destinationitemid' => $point->destinationitemid,
        'triplegid' => $leg->id,
        'tripstayid' => null,
        'planneddate' => $leg->startdate,
        'plannedenddate' => null,
        'starttime' => null,
        'endtime' => null,
        'notes' => $point->notes,
        'sortgroup' => 'legpoint',
        'isrouteanchor' => 0,
        'isgovia' => 1,
        'isovernight' => 0,
        'isstaytarget' => 0,
        'staytype' => null,
        'nightsplanned' => null,
        'mapcolor' => null,
    ];
}

private function resolveLegAnchorTitle(TripLeg $leg, bool $useStart): string
{
    if ($useStart) {
        return trim((string) (
            $leg->fromPlace?->placename
            ?? $leg->fromDestination?->destinationname
            ?? 'Leg start'
        ));
    }

    return trim((string) (
        $leg->toPlace?->placename
        ?? $leg->toDestination?->destinationname
        ?? 'Leg end'
    ));
}

private function makePlannerBoundaryKey(
    ?int $placeId,
    ?int $destinationId,
    ?int $destinationItemId,
    $plannedDate
): string {
    return implode('|', [
        $placeId ?: 0,
        $destinationId ?: 0,
        $destinationItemId ?: 0,
        optional($plannedDate)->format('Y-m-d'),
    ]);
}

protected function makePlannerAnchorKey(
    ?int $placeId,
    ?int $destinationId,
    ?int $destinationItemId,
    $plannedDate,
    ?string $title = null
): string {
    $dateKey = !empty($plannedDate)
        ? Carbon::parse($plannedDate)->toDateString()
        : '';

    return implode('|', [
        $dateKey,
        'place',
        (int) ($placeId ?? 0),
    ]);
}

private function makePlannerLocationDateKey(
    ?int $placeId,
    ?int $destinationId,
    ?int $destinationItemId,
    $plannedDate
): string {
    $resolvedPlaceId = $placeId ?: 0;

    if (! $resolvedPlaceId && $destinationItemId) {
        $destinationItem = DestinationItem::query()
            ->with('destination')
            ->find($destinationItemId);

        $resolvedPlaceId = (int) ($destinationItem?->placeid ?: $destinationItem?->destination?->placeid ?: 0);
    }

    if (! $resolvedPlaceId && $destinationId) {
        $destination = Destination::query()->find($destinationId);
        $resolvedPlaceId = (int) ($destination?->placeid ?: 0);
    }

    $dateKey = ! empty($plannedDate)
        ? Carbon::parse($plannedDate)->toDateString()
        : '';

    return implode('|', [$dateKey, $resolvedPlaceId]);
}

private function mapTripItemTypeToPlannerType(?string $itemType): string
{
    return match ($itemType) {
        'fuel' => 'fuel',
        'detour' => 'detour',
        'note' => 'note',
        default => 'activity',
    };
}


}