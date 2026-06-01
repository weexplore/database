<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\FuelStop;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class FuelStopController extends Controller
{
    public function index(Request $request)
    {
        $fuelTypes = config('fuel.fuel_types');

        $places = Place::query()
            ->orderBy('placename')
            ->select(['id', 'placename'])
            ->get();

        $destinations = Destination::query()
            ->orderBy('destinationname')
            ->select(['id', 'placeid', 'destinationname'])
            ->get();

        $fuelStops = FuelStop::query()
            ->with([
                'place:id,placename',
                'destination:id,placeid,destinationname',
            ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->search);

                $query->where(function ($q) use ($search) {
                    $q->where('stopname', 'like', "%{$search}%")
                        ->orWhere('brandname', 'like', "%{$search}%")
                        ->orWhere('fueltypesavailable', 'like', "%{$search}%")
                        ->orWhereHas('place', function ($placeQuery) use ($search) {
                            $placeQuery->where('placename', 'like', "%{$search}%");
                        })
                        ->orWhereHas('destination', function ($destinationQuery) use ($search) {
                            $destinationQuery->where('destinationname', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('place_id'), function ($query) use ($request) {
                $query->where('placeid', (int) $request->place_id);
            })
            ->when($request->filled('destination_id'), function ($query) use ($request) {
                $query->where('destinationid', (int) $request->destination_id);
            })
            ->when($request->filled('brand'), function ($query) use ($request) {
                $query->where('brandname', 'like', '%' . trim((string) $request->brand) . '%');
            })
            ->when($request->status !== null && $request->status !== '', function ($query) use ($request) {
                $query->where('isactive', (int) $request->status);
            })
            ->orderBy('stopname')
            ->paginate(20)
            ->withQueryString();

        $newFuelStop = new FuelStop([
            'placeid' => $request->filled('place_id') ? (int) $request->place_id : null,
            'destinationid' => $request->filled('destination_id') ? (int) $request->destination_id : null,
            'isactive' => true,
        ]);

        return view('fuelstops.index', compact(
            'fuelStops',
            'places',
            'destinations',
            'fuelTypes',
            'newFuelStop'
        ));
    }

    public function create(Request $request)
    {
        $fuelStop = FuelStop::create([
            'placeid' => $request->filled('place_id') ? (int) $request->place_id : null,
            'destinationid' => $request->filled('destination_id') ? (int) $request->destination_id : null,
            'stopname' => 'New Fuel Stop',
            'isactive' => true,
        ]);

        $returnTo = $request->input('return_to');

        return redirect()->route('fuel-stops.edit', [
            'fuel_stop' => $fuelStop,
            'return_to' => $returnTo,
        ])->with('success', 'Fuel stop created. Update the details below.');
    }

    public function edit(Request $request, FuelStop $fuelStop)
    {
        $places = Place::query()
            ->orderBy('placename')
            ->select(['id', 'placename'])
            ->get();

        $destinations = Destination::query()
            ->orderBy('destinationname')
            ->select(['id', 'placeid', 'destinationname'])
            ->get();

        $fuelTypes = config('fuel.fuel_types');

        $fuelStop->load([
            'place:id,placename',
            'destination:id,placeid,destinationname',
            'fuelPriceObservations' => fn ($query) => $query
                ->orderByDesc('observedon')
                ->orderByDesc('id'),
            'tripFuelPurchases' => fn ($query) => $query
                ->with('trip')
                ->orderByDesc('purchasedate')
                ->orderByDesc('id'),
        ]);

        return view('fuelstops.edit', compact(
            'fuelStop',
            'places',
            'destinations',
            'fuelTypes'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateFuelStop($request);

        $validated['fueltypesavailable'] = implode(',', $validated['fueltypesavailable'] ?? []);
        $validated['hashighflowdiesel'] = $request->boolean('hashighflowdiesel');
        $validated['hasadblue'] = $request->boolean('hasadblue');
        $validated['hascarwash'] = $request->boolean('hascarwash');
        $validated['hasairwater'] = $request->boolean('hasairwater');
        $validated['isactive'] = $request->boolean('isactive');

        $fuelStop = FuelStop::create($validated);

        $returnTo = $request->input('return_to');

        if ($returnTo) {
            return redirect($returnTo)->with('success', 'Fuel stop added.');
        }

        return redirect()
            ->route('fuel-stops.edit', $fuelStop)
            ->with('success', 'Fuel stop added.');
    }

    public function update(Request $request, FuelStop $fuelStop)
    {
        $validated = $this->validateFuelStop($request);

        $validated['fueltypesavailable'] = implode(',', $validated['fueltypesavailable'] ?? []);
        $validated['hashighflowdiesel'] = $request->boolean('hashighflowdiesel');
        $validated['hasadblue'] = $request->boolean('hasadblue');
        $validated['hascarwash'] = $request->boolean('hascarwash');
        $validated['hasairwater'] = $request->boolean('hasairwater');
        $validated['isactive'] = $request->boolean('isactive');

        $fuelStop->update($validated);

        $saveAction = $request->input('save_action', 'stay');
        $returnTo = $request->input('return_to');

        if ($saveAction === 'stay') {
            return redirect()
                ->route('fuel-stops.edit', [
                    'fuel_stop' => $fuelStop->id,
                    'return_to' => $returnTo,
                ])
                ->with('success', 'Fuel stop updated.');
        }

        return redirect($returnTo ?: route('fuel-stops.index'))
            ->with('success', 'Fuel stop updated.');
    }

    public function destroy(Request $request, FuelStop $fuelStop)
    {
        $returnTo = $request->input('return_to');

        $fuelStop->delete();

        if ($returnTo) {
            return redirect($returnTo)->with('success', 'Fuel stop deleted.');
        }

        return redirect()
            ->route('fuel-stops.index')
            ->with('success', 'Fuel stop deleted.');
    }

    protected function validateFuelStop(Request $request): array
    {
        $validator = validator(
            $request->all(),
            [
                'placeid' => ['nullable', 'integer', 'exists:places,id'],
                'destinationid' => ['nullable', 'integer', 'exists:destinations,id'],
                'stopname' => ['required', 'string', 'max:200'],
                'brandname' => ['nullable', 'string', 'max:100'],

                'addressline1' => ['nullable', 'string', 'max:200'],
                'addressline2' => ['nullable', 'string', 'max:200'],
                'addressline3' => ['nullable', 'string', 'max:200'],
                'postcode' => ['nullable', 'string', 'max:20'],
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
                'website' => ['nullable', 'url', 'max:500'],
                'telephone' => ['nullable', 'string', 'max:50'],
                'internetsearch' => ['nullable', 'string', 'max:500'],

                'fueltypesavailable' => ['nullable', 'array'],
                'fueltypesavailable.*' => ['string', Rule::in(array_keys(config('fuel.fuel_types')))],
                'caravanaccessnotes' => ['nullable', 'string'],
                'openingnotes' => ['nullable', 'string'],
                'generalnotes' => ['nullable', 'string'],
                'isactive' => ['nullable', 'boolean'],
            ]
        );

        $validator->after(function (Validator $validator) use ($request) {
            $placeId = $request->input('placeid');
            $destinationId = $request->input('destinationid');

            if (blank($destinationId)) {
                return;
            }

            if (blank($placeId)) {
                $validator->errors()->add(
                    'placeid',
                    'A place is required when a destination is selected.'
                );
                return;
            }

            $matches = Destination::query()
                ->whereKey($destinationId)
                ->where('placeid', $placeId)
                ->exists();

            if (! $matches) {
                $validator->errors()->add(
                    'destinationid',
                    'The selected destination does not belong to the selected place.'
                );
            }
        });

        return $validator->validate();
    }
}