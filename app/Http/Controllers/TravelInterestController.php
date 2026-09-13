<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Destination;
use App\Models\DestinationItem;
use App\Models\Region;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TravelInterestController extends Controller
{
    public function index(Request $request)
    {
        $interestOptions = DestinationItem::visitInterestOptions();

        $filters = $request->validate([
            'countryid' => ['nullable', 'integer', 'exists:countries,id'],
            'stateid' => ['nullable', 'integer', 'exists:states,id'],
            'regionid' => ['nullable', 'integer', 'exists:regions,id'],
            'placeid' => ['nullable', 'integer', 'exists:places,id'],
            'interest' => [
                'nullable',
                'string',
                Rule::in(array_keys($interestOptions)),
            ],
            'itemtypeid' => [
                'nullable',
                'integer',
                'exists:destination_item_types,id',
            ],
            'scope' => [
                'nullable',
                'string',
                Rule::in(['all', 'items', 'destinations']),
            ],
            'showvisited' => ['nullable', 'boolean'],
            'search' => ['nullable', 'string', 'max:200'],
        ]);

        $scope = $filters['scope'] ?? 'all';
        $showVisited = $request->boolean('showvisited');

        $items = collect();
        $destinations = collect();

        if ($scope === 'all' || $scope === 'items') {
            $items = $this->destinationItemQuery(
                $filters,
                $showVisited,
                $interestOptions
            )
                ->paginate(50, ['*'], 'items_page')
                ->withQueryString();
        }

        if ($scope === 'all' || $scope === 'destinations') {
            $destinations = $this->destinationQuery(
                $filters,
                $showVisited,
                $interestOptions
            )
                ->paginate(30, ['*'], 'destinations_page')
                ->withQueryString();
        }

        $countries = Country::query()
            ->orderBy('countryname')
            ->get(['id', 'countryname']);

        $states = State::query()
            ->orderBy('statename')
            ->get(['id', 'countryid', 'statecode', 'statename']);

        $regions = Region::query()
            ->orderBy('regionname')
            ->get(['id', 'countryid', 'stateid', 'regionname']);

        $places = \App\Models\Place::query()
            ->orderBy('placename')
            ->get(['id', 'countryid', 'stateid', 'regionid', 'placename']);

        $itemTypes = \App\Models\DestinationItemType::query()
            ->where('isactive', 1)
            ->orderBy('typename')
            ->get(['id', 'typename']);

        return view('travel-interests.index', compact(
            'items',
            'destinations',
            'countries',
            'states',
            'regions',
            'places',
            'itemTypes',
            'interestOptions',
            'scope',
            'showVisited'
        ));
    }

    private function destinationItemQuery(
    array $filters,
    bool $showVisited,
    array $interestOptions
) {
    $query = DestinationItem::query()
        ->select([
            'destinationitems.*',
            'destinations.destinationname',
            'destinations.destinationtype',
            'destinations.placeid as destination_placeid',
            'effectiveplaces.id as effective_placeid',
            'effectiveplaces.placename as effective_placename',
            'states.id as stateid',
            'states.statecode',
            'states.statename',
            'regions.id as regionid',
            'regions.regionname',
            'countries.id as countryid',
            'countries.countryname',
        ])
        ->join(
            'destinations',
            'destinations.id',
            '=',
            'destinationitems.destinationid'
        )
        ->leftJoin(
            'places as effectiveplaces',
            'effectiveplaces.id',
            '=',
            DB::raw(
                'COALESCE(destinationitems.placeid, destinations.placeid)'
            )
        )
        ->leftJoin(
            'countries',
            'countries.id',
            '=',
            'effectiveplaces.countryid'
        )
        ->leftJoin(
            'states',
            'states.id',
            '=',
            'effectiveplaces.stateid'
        )
        ->leftJoin(
            'regions',
            'regions.id',
            '=',
            'effectiveplaces.regionid'
        )
        ->where('destinationitems.isactive', 1)
        ->whereNotNull('destinationitems.visitinterestlevel')
        ->when(
            ! $showVisited,
            function ($query) {
                $query->where(function ($visitQuery) {
                    $visitQuery
                        ->where('destinationitems.hasvisited', 0)
                        ->orWhere('destinationitems.stillwanttovisit', 1);
                });
            }
        )
        ->when(
            $filters['countryid'] ?? null,
            fn ($query, $countryId) => $query->where(
                'effectiveplaces.countryid',
                $countryId
            )
        )
        ->when(
            $filters['stateid'] ?? null,
            fn ($query, $stateId) => $query->where(
                'effectiveplaces.stateid',
                $stateId
            )
        )
        ->when(
            $filters['regionid'] ?? null,
            fn ($query, $regionId) => $query->where(
                'effectiveplaces.regionid',
                $regionId
            )
        )
        ->when(
            $filters['placeid'] ?? null,
            fn ($query, $placeId) => $query->where(
                'effectiveplaces.id',
                $placeId
            )
        )
        ->when(
            $filters['interest'] ?? null,
            fn ($query, $interest) => $query->where(
                'destinationitems.visitinterestlevel',
                $interest
            )
        )
        ->when(
            $filters['itemtypeid'] ?? null,
            function ($query, $itemTypeId) {
                $query->whereExists(function ($typeQuery) use ($itemTypeId) {
                    $typeQuery
                        ->select(DB::raw(1))
                        ->from('destinationitem_destination_item_type as dit')
                        ->whereColumn(
                            'dit.destinationitem_id',
                            'destinationitems.id'
                        )
                        ->where(
                            'dit.destination_item_type_id',
                            $itemTypeId
                        );
                });
            }
        )
        ->when(
            $filters['search'] ?? null,
            function ($query, $search) {
                $search = '%' . trim($search) . '%';

                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('destinationitems.itemname', 'like', $search)
                        ->orWhere(
                            'destinationitems.shortdescription',
                            'like',
                            $search
                        )
                        ->orWhere(
                            'destinationitems.visitinterestnotes',
                            'like',
                            $search
                        )
                        ->orWhere(
                            'destinations.destinationname',
                            'like',
                            $search
                        )
                        ->orWhere(
                            'effectiveplaces.placename',
                            'like',
                            $search
                        );
                });
            }
        )
        ->with('itemTypes')
        ->orderByRaw("
            CASE destinationitems.visitinterestlevel
                WHEN 'must_visit' THEN 1
                WHEN 'very_interested' THEN 2
                WHEN 'interested' THEN 3
                WHEN 'if_nearby' THEN 4
                ELSE 5
            END
        ")
        ->orderBy('countries.countryname')
        ->orderBy('states.statename')
        ->orderBy('regions.regionname')
        ->orderBy('effectiveplaces.placename')
        ->orderBy('destinations.destinationname')
        ->orderBy('destinationitems.itemname');

    return $query;
}

    private function destinationQuery(
        array $filters,
        bool $showVisited,
        array $interestOptions
    ) {
        $query = Destination::query()
            ->select([
                'destinations.*',
                'places.placename',
                'countries.id as countryid',
                'countries.countryname',
                'states.id as stateid',
                'states.statecode',
                'states.statename',
                'regions.id as regionid',
                'regions.regionname',
            ])
            ->leftJoin(
                'places',
                'places.id',
                '=',
                'destinations.placeid'
            )
            ->leftJoin(
                'countries',
                'countries.id',
                '=',
                'places.countryid'
            )
            ->leftJoin(
                'states',
                'states.id',
                '=',
                'places.stateid'
            )
            ->leftJoin(
                'regions',
                'regions.id',
                '=',
                'places.regionid'
            )
            ->whereNotNull('destinations.visitinterestlevel')
            ->when(
                ! $showVisited,
                fn ($query) => $query->where('destinations.hasvisited', 0)
            )
            ->when(
                $filters['countryid'] ?? null,
                fn ($query, $countryId) => $query->where(
                    'places.countryid',
                    $countryId
                )
            )
            ->when(
                $filters['stateid'] ?? null,
                fn ($query, $stateId) => $query->where(
                    'places.stateid',
                    $stateId
                )
            )
            ->when(
                $filters['regionid'] ?? null,
                fn ($query, $regionId) => $query->where(
                    'places.regionid',
                    $regionId
                )
            )
            ->when(
                $filters['placeid'] ?? null,
                fn ($query, $placeId) => $query->where(
                    'destinations.placeid',
                    $placeId
                )
            )
            ->when(
                $filters['interest'] ?? null,
                fn ($query, $interest) => $query->where(
                    'destinations.visitinterestlevel',
                    $interest
                )
            )
            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {
                    $search = '%' . trim($search) . '%';

                    $query->where(function ($searchQuery) use ($search) {
                        $searchQuery
                            ->where('destinations.destinationname', 'like', $search)
                            ->orWhere(
                                'destinations.visitinterestnotes',
                                'like',
                                $search
                            )
                            ->orWhere('places.placename', 'like', $search);
                    });
                }
            )
            ->orderByRaw("
                CASE destinations.visitinterestlevel
                    WHEN 'must_visit' THEN 1
                    WHEN 'very_interested' THEN 2
                    WHEN 'interested' THEN 3
                    WHEN 'if_nearby' THEN 4
                    ELSE 5
                END
            ")
            ->orderBy('countries.countryname')
            ->orderBy('states.statename')
            ->orderBy('regions.regionname')
            ->orderBy('places.placename')
            ->orderBy('destinations.destinationname');

        return $query;
    }
}