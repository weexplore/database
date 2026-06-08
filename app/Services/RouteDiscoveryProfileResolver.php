<?php

namespace App\Services;

use App\Models\Trip;
use App\Models\TripLeg;
use App\Models\TripLegSearchProfile;

class RouteDiscoveryProfileResolver
{
    public function forTripLeg(TripLeg $tripLeg): ?TripLegSearchProfile
    {
        $tripLeg->loadMissing([
            'searchProfile',
            'trip.selectedSearchProfile',
            'trip.searchProfiles',
        ]);

        if ($tripLeg->searchProfile?->isactive) {
            return $tripLeg->searchProfile;
        }

        if ($tripLeg->trip?->selectedSearchProfile?->isactive) {
            return $tripLeg->trip->selectedSearchProfile;
        }

        if ($tripLeg->trip) {
            return $tripLeg->trip->searchProfiles
                ->where('isactive', true)
                ->sortBy(fn ($profile) => [($profile->isdefault ? 0 : 1), $profile->id])
                ->first();
        }

        return null;
    }

    public function forTrip(Trip $trip): ?TripLegSearchProfile
    {
        $trip->loadMissing([
            'selectedSearchProfile',
            'searchProfiles',
        ]);

        if ($trip->selectedSearchProfile?->isactive) {
            return $trip->selectedSearchProfile;
        }

        return $trip->searchProfiles
            ->where('isactive', true)
            ->sortBy(fn ($profile) => [($profile->isdefault ? 0 : 1), $profile->id])
            ->first();
    }
}