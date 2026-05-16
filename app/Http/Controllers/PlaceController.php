<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Place;
use App\Models\Region;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

class PlaceController extends Controller
{
    private function placeTypeOptions(): array
    {
        return [
            'town' => 'Town',
            'accommodation' => 'Accommodation',
            'caravan_park' => 'Caravan Park',
            'free_camp' => 'Free Camp',
            'showgrounds' => 'Showgrounds',
            'station_stay' => 'Station Stay',
            'campground' => 'Campground',
            'rest_area' => 'Rest Area',
            'dump_point' => 'Dump Point',
            'water_point' => 'Water Point',
            'water_dump_point' => 'Water and Dump Point',
            'visitor_centre' => 'Visitor Centre',
            'boat_ramp' => 'Boat Ramp',
            'day_use_area' => 'Day Use Area',
            'national_park' => 'National Park',
            'other' => 'Other',
        ];
    }

    public function index(Request $request)
{
    $countries = Country::orderBy('countryname')->get();
    $placeTypes = $this->placeTypeOptions();

    $selectedCountryId = $request->filled('country_id') ? (int) $request->country_id : null;
    $selectedStateId = $request->filled('state_id') ? (int) $request->state_id : null;
    $selectedRegionId = $request->filled('region_id') ? (int) $request->region_id : null;

    $filterStates = State::query()
        ->when(
            $selectedCountryId,
            fn ($query) => $query->where('countryid', $selectedCountryId),
            fn ($query) => $query->whereRaw('1 = 0')
        )
        ->orderBy('statename')
        ->get();

    $filterRegions = Region::query()
        ->when(
            $selectedCountryId,
            fn ($query) => $query->where('countryid', $selectedCountryId),
            fn ($query) => $query->whereRaw('1 = 0')
        )
        ->when($selectedStateId, function ($query) use ($selectedStateId) {
            $query->where('stateid', $selectedStateId);
        })
        ->orderBy('regionname')
        ->get();

    $statesByCountry = State::query()
        ->orderBy('statename')
        ->get()
        ->groupBy('countryid');

    $regionsByCountry = Region::query()
        ->whereNull('stateid')
        ->orderBy('regionname')
        ->get()
        ->groupBy('countryid');

    $regionsByState = Region::query()
        ->whereNotNull('stateid')
        ->orderBy('regionname')
        ->get()
        ->groupBy('stateid');

    $query = Place::query()
        ->with(['country', 'state', 'region'])
        ->withCount('destinations');



    if ($selectedCountryId) {
        $query->where('countryid', $selectedCountryId);
    }

    if ($selectedStateId) {
        $query->where('stateid', $selectedStateId);
    }

    if ($selectedRegionId) {
        $query->where('regionid', $selectedRegionId);
    }

    if ($request->filled('placetype')) {
        $query->where('placetype', $request->placetype);
    }

    if ($request->filled('status')) {
        $query->where('isactive', (int) $request->status);
    }

    if ($request->filled('search')) {
        $search = trim((string) $request->search);

        $query->where(function ($q) use ($search) {
            $q->where('placename', 'like', "%{$search}%")
                ->orWhere('locality', 'like', "%{$search}%")
                ->orWhere('placetype', 'like', "%{$search}%")
                ->orWhere('postcode', 'like', "%{$search}%");
        });
    }

    $statesByCountryForJs = $statesByCountry
    ->map(fn ($states) => $states->map(fn ($state) => [
        'id' => $state->id,
        'name' => $state->statename,
    ])->values())
    ->toArray();

    $regionsByCountryForJs = $regionsByCountry
        ->map(fn ($regions) => $regions->map(fn ($region) => [
            'id' => $region->id,
            'name' => $region->regionname,
        ])->values())
        ->toArray();

    $regionsByStateForJs = $regionsByState
        ->map(fn ($regions) => $regions->map(fn ($region) => [
            'id' => $region->id,
            'name' => $region->regionname,
        ])->values())
        ->toArray();

    $places = $query
        ->orderBy('placename')
        ->paginate(25)
        ->withQueryString();

    return view('places.index', compact(
        'places',
        'countries',
        'filterStates',
        'filterRegions',
        'statesByCountry',
        'regionsByCountry',
        'regionsByState',
        'placeTypes',
        'statesByCountryForJs',
        'regionsByCountryForJs',
        'regionsByStateForJs',
        'selectedCountryId',
        'selectedStateId',
        'selectedRegionId'
    ));
}

    public function bulkSave(Request $request)
{
    $payload = $request->all();
    $placeTypeKeys = array_keys($this->placeTypeOptions());

    if (!empty($payload['existing']) && is_array($payload['existing'])) {
        foreach ($payload['existing'] as $id => $row) {
            $payload['existing'][$id]['country_id'] = $this->normaliseFk($row['country_id'] ?? null);
            $payload['existing'][$id]['state_id'] = $this->normaliseFk($row['state_id'] ?? null);
            $payload['existing'][$id]['region_id'] = $this->normaliseFk($row['region_id'] ?? null);
            $payload['existing'][$id]['isactive'] = !empty($row['isactive']) ? 1 : 0;
        }
    }

    if (!empty($payload['new']) && is_array($payload['new'])) {
        $payload['new']['country_id'] = $this->normaliseFk($payload['new']['country_id'] ?? null);
        $payload['new']['state_id'] = $this->normaliseFk($payload['new']['state_id'] ?? null);
        $payload['new']['region_id'] = $this->normaliseFk($payload['new']['region_id'] ?? null);
        $payload['new']['isactive'] = !empty($payload['new']['isactive']) ? 1 : 0;
    }

    $validated = validator($payload, [
        'existing' => ['nullable', 'array'],
        'existing.*.placename' => ['required', 'string', 'max:200'],
        'existing.*.country_id' => ['required', 'integer', 'exists:countries,id'],
        'existing.*.state_id' => ['nullable', 'integer', 'exists:states,id'],
        'existing.*.region_id' => ['nullable', 'integer', 'exists:regions,id'],
        'existing.*.placetype' => ['nullable', 'string', 'max:50', Rule::in($placeTypeKeys)],
        'existing.*.locality' => ['nullable', 'string', 'max:150'],
        'existing.*.postcode' => ['nullable', 'string', 'max:20'],
        'existing.*.latitude' => ['nullable', 'numeric', 'between:-90,90'],
        'existing.*.longitude' => ['nullable', 'numeric', 'between:-180,180'],
        'existing.*.sourcequality' => ['nullable', 'string', 'max:30'],
        'existing.*.isactive' => ['nullable', 'boolean'],

        'new.placename' => ['nullable', 'string', 'max:200'],
        'new.country_id' => ['nullable', 'integer', 'exists:countries,id'],
        'new.state_id' => ['nullable', 'integer', 'exists:states,id'],
        'new.region_id' => ['nullable', 'integer', 'exists:regions,id'],
        'new.placetype' => ['nullable', 'string', 'max:50', Rule::in($placeTypeKeys)],
        'new.locality' => ['nullable', 'string', 'max:150'],
        'new.postcode' => ['nullable', 'string', 'max:20'],
        'new.latitude' => ['nullable', 'numeric', 'between:-90,90'],
        'new.longitude' => ['nullable', 'numeric', 'between:-180,180'],
        'new.sourcequality' => ['nullable', 'string', 'max:30'],
        'new.isactive' => ['nullable', 'boolean'],
    ])->validate();

    if (!empty($validated['existing'])) {
        foreach ($validated['existing'] as $placeId => $row) {
            $place = Place::findOrFail($placeId);

            $place->update([
                'placename' => $row['placename'],
                'countryid' => $row['country_id'],
                'stateid' => $row['state_id'] ?? null,
                'regionid' => $row['region_id'] ?? null,
                'placetype' => $row['placetype'] ?? null,
                'locality' => $row['locality'] ?? null,
                'postcode' => $row['postcode'] ?? null,
                'latitude' => $row['latitude'] ?? null,
                'longitude' => $row['longitude'] ?? null,
                'sourcequality' => $row['sourcequality'] ?? null,
                'addressline1' => $row['addressline1'] ?? null,
                'addressline2' => $row['addressline2'] ?? null,
                'accessnotes' => $row['accessnotes'] ?? null,
                'generalnotes' => $row['generalnotes'] ?? null,
                'isactive' => !empty($row['isactive']),
                'updatedat' => now(),
            ]);
        }
    }

    $new = $validated['new'] ?? [];
    if (!empty(trim((string) ($new['placename'] ?? '')))) {
        validator($new, [
            'placename' => ['required', 'string', 'max:200'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'placetype' => ['nullable', 'string', 'max:50', Rule::in($placeTypeKeys)],
        ])->validate();

        $duplicateExists = Place::query()
            ->whereRaw('LOWER(placename) = ?', [mb_strtolower(trim($new['placename']))])
            ->where('countryid', $new['country_id'])
            ->exists();

        if ($duplicateExists) {
            return redirect()
                ->back()
                ->withErrors([
                    'new.placename' => 'A place with this name already exists in the selected country.',
                ])
                ->withInput();
        }

        Place::create([
            'placename' => $new['placename'],
            'countryid' => $new['country_id'],
            'stateid' => $new['state_id'] ?? null,
            'regionid' => $new['region_id'] ?? null,
            'placetype' => $new['placetype'] ?? null,
            'locality' => $new['locality'] ?? null,
            'postcode' => $new['postcode'] ?? null,
            'latitude' => $new['latitude'] ?? null,
            'longitude' => $new['longitude'] ?? null,
            'sourcequality' => $new['sourcequality'] ?? null,
            'addressline1' => $new['addressline1'] ?? null,
            'addressline2' => $new['addressline2'] ?? null,
            'accessnotes' => $new['accessnotes'] ?? null,
            'generalnotes' => $new['generalnotes'] ?? null,
            'isactive' => !empty($new['isactive']),
            'createdat' => now(),
            'updatedat' => now(),
        ]);
    }

    $returnTo = $request->input('return_to');

    if ($returnTo) {
        return redirect($returnTo)->with('success', 'Places saved successfully.');
    }

    return redirect()
        ->route('places.index', $request->only([
            'search',
            'country_id',
            'state_id',
            'region_id',
            'placetype',
            'status',
            'page',
        ]))
        ->with('success', 'Places saved successfully.');
}

    private function normaliseFk($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    public function destroy(Request $request, Place $place)
    {
        try {
            $place->delete();

            $returnTo = $request->input('return_to');

            if ($returnTo) {
                return redirect($returnTo)->with('success', 'Place deleted successfully.');
            }

            return redirect()
                ->route('places.index', $request->only([
                    'search',
                    'country_id',
                    'state_id',
                    'region_id',
                    'placetype',
                    'status',
                    'page',
                ]))
                ->with('success', 'Place deleted successfully.');
        } catch (\Throwable $e) {
            $returnTo = $request->input('return_to');

            if ($returnTo) {
                return redirect($returnTo)->with('error', 'This place is in use and cannot be deleted.');
            }

            return redirect()
                ->route('places.index', $request->only([
                    'search',
                    'country_id',
                    'state_id',
                    'region_id',
                    'placetype',
                    'status',
                    'page',
                ]))
                ->with('error', 'This place is in use and cannot be deleted.');
        }
    }


public function edit(Request $request, Place $place)
{
    $place->load([
        'destinations' => fn ($query) => $query
            ->with([
                'items' => fn ($itemQuery) => $itemQuery
                    ->with('itemTypes')   // ← add this
                    ->orderBy('itemname'),
            ])
            ->orderBy('destinationname'),
        'fuelStops' => fn ($query) => $query->orderBy('stopname'),
        'tripStays' => fn ($query) => $query
            ->with('trip')
            ->orderByDesc('checkindate')
            ->orderByDesc('id'),
        'tripLegsTo' => fn ($query) => $query
            ->with('trip')
            ->orderByDesc('startdate')
            ->orderByDesc('id'),
        'knowledgeItems' => fn ($query) => $query
            ->with(['itemType', 'primaryCategory'])
            ->orderBy('itemname'),
    ]);

    $destinationItems = $place->destinations
        ->flatMap->items
        ->sortBy('itemname')
        ->values();

    $countries = Country::orderBy('countryname')->get();
    $placeTypes = $this->placeTypeOptions();

    $selectedCountryId = old('countryid', $place->countryid);
    $selectedStateId   = old('stateid', $place->stateid);
    $selectedRegionId  = old('regionid', $place->regionid);

    $states = State::query()
        ->when(
            $selectedCountryId,
            fn ($query) => $query->where('countryid', $selectedCountryId),
            fn ($query) => $query->whereRaw('1 = 0')
        )
        ->orderBy('statename')
        ->get();

    $regions = Region::query()
        ->when(
            $selectedCountryId,
            fn ($query) => $query->where('countryid', $selectedCountryId),
            fn ($query) => $query->whereRaw('1 = 0')
        )
        ->when($selectedStateId, function ($query) use ($selectedStateId) {
            $query->where(function ($subQuery) use ($selectedStateId) {
                $subQuery->where('stateid', $selectedStateId)
                    ->orWhereNull('stateid');
            });
        })
        ->orderBy('regionname')
        ->get();

$itemTypeOptions = $this->destinationItemTypeOptions();

return view('places.edit', compact(
    'place',
    'countries',
    'states',
    'regions',
    'placeTypes',
    'selectedCountryId',
    'selectedStateId',
    'selectedRegionId',
    'destinationItems',
    'itemTypeOptions'
));
}

public function update(Request $request, Place $place)
{
    $placeTypeKeys = array_keys($this->placeTypeOptions());

    $data = $request->validate([
        'placename'     => ['required', 'string', 'max:200'],
        'countryid'     => ['required', 'integer', 'exists:countries,id'],
        'stateid'       => ['nullable', 'integer', 'exists:states,id'],
        'regionid'      => ['nullable', 'integer', 'exists:regions,id'],
        'placetype'     => ['nullable', 'string', 'max:50', Rule::in($placeTypeKeys)],
        'locality'      => ['nullable', 'string', 'max:150'],
        'addressline1'  => ['nullable', 'string', 'max:200'],
        'addressline2'  => ['nullable', 'string', 'max:200'],
        'postcode'      => ['nullable', 'string', 'max:20'],
        'latitude'      => ['nullable', 'numeric', 'between:-90,90'],
        'longitude'     => ['nullable', 'numeric', 'between:-180,180'],
        'accessnotes'   => ['nullable', 'string'],
        'generalnotes'  => ['nullable', 'string'],
        'sourcequality' => ['nullable', 'string', 'max:30'],
        'isactive'      => ['nullable', 'boolean'],
    ]);

    $duplicateExists = Place::query()
        ->whereRaw('LOWER(placename) = ?', [mb_strtolower(trim($data['placename']))])
        ->where('countryid', $data['countryid'])
        ->where('id', '!=', $place->id)
        ->exists();

    if ($duplicateExists) {
        return redirect()
            ->back()
            ->withErrors([
                'placename' => 'A place with this name already exists in the selected country.',
            ])
            ->withInput();
    }

    $place->update([
        'placename'     => $data['placename'],
        'countryid'     => $data['countryid'],
        'stateid'       => $data['stateid'] ?? null,
        'regionid'      => $data['regionid'] ?? null,
        'placetype'     => $data['placetype'] ?? null,
        'locality'      => $data['locality'] ?? null,
        'addressline1'  => $data['addressline1'] ?? null,
        'addressline2'  => $data['addressline2'] ?? null,
        'postcode'      => $data['postcode'] ?? null,
        'latitude'      => $data['latitude'] ?? null,
        'longitude'     => $data['longitude'] ?? null,
        'accessnotes'   => $data['accessnotes'] ?? null,
        'generalnotes'  => $data['generalnotes'] ?? null,
        'sourcequality' => $data['sourcequality'] ?? null,
        'isactive'      => !empty($data['isactive']),
    ]);

    $returnTo = $request->input('return_to');

    if ($request->boolean('create_destination_after_save')) {
        return redirect()->route('places.destinations.create-from-place', [
            'place' => $place,
            'return_to' => route('places.edit', [
                'place' => $place,
                'return_to' => $returnTo,
            ]),
        ])->with('success', 'Place saved. You can now create the destination.');
    }

    if ($returnTo) {
        return redirect($returnTo)->with('success', 'Place updated successfully.');
    }

    return redirect()
        ->route('places.index', $request->only([
            'search',
            'country_id',
            'state_id',
            'region_id',
            'placetype',
            'status',
            'page',
        ]))
        ->with('success', 'Place updated successfully.');
}

public function statesForCountry(Request $request): JsonResponse
{
    $validated = $request->validate([
        'countryid' => ['nullable', 'integer', 'exists:countries,id'],
    ]);

    $states = State::query()
        ->when(
            !empty($validated['countryid']),
            fn ($query) => $query->where('countryid', $validated['countryid']),
            fn ($query) => $query->whereRaw('1 = 0')
        )
        ->orderBy('statename')
        ->get(['id', 'statecode', 'statename']);

    return response()->json([
        'states' => $states->map(fn ($state) => [
            'id' => $state->id,
            'name' => $state->statename,
            'code' => $state->statecode,
        ])->values(),
    ]);
}

public function regionsForCountryState(Request $request): JsonResponse
{
    $validated = $request->validate([
        'countryid' => ['nullable', 'integer', 'exists:countries,id'],
        'stateid' => ['nullable', 'integer', 'exists:states,id'],
    ]);

    $regions = Region::query()
        ->when(
            !empty($validated['countryid']),
            fn ($query) => $query->where('countryid', $validated['countryid']),
            fn ($query) => $query->whereRaw('1 = 0')
        )
        ->when(!empty($validated['stateid']), function ($query) use ($validated) {
            $query->where(function ($subQuery) use ($validated) {
                $subQuery->where('stateid', $validated['stateid'])
                    ->orWhereNull('stateid');
            });
        })
        ->orderBy('regionname')
        ->get(['id', 'regionname']);

    return response()->json([
        'regions' => $regions->map(fn ($region) => [
            'id' => $region->id,
            'name' => $region->regionname,
        ])->values(),
    ]);
}

    public function show(Request $request, Place $place)
    {
        return redirect()->route('places.edit', [
            'place' => $place,
            'return_to' => $request->input('return_to'),
        ]);
    }

    public function referenceBook(Request $request)
{
    $selectedCountryId = $request->query('country_id');
    $selectedStateId = $request->query('state_id');
    $selectedRegionId = $request->query('region_id');
    $selectedPlaceType = $request->query('placetype');
    $selectedStatus = $request->query('status');
    $search = trim((string) $request->query('search'));

    $places = Place::query()
        ->with([
            'country',
            'state',
            'region',
            'destinations' => fn ($query) => $query
                ->with([
                    'items' => fn ($itemQuery) => $itemQuery->orderBy('itemname'),
                ])
                ->orderBy('destinationname'),
            'fuelStops' => fn ($query) => $query->orderBy('stopname'),
            'tripStays' => fn ($query) => $query
                ->with('trip')
                ->orderByDesc('checkindate')
                ->orderByDesc('id'),
        ])
        ->when($search !== '', function ($query) use ($search) {
            $query->where(function ($inner) use ($search) {
                $inner->where('placename', 'like', '%' . $search . '%')
                    ->orWhere('locality', 'like', '%' . $search . '%')
                    ->orWhere('postcode', 'like', '%' . $search . '%');
            });
        })
        ->when($selectedCountryId, fn ($query) => $query->where('countryid', $selectedCountryId))
        ->when($selectedStateId, fn ($query) => $query->where('stateid', $selectedStateId))
        ->when($selectedRegionId, fn ($query) => $query->where('regionid', $selectedRegionId))
        ->when($selectedPlaceType, fn ($query) => $query->where('placetype', $selectedPlaceType))
        ->when($selectedStatus !== null && $selectedStatus !== '', function ($query) use ($selectedStatus) {
            $query->where('isactive', $selectedStatus === 'active' ? 1 : 0);
        })
        ->orderBy('placename')
        ->get();

    return view('reports.places.reference-book', [
        'places' => $places,
        'filters' => [
            'search' => $search,
            'country_id' => $selectedCountryId,
            'state_id' => $selectedStateId,
            'region_id' => $selectedRegionId,
            'placetype' => $selectedPlaceType,
            'status' => $selectedStatus,
        ],
        'returnTo' => route('places.index', $request->only([
            'search',
            'country_id',
            'state_id',
            'region_id',
            'placetype',
            'status',
            'page',
        ])),
    ]);
}

private function normaliseText(?string $value): ?string
{
    $value = trim((string) $value);

    return $value === '' ? null : mb_strtolower($value);
}

private function placeDuplicateKey(?string $placename, $countryId, $stateId, ?string $locality): string
{
    return implode('|', [
        $this->normaliseText($placename) ?? '',
        (string) ($countryId ?? ''),
        (string) ($stateId ?? ''),
        $this->normaliseText($locality) ?? '',
    ]);
}
private function destinationItemTypeOptions(): array
{
    return [
        'attraction' => 'Attraction',
        'walk' => 'Walk',
        'dump_point' => 'Dump Point',
        'water_point' => 'Water Point',
        'museum' => 'Museum',
        'drive' => 'Drive',
        'campground' => 'Campground',
        'lookout' => 'Lookout',
        'other' => 'Other',
    ];
}

}