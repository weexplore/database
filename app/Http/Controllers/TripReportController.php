<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;

class TripReportController extends Controller
{
    public function book(Request $request, Trip $trip)
    {
        // In TripReportController@book

        $trip->load([
                    'travellers',
            'legs' => function ($query) {
                $query
                    ->with([
                        'fromPlace',
                        'fromDestination',
                        'fromDestinationItem',
                        'toPlace',
                        'toDestination',
                        'toDestinationItem',
                        'legPoints' => function ($q) {
                            $q->with([
                                'place',
                                'destination',
                                'destinationItem',
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
                    ->with(['place', 'tripLeg', 'travelledFromPlace'])
                    ->orderBy('checkindate')
                    ->orderBy('id');
            },
            'tripItems' => function ($query) {
                $query
                    ->with(['tripleg', 'stay', 'destination', 'destinationItem', 'place'])
                    ->orderBy('itemdate')
                    ->orderBy('startdatetime')
                    ->orderBy('id');
            },
            'reviews' => function ($query) {
                $query
                    ->with(['traveller', 'stay', 'tripItem', 'destination', 'destinationItem', 'place'])
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
        ]);

        $stayEstimatedTotal = (float) $trip->stays()->sum('estimatedtotalcost');
        $stayActualTotal = (float) $trip->stays()->sum('actualtotalcost');

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

        $overallEstimatedTotal =
            (float) ($foodBudgetTotal ?? 0) +
            (float) ($miscBudgetTotal ?? 0) +
            (float) ($stayEstimatedTotal ?? 0) +
            (float) ($itemEstimatedTotal ?? 0) +
            (float) ($fuelEstimateTotal ?? 0);

        $overallActualTotal =
            (float) ($stayActualTotal ?? 0) +
            (float) ($itemActualTotal ?? 0) +
            (float) ($fuelActualTotal ?? 0);

        return view('reports.trips.book', [
            'trip'                 => $trip,
            'tripDays'             => $tripDays,
            'dailyFoodBudget'      => $dailyFoodBudget,
            'dailyMiscBudget'      => $dailyMiscBudget,
            'foodBudgetTotal'      => $foodBudgetTotal,
            'miscBudgetTotal'      => $miscBudgetTotal,
            'fuelEstimateTotal'    => $fuelEstimateTotal,
            'fuelActualTotal'      => $fuelActualTotal,
            'stayEstimatedTotal'   => $stayEstimatedTotal,
            'stayActualTotal'      => $stayActualTotal,
            'itemEstimatedTotal'   => $itemEstimatedTotal,
            'itemActualTotal'      => $itemActualTotal,
            'overallEstimatedTotal'=> $overallEstimatedTotal,
            'overallActualTotal'   => $overallActualTotal,
        ]);
    }
}