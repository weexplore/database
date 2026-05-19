<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Services\DestinationImportService;
use App\Models\DestinationSource; 

class DestinationController extends Controller
{
private function typeOptions(): array
{
    return [
        'town',
        'suburb',
        'region',
        'locality',
        'attraction',
    ];
}

    public function index(Request $request)
    {
        $places = Place::query()
            ->orderBy('placename')
            ->get();

        $query = Destination::query()
            ->with('place')
            ->withCount('destinationItems');

        if ($request->filled('placeid')) {
            $query->where('placeid', (int) $request->placeid);
        }

        if ($request->filled('destinationtype')) {
            $query->where('destinationtype', $request->destinationtype);
        }

        if ($request->filled('featured')) {
            $query->where('isfeatured', (int) $request->featured);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('destinationname', 'like', '%' . $search . '%')
                    ->orWhere('bestseason', 'like', '%' . $search . '%')
                    ->orWhere('overview', 'like', '%' . $search . '%');
            });
        }

        $destinations = $query
            ->orderBy('destinationname')
            ->paginate(25)
            ->withQueryString();

        return view('destinations.index', [
            'destinations' => $destinations,
            'places' => $places,
            'typeOptions' => $this->typeOptions(),
        ]);
    }

    public function bulkSave(Request $request)
    {
        $validated = $request->validate([
            'existing' => ['nullable', 'array'],

            'existing.*.placeid' => ['nullable', 'integer', 'exists:places,id'],
            'existing.*.destinationname' => ['required', 'string', 'max:200'],
            'existing.*.destinationtype' => ['required', 'string', Rule::in($this->typeOptions())],
            'existing.*.bestseason' => ['nullable', 'string', 'max:100'],
            'existing.*.revisitinterestlevel' => ['nullable', 'string', Rule::in(array_keys($this->revisitOptions()))],
            'existing.*.isfeatured' => ['nullable', 'boolean'],

            'new' => ['nullable', 'array'],
            'new.placeid' => ['nullable', 'integer', 'exists:places,id'],
            'new.destinationname' => ['nullable', 'string', 'max:200'],
            'new.destinationtype' => ['nullable', 'string', Rule::in($this->typeOptions())],
            'new.bestseason' => ['nullable', 'string', 'max:100'],
            'new.revisitinterestlevel' => ['nullable', 'string', Rule::in(array_keys($this->revisitOptions()))],
            'new.isfeatured' => ['nullable', 'boolean'],

            'placeid' => ['nullable', 'integer', 'exists:places,id'],
            'destinationtype' => ['nullable', 'string', Rule::in($this->typeOptions())],
            'featured' => ['nullable', 'in:0,1'],
            'search' => ['nullable', 'string'],
        ]);


        DB::transaction(function () use ($validated) {
            foreach ($validated['existing'] ?? [] as $destinationId => $row) {
                $destinationname = trim((string) ($row['destinationname'] ?? ''));

                if ($destinationname === '') {
                    throw ValidationException::withMessages([
                        "existing.$destinationId.destinationname" => 'Destination name is required.',
                    ]);
                }

                $destination = Destination::findOrFail($destinationId);

                $destination->update([
                    'placeid' => $row['placeid'] ?? null,
                    'destinationname' => $destinationname,
                    'destinationtype' => $row['destinationtype'],
                    'bestseason' => $row['bestseason'] ?? null,
                    'revisitinterestlevel' => $row['revisitinterestlevel'] ?? null,
                    'isfeatured' => (bool) ($row['isfeatured'] ?? false),
                ]);
            }

            $new = $validated['new'] ?? [];
            $newName = trim((string) ($new['destinationname'] ?? ''));
            $hasNewDestination = $newName !== ''
                || !empty($new['destinationtype'] ?? null)
                || !empty($new['bestseason'] ?? null);

            if ($hasNewDestination) {
                if ($newName === '' || empty($new['destinationtype'])) {
                    throw ValidationException::withMessages([
                        'new.destinationname' => 'Destination name is required for a new destination.',
                        'new.destinationtype' => 'Destination type is required for a new destination.',
                    ]);
                }

                Destination::create([
                    'placeid' => $new['placeid'] ?? null,
                    'destinationname' => $newName,
                    'destinationtype' => $new['destinationtype'],
                    'bestseason' => $new['bestseason'] ?? null,
                    'revisitinterestlevel' => $new['revisitinterestlevel'] ?? null,
                    'isfeatured' => (bool) ($new['isfeatured'] ?? false),
                ]);
            }
        });

        $returnTo = $request->input('return_to');

        if ($returnTo) {
            return redirect($returnTo)->with('success', 'Destinations saved successfully.');
        }

        return redirect()
            ->route('destinations.index', [
                'placeid' => $request->input('placeid'),
                'destinationtype' => $request->input('destinationtype'),
                'featured' => $request->input('featured'),
                'search' => $request->input('search'),
                'page' => $request->input('page'),
            ])
            ->with('success', 'Destinations saved successfully.');
    }

public function edit(Request $request, Destination $destination)
{
    $destination->load([
        'place',
        'items' => fn ($query) => $query
            ->with('itemTypes')
            ->orderBy('itemname')
            ->orderByRaw('COALESCE(sortorder, 999999)'),
        'sources' => fn ($query) => $query
            ->orderByRaw("CASE importstatus
                WHEN 'pendingreview' THEN 1
                WHEN 'approved' THEN 2
                WHEN 'rejected' THEN 3
                WHEN 'archived' THEN 4
                ELSE 5
            END")
            ->orderByDesc('retrievedon')
            ->orderByDesc('createdat'),
        'attachments' => fn ($query) => $query
            ->orderByDesc('isprimary')
            ->orderByDesc('uploadedat')
            ->orderByDesc('id'),
    ]);

    $places = Place::orderBy('placename')->get();
    $typeOptions = $this->typeOptions();

    $returnTo = $request->input('return_to', route('destinations.index', $request->only([
        'placeid',
        'destinationtype',
        'featured',
        'search',
        'page',
    ])));

    return view('destinations.edit', compact(
        'destination',
        'places',
        'typeOptions',
        'returnTo'
    ));
}

    public function update(Request $request, Destination $destination)
    {
        $validated = $request->validate([
            'placeid' => ['nullable', 'integer', 'exists:places,id'],
            'destinationname' => ['required', 'string', 'max:200'],
            'revisitinterestlevel' => ['nullable', 'string', Rule::in(array_keys($this->revisitOptions()))],
            'overview' => ['nullable', 'string'],
            'travelnotes' => ['nullable', 'string'],
            'bestseason' => ['nullable', 'string', 'max:100'],
            'suitability' => ['nullable', 'string'],
            'accessnotes' => ['nullable', 'string'],
            'personalcommentary' => ['nullable', 'string'],
            'isfeatured' => ['nullable', 'boolean'],
        ]);

        $destination->update([
            'placeid' => $validated['placeid'] ?? null,
            'destinationname' => trim($validated['destinationname']),
            'destinationtype' => ['required', 'string', Rule::in($this->typeOptions())],
            'overview' => $validated['overview'] ?? null,
            'travelnotes' => $validated['travelnotes'] ?? null,
            'bestseason' => $validated['bestseason'] ?? null,
            'suitability' => $validated['suitability'] ?? null,
            'accessnotes' => $validated['accessnotes'] ?? null,
            'personalcommentary' => $validated['personalcommentary'] ?? null,
            'revisitinterestlevel' => $validated['revisitinterestlevel'] ?? null,
            'isfeatured' => (bool) ($validated['isfeatured'] ?? false),
        ]);

        $returnTo = $request->input('return_to');

        if ($returnTo) {
            return redirect($returnTo)->with('success', 'Destination updated successfully.');
        }

        return redirect()
            ->route('destinations.edit', $destination)
            ->with('success', 'Destination updated successfully.');
    }

    public function destroy(Request $request, Destination $destination)
    {
        try {
            $destination->delete();

            $returnTo = $request->input('return_to');

            if ($returnTo) {
                return redirect($returnTo)->with('success', 'Destination deleted successfully.');
            }

            return redirect()
                ->route('destinations.index', [
                    'placeid' => $request->input('placeid'),
                    'destinationtype' => $request->input('destinationtype'),
                    'featured' => $request->input('featured'),
                    'search' => $request->input('search'),
                    'page' => $request->input('page'),
                ])
                ->with('success', 'Destination deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('destinations.index', [
                    'placeid' => $request->input('placeid'),
                    'destinationtype' => $request->input('destinationtype'),
                    'featured' => $request->input('featured'),
                    'search' => $request->input('search'),
                ])
                ->with('error', 'This destination is in use and cannot be deleted.');
        }
    }
    
    public function suggestFromWeb(Destination $destination, DestinationImportService $service)
    {
        $destination->load('place');

        $result = $service->suggestForDestination($destination);

        return response()->json($result);
    }

    public function createFromPlace(Request $request, Place $place)
{
    $returnTo = $request->input('return_to');

    $baseName = trim((string) $place->placename);
    $name = $baseName;
    $suffix = 2;

    while (
        Destination::query()
            ->where('placeid', $place->id)
            ->whereRaw('LOWER(destinationname) = ?', [mb_strtolower($name)])
            ->exists()
    ) {
        $name = $baseName . ' - ' . $suffix;
        $suffix++;
    }
    $typeOptions = $this->typeOptions();
    $defaultType = null;

    $placeType = (string) $place->placetype;

    if (in_array('town', $typeOptions, true) && $placeType === 'town') {
        $defaultType = 'town';
    } elseif (in_array('region', $typeOptions, true) && $placeType === 'national_park') {
        $defaultType = 'region';
    } elseif (in_array('attraction', $typeOptions, true) && in_array($placeType, ['day_use_area', 'campground', 'other'], true)) {
        $defaultType = 'attraction';
    } elseif (in_array('locality', $typeOptions, true) && in_array($placeType, ['accommodation', 'caravan_park', 'free_camp', 'showgrounds', 'station_stay'], true)) {
        $defaultType = 'locality';
    }

    if ($defaultType === null) {
        $defaultType = $typeOptions[0] ?? 'town';
    }

    $baseName = trim((string) $name);

    $duplicateExists = Destination::query()
        ->where('placeid', $place->id)
        ->whereRaw('LOWER(destinationname) = ?', [mb_strtolower($baseName)])
        ->exists();

    if ($duplicateExists) {
        return redirect()
            ->route('places.edit', [
                'place' => $place,
                'return_to' => $returnTo,
            ])
            ->with('error', 'A destination with this name is already linked to this place. Rename the existing destination or create a more specific one.');
    }

    $destination = Destination::create([
        'placeid' => $place->id,
        'destinationname' => $name,
        'destinationtype' => $defaultType,
        'overview' => null,
        'travelnotes' => null,
        'bestseason' => null,
        'suitability' => null,
        'accessnotes' => $place->accessnotes,
        'personalcommentary' => null,
        'revisitinterestlevel' => null,
        'isfeatured' => false,
    ]);

    return redirect()
        ->route('destinations.edit', [
            'destination' => $destination,
            'return_to' => $returnTo,
        ])
        ->with('success', 'Destination created from place. Rename it as needed for suburb, locality, or attraction context.');
}

public function create(Request $request)
{
    return redirect()->route('destination-items.index', array_filter([
        'show_create' => 1,
        'destination_id' => $request->input('destination_id'),
        'return_to' => $request->input('return_to'),
    ]));
}

public static function revisitOptions(): array
{
    return [
        'very_likely' => 'Very Likely',
        'likely' => 'Likely',
        'neutral' => 'Neutral',
        'unlikely' => 'Unlikely',
        'very_unlikely' => 'Very Unlikely',
    ];
}

}