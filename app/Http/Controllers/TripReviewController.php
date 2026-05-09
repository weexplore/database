<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\DestinationItem;
use App\Models\Place;
use App\Models\Review;
use App\Models\Traveller;
use App\Models\Trip;
use App\Models\TripItem;
use App\Models\TripLeg;
use App\Models\TripStay;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TripReviewController extends Controller
{
    public function index(Trip $trip, Request $request)
    {
        $travellers = Traveller::orderBy('lastname')->orderBy('firstname')->get();
        $stays = TripStay::where('tripid', $trip->id)
            ->orderBy('checkindate')
            ->orderBy('stayname')
            ->get();
        $tripItems = TripItem::where('tripid', $trip->id)
            ->orderBy('startdatetime')
            ->orderBy('title')
            ->get();
        $tripLegs = TripLeg::with(['fromPlace', 'toPlace'])
            ->where('tripid', $trip->id)
            ->orderBy('legnumber')
            ->orderBy('startdate')
            ->get();
        $destinations = Destination::orderBy('destinationname')->get();
        $destinationItems = DestinationItem::orderBy('itemname')->get();
        $places = Place::orderBy('placename')->get();

        $query = Review::with([
                'trip',
                'traveller',
                'stay',
                'tripItem',
                'destination',
                'destinationItem',
                'place',
            ])
            ->where('tripid', $trip->id);

        if ($request->filled('traveller_id')) {
            $query->where('travellerid', (int) $request->traveller_id);
        }

        if ($request->filled('rating_min')) {
            $query->where('ratingoverall', '>=', (int) $request->rating_min);
        }

        if ($request->boolean('only_public')) {
            $query->where('isprivate', false);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('comments', 'like', "%{$search}%");
            });
        }

        $reviews = $query
            ->orderBy('reviewdate', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(25)
            ->withQueryString();

        return view('tripreviews.index', compact(
            'trip',
            'reviews',
            'travellers',
            'stays',
            'tripItems',
            'tripLegs',
            'destinations',
            'destinationItems',
            'places',
        ));
    }

    public function store(Trip $trip, Request $request)
    {
        $data = $this->validatedData($request, $trip);

        $data['tripid'] = $trip->id;

        Review::create($data);

        return redirect()
            ->route('trips.reviews.index', $trip)
            ->with('success', 'Review added.');
    }

    public function edit(Trip $trip, Review $review)
    {
        abort_unless((int) $review->tripid === (int) $trip->id, 404);

        $travellers = Traveller::orderBy('lastname')->orderBy('firstname')->get();
        $stays = TripStay::where('tripid', $trip->id)
            ->orderBy('checkindate')
            ->orderBy('stayname')
            ->get();
        $tripItems = TripItem::where('tripid', $trip->id)
            ->orderBy('startdate')
            ->orderBy('title')
            ->get();
        $tripLegs = TripLeg::with(['fromPlace', 'toPlace'])
            ->where('tripid', $trip->id)
            ->orderBy('legnumber')
            ->orderBy('startdate')
            ->get();
        $destinations = Destination::orderBy('destinationname')->get();
        $destinationItems = DestinationItem::orderBy('itemname')->get();
        $places = Place::orderBy('placename')->get();

        return view('tripreviews.edit', compact(
            'trip',
            'review',
            'travellers',
            'stays',
            'tripItems',
            'tripLegs',
            'destinations',
            'destinationItems',
            'places',
        ));
    }

    public function update(Trip $trip, Request $request, Review $review)
    {
        abort_unless((int) $review->tripid === (int) $trip->id, 404);

        $data = $this->validatedData($request, $trip);

        $review->update($data);

        return redirect()
            ->route('trips.reviews.index', $trip)
            ->with('success', 'Review updated.');
    }

    public function destroy(Trip $trip, Review $review)
    {
        abort_unless((int) $review->tripid === (int) $trip->id, 404);

        $review->delete();

        return redirect()
            ->route('trips.reviews.index', $trip)
            ->with('success', 'Review deleted.');
    }

    protected function validatedData(Request $request, Trip $trip): array
    {
        return $request->validate([
            'travellerid' => [
                'nullable',
                'integer',
                'exists:travellers,id',
            ],
            'tripstayid' => [
                'nullable',
                'integer',
                Rule::exists('tripstays', 'id')->where(fn($q) => $q->where('tripid', $trip->id)),
            ],
            'tripitemid' => [
                'nullable',
                'integer',
                Rule::exists('tripitems', 'id')->where(fn($q) => $q->where('tripid', $trip->id)),
            ],
            'destinationid' => ['nullable', 'integer', 'exists:destinations,id'],
            'destinationitemid' => ['nullable', 'integer', 'exists:destinationitems,id'],
            'placeid' => ['nullable', 'integer', 'exists:places,id'],
            'reviewdate' => ['nullable', 'date'],
            'ratingoverall' => ['nullable', 'integer', 'between:1,10'],
            'ratingvalue' => ['nullable', 'integer', 'between:1,10'],
            'ratingfacility' => ['nullable', 'integer', 'between:1,10'],
            'ratingaccess' => ['nullable', 'integer', 'between:1,10'],
            'ratingambience' => ['nullable', 'integer', 'between:1,10'],
            'title' => ['nullable', 'string', 'max:150'],
            'comments' => ['nullable', 'string'],
            'returninterestlevel' => ['nullable', 'integer', 'between:1,5'],
            'wouldreturn' => ['nullable', 'boolean'],
            'isprivate' => ['nullable', 'boolean'],
        ]);
    }
}