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
            ->with(['place', 'destination', 'destinationItem', 'tripLeg', 'tripStay'])
            ->where('tripid', $trip->id)
            ->orderByRaw('planneddate IS NULL, planneddate ASC')
            ->orderBy('sequence_no')
            ->orderBy('id')
            ->get();

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

        $showCreate = $request->boolean('show_create');
        $planTypeOptions = $this->planTypeOptions();
        $stayTypeOptions = $this->stayTypeOptions();

        $selectedPlanType = $request->input('plantype');
        $selectedPlaceId = $request->integer('place_id');
        $selectedDestinationId = $request->integer('destination_id');
        $selectedDestinationItemId = $request->integer('destinationitem_id');

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
            'selectedDestinationItemId'
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

    // Additional field from the unified form: optional related destination items
    $selectedDestinationItemIds = $request->input('selected_destinationitemids', []);
    $selectedDestinationItemIds = is_array($selectedDestinationItemIds)
        ? array_filter($selectedDestinationItemIds)
        : [];

    if (empty($validated['sequence_no'])) {
        $validated['sequence_no'] = ((int) TripPlanItem::where('tripid', $trip->id)->max('sequence_no')) + 1;
    }

    $validated['tripid'] = $trip->id;

    DB::transaction(function () use ($trip, $validated, $selectedDestinationItemIds) {
        // 1. Create the main planning item exactly as before
        $mainItem = TripPlanItem::create($validated);

        if (empty($selectedDestinationItemIds)) {
            return;
        }

        // 2. Load the selected destination items, so we can derive place/destination
        $destinationItems = DestinationItem::query()
            ->with(['destination', 'place'])
            ->whereIn('id', $selectedDestinationItemIds)
            ->get();

        if ($destinationItems->isEmpty()) {
            return;
        }

        // Avoid double-creating the main destination item if it is also in the list
        $destinationItems = $destinationItems->reject(function ($item) use ($validated) {
            return isset($validated['destinationitemid'])
                && (int) $validated['destinationitemid'] === (int) $item->id;
        });

        if ($destinationItems->isEmpty()) {
            return;
        }

        // Start sequencing after the main row
        $nextSequence = (int) TripPlanItem::query()
            ->where('tripid', $trip->id)
            ->max('sequence_no');

        $startDate = $validated['planneddate'] ?? null;
        $endDate   = $validated['plannedenddate'] ?? null;
        $sortGroup = $validated['sortgroup'] ?? null;
        $notes     = $validated['notes'] ?? null;

        foreach ($destinationItems as $destinationItem) {
            $nextSequence++;

            $placeId = $destinationItem->placeid ?: $destinationItem->destination?->placeid;
            $destinationId = $destinationItem->destinationid;

            TripPlanItem::create([
                'tripid'            => $trip->id,
                'sequence_no'       => $nextSequence,
                'plantype'          => 'destination_item',
                'title'             => $destinationItem->itemname,
                'sortgroup'         => $sortGroup,
                'placeid'           => $placeId,
                'destinationid'     => $destinationId,
                'destinationitemid' => $destinationItem->id,
                'planneddate'       => $startDate,
                'plannedenddate'    => $endDate,
                'notes'             => $notes,
                'isrouteanchor'     => 0,
                'isovernight'       => 0,
                'isstaytarget'      => 0,
            ]);
        }
    });

    return redirect()->route('trips.planner.index', $trip)
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

        $existingTripDestinationItemIds = TripPlanItem::query()
            ->where('tripid', $trip->id)
            ->whereNotNull('destinationitemid')
            ->pluck('destinationitemid')
            ->map(fn ($id) => (int) $id)
            ->unique();

        $missingIds = $selectedDestinationItemIds
            ->reject(function ($id) use ($existingTripDestinationItemIds, $validated) {
                if ($existingTripDestinationItemIds->contains($id)) {
                    return true;
                }

                if (!empty($validated['destinationitemid']) && (int) $validated['destinationitemid'] === (int) $id) {
                    return true;
                }

                return false;
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

        $nextSequence = (int) TripPlanItem::query()
            ->where('tripid', $trip->id)
            ->max('sequence_no');

        foreach ($destinationItems as $destinationItem) {
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
                'notes' => $validated['notes'] ?? null,
                'isrouteanchor' => 0,
                'isovernight' => 0,
                'isstaytarget' => 0,
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
    'staytype' => ['nullable', Rule::in(array_keys($this->stayTypeOptions()))],
    'nightsplanned' => ['nullable', 'integer', 'min:0'],
    'sortgroup' => ['nullable', 'string', 'max:30'],
    'mapcolor' => ['nullable', 'string', 'max:20'],
]);

        foreach (['isovernight', 'isstaytarget', 'isrouteanchor'] as $field) {
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

            $item->update([
                'sequence_no' => $row['sequence_no'] ?? $item->sequence_no,
                'planneddate' => $row['planneddate'] ?? null,
                'plannedenddate' => $row['plannedenddate'] ?? null,
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
        ->orderBy('sequence_no')
        ->orderBy('id')
        ->get();

    if ($items->isEmpty()) {
        return redirect()
            ->route('trips.planner.index', $trip)
            ->with('success', 'No planning items to renumber.');
    }

    \DB::transaction(function () use ($items, $trip) {
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
        &$createdLegsCount,
        &$createdStaysCount,
        &$createdItemsCount,
        &$createdLegPointsCount,
        &$deletedLegsCount,
        &$deletedStaysCount,
        &$deletedItemsCount,
        &$deletedLegPointsCount
    ) {
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

            $generatedLegs->push([
                'model' => $leg,
                'from_item_id' => $fromItem->id,
                'to_item_id' => $toItem->id,
                'from_sequence' => (int) ($fromItem->sequence_no ?? 0),
                'to_sequence' => (int) ($toItem->sequence_no ?? 0),
                'start_date' => !empty($legData['start_date']) ? \Carbon\Carbon::parse($legData['start_date'])->startOfDay() : null,
                'end_date' => !empty($legData['end_date']) ? \Carbon\Carbon::parse($legData['end_date'])->startOfDay() : null,
            ]);

            $createdLegsCount++;
        }

        $findLegForPlannerItem = function ($plannerItem) use ($generatedLegs) {
            $itemSequence = (int) ($plannerItem->sequence_no ?? 0);
            $itemDate = !empty($plannerItem->planneddate)
                ? \Carbon\Carbon::parse($plannerItem->planneddate)->startOfDay()
                : null;

            $sameDayStartingLeg = $generatedLegs->first(function ($legRow) use ($itemDate) {
                return $itemDate
                    && $legRow['start_date']
                    && $legRow['start_date']->equalTo($itemDate);
            });

            if ($sameDayStartingLeg) {
                return $sameDayStartingLeg['model'];
            }

            $sequenceMatchedLeg = $generatedLegs->first(function ($legRow) use ($itemSequence) {
                return $itemSequence >= $legRow['from_sequence']
                    && $itemSequence <= $legRow['to_sequence'];
            });

            if ($sequenceMatchedLeg) {
                return $sequenceMatchedLeg['model'];
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
                ? \Carbon\Carbon::parse($stayItem->planneddate)->startOfDay()
                : null;

            TripStay::create([
                'tripid' => $trip->id,
                'triplegid' => $matchedLeg?->id,
                'placeid' => $stayItem->placeid ?? null,
                'destinationitemid' => $stayItem->destinationitemid ?? null,
                'stayname' => $stayItem->display_title,
                'staytype' => $stayItem->staytype ?? null,
                'checkindate' => $checkIn,
                'checkoutdate' => $checkIn ? $checkIn->copy()->addDay() : null,
                'nights' => 1,
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
                'pointtype' => 'waypoint',
                'placeid' => $pointItem->placeid ?? null,
                'destinationid' => $pointItem->destinationid ?? null,
                'destinationitemid' => $pointItem->destinationitemid ?? null,
                'title' => $pointItem->display_title,
                'notes' => $pointItem->notes ?? null,
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
    $planItems = $trip->planItems()
        ->with(['place', 'destination', 'destinationItem', 'tripLeg', 'tripStay'])
        ->orderByRaw('planneddate IS NULL, planneddate ASC')
        ->orderBy('sequence_no')
        ->orderBy('id')
        ->get();

    $candidateStayItems = $planItems->filter(function ($item) {
        return $item->isovernight
            || $item->isstaytarget
            || $item->plantype === 'overnight';
    })->values();

    $candidateLegBoundaries = $planItems->filter(function ($item) {
        $isRouteAnchor = $item->isrouteanchor || $item->plantype === 'route_anchor';

        if (! $isRouteAnchor) {
            return false;
        }

        return in_array($item->plantype, ['place', 'destination', 'overnight', 'route_anchor'], true);
    })->values();

    $candidateLegPoints = $planItems->filter(function ($item) use ($candidateStayItems, $candidateLegBoundaries) {
        $isRouteAnchor = $item->isrouteanchor || $item->plantype === 'route_anchor';

        if (! $isRouteAnchor) {
            return false;
        }

        if ($candidateStayItems->contains('id', $item->id)) {
            return false;
        }

        if ($candidateLegBoundaries->contains('id', $item->id)) {
            return false;
        }

        return true;
    })->values();

    $candidateTripItems = $planItems->filter(function ($item) use ($candidateStayItems, $candidateLegBoundaries) {
        if ($candidateStayItems->contains('id', $item->id)) {
            return false;
        }

        if ($candidateLegBoundaries->contains('id', $item->id)) {
            return false;
        }

        return in_array($item->plantype, [
            'destination_item',
            'activity',
            'fuel',
            'detour',
            'note',
        ], true);
    })->values();

    $candidateLegs = collect();

    if ($candidateLegBoundaries->count() > 1) {
        for ($i = 1; $i < $candidateLegBoundaries->count(); $i++) {
            $fromItem = $candidateLegBoundaries[$i - 1];
            $toItem = $candidateLegBoundaries[$i];

            $candidateLegs->push([
                'from_item' => $fromItem,
                'to_item' => $toItem,
                'from_label' => $fromItem->display_title,
                'to_label' => $toItem->display_title,
                'planned_start' => $fromItem->planneddate,
                'planned_end' => $toItem->planneddate,
                'start_date' => $fromItem->planneddate,
                'end_date' => $toItem->planneddate,
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
}