<?php

namespace App\Http\Controllers;

use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlaceNearbyController extends Controller
{
    public function index(Request $request, Place $place): View
{
    $radiusKm = (int) $request->input('radius_km', 50);

    $radiusOptions = [25, 50, 100, 150, 200];

    if (!in_array($radiusKm, $radiusOptions, true)) {
        $radiusKm = 50;
    }

    abort_if(is_null($place->latitude) || is_null($place->longitude), 404, 'This place does not have coordinates.');

    $nearbyPlaces = Place::query()
        ->nearbyToPlace($place, $radiusKm)
        ->limit(200)
        ->get();

       return view('places.nearby', [
        'place' => $place,
        'radiusKm' => $radiusKm,
        'radiusOptions' => $radiusOptions,
        'nearbyPlaces' => $nearbyPlaces,
        'returnTo' => $request->input('returnto', route('places.edit', $place)),
    ]);
}
}