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
    ->select(['id', 'placename'])
    ->get();    

   $query = Destination::query()
        ->select([
            'id',
            'placeid',
            'destinationname',
            'destinationtype',
            'bestseason',
            'revisitinterestlevel',
            'hasvisited',
            'visitinterestlevel',
            'isfeatured',
        ])
        ->withCount([
            'items as destination_items_count',
        ]);

    if ($request->filled('placeid')) {
        $query->where('placeid', (int) $request->placeid);
    }

    if ($request->filled('destinationtype')) {
        $query->where('destinationtype', $request->destinationtype);
    }

    if ($request->filled('visited')) {
        $query->where('hasvisited', (int) $request->visited);
    }

    if ($request->filled('featured')) {
        $query->where('isfeatured', (int) $request->featured);
    }
    if ($request->filled('visitinterestlevel')) {
        $query->where('visitinterestlevel', $request->visitinterestlevel);
    }

    if ($request->filled('search')) {
        $search = trim((string) $request->search);

        $query->where(function ($q) use ($search) {
            $q->where('destinationname', 'like', '%' . $search . '%')
              ->orWhere('bestseason', 'like', '%' . $search . '%');
        });
    }

    $totalDestinations = (clone $query)->count('id');

    $destinations = $query
        ->orderBy('destinationname')
        ->paginate(20)
        ->withQueryString();

    return view('destinations.index', [
        'destinations' => $destinations,
        'places' => $places,
        'typeOptions' => $this->typeOptions(),
        'totalDestinations' => $totalDestinations,
        'revisitOptions' => self::revisitOptions(),
        'visitInterestOptions' => $this->visitInterestOptions(),
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
            'existing.*.revisitinterestlevel' => [
                'nullable',
                'string',
                Rule::in(array_keys($this->revisitOptions())),
            ],
            'existing.*.hasvisited' => ['nullable', 'boolean'],
            'existing.*.visitinterestlevel' => [
                'nullable',
                'string',
                Rule::in(array_keys($this->visitInterestOptions())),
            ],
            'existing.*.visitinterestnotes' => ['nullable', 'string'],
            'existing.*.visitedat' => ['nullable', 'date'],
            'existing.*.isfeatured' => ['nullable', 'boolean'],


            'new' => ['nullable', 'array'],
            'new.placeid' => ['nullable', 'integer', 'exists:places,id'],
            'new.destinationname' => ['nullable', 'string', 'max:200'],
            'new.destinationtype' => ['nullable', 'string', Rule::in($this->typeOptions())],
            'new.bestseason' => ['nullable', 'string', 'max:100'],
            'new.revisitinterestlevel' => [
                'nullable',
                'string',
                Rule::in(array_keys($this->revisitOptions())),
            ],
            'new.hasvisited' => ['nullable', 'boolean'],
            'new.visitinterestlevel' => [
                'nullable',
                'string',
                Rule::in(array_keys($this->visitInterestOptions())),
            ],
            'new.visitinterestnotes' => ['nullable', 'string'],
            'new.visitedat' => ['nullable', 'date'],
            'new.isfeatured' => ['nullable', 'boolean'],

            'placeid' => ['nullable', 'integer', 'exists:places,id'],
            'destinationtype' => ['nullable', 'string', Rule::in($this->typeOptions())],
            'visitinterestlevel' => [
                'nullable',
                'string',
                Rule::in(array_keys($this->visitInterestOptions())),
            ],
            'featured' => ['nullable', 'in:0,1'],
            'search' => ['nullable', 'string'],
            'visited' => ['nullable', 'in:0,1'],
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
                $visitInterestLevel = $row['visitinterestlevel'] ?: null;

                $wishlistAddedAt = $destination->wishlistaddedat;

                if (
                    filled($visitInterestLevel)
                    && blank($wishlistAddedAt)
                ) {
                    $wishlistAddedAt = now();
                }

                $destination->update([
                    'placeid' => array_key_exists('placeid', $row)
                        ? ($row['placeid'] !== '' ? $row['placeid'] : null)
                        : $destination->placeid,
                    'destinationname' => $destinationname,
                    'destinationtype' => $row['destinationtype'],
                    'bestseason' => $row['bestseason'] ?? null,
                    'revisitinterestlevel' => $row['revisitinterestlevel'] ?? null,
                    'hasvisited' => (bool) ($row['hasvisited'] ?? false),
                    'isfeatured' => (bool) ($row['isfeatured'] ?? false),
                    'visitinterestlevel' => $visitInterestLevel,
                    'visitinterestnotes' => array_key_exists('visitinterestnotes', $row)
                        ? ($row['visitinterestnotes'] ?: null)
                        : $destination->visitinterestnotes,
                    'wishlistaddedat' => $wishlistAddedAt,
                    'visitedat' => array_key_exists('visitedat', $row)
                        ? ($row['visitedat'] ?: null)
                        : $destination->visitedat,
                ]);
            }

            $new = $validated['new'] ?? [];
            $newName = trim((string) ($new['destinationname'] ?? ''));
            $hasNewDestination = $newName !== ''
                || !empty($new['destinationtype'] ?? null)
                || !empty($new['bestseason'] ?? null)
                || !empty($new['visitinterestlevel'] ?? null);

            if ($hasNewDestination) {
                if ($newName === '' || empty($new['destinationtype'])) {
                    throw ValidationException::withMessages([
                        'new.destinationname' => 'Destination name is required for a new destination.',
                        'new.destinationtype' => 'Destination type is required for a new destination.',
                    ]);
                }
                $newVisitInterestLevel = $new['visitinterestlevel'] ?: null;

                Destination::create([
                    'placeid' => $new['placeid'] ?? null,
                    'destinationname' => $newName,
                    'destinationtype' => $new['destinationtype'],
                    'bestseason' => $new['bestseason'] ?? null,
                    'revisitinterestlevel' => $new['revisitinterestlevel'] ?? null,
                    'hasvisited' => (bool) ($new['hasvisited'] ?? false),
                    'visitinterestlevel' => $newVisitInterestLevel,
                    'visitinterestnotes' => $new['visitinterestnotes'] ?? null,
                    'wishlistaddedat' => filled($newVisitInterestLevel) ? now() : null,
                    'visitedat' => $new['visitedat'] ?? null,
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
                'visitinterestlevel' => $request->input('visitinterestlevel'),
                'visited' => $request->input('visited'),
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
            ->with('extracts')
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
    $visitInterestOptions = Destination::visitInterestOptions();
    $revisitOptions = self::revisitOptions();

   $returnTo = $request->input('return_to', route(
        'destinations.index',
        $request->only([
            'placeid',
            'destinationtype',
            'featured',
            'visitinterestlevel',
            'visited',
            'search',
            'page',
        ])
    ));

    return view('destinations.edit', compact(
        'destination',
        'places',
        'typeOptions',
        'returnTo',
        'visitInterestOptions',
        'revisitOptions'
    ));
}

    public function update(Request $request, Destination $destination)
{
    $validated = $request->validate([
        'placeid' => ['nullable', 'integer', 'exists:places,id'],
        'destinationname' => ['required', 'string', 'max:200'],
        'destinationtype' => ['required', 'string', Rule::in($this->typeOptions())],
        'revisitinterestlevel' => ['nullable', 'string', Rule::in(array_keys($this->revisitOptions()))],
        'hasvisited' => ['nullable', 'boolean'],
        'visitinterestlevel' => ['nullable', 'string', Rule::in(array_keys($this->visitInterestOptions()))],
        'visitinterestnotes' => ['nullable', 'string'],
        'visitedat' => ['nullable', 'date'],
        'overview' => ['nullable', 'string'],
        'travelnotes' => ['nullable', 'string'],
        'bestseason' => ['nullable', 'string', 'max:100'],
        'suitability' => ['nullable', 'string'],
        'accessnotes' => ['nullable', 'string'],
        'personalcommentary' => ['nullable', 'string'],
        'isfeatured' => ['nullable', 'boolean'],
        'tab' => ['nullable', 'string', Rule::in([
            'destination', 'destination-items', 'record-information',
            'attachments', 'internet-research',
        ])],
    ]);

    $visitInterestLevel = ($validated['visitinterestlevel'] ?? null) ?: null;
    $wishlistAddedAt = $destination->wishlistaddedat;

    if (filled($visitInterestLevel) && blank($wishlistAddedAt)) {
        $wishlistAddedAt = now();
    }

    $destination->update([
        'placeid' => $validated['placeid'] ?? null,
        'destinationname' => trim($validated['destinationname']),
        'destinationtype' => $validated['destinationtype'],
        'overview' => $validated['overview'] ?? null,
        'travelnotes' => $validated['travelnotes'] ?? null,
        'bestseason' => $validated['bestseason'] ?? null,
        'suitability' => $validated['suitability'] ?? null,
        'accessnotes' => $validated['accessnotes'] ?? null,
        'personalcommentary' => $validated['personalcommentary'] ?? null,
        'revisitinterestlevel' => $validated['revisitinterestlevel'] ?? null,
        'hasvisited' => (bool) ($validated['hasvisited'] ?? false),
        'visitinterestlevel' => $visitInterestLevel,
        'visitinterestnotes' => $validated['visitinterestnotes'] ?? null,
        'wishlistaddedat' => $wishlistAddedAt,
        'visitedat' => $validated['visitedat'] ?? null,
        'isfeatured' => (bool) ($validated['isfeatured'] ?? false),
    ]);

    $parameters = [
        'destination' => $destination,
        'tab' => $validated['tab'] ?? 'destination',
    ];

    $returnTo = $request->input('return_to');
    if (is_string($returnTo) && $returnTo !== '') {
        $parameters['return_to'] = $returnTo;
    }

    return redirect()
        ->route('destinations.edit', $parameters)
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
                    'visitinterestlevel' => $request->input('visitinterestlevel'),
                    'visited' => $request->input('visited'),
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
                    'visitinterestlevel' => $request->input('visitinterestlevel'),
                    'visited' => $request->input('visited'),
                    'search' => $request->input('search'),
                ])
                ->with('error', 'This destination is in use and cannot be deleted.');
        }
    }
    
    public function suggestFromWeb(
    Destination $destination,
    DestinationImportService $service
) {
    if (blank(config('services.serpapi.api_key'))) {
        return response()->json([
            'message' => 'Internet search is not configured. Check the SerpAPI key.',
        ], 503);
    }

    $destination->load('place');

    try {
        $result = $service->suggestForDestination($destination);
    } catch (\Illuminate\Http\Client\ConnectionException $exception) {
        report($exception);

        return response()->json([
            'message' => 'The search provider could not be reached. Please try again later.',
        ], 502);
    }

    $destination->load([
        'sources' => fn ($query) => $query
            ->with('extracts')
            ->orderByRaw("CASE importstatus
                WHEN 'pendingreview' THEN 1
                WHEN 'approved' THEN 2
                WHEN 'rejected' THEN 3
                WHEN 'archived' THEN 4
                ELSE 5
            END")
            ->orderByDesc('retrievedon')
            ->orderByDesc('createdat'),
    ]);

    $returnedSourceCount = count($result['sources'] ?? []);

    $sourceListHtml = view(
        'destinations.partials.source-list',
        [
            'destination' => $destination,
            'sourceReturnTo' => route(
                'destinations.edit',
                $destination
            ),
            'sourceFormAction' => null,
            'sourceFormId' => null,
        ]
    )->render();

    return response()->json([
        'message' => $returnedSourceCount > 0
            ? "Search returned {$returnedSourceCount} usable source records. Existing records are included in that count. Destination notes have not been changed."
            : 'No usable source records were returned. Existing sources remain displayed. Destination notes have not been changed.',
        'query' => $result['query'] ?? null,
        'returned_source_count' => $returnedSourceCount,
        'source_list_html' => $sourceListHtml,
    ]);
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
        'hasvisited' => false,
        'visitinterestlevel' => null,
        'visitinterestnotes' => null,
        'wishlistaddedat' => null,
        'visitedat' => null,
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

private function visitInterestOptions(): array
{
    return Destination::visitInterestOptions();
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
public function storeSource(Request $request, Destination $destination)
{
    $validated = $this->validatedNestedSourceData(
        $request,
        $destination
    );

    $destination->sources()->create([
        ...$validated,
        'sourcetype' => 'website',
        'retrievedon' => $validated['retrievedon']
            ?? now()->toDateString(),
    ]);

return redirect()
    ->route(
        'destinations.edit',
        $this->destinationResearchRedirectParameters(
            $request,
            $destination
        )
    )
    ->with(
        'success',
        'Internet source saved successfully.'
    );
}

public function updateSource(
    Request $request,
    Destination $destination,
    DestinationSource $source
) {
    abort_unless(
        (int) $source->destinationid === (int) $destination->id,
        404
    );

    $validated = $this->validatedNestedSourceData(
        $request,
        $destination,
        $source
    );

    $source->update($validated);

    return redirect()
        ->route(
            'destinations.edit',
            $this->destinationResearchRedirectParameters(
                $request,
                $destination
            )
        )
        ->with(
            'success',
            'Internet source saved successfully.'
        );
}

public function destroySource(
    Request $request,
    Destination $destination,
    DestinationSource $source
) {
    abort_unless(
        (int) $source->destinationid === (int) $destination->id,
        404
    );

    DB::transaction(function () use ($destination, $source) {
        $lockedSource = DestinationSource::query()
            ->lockForUpdate()
            ->findOrFail($source->getKey());

        abort_unless(
            (int) $lockedSource->destinationid
                === (int) $destination->id,
            404
        );

        $hasAppliedExtracts = $lockedSource->extracts()
            ->where('extractstatus', 'applied')
            ->exists();

        $hasResolvedCandidates = $lockedSource->itemCandidates()
            ->whereIn('candidatestatus', ['created', 'linked'])
            ->exists();

        if ($hasAppliedExtracts || $hasResolvedCandidates) {
            throw ValidationException::withMessages([
                'source' => [
                    'This source contains applied history and cannot be deleted. Archive it instead.',
                ],
            ]);
        }

        $lockedSource->delete();
    });

    if ($request->expectsJson()) {
        return response()->noContent();
    }

    return redirect()
    ->route(
        'destinations.edit',
        $this->destinationResearchRedirectParameters(
            $request,
            $destination
        )
    )
    ->with(
        'success',
        'Internet source deleted successfully.'
    );
}

protected function validatedNestedSourceData(
    Request $request,
    Destination $destination,
    ?DestinationSource $source = null
): array {
    $uniqueUrl = Rule::unique(
        'destinationsources',
        'sourceurl'
    )->where(
        fn ($query) => $query->where(
            'destinationid',
            $destination->getKey()
        )
    );

    if ($source) {
        $uniqueUrl->ignore($source);
    }

    $validated = $request->validate([
        'sourcetitle' => [
            'required',
            'string',
            'max:255',
        ],
        'sourcepublisher' => [
            'nullable',
            'string',
            'max:150',
        ],
        'sourceurl' => [
            'nullable',
            'url:http,https',
            'max:255',
            $uniqueUrl,
        ],
        'retrievedon' => [
            'nullable',
            'date',
        ],
        'importstatus' => [
            'required',
            Rule::in(DestinationSource::importStatusValues()),
        ],
        'importedsummary' => [
            'nullable',
            'string',
        ],
        'importednotes' => [
            'nullable',
            'string',
        ],
    ]);

    foreach ([
        'sourcetitle',
        'sourcepublisher',
        'sourceurl',
        'importedsummary',
        'importednotes',
    ] as $field) {
        $value = trim((string) ($validated[$field] ?? ''));

        $validated[$field] = $value !== '' ? $value : null;
    }

    $validated['retrievedon'] =
        $validated['retrievedon'] ?? null;

    if ($validated['sourcetitle'] === null) {
        throw ValidationException::withMessages([
            'sourcetitle' => [
                'A source title is required.',
            ],
        ]);
    }

    if ($validated['importstatus'] === 'approved') {
        $hasSubmittedContent =
            $validated['importedsummary'] !== null
            || $validated['importednotes'] !== null;

        $hasExistingContent = $source
            && (
                filled($source->rawcontent)
                || $source->extracts()->exists()
                || $source->itemCandidates()->exists()
            );

        if (!$hasSubmittedContent && !$hasExistingContent) {
            throw ValidationException::withMessages([
                'importedsummary' => [
                    'An approved source must contain imported content, raw content, an extract, or an item candidate.',
                ],
            ]);
        }
    }

    return $validated;
}

protected function destinationResearchRedirectParameters(
    Request $request,
    Destination $destination
): array {
    $parameters = [
        'destination' => $destination,
        'tab' => 'internet-research',
    ];

    $candidate = $request->input('return_to');

    $expected = parse_url(
        route('destinations.edit', $destination)
    );

    if (!is_string($candidate)) {
        return $parameters;
    }

    $parts = parse_url($candidate);

    if (!is_array($parts) || !is_array($expected)) {
        return $parameters;
    }

    $port = fn (array $url): int => (int) (
        $url['port']
        ?? (($url['scheme'] ?? '') === 'https' ? 443 : 80)
    );

    if (
        isset($parts['user'])
        || isset($parts['pass'])
        || ($parts['scheme'] ?? '') !== ($expected['scheme'] ?? '')
        || strtolower($parts['host'] ?? '')
            !== strtolower($expected['host'] ?? '')
        || $port($parts) !== $port($expected)
        || ($parts['path'] ?? '') !== ($expected['path'] ?? '')
    ) {
        return $parameters;
    }

    parse_str($parts['query'] ?? '', $query);

    $backUrl = $query['return_to'] ?? null;

    if (
        !is_string($backUrl)
        || $backUrl === ''
        || str_contains($backUrl, '\\')
        || preg_match('/[\x00-\x20\x7F]/', $backUrl)
    ) {
        return $parameters;
    }

    if (
        str_starts_with($backUrl, '/')
        && !str_starts_with($backUrl, '//')
    ) {
        $parameters['return_to'] = $backUrl;

        return $parameters;
    }

    $back = parse_url($backUrl);

    if (
        is_array($back)
        && !isset($back['user'])
        && !isset($back['pass'])
        && ($back['scheme'] ?? '') === ($expected['scheme'] ?? '')
        && strtolower($back['host'] ?? '')
            === strtolower($expected['host'] ?? '')
        && $port($back) === $port($expected)
    ) {
        $parameters['return_to'] = $backUrl;
    }

    return $parameters;
}

}