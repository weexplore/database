<?php

namespace App\Http\Controllers;

use App\Models\Traveller;
use App\Models\Trip;
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
            $query->where('tripstatus', $request->tripstatus);
        }

        if ($request->filled('year')) {
            $year = (int) $request->year;

            $query->where(function ($q) use ($year) {
                $q->whereYear('startdate', $year)
                    ->orWhereYear('enddate', $year);
            });
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('tripname', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%')
                    ->orWhere('summary', 'like', '%' . $search . '%');
            });
        }

        $trips = $query
            ->orderByDesc('startdate')
            ->orderBy('tripname')
            ->paginate(25)
            ->withQueryString();

        $availableYears = Trip::query()
            ->whereNotNull('startdate')
            ->selectRaw('YEAR(startdate) as tripyear')
            ->distinct()
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
                || !empty($new['tripstatus'] ?? null)
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
            ])
            ->with('success', 'Trips saved successfully.');
    }

    public function edit(Trip $trip)
    {
        $trip->load(['travellers', 'tripTravellerLinks']);
        $travellers = Traveller::query()
            ->where('isactive', 1)
            ->orderBy('displayname')
            ->get();

        $selectedTravellers = $trip->travellers
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return view('trips.edit', [
            'trip' => $trip,
            'travellers' => $travellers,
            'selectedTravellers' => $selectedTravellers,
            'statusOptions' => $this->statusOptions,
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
        });

        return redirect()
            ->route('trips.edit', $trip)
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
}