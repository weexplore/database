<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Region;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegionController extends Controller
{
    public function index(Request $request)
    {
        $countries = Country::query()
            ->where('isactive', 1)
            ->orderBy('countryname')
            ->get();

        $states = State::query()
            ->where('isactive', 1)
            ->orderBy('statename')
            ->get();

        $regions = Region::query()
            ->with(['country', 'state']);

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $regions->where('regionname', 'like', '%' . $search . '%');
        }

        if ($request->filled('country_id')) {
            $regions->where('countryid', $request->integer('country_id'));
        }

        if ($request->filled('state_id')) {
            $regions->where('stateid', $request->integer('state_id'));
        }

        if ($request->filled('status')) {
            $regions->where('isactive', (int) $request->status);
        }

        $regions = $regions
            ->orderByRaw('COALESCE(sortorder, 999999)')
            ->orderBy('regionname')
            ->get();

        return view('regions.index', compact('regions', 'countries', 'states'));
    }

    public function bulkSave(Request $request)
    {
        $validated = $request->validate([
            'existing' => ['nullable', 'array'],
            'existing.*.regionname' => ['required', 'string', 'max:150'],
            'existing.*.country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'existing.*.state_id' => ['nullable', 'integer', 'exists:states,id'],
            'existing.*.regiontype' => ['nullable', 'string', 'max:50'],
            'existing.*.sortorder' => ['nullable', 'integer', 'min:0'],
            'existing.*.isactive' => ['nullable', 'boolean'],
            'existing.*.notes' => ['nullable', 'string'],
            'existing.*.parentregionid' => ['nullable', 'integer', 'exists:regions,id'],

            'new' => ['nullable', 'array'],
            'new.regionname' => ['nullable', 'string', 'max:150'],
            'new.country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'new.state_id' => ['nullable', 'integer', 'exists:states,id'],
            'new.regiontype' => ['nullable', 'string', 'max:50'],
            'new.sortorder' => ['nullable', 'integer', 'min:0'],
            'new.isactive' => ['nullable', 'boolean'],
            'new.notes' => ['nullable', 'string'],
            'new.parentregionid' => ['nullable', 'integer', 'exists:regions,id'],

            'search' => ['nullable', 'string'],
            'country_id' => ['nullable', 'integer'],
            'state_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:0,1'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['existing'] ?? [] as $regionId => $row) {
                $region = Region::findOrFail($regionId);

                $region->update([
                    'regionname' => $row['regionname'],
                    'countryid' => $row['country_id'] ?? null,
                    'stateid' => $row['state_id'] ?? null,
                    'regiontype' => $row['regiontype'] ?? null,
                    'sortorder' => $row['sortorder'] ?? null,
                    'isactive' => (bool) ($row['isactive'] ?? false),
                    'notes' => $row['notes'] ?? null,
                    'parentregionid' => $row['parentregionid'] ?? null,
                ]);
            }

            $new = $validated['new'] ?? [];
            $hasNewRegion = filled($new['regionname'] ?? null);

            if ($hasNewRegion) {
                Region::create([
                    'regionname' => $new['regionname'],
                    'countryid' => $new['country_id'] ?? null,
                    'stateid' => $new['state_id'] ?? null,
                    'regiontype' => $new['regiontype'] ?? null,
                    'sortorder' => $new['sortorder'] ?? null,
                    'isactive' => (bool) ($new['isactive'] ?? false),
                    'notes' => $new['notes'] ?? null,
                    'parentregionid' => $new['parentregionid'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('regions.index', [
                'search' => $request->input('search'),
                'country_id' => $request->input('country_id'),
                'state_id' => $request->input('state_id'),
                'status' => $request->input('status'),
            ])
            ->with('success', 'Regions saved successfully.');
    }

    public function destroy(Request $request, Region $region)
    {
        try {
            $region->delete();

            return redirect()
                ->route('regions.index', [
                    'search' => $request->input('search'),
                    'country_id' => $request->input('country_id'),
                    'state_id' => $request->input('state_id'),
                    'status' => $request->input('status'),
                ])
                ->with('success', 'Region deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('regions.index', [
                    'search' => $request->input('search'),
                    'country_id' => $request->input('country_id'),
                    'state_id' => $request->input('state_id'),
                    'status' => $request->input('status'),
                ])
                ->with('error', 'This region is in use and cannot be deleted.');
        }
    }
}