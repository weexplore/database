<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TripReportController extends Controller
{
    public function book(Request $request, Trip $trip)
    {
        $trip->load([
            'travellers',

            'legs' => function ($query) {
                $query
                    ->with([
                        'fromPlace',
                        'fromDestination',
                        'fromDestinationItem.place',
                        'toPlace',
                        'toDestination',
                        'toDestinationItem.place',
                        'legPoints' => function ($q) {
                            $q->with([
                                'place',
                                'destination',
                                'destinationItem.place',
                            ])
                            ->orderBy('sequence_no')
                            ->orderBy('id');
                        },
                    ])
                    ->orderBy('legnumber')
                    ->orderBy('id');
            },

            'stays' => function ($query) {
                $query
                    ->with([
                        'place',
                        'tripLeg',
                        'travelledFromPlace',
                    ])
                    ->orderBy('checkindate')
                    ->orderBy('id');
            },

            'tripItems' => function ($query) {
                $query
                    ->with([
                        'tripleg',
                        'stay',
                        'destination',
                        'destinationItem.place',
                        'destinationItem.itemTypes',
                        'place',
                    ])
                    ->orderBy('itemdate')
                    ->orderBy('startdatetime')
                    ->orderBy('id');
            },

            'reviews' => function ($query) {
                $query
                    ->with([
                        'traveller',
                        'stay',
                        'tripItem',
                        'destination',
                        'destinationItem.place',
                        'place',
                    ])
                    ->orderBy('reviewdate')
                    ->orderBy('id');
            },

            'fuelEstimates' => function ($query) {
                $query
                    ->with(['tripLeg', 'fuelStop', 'place'])
                    ->orderBy('estimatedate')
                    ->orderBy('id');
            },

            'fuelPurchases' => function ($query) {
                $query
                    ->with(['leg', 'fuelStop', 'place'])
                    ->orderBy('purchasedate')
                    ->orderBy('id');
            },
            'expenses' => function ($query) {
                $query
                    ->with([
                        'tripLeg',
                        'tripStay',
                        'place',
                    ])
                    ->orderBy('expensedate')
                    ->orderBy('id');
            },
        ]);

        $tripItemsByLeg = $trip->tripItems
            ->groupBy(fn ($item) => (int) ($item->triplegid ?? 0));

        $staysByLeg = $trip->stays
            ->groupBy(fn ($stay) => (int) ($stay->triplegid ?? 0));

        $trip->legs->each(function ($leg) use ($tripItemsByLeg, $staysByLeg) {
            $legItems = $tripItemsByLeg
                ->get((int) $leg->id, collect())
                ->sortBy([
                    ['itemdate', 'asc'],
                    ['startdatetime', 'asc'],
                    ['sortorder', 'asc'],
                    ['id', 'asc'],
                ])
                ->values();

            $legStays = $staysByLeg
                ->get((int) $leg->id, collect())
                ->sortBy([
                    ['checkindate', 'asc'],
                    ['checkoutdate', 'asc'],
                    ['id', 'asc'],
                ])
                ->values();

            $isReportDayTrip =
                !empty($leg->fromplaceid) &&
                !empty($leg->toplaceid) &&
                (int) $leg->fromplaceid === (int) $leg->toplaceid;

            $basePlaceId = (int) ($leg->fromplaceid ?? 0);

            $reportTripItems = $isReportDayTrip
                ? $legItems->reject(function ($item) use ($basePlaceId) {
                    return (int) ($item->placeid ?? 0) === $basePlaceId;
                })->values()
                : $legItems;

            $reportClosingStay = $legStays->last();

            $reportTransitStays = $legStays->count() > 1
                ? $legStays->slice(0, $legStays->count() - 1)->values()
                : collect();

            $leg->setRelation('reportTripItems', $reportTripItems);
            $leg->setRelation('reportStays', $legStays);
            $leg->setRelation('reportTransitStays', $reportTransitStays);
            $leg->setRelation('reportClosingStay', $reportClosingStay);

            $leg->is_report_day_trip = $isReportDayTrip;
            $leg->report_base_place_id = $basePlaceId;
        });

        $stayEstimatedTotal = (float) $trip->stays()->sum('estimatedtotalcost');
        $stayActualTotal = (float) $trip->stays()->sum('actualtotalcost');
        $stayNightsTotal = (int) $trip->stays->sum(
            fn ($stay) => (int) ($stay->nights ?? 0)
        );

        $itemEstimatedTotal = (float) $trip->items()->sum('estimatedtotalcost');
        $itemActualTotal = (float) $trip->items()->sum('actualcost');

        $estimatedDistanceKm = $trip->estimatedtotaldistancekm;
        $defaultFuelConsumption = $trip->defaultfuelconsumptionlper100km;
        $defaultFuelPrice = $trip->defaultfuelpriceperlitre;

        $fuelEstimateLitres = null;
        $fuelEstimateTotal = null;

        if (
            $estimatedDistanceKm !== null &&
            $defaultFuelConsumption !== null &&
            $defaultFuelPrice !== null
        ) {
            $fuelEstimateLitres = ((float) $estimatedDistanceKm / 100) * (float) $defaultFuelConsumption;
            $fuelEstimateTotal = $fuelEstimateLitres * (float) $defaultFuelPrice;
        }

        $fuelActualTotal = (float) $trip->fuelPurchases()->sum('fueltotal');

        $tripDays = null;

        if ($trip->startdate && $trip->enddate) {
            $tripDays = $trip->startdate->diffInDays($trip->enddate) + 1;
        }

        $dailyFoodBudget = $trip->defaultdailyfoodbudget;
        $dailyMiscBudget = $trip->defaultdailymiscbudget;

        $foodBudgetTotal = ($tripDays !== null && $dailyFoodBudget !== null)
            ? $tripDays * (float) $dailyFoodBudget
            : null;

        $miscBudgetTotal = ($tripDays !== null && $dailyMiscBudget !== null)
            ? $tripDays * (float) $dailyMiscBudget
            : null;

        /*
        * Actual Trip Expenses:
        * - Food is any expense explicitly categorised as food.
        * - Everything else is reported as Miscellaneous in the Book,
        *   while still retaining its detailed category in the expense table.
        */
        $foodActualTotal = (float) $trip->expenses
            ->where('expensecategory', 'food')
            ->sum('amount');

        $miscActualTotal = (float) $trip->expenses
            ->where('expensecategory', '!=', 'food')
            ->sum('amount');

        $overallEstimatedTotal =
            (float) ($foodBudgetTotal ?? 0) +
            (float) ($miscBudgetTotal ?? 0) +
            (float) ($stayEstimatedTotal ?? 0) +
            (float) ($itemEstimatedTotal ?? 0) +
            (float) ($fuelEstimateTotal ?? 0);

        $overallActualTotal =
            (float) ($foodActualTotal ?? 0) +
            (float) ($miscActualTotal ?? 0) +
            (float) ($stayActualTotal ?? 0) +
            (float) ($itemActualTotal ?? 0) +
            (float) ($fuelActualTotal ?? 0);

        $bookTotals = [
            'trip_days' => $tripDays,
            'stay_nights' => $stayNightsTotal,

            'planned_distance_km' => $trip->estimatedtotaldistancekm !== null
                ? (float) $trip->estimatedtotaldistancekm
                : null,

            'actual_distance_km' => $trip->actualtotaldistancekm !== null
                ? (float) $trip->actualtotaldistancekm
                : null,

            'food_planned' => $foodBudgetTotal,
            'food_actual' => $foodActualTotal,

            'misc_planned' => $miscBudgetTotal,
            'misc_actual' => $miscActualTotal,

            'stay_planned' => $stayEstimatedTotal,
            'stay_actual' => $stayActualTotal,

            'item_planned' => $itemEstimatedTotal,
            'item_actual' => $itemActualTotal,

            'fuel_planned' => $fuelEstimateTotal,
            'fuel_actual' => $fuelActualTotal,

            'overall_planned' => $overallEstimatedTotal,
            'overall_actual' => $overallActualTotal,
        ];

        return view('reports.trips.book', [
            'trip' => $trip,

            'tripDays' => $tripDays,
            'stayNightsTotal' => $stayNightsTotal,

            'dailyFoodBudget' => $dailyFoodBudget,
            'dailyMiscBudget' => $dailyMiscBudget,

            'foodBudgetTotal' => $foodBudgetTotal,
            'foodActualTotal' => $foodActualTotal,

            'miscBudgetTotal' => $miscBudgetTotal,
            'miscActualTotal' => $miscActualTotal,

            'fuelEstimateTotal' => $fuelEstimateTotal,
            'fuelActualTotal' => $fuelActualTotal,

            'stayEstimatedTotal' => $stayEstimatedTotal,
            'stayActualTotal' => $stayActualTotal,

            'itemEstimatedTotal' => $itemEstimatedTotal,
            'itemActualTotal' => $itemActualTotal,

            'overallEstimatedTotal' => $overallEstimatedTotal,
            'overallActualTotal' => $overallActualTotal,

            'expenses' => $trip->expenses,
            'bookTotals' => $bookTotals,
        ]);
    }
    public function summary(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'trip_status' => ['nullable', 'string', 'max:30'],
        ]);

        $dateFrom = $validated['date_from'] ?? null;
        $dateTo = $validated['date_to'] ?? null;
        $tripStatus = $validated['trip_status'] ?? null;

        /*
        * Include whole trips that overlap the selected date range.
        *
        * A trip that begins before the selected range but ends during it,
        * or begins during it and ends later, is still relevant and is
        * therefore included.
        */
        $trips = Trip::query()
            ->with([
                'travellers',
            ])
            ->withSum('stays as stay_nights_total', 'nights')
            ->withSum('stays as stay_estimated_total', 'estimatedtotalcost')
            ->withSum('stays as stay_actual_total', 'actualtotalcost')
            ->withSum('tripItems as item_estimated_total', 'estimatedtotalcost')
            ->withSum('tripItems as item_actual_total', 'actualcost')
            ->withSum('fuelPurchases as fuel_actual_total', 'fueltotal')
            ->withSum(
                [
                    'expenses as food_actual_total' => fn ($query) => $query
                        ->where('expensecategory', 'food'),
                ],
                'amount'
            )
            ->withSum(
                [
                    /*
                    * The detailed Trip Expense categories—tolls, parking, laundry,
                    * medical, pharmacy, supplies, maintenance and other—are all
                    * reported under the high-level Misc column.
                    */
                    'expenses as misc_actual_total' => fn ($query) => $query
                        ->where('expensecategory', '!=', 'food'),
                ],
                'amount'
            )
            ->when($tripStatus, function ($query) use ($tripStatus) {
                $query->where('tripstatus', $tripStatus);
            })
            ->when($dateFrom, function ($query) use ($dateFrom) {
                $query->whereDate('enddate', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query) use ($dateTo) {
                $query->whereDate('startdate', '<=', $dateTo);
            })
            ->orderByRaw('startdate IS NULL, startdate ASC')
            ->orderBy('startdate')
            ->orderBy('tripname')
            ->get();

        /*
        * Derive the report figures using the same rules as the single
        * Trip Book report.
        */
        $reportRows = $trips
            ->map(function (Trip $trip) {
                $tripDays = null;

                if ($trip->startdate && $trip->enddate) {
                    $tripDays = $trip->startdate
                        ->copy()
                        ->startOfDay()
                        ->diffInDays(
                            $trip->enddate
                                ->copy()
                                ->startOfDay()
                        ) + 1;
                }

                $stayNights = (int) ($trip->stay_nights_total ?? 0);

                $plannedDistanceKm = $trip->estimatedtotaldistancekm !== null
                    ? (float) $trip->estimatedtotaldistancekm
                    : null;

                $actualDistanceKm = $trip->actualtotaldistancekm !== null
                    ? (float) $trip->actualtotaldistancekm
                    : null;

                $foodPlanned = $tripDays !== null
                    && $trip->defaultdailyfoodbudget !== null
                    ? $tripDays * (float) $trip->defaultdailyfoodbudget
                    : null;

                $miscPlanned = $tripDays !== null
                    && $trip->defaultdailymiscbudget !== null
                    ? $tripDays * (float) $trip->defaultdailymiscbudget
                    : null;

                $stayPlanned = (float) ($trip->stay_estimated_total ?? 0);
                $stayActual = (float) ($trip->stay_actual_total ?? 0);

                $itemPlanned = (float) ($trip->item_estimated_total ?? 0);
                $itemActual = (float) ($trip->item_actual_total ?? 0);

                $fuelPlanned = null;

                if (
                    $plannedDistanceKm !== null
                    && $trip->defaultfuelconsumptionlper100km !== null
                    && $trip->defaultfuelpriceperlitre !== null
                ) {
                    $fuelLitres = ($plannedDistanceKm / 100)
                        * (float) $trip->defaultfuelconsumptionlper100km;

                    $fuelPlanned = $fuelLitres
                        * (float) $trip->defaultfuelpriceperlitre;
                }

                $fuelActual = (float) ($trip->fuel_actual_total ?? 0);

                /*
                * Planned amounts are derived from the Trip-level daily budget.
                * Actual amounts are from TripExpense.amount:
                * - food category becomes Food;
                * - all other categories become Misc.
                */
                $foodActual = (float) ($trip->food_actual_total ?? 0);
                $miscActual = (float) ($trip->misc_actual_total ?? 0);

                $overallPlanned =
                    (float) ($foodPlanned ?? 0) +
                    (float) ($miscPlanned ?? 0) +
                    $stayPlanned +
                    $itemPlanned +
                    (float) ($fuelPlanned ?? 0);

                $overallActual =
                    $foodActual +
                    $miscActual +
                    $stayActual +
                    $itemActual +
                    $fuelActual;

                return [
                    'trip' => $trip,

                    'trip_days' => $tripDays,
                    'stay_nights' => $stayNights,

                    'planned_distance_km' => $plannedDistanceKm,
                    'actual_distance_km' => $actualDistanceKm,

                    'food_planned' => $foodPlanned,
                    'food_actual' => $foodActual,

                    'misc_planned' => $miscPlanned,
                    'misc_actual' => $miscActual,

                    'stay_planned' => $stayPlanned,
                    'stay_actual' => $stayActual,

                    'item_planned' => $itemPlanned,
                    'item_actual' => $itemActual,

                    'fuel_planned' => $fuelPlanned,
                    'fuel_actual' => $fuelActual,

                    'overall_planned' => $overallPlanned,
                    'overall_actual' => $overallActual,
                ];
            })
            ->values();

        $reportTotals = [
            'trip_count' => $reportRows->count(),
            'trip_days' => (int) $reportRows->sum(
                fn (array $row) => (int) ($row['trip_days'] ?? 0)
            ),
            'stay_nights' => (int) $reportRows->sum(
                fn (array $row) => (int) ($row['stay_nights'] ?? 0)
            ),

            'planned_distance_km' => (float) $reportRows->sum(
                fn (array $row) => (float) ($row['planned_distance_km'] ?? 0)
            ),

            'actual_distance_km' => (float) $reportRows->sum(
                fn (array $row) => (float) ($row['actual_distance_km'] ?? 0)
            ),

            'food_planned' => (float) $reportRows->sum(
                fn (array $row) => (float) ($row['food_planned'] ?? 0)
            ),

            'food_actual' => (float) $reportRows->sum(
                fn (array $row) => (float) ($row['food_actual'] ?? 0)
            ),

            'misc_planned' => (float) $reportRows->sum(
                fn (array $row) => (float) ($row['misc_planned'] ?? 0)
            ),

            'misc_actual' => (float) $reportRows->sum(
                fn (array $row) => (float) ($row['misc_actual'] ?? 0)
            ),

            'stay_planned' => (float) $reportRows->sum(
                fn (array $row) => (float) ($row['stay_planned'] ?? 0)
            ),

            'stay_actual' => (float) $reportRows->sum(
                fn (array $row) => (float) ($row['stay_actual'] ?? 0)
            ),

            'item_planned' => (float) $reportRows->sum(
                fn (array $row) => (float) ($row['item_planned'] ?? 0)
            ),

            'item_actual' => (float) $reportRows->sum(
                fn (array $row) => (float) ($row['item_actual'] ?? 0)
            ),

            'fuel_planned' => (float) $reportRows->sum(
                fn (array $row) => (float) ($row['fuel_planned'] ?? 0)
            ),

            'fuel_actual' => (float) $reportRows->sum(
                fn (array $row) => (float) ($row['fuel_actual'] ?? 0)
            ),

            'overall_planned' => (float) $reportRows->sum(
                fn (array $row) => (float) ($row['overall_planned'] ?? 0)
            ),

            'overall_actual' => (float) $reportRows->sum(
                fn (array $row) => (float) ($row['overall_actual'] ?? 0)
            ),
        ];

        $tripStatusOptions = [
            'planned' => 'Planned',
            'active' => 'Active',
            'completed' => 'Completed',
            'archived' => 'Archived',
            'cancelled' => 'Cancelled',
        ];

        return view('reports.trips.summary', [
            'reportRows' => $reportRows,
            'reportTotals' => $reportTotals,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'tripStatus' => $tripStatus,
            'tripStatusOptions' => $tripStatusOptions,
        ]);
    }
}