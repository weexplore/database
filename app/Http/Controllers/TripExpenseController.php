<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripExpense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TripExpenseController extends Controller
{
    public function index(Trip $trip): View
    {
        $expenses = $trip->expenses()
            ->with([
                'tripLeg',
                'tripStay',
                'place',
            ])
            ->orderByDesc('expensedate')
            ->orderByDesc('id')
            ->get();

        $totals = [
            'food_actual' => (float) $expenses
                ->where('expensecategory', 'food')
                ->sum('amount'),

            /*
             * Everything other than Food is treated as Miscellaneous
             * in the Summary Report.
             */
            'misc_actual' => (float) $expenses
                ->where('expensecategory', '!=', 'food')
                ->sum('amount'),

            'overall_actual' => (float) $expenses->sum('amount'),
        ];

        return view('trips.expenses.index', [
            'trip' => $trip,
            'expenses' => $expenses,
            'totals' => $totals,
            'expenseCategories' => collect(TripExpense::categoryOptions()),
            'paymentMethods' => collect(TripExpense::paymentMethodOptions()),
            'tripLegs' => $trip->legs()
                ->orderBy('legnumber')
                ->get(),
            'tripStays' => $trip->stays()
                ->orderBy('checkindate')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function store(Request $request, Trip $trip): RedirectResponse
    {
        $validated = $this->validatedExpense($request, $trip);

        $this->ensureOptionalLinksBelongToTrip($trip, $validated);

        $trip->expenses()->create($validated);

        return redirect()
            ->route('trips.expenses.index', $trip)
            ->with('success', 'Expense added successfully.');
    }

    public function update(
        Request $request,
        Trip $trip,
        TripExpense $expense
    ): RedirectResponse {
        $this->ensureExpenseBelongsToTrip($trip, $expense);

        $validated = $this->validatedExpense($request, $trip);

        $this->ensureOptionalLinksBelongToTrip($trip, $validated);

        $expense->update($validated);

        return redirect()
            ->route('trips.expenses.index', $trip)
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(
        Trip $trip,
        TripExpense $expense
    ): RedirectResponse {
        $this->ensureExpenseBelongsToTrip($trip, $expense);

        $expense->delete();

        return redirect()
            ->route('trips.expenses.index', $trip)
            ->with('success', 'Expense deleted successfully.');
    }

    private function validatedExpense(Request $request, Trip $trip): array
    {
        $dateRules = [
            'required',
            'date',
        ];

        if ($trip->startdate) {
            $dateRules[] = 'after_or_equal:' . $trip->startdate->toDateString();
        }

        if ($trip->enddate) {
            $dateRules[] = 'before_or_equal:' . $trip->enddate->toDateString();
        }

        return $request->validate([
            'expensedate' => $dateRules,

            'expensecategory' => [
                'required',
                'in:' . implode(',', array_keys(TripExpense::categoryOptions())),
            ],

            'subcategory' => [
                'nullable',
                'string',
                'max:100',
            ],

            'description' => [
                'required',
                'string',
                'max:200',
            ],

            'payee' => [
                'nullable',
                'string',
                'max:200',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
            ],

            'currency' => [
                'required',
                'string',
                'size:3',
            ],

            'paymentmethod' => [
                'nullable',
                'in:' . implode(',', array_keys(TripExpense::paymentMethodOptions())),
            ],

            'triplegid' => [
                'nullable',
                'integer',
                'exists:triplegs,id',
            ],

            'tripstayid' => [
                'nullable',
                'integer',
                'exists:tripstays,id',
            ],

            'placeid' => [
                'nullable',
                'integer',
                'exists:places,id',
            ],

            'receiptreference' => [
                'nullable',
                'string',
                'max:150',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);
    }

    private function ensureExpenseBelongsToTrip(
        Trip $trip,
        TripExpense $expense
    ): void {
        abort_unless(
            (int) $expense->tripid === (int) $trip->id,
            404
        );
    }


    private function ensureOptionalLinksBelongToTrip(
        Trip $trip,
        array $validated
    ): void {
        if (! empty($validated['triplegid'])) {
            abort_unless(
                $trip->legs()
                    ->whereKey($validated['triplegid'])
                    ->exists(),
                422
            );
        }

        if (! empty($validated['tripstayid'])) {
            abort_unless(
                $trip->stays()
                    ->whereKey($validated['tripstayid'])
                    ->exists(),
                422
            );
        }
    }
}