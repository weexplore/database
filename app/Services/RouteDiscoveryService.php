<?php

namespace App\Services;

use App\Models\DestinationItem;
use App\Models\Place;
use App\Models\TripLeg;
use Illuminate\Support\Collection;

class RouteDiscoveryService
{
    public function buildTripLegSuggestions(TripLeg $tripLeg, $profile = null): array
    {
        $routePoints = $this->getLegRoutePoints(
            $tripLeg,
            (bool) ($profile?->usedestinationitemsasanchors ?? true)
        );

        $segments = $this->buildRouteSegments($routePoints);

        $bufferKm = (float) (
            $profile?->searchbufferkm
            ?? $profile?->maxdistancefromroutekm
            ?? 20
        );

        $maxResults = max(0, (int) ($profile?->defaultmaxresults ?? 0));
        $corridorType = $profile?->corridortype ?? 'route_buffer';
        $includeLegPointsInDistance = (bool) ($profile?->includelegpointsindistance ?? true);

        $result = [
            'hasRoute' => false,
            'places' => collect(),
            'destinations' => collect(),
            'fuelStops' => collect(),
            'stays' => collect(),
            'message' => null,
            'profile' => $profile,
            'corridorType' => $corridorType,
            'routePoints' => $routePoints,
            'routeSegments' => $segments,
            'routePointCount' => $routePoints->count(),
            'routeSegmentCount' => $segments->count(),
            'routeDistanceKm' => round((float) $segments->sum('length_km'), 1),
            'searchPasses' => collect([
                [
                    'buffer_km' => round($bufferKm, 1),
                    'max_results' => $maxResults,
                ]
            ]),
            'finalRadiusKm' => round($bufferKm, 1),
            'bufferKm' => round($bufferKm, 1),
            'minimumResults' => 0,
        ];

        if ($routePoints->count() < 2) {
            $result['message'] = 'Suggestions need at least two route points with valid coordinates.';
            return $result;
        }

        if ($segments->isEmpty()) {
            $result['message'] = 'Suggestions need at least one valid route segment.';
            return $result;
        }

        $result['hasRoute'] = true;

        $places = $this->findPlacesAlongRoute(
            $tripLeg,
            $segments,
            $bufferKm,
            true,
            $profile,
            $includeLegPointsInDistance
        );

        logger()->info('RouteDiscovery buildTripLegSuggestions', [
            'trip_leg_id' => $tripLeg->id,
            'profile_id' => $profile?->id,
            'buffer_km' => $bufferKm,
            'route_points' => $routePoints->toArray(),
            'route_point_count' => $routePoints->count(),
            'segment_count' => $segments->count(),
            'place_ids_before_interval' => $places->pluck('id')->all(),
            'place_names_before_interval' => $places->pluck('placename')->all(),
        ]);

        if ($maxResults > 0) {
            $places = $places->values();
            //$places = $this->applyMinimumStopInterval($places, (float) ($profile?->minstopintervalkm ?? 0))
            //    ->take($maxResults)
            //    ->values();
        } else {
            $places = $places->values();
            //$places = $this->applyMinimumStopInterval($places, (float) ($profile?->minstopintervalkm ?? 0))
            //    ->values();
        }

        $result['places'] = $places;
        $result['destinations'] = collect();
        $result['fuelStops'] = collect();
        $result['stays'] = collect();

        if ($places->isEmpty()) {
            $result['message'] = 'No nearby route suggestions were found for the selected profile settings.';
        }

        return $result;
    }

    private function findPlacesAlongRoute(
    TripLeg $tripLeg,
    Collection $segments,
    float $radiusKm,
    bool $includeDestinationItems,
    $profile = null,
    bool $includeLegPointsInDistance = true
): Collection {
    $boundsPaddingDegrees = max(0.05, $radiusKm / 111);
    $bounds = $this->buildSegmentsBounds($segments, $boundsPaddingDegrees);

    $includePlaceTypes = $this->splitCsvValues($profile?->includeplacetypes);
    $excludePlaceTypes = $this->splitCsvValues($profile?->excludeplacetypes);

    $distanceSegments = $includeLegPointsInDistance
        ? $segments
        : $this->buildRouteSegments($this->getLegRoutePoints($tripLeg, false));

    $placesQuery = Place::query()
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->whereBetween('latitude', [$bounds['minLat'], $bounds['maxLat']])
        ->whereBetween('longitude', [$bounds['minLng'], $bounds['maxLng']]);

    if (!empty($includePlaceTypes)) {
        $placesQuery->whereIn('placetype', $includePlaceTypes);
    }

    if (!empty($excludePlaceTypes)) {
        $placesQuery->where(function ($query) use ($excludePlaceTypes) {
            $query->whereNull('placetype')
                ->orWhereNotIn('placetype', $excludePlaceTypes);
        });
    }

    $corridorPlaces = $placesQuery
        ->get()
        ->map(function ($place) use ($distanceSegments) {
            $projection = $this->projectPointToRoute(
                (float) $place->latitude,
                (float) $place->longitude,
                $distanceSegments
            );

            if (!$projection) {
                return null;
            }

            $place->distancefromroutekm = round($projection['distance_km'], 1);
            $place->distancealongroutekm = round($projection['distance_along_route_km'], 1);
            $place->path_progress = $projection['distance_along_route_km'];
            $place->segment_index = $projection['segment_index'];

            return $place;
        })
        ->filter()
        ->filter(fn ($place) => $place->distancefromroutekm <= $radiusKm)
        ->values();

    $mandatoryPlaceIds = collect();

    $routePointPlaceIds = $this->getLegRoutePoints(
        $tripLeg,
        (bool) ($profile?->usedestinationitemsasanchors ?? true)
    )->pluck('place_id');

    $legPointPlaceIds = $tripLeg->legPoints
        ->pluck('placeid')
        ->filter();

    $mandatoryPlaceIds = $mandatoryPlaceIds
        ->concat($routePointPlaceIds)
        ->concat($legPointPlaceIds)
        ->unique()
        ->values();

    logger()->info('RouteDiscovery mandatory places', [
        'trip_leg_id' => $tripLeg->id,
        'route_point_place_ids' => $routePointPlaceIds->all(),
        'leg_point_place_ids' => $legPointPlaceIds->all(),
        'mandatory_place_ids' => $mandatoryPlaceIds->all(),
    ]); 

    $mandatoryPlaces = $mandatoryPlaceIds->isEmpty()
        ? collect()
        : Place::query()
            ->whereIn('id', $mandatoryPlaceIds->all())
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($place) use ($distanceSegments, $legPointPlaceIds) {
                $projection = $this->projectPointToRoute(
                    (float) $place->latitude,
                    (float) $place->longitude,
                    $distanceSegments
                );

                if ($projection) {
                    $place->distancefromroutekm = round($projection['distance_km'], 1);
                    $place->distancealongroutekm = round($projection['distance_along_route_km'], 1);
                    $place->path_progress = $projection['distance_along_route_km'];
                    $place->segment_index = $projection['segment_index'];
                }

                // Flag leg-point places
                $place->is_leg_point = $legPointPlaceIds->contains($place->id);

                return $place;
            });

    $places = $corridorPlaces
        ->concat($mandatoryPlaces)
        ->unique('id')
        ->sortBy([
            ['distancealongroutekm', 'asc'],
            ['distancefromroutekm', 'asc'],
            ['placename', 'asc'],
        ])
        ->values();

    $destinationItemsByPlaceId = $includeDestinationItems
        ? $this->getDestinationItemsGroupedByPlaceForRoute($places->pluck('id')->all(), $distanceSegments, $radiusKm)
        : collect();

    $legPointDestinationItemsByPlaceId = ($includeDestinationItems && ($profile?->usedestinationitemsasanchors ?? true))
        ? $this->getLegPointDestinationItemsGroupedByPlace($tripLeg)
        : collect();
        

    return $places->map(function ($place) use ($destinationItemsByPlaceId, $legPointDestinationItemsByPlaceId) {
        $routeItems = $destinationItemsByPlaceId->get($place->id, collect());
        $legPointItems = $legPointDestinationItemsByPlaceId->get($place->id, collect());

        $place->destination_items = $routeItems
            ->concat($legPointItems)
            ->unique('id')
            ->sortBy([
                ['distancealongroutekm', 'asc'],
                ['itemtype', 'asc'],
                ['itemname', 'asc'],
            ])
            ->values();

        return $place;
    });
}

    public function getLegRoutePoints(TripLeg $tripLeg, bool $useDestinationItemsAsAnchors = true): Collection
    {
        $tripLeg->loadMissing([
            'fromPlace',
            'fromDestination.place',
            'fromDestinationItem.destination.place',
            'fromDestinationItem.place',
            'toPlace',
            'toDestination.place',
            'toDestinationItem.destination.place',
            'toDestinationItem.place',
            'legPoints' => fn ($query) => $query
                ->with([
                    'place',
                    'destination.place',
                    'destinationItem.destination.place',
                    'destinationItem.place',
                ])
                ->orderBy('sequenceno'),
        ]);

        $points = collect();

        $startPoint = $useDestinationItemsAsAnchors
            ? $this->resolveTripLegEndpoint(
                $tripLeg->fromDestinationItem,
                $tripLeg->fromDestination,
                $tripLeg->fromPlace,
                'start'
            )
            : $this->resolveTripLegEndpoint(
                null,
                $tripLeg->fromDestination,
                $tripLeg->fromPlace,
                'start'
            );

        if ($startPoint) {
            $points->push($startPoint);
        }

        foreach ($tripLeg->legPoints as $legPoint) {
            $resolved = $useDestinationItemsAsAnchors
                ? $this->resolveLegPointLocation($legPoint)
                : $this->resolveLegPointLocationWithoutDestinationItem($legPoint);

            if ($resolved) {
                $points->push($resolved);
            }
        }

        $endPoint = $useDestinationItemsAsAnchors
            ? $this->resolveTripLegEndpoint(
                $tripLeg->toDestinationItem,
                $tripLeg->toDestination,
                $tripLeg->toPlace,
                'end'
            )
            : $this->resolveTripLegEndpoint(
                null,
                $tripLeg->toDestination,
                $tripLeg->toPlace,
                'end'
            );

        if ($endPoint) {
            $points->push($endPoint);
        }

        return $points
            ->filter(fn ($point) => $point['lat'] !== null && $point['lng'] !== null)
            ->values();
    }

    private function applyMinimumStopInterval(Collection $places, float $minStopIntervalKm): Collection
    {
        if ($minStopIntervalKm <= 0) {
            return $places->values();
        }

        $selected = collect();

        foreach ($places as $place) {
            // Always keep leg point places
            if (!empty($place->is_leg_point)) {
                $selected->push($place);
                continue;
            }

            $tooClose = $selected->contains(function ($selectedPlace) use ($place, $minStopIntervalKm) {
                return abs(
                    (float) $place->distancealongroutekm - (float) $selectedPlace->distancealongroutekm
                ) < $minStopIntervalKm;
            });

            if (!$tooClose) {
                $selected->push($place);
            }
        }

        return $selected
            ->sortBy([
                ['distancealongroutekm', 'asc'],
                ['distancefromroutekm', 'asc'],
                ['placename', 'asc'],
            ])
            ->values();
    }

    private function splitCsvValues(?string $value): array
    {
        if (!$value) {
            return [];
        }

        return collect(explode(',', $value))
            ->map(fn ($part) => trim($part))
            ->filter()
            ->values()
            ->all();
    }

    private function resolveLegPointLocationWithoutDestinationItem($legPoint): ?array
    {
        $resolvedPlace = null;
        $label = $legPoint->title ?: 'Leg Point';

        if ($legPoint->destination) {
            $resolvedPlace = $legPoint->destination->place;
            $label = $legPoint->title ?: $legPoint->destination->destinationname ?: $label;
        } elseif ($legPoint->place) {
            $resolvedPlace = $legPoint->place;
            $label = $legPoint->title ?: $legPoint->place->placename ?: $label;
        }

        if (!$resolvedPlace || $resolvedPlace->latitude === null || $resolvedPlace->longitude === null) {
            return null;
        }

        return [
            'label' => $label,
            'place_id' => $resolvedPlace->id,
            'lat' => (float) $resolvedPlace->latitude,
            'lng' => (float) $resolvedPlace->longitude,
        ];
    }
    public function buildRouteSegments(Collection $points): Collection
    {
        $segments = collect();
        if ($points->count() < 2) {
            return $segments;
        }

        $cumulativeKm = 0.0;

        for ($i = 0; $i < $points->count() - 1; $i++) {
            $from = $points[$i];
            $to = $points[$i + 1];
            $segmentLengthKm = $this->calculateDistanceKm($from['lat'], $from['lng'], $to['lat'], $to['lng']);

            $segments->push([
                'index' => $i,
                'from' => $from,
                'to' => $to,
                'start_km' => $cumulativeKm,
                'length_km' => $segmentLengthKm,
            ]);

            $cumulativeKm += $segmentLengthKm;
        }

        return $segments;
    }

    public function buildSegmentsBounds(Collection $segments, float $paddingDegrees = 0.30): array
    {
        $lats = [];
        $lngs = [];

        foreach ($segments as $segment) {
            $lats[] = $segment['from']['lat'];
            $lats[] = $segment['to']['lat'];
            $lngs[] = $segment['from']['lng'];
            $lngs[] = $segment['to']['lng'];
        }

        return [
            'minLat' => min($lats) - $paddingDegrees,
            'maxLat' => max($lats) + $paddingDegrees,
            'minLng' => min($lngs) - $paddingDegrees,
            'maxLng' => max($lngs) + $paddingDegrees,
        ];
    }

    private function getLegPointDestinationItemsGroupedByPlace(TripLeg $tripLeg): Collection
    {
        $tripLeg->loadMissing([
            'legPoints.destinationItem.destination.place',
            'legPoints.destinationItem.place',
        ]);

        return $tripLeg->legPoints
            ->map(function ($legPoint) {
                $item = $legPoint->destinationItem;
                if (!$item) {
                    return null;
                }

                $resolvedPlace = $item->place ?: optional($item->destination)->place;
                if (!$resolvedPlace) {
                    return null;
                }

                $item->resolved_place = $resolvedPlace;
                $item->distancefromroutekm = $item->distancefromroutekm ?? 0.0;
                $item->distancealongroutekm = $item->distancealongroutekm ?? 0.0;

                return $item;
            })
            ->filter()
            ->groupBy(fn ($item) => $item->resolved_place->id)
            ->map(fn ($items) => $items->unique('id')->values());
    }

    private function getDestinationItemsGroupedByPlaceForRoute(array $placeIds, Collection $segments, float $bufferKm): Collection
    {
        if (empty($placeIds)) {
            return collect();
        }

        return DestinationItem::query()
            ->with(['destination.place', 'place'])
            ->where('isactive', 1)
            ->where(function ($query) use ($placeIds) {
                $query->whereIn('placeid', $placeIds)
                    ->orWhereHas('destination', function ($destinationQuery) use ($placeIds) {
                        $destinationQuery->whereIn('placeid', $placeIds);
                    });
            })
            ->get()
            ->map(function ($item) use ($segments) {
                $resolvedPlace = $item->place ?: optional($item->destination)->place;
                if (!$resolvedPlace || $resolvedPlace->latitude === null || $resolvedPlace->longitude === null) {
                    return null;
                }

                $projection = $this->projectPointToRoute(
                    (float) $resolvedPlace->latitude,
                    (float) $resolvedPlace->longitude,
                    $segments
                );

                if (!$projection) {
                    return null;
                }

                $item->resolved_place = $resolvedPlace;
                $item->distancefromroutekm = round($projection['distance_km'], 1);
                $item->distancealongroutekm = round($projection['distance_along_route_km'], 1);

                return $item;
            })
            ->filter()
            ->filter(fn ($item) => $item->distancefromroutekm <= $bufferKm)
            ->groupBy(fn ($item) => $item->resolved_place->id)
            ->map(fn ($group) => $group->sortBy([
                ['distancealongroutekm', 'asc'],
                ['itemtype', 'asc'],
                ['itemname', 'asc'],
            ])->values());
    }

    public function projectPointToRoute(float $pointLat, float $pointLng, Collection $segments): ?array
    {
        if ($segments->isEmpty()) {
            return null;
        }

        $best = null;

        foreach ($segments as $segment) {
            $projection = $this->projectPointToSegment(
                $pointLat,
                $pointLng,
                $segment['from']['lat'],
                $segment['from']['lng'],
                $segment['to']['lat'],
                $segment['to']['lng']
            );

            $distanceAlongRouteKm = $segment['start_km'] + ($segment['length_km'] * $projection['progress']);
            $candidate = [
                'distance_km' => $projection['distance_km'],
                'segment_index' => $segment['index'],
                'segment_progress' => $projection['progress'],
                'distance_along_route_km' => $distanceAlongRouteKm,
            ];

            if ($best === null || $candidate['distance_km'] < $best['distance_km'] || (abs($candidate['distance_km'] - $best['distance_km']) < 0.001 && $candidate['distance_along_route_km'] < $best['distance_along_route_km'])) {
                $best = $candidate;
            }
        }

        return $best;
    }

    private function resolveTripLegEndpoint($destinationItem = null, $destination = null, $place = null, string $fallbackLabel = 'point'): ?array
    {
        $resolvedPlace = null;
        $label = $fallbackLabel;

        if ($destinationItem) {
            $resolvedPlace = $destinationItem->place ?: optional($destinationItem->destination)->place;
            $label = $destinationItem->itemname ?: $fallbackLabel;
        } elseif ($destination) {
            $resolvedPlace = $destination->place;
            $label = $destination->destinationname ?: $fallbackLabel;
        } elseif ($place) {
            $resolvedPlace = $place;
            $label = $place->placename ?: $fallbackLabel;
        }

        if (!$resolvedPlace || $resolvedPlace->latitude === null || $resolvedPlace->longitude === null) {
            return null;
        }

        return [
            'label' => $label,
            'place_id' => $resolvedPlace->id,
            'lat' => (float) $resolvedPlace->latitude,
            'lng' => (float) $resolvedPlace->longitude,
        ];
    }

    private function resolveLegPointLocation($legPoint): ?array
    {
        $resolvedPlace = null;
        $label = $legPoint->title ?: 'Leg Point';

        if ($legPoint->destinationItem) {
            $resolvedPlace = $legPoint->destinationItem->place ?: optional($legPoint->destinationItem->destination)->place;
            $label = $legPoint->title ?: $legPoint->destinationItem->itemname ?: $label;
        } elseif ($legPoint->destination) {
            $resolvedPlace = $legPoint->destination->place;
            $label = $legPoint->title ?: $legPoint->destination->destinationname ?: $label;
        } elseif ($legPoint->place) {
            $resolvedPlace = $legPoint->place;
            $label = $legPoint->title ?: $legPoint->place->placename ?: $label;
        }

        if (!$resolvedPlace || $resolvedPlace->latitude === null || $resolvedPlace->longitude === null) {
            return null;
        }

        return [
            'label' => $label,
            'place_id' => $resolvedPlace->id,
            'lat' => (float) $resolvedPlace->latitude,
            'lng' => (float) $resolvedPlace->longitude,
        ];
    }

    private function calculateDistanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadiusKm * $c;
    }

    private function projectPointToSegment(float $pointLat, float $pointLng, float $startLat, float $startLng, float $endLat, float $endLng): array
    {
        $meanLatRad = deg2rad(($startLat + $endLat + $pointLat) / 3);
        $x1 = $startLng * 111.320 * cos($meanLatRad);
        $y1 = $startLat * 110.574;
        $x2 = $endLng * 111.320 * cos($meanLatRad);
        $y2 = $endLat * 110.574;
        $xp = $pointLng * 111.320 * cos($meanLatRad);
        $yp = $pointLat * 110.574;
        $dx = $x2 - $x1;
        $dy = $y2 - $y1;

        if (abs($dx) < 0.000001 && abs($dy) < 0.000001) {
            $distance = sqrt((($xp - $x1) ** 2) + (($yp - $y1) ** 2));
            return ['distance_km' => $distance, 'progress' => 0.0];
        }

        $rawT = ((($xp - $x1) * $dx) + (($yp - $y1) * $dy)) / (($dx ** 2) + ($dy ** 2));
        $clampedT = max(0, min(1, $rawT));
        $closestX = $x1 + ($clampedT * $dx);
        $closestY = $y1 + ($clampedT * $dy);
        $distance = sqrt((($xp - $closestX) ** 2) + (($yp - $closestY) ** 2));

        return ['distance_km' => $distance, 'progress' => $clampedT];
    }
}