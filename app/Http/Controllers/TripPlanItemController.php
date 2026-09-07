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
use Illuminate\Http\RedirectResponse;

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

    public function store(Request $request, Trip $trip): RedirectResponse
{
    $validated = $this->validateData($request, $trip);

    $selectedDestinationItemIds = collect(
        $request->input('selected_destinationitemids', [])
    )
        ->filter(fn ($id) => filled($id))
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values();

    $mainDestinationItemId = filled($validated['destinationitemid'] ?? null)
        ? (int) $validated['destinationitemid']
        : null;

    /*
     * Include the primary selection in the list so a single destination
     * item works whether it comes from destinationitemid, the multi-select
     * array, or both.
     */
    if ($mainDestinationItemId) {
        $selectedDestinationItemIds = $selectedDestinationItemIds
            ->prepend($mainDestinationItemId)
            ->unique()
            ->values();
    }

    $validated['tripid'] = $trip->id;
    $validated['nightsplanned'] = $this->calculateNightsPlanned(
        $validated['planneddate'] ?? null,
        $validated['plannedenddate'] ?? null,
        !empty($validated['isovernight']) || !empty($validated['isstaytarget'])
    );

    DB::transaction(function () use (
        $trip,
        $validated,
        $selectedDestinationItemIds
    ) {
        $currentMaxSequence = (int) TripPlanItem::query()
            ->where('tripid', $trip->id)
            ->max('sequence_no');

        $baseSequence = filled($validated['sequence_no'] ?? null)
            ? (int) $validated['sequence_no']
            : $currentMaxSequence + 1;

        /*
         * Standard planner item:
         * only create it where no destination item has been selected.
         */
        if ($selectedDestinationItemIds->isEmpty()) {
            $validated['sequence_no'] = $baseSequence;

            TripPlanItem::create($validated);

            return;
        }

        $destinationItems = DestinationItem::query()
            ->with(['destination.place', 'place'])
            ->whereIn('id', $selectedDestinationItemIds->all())
            ->get()
            ->keyBy('id');

        $orderedDestinationItems = $selectedDestinationItemIds
            ->map(fn (int $id) => $destinationItems->get($id))
            ->filter()
            ->values();

        if ($orderedDestinationItems->isEmpty()) {
            return;
        }

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

        foreach ($orderedDestinationItems as $index => $destinationItem) {
            $placeId = $destinationItem->placeid
                ?: $destinationItem->destination?->placeid;

            TripPlanItem::create([
                'tripid' => $trip->id,
                'sequence_no' => $baseSequence + $index,
                'plantype' => 'destination_item',
                'title' => $destinationItem->itemname,
                'sortgroup' => $sortGroup,
                'placeid' => $placeId,
                'destinationid' => $destinationItem->destinationid,
                'destinationitemid' => $destinationItem->id,
                'planneddate' => $startDate,
                'plannedenddate' => $endDate,
                'starttime' => $startTime,
                'endtime' => $endTime,

                /*
                 * Preserve planner-level notes and flags on the first
                 * destination item only. Extra selected items are normal
                 * activity items, rather than duplicate overnight stops.
                 */
                'notes' => $index === 0 ? ($validated['notes'] ?? null) : null,
                'isrouteanchor' => $index === 0
                    ? !empty($validated['isrouteanchor'])
                    : false,
                'isovernight' => $index === 0
                    ? !empty($validated['isovernight'])
                    : false,
                'isstaytarget' => $index === 0
                    ? !empty($validated['isstaytarget'])
                    : false,
                'staytype' => $index === 0
                    ? ($validated['staytype'] ?? null)
                    : null,
                'nightsplanned' => $index === 0
                    ? $nightsPlanned
                    : 0,
                'triplegid' => null,
                'tripstayid' => null,
            ]);
        }
    });

    return redirect()
        ->route('trips.planner.index', $trip)
        ->with(
            'success',
            'Planning item created with selected destination items.'
        );
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
    $resolvePlaceId = $candidates['resolvePlaceId'];
    $resolveDestinationId = $candidates['resolveDestinationId'];
    $resolveDestinationItemId = $candidates['resolveDestinationItemId'];

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
        $resolvePlaceId,
        $resolveDestinationId,
        $resolveDestinationItemId,
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

            $estimatedDistanceKm = null;

            if (($legData['leg_kind'] ?? 'transfer') === 'day_trip') {
                $dayTripTarget = $legData['day_trip_first_item'] ?? null;

                if ($dayTripTarget) {
                    $outboundKm = $this->estimatePlanningLegDistanceKm(
                        $fromItem,
                        $dayTripTarget
                    );

                    $returnKm = $this->estimatePlanningLegDistanceKm(
                        $dayTripTarget,
                        $toItem
                    );

                    if ($outboundKm !== null && $returnKm !== null) {
                        $estimatedDistanceKm = round($outboundKm + $returnKm, 1);
                    }
                }
            } else {
                $estimatedDistanceKm = $this->estimatePlanningLegDistanceKm(
                    $fromItem,
                    $toItem
                );
            }

            /*
            * A normal travel leg inherits its destination context from its arrival
            * / “to” planner item. A day trip instead uses the off-base activity
            * which caused the day-trip candidate to be created.
            */
            $legPlaceId = ($legData['leg_kind'] ?? 'transfer') === 'day_trip'
                ? ($legData['day_trip_placeid'] ?? $resolvePlaceId($toItem))
                : ($legData['to_placeid'] ?? $resolvePlaceId($toItem));

            $legDestinationId = ($legData['leg_kind'] ?? 'transfer') === 'day_trip'
                ? ($legData['day_trip_destinationid'] ?? $resolveDestinationId($toItem))
                : ($legData['to_destinationid'] ?? $resolveDestinationId($toItem));

            $legDestinationItemId = ($legData['leg_kind'] ?? 'transfer') === 'day_trip'
                ? ($legData['day_trip_destinationitemid'] ?? $resolveDestinationItemId($toItem))
                : ($legData['to_destinationitemid'] ?? $resolveDestinationItemId($toItem));

            $leg = TripLeg::create([
                'tripid' => $trip->id,
                'legnumber' => $nextLegNumber++,
                'startdate' => $legData['start_date'],
                'enddate' => $legData['end_date'],
                'nightsplanned' => null,

                'fromplaceid' => $legData['from_placeid'] ?? $resolvePlaceId($fromItem),
                'toplaceid' => $legData['to_placeid'] ?? $resolvePlaceId($toItem),

                'fromdestinationid' => $legData['from_destinationid']
                    ?? $resolveDestinationId($fromItem),

                'todestinationid' => $legData['to_destinationid']
                    ?? $resolveDestinationId($toItem),

                'fromdestinationitemid' => $legData['from_destinationitemid']
                    ?? $resolveDestinationItemId($fromItem),

                'todestinationitemid' => $legData['to_destinationitemid']
                    ?? $resolveDestinationItemId($toItem),

                'title' => trim($legData['from_label'] . ' → ' . $legData['to_label']),
                'description' => null,
                'distancekm' => $estimatedDistanceKm,
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
                'from_item' => $fromItem,
                'to_item' => $toItem,
                'from_item_id' => $fromItem->id,
                'to_item_id' => $toItem->id,
                'from_sequence' => (int) ($legData['from_sequence'] ?? $fromItem->sequence_no ?? 0),
                'to_sequence' => (int) ($legData['to_sequence'] ?? $toItem->sequence_no ?? 0),
                'start_date' => !empty($legData['start_date'])
                    ? Carbon::parse($legData['start_date'])->startOfDay()
                    : null,
                'end_date' => !empty($legData['end_date'])
                    ? Carbon::parse($legData['end_date'])->startOfDay()
                    : null,
                'day_key' => $legData['day_key'] ?? (!empty($legData['start_date'])
                    ? Carbon::parse($legData['start_date'])->toDateString()
                    : null),
                'leg_kind' => $legData['leg_kind'] ?? 'transfer',
            ]);

            $fromItem->update([
                'triplegid' => $leg->id,
            ]);

            if ((int) $toItem->id !== (int) $fromItem->id) {
                $toItem->update([
                    'triplegid' => $leg->id,
                ]);
            }

            $createdLegsCount++;
        }

        $findLegForPlannerItem = function ($plannerItem) use ($generatedLegs) {
    $itemSequence = (int) ($plannerItem->sequence_no ?? 0);
    $itemDate = !empty($plannerItem->planneddate)
        ? Carbon::parse($plannerItem->planneddate)->startOfDay()
        : null;
    $itemDayKey = $itemDate?->toDateString();

    if ($itemDayKey) {
        $sameDayLegs = $generatedLegs
            ->filter(fn ($legRow) => ($legRow['day_key'] ?? null) === $itemDayKey)
            ->sortBy([
                ['from_sequence', 'asc'],
                ['to_sequence', 'asc'],
            ])
            ->values();

        $sequenceMatchedLeg = $sameDayLegs->first(function ($legRow) use ($itemSequence) {
            return $itemSequence >= (int) ($legRow['from_sequence'] ?? 0)
                && $itemSequence <= (int) ($legRow['to_sequence'] ?? 0);
        });

        if ($sequenceMatchedLeg) {
            return $sequenceMatchedLeg['model'];
        }

        if ($sameDayLegs->count() === 1) {
            return $sameDayLegs->first()['model'];
        }
    }

    $sequenceMatchedLeg = $generatedLegs->first(function ($legRow) use ($itemSequence) {
        return $itemSequence >= (int) ($legRow['from_sequence'] ?? 0)
            && $itemSequence <= (int) ($legRow['to_sequence'] ?? 0);
    });

    if ($sequenceMatchedLeg) {
        return $sequenceMatchedLeg['model'];
    }

    return null;
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

                'placeid' => $resolvePlaceId($stayItem),
                'destinationid' => $resolveDestinationId($stayItem),
                'destinationitemid' => $resolveDestinationItemId($stayItem),

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

                'placeid' => $resolvePlaceId($item),
                'destinationid' => $resolveDestinationId($item),
                'destinationitemid' => $resolveDestinationItemId($item),

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

        $legPointSequenceByLeg = [];

        foreach ($candidateLegPoints as $pointItem) {
            $matchedLeg = $findLegForPlannerItem($pointItem);

            if (! $matchedLeg) {
                continue;
            }

            $legPointSequenceByLeg[$matchedLeg->id] = ($legPointSequenceByLeg[$matchedLeg->id] ?? 0) + 1;

            TripLegPoint::create([
                'triplegid' => $matchedLeg->id,
                'sequence_no' => $legPointSequenceByLeg[$matchedLeg->id],
                'pointtype' => $pointItem->isgovia ? 'govia' : 'waypoint',

                'placeid' => $resolvePlaceId($pointItem),
                'destinationid' => $resolveDestinationId($pointItem),
                'destinationitemid' => $resolveDestinationItemId($pointItem),

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
            ->with([
                'place',
                'destination.place',
                'destinationItem.place',
                'destinationItem.destination.place',
                'tripLeg',
                'tripStay',
            ])
            ->orderByRaw('planneddate IS NULL, planneddate ASC')
            ->orderByRaw('plannedenddate IS NOT NULL, plannedenddate ASC')
            ->orderBy('sequence_no')
            ->orderBy('id'),
    ]);

    $planItems = $trip->planItems
        ->sortBy([
            ['sequence_no', 'asc'],
            ['id', 'asc'],
        ])
        ->values();

    /*
     * Prefer the explicitly selected Place. If the planning item was
     * created from a Destination Item or Destination, fall back through
     * those related records to obtain its effective Place.
     */
    $resolvePlaceId = function ($item): ?int {
        $placeId = $item->placeid
            ?: $item->destinationItem?->placeid
            ?: $item->destinationItem?->destination?->placeid
            ?: $item->destination?->placeid
            ?: null;

        return filled($placeId)
            ? (int) $placeId
            : null;
    };

    /*
     * Prefer the planning item's explicit Destination. A selected
     * Destination Item also identifies its owning Destination, so use
     * that as a safe fallback where destinationid is not populated.
     */
    $resolveDestinationId = function ($item): ?int {
        $destinationId = $item->destinationid
            ?: $item->destinationItem?->destinationid
            ?: null;

        return filled($destinationId)
            ? (int) $destinationId
            : null;
    };

    /*
     * A Destination Item must be explicitly linked on the planning item.
     * It cannot be inferred merely from the selected Place or Destination,
     * because many items may exist beneath the same destination.
     */
    $resolveDestinationItemId = function ($item): ?int {
        return filled($item->destinationitemid)
            ? (int) $item->destinationitemid
            : null;
    };

    $hasPlanningLocation = function ($item) use (
        $resolvePlaceId,
        $resolveDestinationId,
        $resolveDestinationItemId
    ): bool {
        return $resolvePlaceId($item) !== null
            || $resolveDestinationId($item) !== null
            || $resolveDestinationItemId($item) !== null;
    };

    $samePlace = function ($a, $b) use ($resolvePlaceId): bool {
        if (! $a || ! $b) {
            return false;
        }

        $aPlaceId = $resolvePlaceId($a);
        $bPlaceId = $resolvePlaceId($b);

        return $aPlaceId !== null
            && $bPlaceId !== null
            && $aPlaceId === $bPlaceId;
    };

    /*
     * A planning item becomes a candidate Stay when it has a location and
     * is marked as an overnight stop or a stay target.
     */
    $candidateStayItems = $planItems
        ->filter(function ($item) use ($hasPlanningLocation) {
            return $hasPlanningLocation($item)
                && ((bool) $item->isovernight || (bool) $item->isstaytarget);
        })
        ->values();

    $candidateLegs = collect();

    $firstLocatedItem = $planItems->first(function ($item) use ($hasPlanningLocation) {
        return $hasPlanningLocation($item);
    });

    /*
     * If there is a located planning item before the first candidate stay,
     * generate an initial transfer leg to that first stay.
     */
    if ($candidateStayItems->isNotEmpty()) {
        $firstStay = $candidateStayItems->first();

        if (
            $firstLocatedItem
            && (int) $firstLocatedItem->id !== (int) $firstStay->id
            && (int) ($firstLocatedItem->sequence_no ?? 0) < (int) ($firstStay->sequence_no ?? 0)
        ) {
            $candidateLegs->push([
                'from_item' => $firstLocatedItem,
                'to_item' => $firstStay,

                'from_label' => $firstLocatedItem->display_title,
                'to_label' => $firstStay->display_title,

                'from_sequence' => (int) ($firstLocatedItem->sequence_no ?? 0),
                'to_sequence' => (int) ($firstStay->sequence_no ?? 0),

                'from_placeid' => $resolvePlaceId($firstLocatedItem),
                'from_destinationid' => $resolveDestinationId($firstLocatedItem),
                'from_destinationitemid' => $resolveDestinationItemId($firstLocatedItem),

                'to_placeid' => $resolvePlaceId($firstStay),
                'to_destinationid' => $resolveDestinationId($firstStay),
                'to_destinationitemid' => $resolveDestinationItemId($firstStay),

                'planned_start' => $firstStay->planneddate,
                'planned_end' => $firstStay->planneddate,
                'start_date' => $firstStay->planneddate,
                'end_date' => $firstStay->planneddate,

                'day_key' => filled($firstStay->planneddate)
                    ? Carbon::parse($firstStay->planneddate)->toDateString()
                    : null,

                'leg_kind' => 'transfer',
            ]);
        }

        for ($i = 1; $i < $candidateStayItems->count(); $i++) {
            $fromStay = $candidateStayItems[$i - 1];
            $toStay = $candidateStayItems[$i];

            $betweenItems = $planItems
                ->filter(function ($item) use ($fromStay, $toStay) {
                    $sequence = (int) ($item->sequence_no ?? 0);

                    return $sequence > (int) ($fromStay->sequence_no ?? 0)
                        && $sequence < (int) ($toStay->sequence_no ?? 0);
                })
                ->values();

            /*
             * Different Places mean this is a conventional transfer leg.
             */
            if (! $samePlace($fromStay, $toStay)) {
                $candidateLegs->push([
                    'from_item' => $fromStay,
                    'to_item' => $toStay,

                    'from_label' => $fromStay->display_title,
                    'to_label' => $toStay->display_title,

                    'from_sequence' => (int) ($fromStay->sequence_no ?? 0),
                    'to_sequence' => (int) ($toStay->sequence_no ?? 0),

                    'from_placeid' => $resolvePlaceId($fromStay),
                    'from_destinationid' => $resolveDestinationId($fromStay),
                    'from_destinationitemid' => $resolveDestinationItemId($fromStay),

                    'to_placeid' => $resolvePlaceId($toStay),
                    'to_destinationid' => $resolveDestinationId($toStay),
                    'to_destinationitemid' => $resolveDestinationItemId($toStay),

                    'planned_start' => $toStay->planneddate,
                    'planned_end' => $toStay->planneddate,
                    'start_date' => $toStay->planneddate,
                    'end_date' => $toStay->planneddate,

                    'day_key' => filled($toStay->planneddate)
                        ? Carbon::parse($toStay->planneddate)->toDateString()
                        : null,

                    'leg_kind' => 'transfer',
                ]);

                continue;
            }

            /*
             * The two stays are at the same Place. Look for intervening
             * non-stay records at another Place; these create a day trip.
             */
            $offBaseItems = $betweenItems
                ->filter(function ($item) use ($fromStay, $samePlace, $hasPlanningLocation) {
                    if (! $hasPlanningLocation($item)) {
                        return false;
                    }

                    if ((bool) $item->isovernight || (bool) $item->isstaytarget) {
                        return false;
                    }

                    return ! $samePlace($item, $fromStay);
                })
                ->values();

            if ($offBaseItems->isEmpty()) {
                continue;
            }

            $firstOffBase = $offBaseItems->first();
            $lastOffBase = $offBaseItems->last();

            $candidateLegs->push([
                'from_item' => $fromStay,
                'to_item' => $toStay,

                'from_label' => $fromStay->display_title,
                'to_label' => $toStay->display_title,

                /*
                 * For a day trip, the effective sequence boundaries are the
                 * off-base points, so related planned activities fall inside
                 * the generated day-trip leg.
                 */
                'from_sequence' => (int) ($firstOffBase->sequence_no ?? $fromStay->sequence_no ?? 0),
                'to_sequence' => (int) ($lastOffBase->sequence_no ?? $toStay->sequence_no ?? 0),

                'from_placeid' => $resolvePlaceId($fromStay),
                'from_destinationid' => $resolveDestinationId($fromStay),
                'from_destinationitemid' => $resolveDestinationItemId($fromStay),

                'to_placeid' => $resolvePlaceId($toStay),
                'to_destinationid' => $resolveDestinationId($toStay),
                'to_destinationitemid' => $resolveDestinationItemId($toStay),

                /*
                 * Preserve the actual off-base destination context separately.
                 * This is particularly useful if the later generator wants
                 * the day-trip target to define the leg title/destination.
                 */
                'day_trip_first_item' => $firstOffBase,
                'day_trip_last_item' => $lastOffBase,

                'day_trip_placeid' => $resolvePlaceId($firstOffBase),
                'day_trip_destinationid' => $resolveDestinationId($firstOffBase),
                'day_trip_destinationitemid' => $resolveDestinationItemId($firstOffBase),

                'planned_start' => $firstOffBase->planneddate ?? $fromStay->planneddate,
                'planned_end' => $lastOffBase->planneddate ?? $toStay->planneddate,
                'start_date' => $firstOffBase->planneddate ?? $fromStay->planneddate,
                'end_date' => $lastOffBase->planneddate ?? $toStay->planneddate,

                'day_key' => filled($firstOffBase->planneddate)
                    ? Carbon::parse($firstOffBase->planneddate)->toDateString()
                    : (filled($fromStay->planneddate)
                        ? Carbon::parse($fromStay->planneddate)->toDateString()
                        : null),

                'leg_kind' => 'day_trip',
            ]);
        }
    }

    $candidateLegs = $candidateLegs
        ->sortBy(function ($leg) {
            $startTimestamp = filled($leg['start_date'] ?? null)
                ? Carbon::parse($leg['start_date'])->startOfDay()->timestamp
                : PHP_INT_MAX;

            $fromSequence = (int) ($leg['from_sequence'] ?? PHP_INT_MAX);
            $toSequence = (int) ($leg['to_sequence'] ?? PHP_INT_MAX);

            return sprintf(
                '%010d-%09d-%09d',
                $startTimestamp,
                $fromSequence,
                $toSequence
            );
        })
        ->values();

    $candidateLegBoundaryIds = $candidateLegs
        ->flatMap(fn ($leg) => [
            (int) $leg['from_item']->id,
            (int) $leg['to_item']->id,
        ])
        ->unique()
        ->values();

    $candidateLegBoundaries = $planItems
        ->filter(fn ($item) => $candidateLegBoundaryIds->contains((int) $item->id))
        ->values();

    $isItemWithinLeg = function ($item, array $leg): bool {
        $sequence = (int) ($item->sequence_no ?? 0);

        if (($leg['leg_kind'] ?? null) === 'day_trip') {
            return $sequence >= (int) ($leg['from_sequence'] ?? 0)
                && $sequence <= (int) ($leg['to_sequence'] ?? 0);
        }

        return $sequence > (int) ($leg['from_sequence'] ?? 0)
            && $sequence < (int) ($leg['to_sequence'] ?? PHP_INT_MAX);
    };

    /*
     * These are located non-stay items specifically marked as “Go via”.
     * They are excluded from normal generated Trip Items because they are
     * intended to become route points for a generated Trip Leg.
     */
    $candidateLegPoints = $planItems
        ->filter(function ($item) use (
            $candidateStayItems,
            $candidateLegBoundaryIds,
            $candidateLegs,
            $hasPlanningLocation,
            $isItemWithinLeg
        ) {
            if (! $hasPlanningLocation($item)) {
                return false;
            }

            if ($candidateStayItems->contains('id', $item->id)) {
                return false;
            }

            if ($candidateLegBoundaryIds->contains((int) $item->id)) {
                return false;
            }

            if (! (bool) $item->isgovia) {
                return false;
            }

            return $candidateLegs->contains(
                fn ($leg) => $isItemWithinLeg($item, $leg)
            );
        })
        ->values();

    /*
     * These become normal generated Trip Items. They are neither stays,
     * leg boundaries, nor route/via points.
     */
    $candidateTripItems = $planItems
        ->filter(function ($item) use (
            $candidateStayItems,
            $candidateLegBoundaryIds,
            $candidateLegPoints,
            $candidateLegs,
            $hasPlanningLocation,
            $isItemWithinLeg
        ) {
            if (! $hasPlanningLocation($item)) {
                return false;
            }

            if ($candidateStayItems->contains('id', $item->id)) {
                return false;
            }

            if ($candidateLegBoundaryIds->contains((int) $item->id)) {
                return false;
            }

            if ($candidateLegPoints->contains('id', $item->id)) {
                return false;
            }

            return $candidateLegs->contains(
                fn ($leg) => $isItemWithinLeg($item, $leg)
            );
        })
        ->values();

    return [
        'planItems' => $planItems,

        'candidateLegs' => $candidateLegs,
        'candidateStayItems' => $candidateStayItems,
        'candidateTripItems' => $candidateTripItems,
        'candidateLegPoints' => $candidateLegPoints,
        'candidateLegBoundaries' => $candidateLegBoundaries,

        /*
         * These closures are returned so the later persistence/generation
         * method can use exactly the same fallback rules when it maps
         * planning records into Trip Legs, Stays, Items, or Leg Points.
         */
        'resolvePlaceId' => $resolvePlaceId,
        'resolveDestinationId' => $resolveDestinationId,
        'resolveDestinationItemId' => $resolveDestinationItemId,
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

protected function estimatePlanningLegDistanceKm(
    TripPlanItem $fromItem,
    TripPlanItem $toItem
): ?float {
    $fromCoordinates = $this->resolvePlanningItemCoordinates($fromItem);
    $toCoordinates = $this->resolvePlanningItemCoordinates($toItem);

    if (! $fromCoordinates['has_coordinates'] || ! $toCoordinates['has_coordinates']) {
        return null;
    }

    $straightLineKm = $this->haversineDistanceKm(
        $fromCoordinates['latitude'],
        $fromCoordinates['longitude'],
        $toCoordinates['latitude'],
        $toCoordinates['longitude']
    );

    return round($this->estimateRoadDistanceKm($straightLineKm), 1);
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
        'stays' => fn ($query) => $query
            ->with(['destinationItem.destination'])
            ->orderBy('checkindate')
            ->orderBy('id'),
        'tripItems' => fn ($query) => $query
            ->orderByRaw('itemdate IS NULL, itemdate ASC')
            ->orderBy('sortorder')
            ->orderBy('id'),
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
                    destinationId: $stay->destinationid
                        ?? $stay->destinationItem?->destinationid
                        ?? null,
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

                'destinationid' => $stay->destinationid
                    ?? $stay->destinationItem?->destinationid
                    ?? null,

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