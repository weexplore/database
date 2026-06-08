<?php

namespace App\Http\Controllers;

use App\Models\Traveller;
use App\Models\Trip;
use App\Models\TripLegSearchProfile;
use App\Models\Vehicle;
use App\Models\TripPlanItem;
use App\Models\TripLeg;
use App\Models\TripStay;
use App\Models\TripItem;
use App\Models\TripLegPoint;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TripController extends Controller
{
    protected array $statusOptions = [
        'planned',
        'active',
        'completed',
        'archived',
        'cancelled',
    ];

    public function index(Request $request)
{
    $query = Trip::query()->with('travellers');

    if ($request->filled('tripstatus')) {
        $query->where('tripstatus', $request->input('tripstatus'));
    }

    if ($request->filled('year')) {
        $year = (int) $request->input('year');
        $startOfYear = $year . '-01-01';
        $endOfYear = $year . '-12-31';

        $query->where(function ($q) use ($year, $startOfYear, $endOfYear) {
            $q->whereBetween('startdate', [$startOfYear, $endOfYear])
                ->orWhereBetween('enddate', [$startOfYear, $endOfYear])
                ->orWhere(function ($q2) use ($startOfYear, $endOfYear) {
                    $q2->whereNotNull('startdate')
                        ->whereNotNull('enddate')
                        ->where('startdate', '<=', $endOfYear)
                        ->where('enddate', '>=', $startOfYear);
                })
                ->orWhere(function ($q2) {
                    $q2->whereNull('startdate')
                        ->whereNull('enddate');
                });
        });
    }

    if ($request->filled('search')) {
        $search = trim((string) $request->input('search'));

        $query->where(function ($q) use ($search) {
            $q->where('tripname', 'like', '%' . $search . '%')
                ->orWhere('slug', 'like', '%' . $search . '%')
                ->orWhere('summary', 'like', '%' . $search . '%');
        });
    }

    $trips = $query
        ->orderByRaw('startdate IS NULL ASC')
        ->orderByDesc('startdate')
        ->orderBy('tripname')
        ->paginate(25)
        ->withQueryString();

    $availableYears = Trip::query()
        ->selectRaw('YEAR(startdate) as tripyear')
        ->whereNotNull('startdate')
        ->union(
            Trip::query()
                ->selectRaw('YEAR(enddate) as tripyear')
                ->whereNotNull('enddate')
        )
        ->orderByDesc('tripyear')
        ->pluck('tripyear');

    return view('trips.index', [
        'trips' => $trips,
        'availableYears' => $availableYears,
        'statusOptions' => $this->statusOptions,
    ]);
}

    public function bulkSave(Request $request)
    {
        $validated = $request->validate([
            'existing' => ['nullable', 'array'],

            'existing.*.tripname' => ['required', 'string', 'max:200'],
            'existing.*.tripstatus' => ['required', 'string', Rule::in($this->statusOptions)],
            'existing.*.startdate' => ['nullable', 'date'],
            'existing.*.enddate' => ['nullable', 'date', 'after_or_equal:existing.*.startdate'],
            'existing.*.travellercount' => ['nullable', 'integer', 'min:1', 'max:20'],
            'existing.*.islocked' => ['nullable', 'boolean'],

            'new' => ['nullable', 'array'],
            'new.tripname' => ['nullable', 'string', 'max:200'],
            'new.tripstatus' => ['nullable', 'string', Rule::in($this->statusOptions)],
            'new.startdate' => ['nullable', 'date'],
            'new.enddate' => ['nullable', 'date'],
            'new.travellercount' => ['nullable', 'integer', 'min:1', 'max:20'],
            'new.islocked' => ['nullable', 'boolean'],

            'tripstatus' => ['nullable', 'string', Rule::in($this->statusOptions)],
            'year' => ['nullable', 'integer'],
            'search' => ['nullable', 'string'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['existing'] ?? [] as $tripId => $row) {
                $tripname = trim((string) ($row['tripname'] ?? ''));

                if ($tripname === '') {
                    throw ValidationException::withMessages([
                        "existing.$tripId.tripname" => 'Trip name is required.',
                    ]);
                }

                $trip = Trip::findOrFail($tripId);

                $trip->update([
                    'tripname' => $tripname,
                    'tripstatus' => $row['tripstatus'],
                    'startdate' => $row['startdate'] ?? null,
                    'enddate' => $row['enddate'] ?? null,
                    'travellercount' => $row['travellercount'] ?? 2,
                    'islocked' => (bool) ($row['islocked'] ?? false),
                    'slug' => $trip->slug ?: Str::slug($tripname),
                ]);
            }

            $new = $validated['new'] ?? [];
            $newName = trim((string) ($new['tripname'] ?? ''));
            $hasNewTrip = $newName !== ''
                || !empty($new['startdate'] ?? null)
                || !empty($new['enddate'] ?? null);

            if ($hasNewTrip) {
                if ($newName === '') {
                    throw ValidationException::withMessages([
                        'new.tripname' => 'Trip name is required for a new trip.',
                    ]);
                }

                Trip::create([
                    'tripname' => $newName,
                    'slug' => Str::slug($newName),
                    'tripstatus' => $new['tripstatus'] ?? 'planned',
                    'startdate' => $new['startdate'] ?? null,
                    'enddate' => $new['enddate'] ?? null,
                    'travellercount' => $new['travellercount'] ?? 2,
                    'islocked' => (bool) ($new['islocked'] ?? false),
                ]);
            }
        });

        return redirect()
            ->route('trips.index', [
                'tripstatus' => $request->input('tripstatus'),
                'year' => $request->input('year'),
                'search' => $request->input('search'),
                'page' => $request->input('page'),
            ])
            ->with('success', 'Trips saved successfully.');
    }

    public function edit(Request $request, Trip $trip)
{
    $trip->load([
        'travellers',
        'tripTravellerLinks',
        'tripVehicles.vehicle',
        'tripLegSearchProfile',
    ]);

    $travellers = Traveller::query()
        ->where('isactive', 1)
        ->orderBy('displayname')
        ->get();

    $vehicles = Vehicle::query()
        ->where('isactive', 1)
        ->orderBy('vehiclename')
        ->get();

    $tripLegSearchProfiles = TripLegSearchProfile::query()
        ->where('isactive', 1)
        ->where(function ($query) use ($trip) {
            $query->whereNull('tripid')
                ->orWhere('tripid', $trip->id);
        })
        ->orderByDesc('isdefault')
        ->orderBy('profiletype')
        ->orderBy('profilename')
        ->get();

    $selectedTravellers = $trip->travellers
        ->pluck('id')
        ->map(fn ($id) => (int) $id)
        ->all();

    $calculatedEstimatedDistance = (float) $trip->legs()->sum('distancekm');

    $vehicleRoleOptions = [
        'towvehicle',
        'caravan',
        'trailer',
        'supportvehicle',
        'other',
    ];
    $allowedTabs = ['details', 'notes', 'budget', 'vehicles', 'travellers', 'workflow'];
    $activeTab = old('tab', $request->string('tab')->value() ?: 'details');

    if (! in_array($activeTab, $allowedTabs, true)) {
        $activeTab = 'details';
    }

    return view('trips.edit', [
        'trip' => $trip,
        'travellers' => $travellers,
        'vehicles' => $vehicles,
        'tripLegSearchProfiles' => $tripLegSearchProfiles,
        'selectedTravellers' => $selectedTravellers,
        'statusOptions' => $this->statusOptions,
        'calculatedEstimatedDistance' => $calculatedEstimatedDistance,
        'vehicleRoleOptions' => $vehicleRoleOptions,
        'activeTab' => $activeTab,
    ]);
}

    public function update(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'tripname' => ['required', 'string', 'max:200'],
            'slug' => [
                'nullable',
                'string',
                'max:220',
                Rule::unique('trips', 'slug')->ignore($trip->id),
            ],
            'tripstatus' => ['required', 'string', Rule::in($this->statusOptions)],
            'startdate' => ['nullable', 'date'],
            'enddate' => ['nullable', 'date', 'after_or_equal:startdate'],
            'summary' => ['nullable', 'string'],
            'planningnotes' => ['nullable', 'string'],
            'actualnotes' => ['nullable', 'string'],
            'travellercount' => ['nullable', 'integer', 'min:1', 'max:20'],
            'defaultdailyfoodbudget' => ['nullable', 'numeric', 'min:0'],
            'defaultdailymiscbudget' => ['nullable', 'numeric', 'min:0'],
            'defaultfuelpriceperlitre' => ['nullable', 'numeric', 'min:0'],
            'defaultfuelconsumptionlper100km' => ['nullable', 'numeric', 'min:0'],
            'estimatedtotaldistancekm' => ['nullable', 'numeric', 'min:0'],
            'actualtotaldistancekm' => ['nullable', 'numeric', 'min:0'],
            'islocked' => ['nullable', 'boolean'],
            'travellerids' => ['nullable', 'array'],
            'travellerids.*' => ['integer', 'exists:travellers,id'],
            'tripvehicles' => ['nullable', 'array'],
            'tripvehicles.*.vehicleid' => ['nullable', 'integer', 'exists:vehicles,id'],
            'tripvehicles.*.vehiclerole' => ['nullable', 'string', Rule::in([
                'towvehicle',
                'caravan',
                'trailer',
                'supportvehicle',
                'other',
            ])],
            'tripvehicles.*.sortorder' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'tripvehicles.*.isdefaultforlegs' => ['nullable', 'boolean'],
            'tripvehicles.*.notes' => ['nullable', 'string'],
            'triplegsearchprofileid' => [
                'nullable',
                'integer',
                Rule::exists('trip_leg_search_profiles', 'id')->where(function ($query) use ($trip) {
                    $query->where(function ($q) use ($trip) {
                        $q->whereNull('tripid')
                        ->orWhere('tripid', $trip->id);
                    });
                }),
            ],
        ]);

        DB::transaction(function () use ($validated, $trip) {
            $tripname = trim($validated['tripname']);

            $trip->update([
                'tripname' => $tripname,
                'slug' => filled($validated['slug'] ?? null)
                    ? Str::slug($validated['slug'])
                    : Str::slug($tripname),
                'tripstatus' => $validated['tripstatus'],
                'startdate' => $validated['startdate'] ?? null,
                'enddate' => $validated['enddate'] ?? null,
                'summary' => $validated['summary'] ?? null,
                'planningnotes' => $validated['planningnotes'] ?? null,
                'actualnotes' => $validated['actualnotes'] ?? null,
                'travellercount' => $validated['travellercount'] ?? 2,
                'defaultdailyfoodbudget' => $validated['defaultdailyfoodbudget'] ?? null,
                'defaultdailymiscbudget' => $validated['defaultdailymiscbudget'] ?? null,
                'defaultfuelpriceperlitre' => $validated['defaultfuelpriceperlitre'] ?? null,
                'defaultfuelconsumptionlper100km' => $validated['defaultfuelconsumptionlper100km'] ?? null,
                'estimatedtotaldistancekm' => $validated['estimatedtotaldistancekm'] ?? null,
                'actualtotaldistancekm' => $validated['actualtotaldistancekm'] ?? null,
                'triplegsearchprofileid' => $validated['triplegsearchprofileid'] ?? null,
                'islocked' => (bool) ($validated['islocked'] ?? false),
            ]);

            $selectedTravellerIds = collect($validated['travellerids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $syncData = [];
            foreach ($selectedTravellerIds as $travellerId) {
                $syncData[$travellerId] = [];
            }


            $trip->travellers()->sync($syncData);

                        $tripVehicleRows = collect($validated['tripvehicles'] ?? [])
                ->map(function ($row) {
                    return [
                        'vehicleid' => isset($row['vehicleid']) && $row['vehicleid'] !== '' ? (int) $row['vehicleid'] : null,
                        'vehiclerole' => $row['vehiclerole'] ?? null,
                        'sortorder' => isset($row['sortorder']) && $row['sortorder'] !== '' ? (int) $row['sortorder'] : null,
                        'isdefaultforlegs' => (bool) ($row['isdefaultforlegs'] ?? false),
                        'notes' => $row['notes'] ?? null,
                    ];
                })
                ->filter(function ($row) {
                    return !empty($row['vehicleid']);
                })
                ->values();

            $trip->tripVehicles()->delete();

            foreach ($tripVehicleRows as $row) {
                $trip->tripVehicles()->create($row);
            }

        });

        return redirect()
            ->route('trips.edit', [
                'trip' => $trip,
                'tab' => $request->input('tab', 'details'),
            ])
            ->with('success', 'Trip updated successfully.');
    }

    public function destroy(Request $request, Trip $trip)
    {
        try {
            $trip->delete();

            return redirect()
                ->route('trips.index', [
                    'tripstatus' => $request->input('tripstatus'),
                    'year' => $request->input('year'),
                    'search' => $request->input('search'),
                    'page' => $request->input('page'),
                ])
                ->with('success', 'Trip deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('trips.index', [
                    'tripstatus' => $request->input('tripstatus'),
                    'year' => $request->input('year'),
                    'search' => $request->input('search'),
                ])
                ->with('error', 'This trip is in use and cannot be deleted.');
        }
    }
    public function shiftPlannerDatesFromTrip(Request $request, Trip $trip)
{
    $validated = $request->validate([
        'tab' => ['nullable', 'string'],
        'regenerate_outputs' => ['nullable', 'boolean'],
    ]);

    if (($trip->tripstatus ?? null) !== 'planned') {
        return redirect()
            ->route('trips.edit', [
                'trip' => $trip,
                'tab' => 'workflow',
            ])
            ->with('error', 'Planner date shifting is only available while the trip status is Planned.');
    }

    $trip->refresh();

    if (empty($trip->startdate)) {
        return redirect()
            ->route('trips.edit', [
                'trip' => $trip,
                'tab' => 'workflow',
            ])
            ->with('error', 'Set the Trip Start Date first before shifting planner dates.');
    }

    $planItems = TripPlanItem::query()
        ->where('tripid', $trip->id)
        ->orderByRaw('planneddate IS NULL, planneddate ASC')
        ->orderBy('sequence_no')
        ->orderBy('id')
        ->get();

    $firstPlannedItem = $planItems->first(function ($item) {
        return ! empty($item->planneddate);
    });

    if (! $firstPlannedItem || empty($firstPlannedItem->planneddate)) {
        return redirect()
            ->route('trips.edit', [
                'trip' => $trip,
                'tab' => 'workflow',
            ])
            ->with('error', 'There are no planner items with dates to shift.');
    }

    $tripStartDate = Carbon::parse($trip->startdate)->startOfDay();
    $currentPlannerStartDate = Carbon::parse($firstPlannedItem->planneddate)->startOfDay();
    $dayOffset = $currentPlannerStartDate->diffInDays($tripStartDate, false);

    if ($dayOffset === 0) {
        return redirect()
            ->route('trips.edit', [
                'trip' => $trip,
                'tab' => 'workflow',
            ])
            ->with('success', 'Trip start date already matches the first planner date. No planner date changes were needed.');
    }

    $regenerateOutputs = (bool) ($validated['regenerate_outputs'] ?? false);

    $shiftedItemCount = 0;
    $deletedLegsCount = 0;
    $deletedStaysCount = 0;
    $deletedItemsCount = 0;
    $deletedLegPointsCount = 0;
    $createdLegsCount = 0;
    $createdStaysCount = 0;
    $createdItemsCount = 0;
    $createdLegPointsCount = 0;

    DB::transaction(function () use (
        $trip,
        $planItems,
        $dayOffset,
        $regenerateOutputs,
        &$shiftedItemCount,
        &$deletedLegsCount,
        &$deletedStaysCount,
        &$deletedItemsCount,
        &$deletedLegPointsCount,
        &$createdLegsCount,
        &$createdStaysCount,
        &$createdItemsCount,
        &$createdLegPointsCount
    ) {
        foreach ($planItems as $item) {
            $updates = [];

            if (! empty($item->planneddate)) {
                $updates['planneddate'] = Carbon::parse($item->planneddate)
                    ->addDays($dayOffset)
                    ->toDateString();
            }

            if (! empty($item->plannedenddate)) {
                $updates['plannedenddate'] = Carbon::parse($item->plannedenddate)
                    ->addDays($dayOffset)
                    ->toDateString();
            }

            if (! empty($updates)) {
                $item->update($updates);
                $shiftedItemCount++;
            }
        }

        if (! $regenerateOutputs) {
            return;
        }

        $candidates = $this->buildGenerationCandidatesForTrip($trip);

        $candidateLegs = $candidates['candidateLegs'];
        $candidateStayItems = $candidates['candidateStayItems'];
        $candidateTripItems = $candidates['candidateTripItems'];
        $candidateLegPoints = $candidates['candidateLegPoints'];

        $trip->load([
            'tripVehicles' => function ($query) {
                $query->where('isdefaultforlegs', 1)
                    ->orderByRaw('COALESCE(sortorder, 999999), id');
            },
            'legs',
            'stays',
            'tripItems',
        ]);

        $defaultVehicleSync = $trip->tripVehicles
            ->filter(fn ($tripVehicle) => ! empty($tripVehicle->vehicleid))
            ->mapWithKeys(function ($tripVehicle) {
                return [
                    $tripVehicle->vehicleid => [
                        'vehiclerole' => $tripVehicle->vehiclerole ?? null,
                        'sortorder' => $tripVehicle->sortorder ?? null,
                    ],
                ];
            })
            ->all();

        $existingLegIds = $trip->legs->pluck('id');

        if ($existingLegIds->isNotEmpty()) {
            $deletedLegPointsCount = TripLegPoint::query()
                ->whereIn('triplegid', $existingLegIds)
                ->count();

            TripLegPoint::query()
                ->whereIn('triplegid', $existingLegIds)
                ->delete();
        }

        $deletedItemsCount = $trip->tripItems->count();
        $trip->tripItems()->delete();

        $deletedStaysCount = $trip->stays->count();
        $trip->stays()->delete();

        $deletedLegsCount = $trip->legs->count();
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
                'fromdestinationid' => $fromItem->destinationid ?? null,
                'fromdestinationitemid' => $fromItem->destinationitemid ?? null,
                'destinationitemid' => $toItem->destinationitemid ?? null,
                'todestinationitemid' => $toItem->destinationitemid ?? null,
                'title' => trim($legData['from_label'].' - '.$legData['to_label']),
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

            if (! empty($defaultVehicleSync)) {
                $leg->vehicles()->sync($defaultVehicleSync);
            }

            $generatedLegs->push([
                'model' => $leg,
                'from_item_id' => $fromItem->id,
                'to_item_id' => $toItem->id,
                'from_sequence' => (int) ($fromItem->sequence_no ?? 0),
                'to_sequence' => (int) ($toItem->sequence_no ?? 0),
                'start_date' => ! empty($legData['start_date']) ? Carbon::parse($legData['start_date'])->startOfDay() : null,
                'end_date' => ! empty($legData['end_date']) ? Carbon::parse($legData['end_date'])->startOfDay() : null,
            ]);

            $createdLegsCount++;
        }

        $findLegForPlannerItem = function ($plannerItem) use ($generatedLegs) {
            $itemSequence = (int) ($plannerItem->sequence_no ?? 0);
            $itemDate = ! empty($plannerItem->planneddate)
                ? Carbon::parse($plannerItem->planneddate)->startOfDay()
                : null;

            $sequenceMatchedLeg = $generatedLegs->first(function ($legRow) use ($itemSequence) {
                return $itemSequence >= $legRow['from_sequence']
                    && $itemSequence <= $legRow['to_sequence'];
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
            $checkIn = ! empty($stayItem->planneddate)
                ? Carbon::parse($stayItem->planneddate)->startOfDay()
                : null;

            $checkOut = ! empty($stayItem->plannedenddate)
                ? Carbon::parse($stayItem->plannedenddate)->startOfDay()
                : ($checkIn ? $checkIn->copy()->addDay() : null);

            $nights = $stayItem->nightsplanned;

            if (is_null($nights) && $checkIn && $checkOut) {
                $nights = $checkIn->diffInDays($checkOut);
            }

            $nights = max((int) ($nights ?? 1), 1);

            TripStay::create([
                'tripid' => $trip->id,
                'triplegid' => $matchedLeg?->id,
                'placeid' => $stayItem->placeid ?? null,
                'destinationitemid' => $stayItem->destinationitemid ?? null,
                'stayname' => $stayItem->display_title,
                'staytype' => $stayItem->staytype ?? null,
                'checkindate' => $checkIn,
                'checkoutdate' => $checkOut,
                'nights' => $nights,
                'isaccommodationpaid' => false,
                'costpernight' => null,
                'estimatedtotalcost' => null,
                'actualtotalcost' => null,
                'travelledfromplaceid' => $matchedLeg?->fromplaceid,
                'distancetravelledkm' => null,
                'description' => $stayItem->notes ?? null,
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
                'pointtype' => $pointItem->isgovia ? 'govia' : 'waypoint',
                'placeid' => $pointItem->placeid ?? null,
                'destinationid' => $pointItem->destinationid ?? null,
                'destinationitemid' => $pointItem->destinationitemid ?? null,
                'title' => $pointItem->display_title,
                'notes' => $pointItem->notes ?? null,
            ]);

            $createdLegPointsCount++;
        }
    });

    $message = "Shifted {$shiftedItemCount} planner item date(s) by {$dayOffset} day(s) to align with the Trip start date.";

    if ($regenerateOutputs) {
        $message .= sprintf(
            ' Regenerated outputs: deleted %d legs, %d stays, %d items, %d leg points; created %d legs, %d stays, %d items, %d leg points.',
            $deletedLegsCount,
            $deletedStaysCount,
            $deletedItemsCount,
            $deletedLegPointsCount,
            $createdLegsCount,
            $createdStaysCount,
            $createdItemsCount,
            $createdLegPointsCount
        );
    }

    return redirect()
        ->route('trips.edit', [
            'trip' => $trip,
            'tab' => 'workflow',
        ])
        ->with('success', $message);
}
protected function buildGenerationCandidatesForTrip(Trip $trip): array
{
    $planItems = $trip->planItems()
        ->with(['place', 'destination', 'destinationItem', 'tripLeg', 'tripStay'])
        ->orderByRaw('planneddate IS NULL, planneddate ASC')
        ->orderBy('sequence_no')
        ->orderBy('id')
        ->get();

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

            if (
                (int) ($fromItem->placeid ?? 0) > 0 &&
                (int) ($fromItem->placeid ?? 0) === (int) ($toItem->placeid ?? 0) &&
                optional($fromItem->planneddate)->toDateString() !== optional($toItem->planneddate)->toDateString()
            ) {
                continue;
            }

            $candidateLegs->push([
                'from_item' => $fromItem,
                'to_item' => $toItem,
                'from_label' => $fromItem->display_title,
                'to_label' => $toItem->display_title,
                'from_sequence' => $fromItem->sequence_no,
                'to_sequence' => $toItem->sequence_no,
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