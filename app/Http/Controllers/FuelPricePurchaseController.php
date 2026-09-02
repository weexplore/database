<?php

namespace App\Http\Controllers;

use App\Models\FuelPricePurchase;
use App\Models\FuelStop;
use App\Models\Place;
use App\Models\Trip;
use App\Models\TripLeg;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FuelPricePurchaseController extends Controller
{
    /**
     * Display the global fuel-purchase register.
     */
    public function index(Request $request): View
    {
        $query = FuelPricePurchase::query()
            ->with([
                'trip:id,tripname,startdate,enddate,tripstatus',
                'leg:id,tripid,legnumber,title,fromplaceid,toplaceid',
                'leg.fromPlace:id,placename',
                'leg.toPlace:id,placename',
                'fuelStop:id,stopname,placeid',
                'fuelStop.place:id,placename',
                'place:id,placename',
            ]);

        if ($request->input('assignment') === 'unassigned') {
            $query->whereNull('tripid');
        }

        if ($request->input('assignment') === 'assigned') {
            $query->whereNotNull('tripid');
        }

        if ($request->filled('tripid')) {
            $query->where('tripid', $request->integer('tripid'));
        }

        if ($request->filled('triplegid')) {
            $query->where('triplegid', $request->integer('triplegid'));
        }

        if ($request->filled('fuelstopid')) {
            $query->where('fuelstopid', $request->integer('fuelstopid'));
        }

        if ($request->filled('fueltype')) {
            $query->where(
                'fueltype',
                $request->string('fueltype')->toString()
            );
        }

        if ($request->filled('date_from')) {
            $query->whereDate(
                'purchasedate',
                '>=',
                $request->input('date_from')
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'purchasedate',
                '<=',
                $request->input('date_to')
            );
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($purchaseQuery) use ($search) {
                $purchaseQuery
                    ->where('receiptreference', 'like', '%' . $search . '%')
                    ->orWhere('notes', 'like', '%' . $search . '%');
            });
        }

        $purchases = $query
            ->orderByDesc('purchasedate')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('fuelpurchases.index', [
            'purchases' => $purchases,
            'trips' => $this->tripsForSelection(),
            'tripLegs' => $this->tripLegsForSelection(),
            'fuelStops' => $this->fuelStopsForSelection(),
            'fuelTypes' => $this->fuelTypes(),
        ]);
    }

    /**
     * Display the global create form.
     *
     * A trip can be selected or left blank for an unassigned receipt.
     */
    public function create(Request $request): View
    {
        $selectedTripId = $request->filled('tripid')
            ? $request->integer('tripid')
            : null;

        return view('fuelpurchases.create', [
            'fuelPurchase' => new FuelPricePurchase(),
            'trips' => $this->tripsForSelection(),
            'tripLegs' => $this->tripLegsForSelection(),
            'fuelStops' => $this->fuelStopsForSelection(),
            'places' => $this->placesForSelection(),
            'fuelTypes' => $this->fuelTypes(),
            'selectedTripId' => $selectedTripId,
            'returnTo' => route('fuel-purchases.index'),
        ]);
    }

    /**
     * Store a global fuel purchase.
     *
     * The trip relationship is optional. When omitted, the purchase becomes
     * part of the unassigned receipt register.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        $fuelPurchase = FuelPricePurchase::create($data);

        return redirect()
            ->route('fuel-purchases.index')
            ->with('success', 'Fuel purchase recorded.');
    }

    /**
     * Display the global edit form.
     */
    public function edit(
        FuelPricePurchase $fuelPurchase,
        Request $request
    ): View {
        $returnTo = $this->validatedReturnTo(
            $request->input('return_to'),
            route('fuel-purchases.index')
        );

        return view('fuelpurchases.edit', [
            'fuelPurchase' => $fuelPurchase,
            'trips' => $this->tripsForSelection(),
            'tripLegs' => $this->tripLegsForSelection(),
            'fuelStops' => $this->fuelStopsForSelection(),
            'places' => $this->placesForSelection(),
            'fuelTypes' => $this->fuelTypes(),
            'selectedTripId' => $fuelPurchase->tripid,
            'returnTo' => $returnTo,
        ]);
    }

    /**
     * Update a global fuel purchase, including its optional trip/leg allocation.
     */
    public function update(
        Request $request,
        FuelPricePurchase $fuelPurchase
    ): RedirectResponse {
        $data = $this->validatedData($request);

        $fuelPurchase->update($data);

        $returnTo = $this->validatedReturnTo(
            $request->input('return_to'),
            route('fuel-purchases.index')
        );

        if ($request->input('save_action') === 'stay') {
            return redirect()
                ->route('fuel-purchases.edit', [
                    'fuelPurchase' => $fuelPurchase,
                    'return_to' => $returnTo,
                ])
                ->with('success', 'Fuel purchase updated.');
        }

        return redirect($returnTo)
            ->with('success', 'Fuel purchase updated.');
    }

    /**
     * Delete a global fuel purchase.
     */
    public function destroy(
        Request $request,
        FuelPricePurchase $fuelPurchase
    ): RedirectResponse {
        $fuelPurchase->delete();

        $returnTo = $this->validatedReturnTo(
            $request->input('return_to'),
            route('fuel-purchases.index')
        );

        return redirect($returnTo)
            ->with('success', 'Fuel purchase deleted.');
    }

    /**
     * Quickly assign an unassigned purchase to a trip.
     *
     * A trip leg is deliberately cleared because a leg should only be
     * selected through the full edit form after reviewing the itinerary.
     */
    public function assignTrip(
        Request $request,
        FuelPricePurchase $fuelPurchase
    ): RedirectResponse {
        $data = $request->validate([
            'tripid' => ['required', 'integer', 'exists:trips,id'],
        ]);

        $fuelPurchase->update([
            'tripid' => $data['tripid'],
            'triplegid' => null,
        ]);

        return back()->with(
            'success',
            'Fuel purchase assigned to the selected trip.'
        );
    }

    /**
     * Validate and normalise the common create/update payload.
     */
    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'tripid' => ['nullable', 'integer', 'exists:trips,id'],
            'triplegid' => ['nullable', 'integer', 'exists:triplegs,id'],
            'fuelstopid' => ['nullable', 'integer', 'exists:fuelstops,id'],
            'placeid' => ['nullable', 'integer', 'exists:places,id'],

            'purchasedate' => ['required', 'date'],

            'fueltype' => [
                'required',
                'string',
                'max:30',
            ],

            'litres' => ['required', 'numeric', 'gt:0'],
            'priceperlitre' => ['required', 'numeric', 'gte:0'],

            'odometerkm' => ['nullable', 'numeric', 'gte:0'],
            'distancesincelastfillkm' => ['nullable', 'numeric', 'gte:0'],

            'servicecosts' => ['nullable', 'numeric', 'gte:0'],
            'repairscost' => ['nullable', 'numeric', 'gte:0'],

            'receiptreference' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
        ]);

        /*
         * Convert empty optional form fields to real nulls rather than
         * retaining empty strings in MariaDB decimal / foreign-key columns.
         */
        foreach ([
            'tripid',
            'triplegid',
            'fuelstopid',
            'placeid',
            'odometerkm',
            'distancesincelastfillkm',
            'servicecosts',
            'repairscost',
            'receiptreference',
            'notes',
        ] as $field) {
            if (! array_key_exists($field, $data) || $data[$field] === '') {
                $data[$field] = null;
            }
        }

        /*
         * A trip leg cannot exist independently of a trip.
         */
        if (! empty($data['triplegid']) && empty($data['tripid'])) {
            throw ValidationException::withMessages([
                'triplegid' => 'Select a trip before selecting a trip leg.',
            ]);
        }

        /*
         * Ensure the selected leg is genuinely part of the selected trip.
         * This protects the database even if an old browser tab or manually
         * altered form submits an invalid trip/leg combination.
         */
        if (! empty($data['triplegid']) && ! empty($data['tripid'])) {
            $legBelongsToTrip = TripLeg::query()
                ->whereKey($data['triplegid'])
                ->where('tripid', $data['tripid'])
                ->exists();

            if (! $legBelongsToTrip) {
                throw ValidationException::withMessages([
                    'triplegid' => 'The selected trip leg does not belong to the selected trip.',
                ]);
            }
        }

        /*
         * If no trip is selected, ensure no historical leg link remains.
         */
        if (empty($data['tripid'])) {
            $data['triplegid'] = null;
        }

        /*
         * The amount shown in the browser is convenient, but calculate the
         * stored total server-side for reliable financial data.
         */
        $data['fueltotal'] = round(
            (float) $data['litres'] * (float) $data['priceperlitre'],
            2
        );

        return $data;
    }

    /**
     * Trips in a practical order for dropdowns:
     * newest/current trips first, then alphabetical for identical dates.
     */
    private function tripsForSelection()
    {
        return Trip::query()
            ->orderByDesc('startdate')
            ->orderBy('tripname')
            ->get([
                'id',
                'tripname',
                'tripstatus',
                'startdate',
                'enddate',
            ]);
    }

    /**
     * All trip legs for the global leg selector.
     *
     * The form JavaScript should display only legs that belong to the
     * currently selected trip via each leg's tripid data attribute.
     */
    private function tripLegsForSelection()
    {
        return TripLeg::query()
            ->with([
                'fromPlace:id,placename',
                'toPlace:id,placename',
            ])
            ->orderBy('tripid')
            ->orderBy('legnumber')
            ->orderBy('id')
            ->get([
                'id',
                'tripid',
                'legnumber',
                'title',
                'fromplaceid',
                'toplaceid',
            ]);
    }

    /**
     * Reusable fuel-stop choices with their locality label.
     */
    private function fuelStopsForSelection()
    {
        return FuelStop::query()
            ->with('place:id,placename')
            ->orderBy('stopname')
            ->get([
                'id',
                'stopname',
                'placeid',
            ]);
    }

    /**
     * Reusable fallback places, used when no Fuel Stop exists yet.
     */
    private function placesForSelection()
    {
        return Place::query()
            ->orderBy('placename')
            ->get([
                'id',
                'placename',
            ]);
    }

    /**
     * Fuel types presented consistently in filters and forms.
     *
     * If you added FuelPricePurchase::fuelTypes() to the model, replace this
     * method's return statement with:
     *
     * return FuelPricePurchase::fuelTypes();
     */
    private function fuelTypes(): array
    {
        return [
            'diesel' => 'Diesel',
            'premiumdiesel' => 'Premium Diesel',
            'unleaded91' => 'Unleaded 91',
            'unleaded95' => 'Premium Unleaded 95',
            'unleaded98' => 'Premium Unleaded 98',
            'lpg' => 'LPG',
            'adblue' => 'AdBlue',
            'other' => 'Other',
        ];
    }

    /**
     * Prevent an arbitrary external URL from being used as a post-save
     * redirect target. Only retain a local application URL.
     */
    private function validatedReturnTo(?string $returnTo, string $default): string
    {
        if (blank($returnTo)) {
            return $default;
        }

        $appUrl = rtrim(config('app.url'), '/');
        $returnTo = trim($returnTo);

        if (str_starts_with($returnTo, '/')) {
            return url($returnTo);
        }

        if (str_starts_with($returnTo, $appUrl . '/')
            || $returnTo === $appUrl
        ) {
            return $returnTo;
        }

        return $default;
    }
}