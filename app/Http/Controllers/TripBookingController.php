<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Destination;
use App\Models\DestinationItem;
use App\Models\Place;
use App\Models\Trip;
use App\Models\TripItem;
use App\Models\TripStay;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TripBookingController extends Controller
{
    public function index(Trip $trip, Request $request)
    {
        $bookingTypes = config('bookings.types', []);
        $bookingStatuses = config('bookings.statuses', []);
        $paymentStatuses = config('bookings.payment_statuses', []);
        $currencies = config('bookings.currencies', []);

        $stays = TripStay::where('tripid', $trip->id)
            ->orderBy('checkindate')
            ->orderBy('stayname')
            ->get();

        $tripItems = TripItem::where('tripid', $trip->id)
            ->orderBy('startdatetime')
            ->orderBy('title')
            ->get();

        $destinations = Destination::orderBy('destinationname')->get();
        $destinationItems = DestinationItem::orderBy('itemname')->get();
        $places = Place::orderBy('placename')->get();

        $query = Booking::with(['trip', 'stay', 'tripItem', 'destination', 'destinationItem', 'place'])
            ->where('tripid', $trip->id);

        if ($request->filled('booking_type')) {
            $query->where('bookingtype', $request->string('booking_type')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('payment_status')) {
            $query->where('paymentstatus', $request->string('payment_status')->toString());
        }

        if ($request->filled('trip_stay_id')) {
            $query->where('tripstayid', (int) $request->trip_stay_id);
        }

        if ($request->filled('trip_item_id')) {
            $query->where('tripitemid', (int) $request->trip_item_id);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('providername', 'like', "%{$search}%")
                    ->orWhere('providercontact', 'like', "%{$search}%")
                    ->orWhere('externalreference', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $bookings = $query
            ->orderByRaw('COALESCE(startdate, confirmedon, requestedon) asc')
            ->orderBy('id')
            ->paginate(25)
            ->withQueryString();

        return view('tripbookings.index', compact(
            'trip',
            'bookings',
            'stays',
            'tripItems',
            'destinations',
            'destinationItems',
            'places',
            'bookingTypes',
            'bookingStatuses',
            'paymentStatuses',
            'currencies',
        ));
    }

    public function store(Trip $trip, Request $request)
    {
        $data = $this->validatedData($request, $trip);

        $data['tripid'] = $trip->id;

        Booking::create($data);

        return redirect()
            ->route('trips.bookings.index', $trip)
            ->with('success', 'Booking created successfully.');
    }

    public function edit(Trip $trip, Booking $booking)
    {
        abort_unless((int) $booking->tripid === (int) $trip->id, 404);

        $bookingTypes = config('bookings.types', []);
        $bookingStatuses = config('bookings.statuses', []);
        $paymentStatuses = config('bookings.payment_statuses', []);
        $currencies = config('bookings.currencies', []);

        $stays = TripStay::where('tripid', $trip->id)
            ->orderBy('checkindate')
            ->orderBy('stayname')
            ->get();

        $tripItems = TripItem::where('tripid', $trip->id)
            ->orderBy('startdatetime')
            ->orderBy('title')
            ->get();

        $destinations = Destination::orderBy('destinationname')->get();
        $destinationItems = DestinationItem::orderBy('itemname')->get();
        $places = Place::orderBy('placename')->get();

        return view('tripbookings.edit', compact(
            'trip',
            'booking',
            'stays',
            'tripItems',
            'destinations',
            'destinationItems',
            'places',
            'bookingTypes',
            'bookingStatuses',
            'paymentStatuses',
            'currencies',
        ));
    }

    public function update(Trip $trip, Request $request, Booking $booking)
    {
        abort_unless((int) $booking->tripid === (int) $trip->id, 404);

        $data = $this->validatedData($request, $trip);

        $booking->update($data);

        return redirect()
            ->route('trips.bookings.index', $trip)
            ->with('success', 'Booking updated successfully.');
    }

    public function destroy(Trip $trip, Booking $booking)
    {
        abort_unless((int) $booking->tripid === (int) $trip->id, 404);

        $booking->delete();

        return redirect()
            ->route('trips.bookings.index', $trip)
            ->with('success', 'Booking deleted successfully.');
    }

    protected function validatedData(Request $request, Trip $trip): array
    {
        return $request->validate([
            'tripstayid' => [
                'nullable',
                'integer',
                Rule::exists('tripstays', 'id')->where(fn ($q) => $q->where('tripid', $trip->id)),
            ],
            'tripitemid' => [
                'nullable',
                'integer',
                Rule::exists('tripitems', 'id')->where(fn ($q) => $q->where('tripid', $trip->id)),
            ],
            'destinationid' => ['nullable', 'integer', 'exists:destinations,id'],
            'destinationitemid' => ['nullable', 'integer', 'exists:destinationitems,id'],
            'placeid' => ['nullable', 'integer', 'exists:places,id'],
            'bookingtype' => ['required', 'string', Rule::in(array_keys(config('bookings.types', [])))],
            'providername' => ['required', 'string', 'max:100'],
            'providercontact' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'externalreference' => ['nullable', 'string', 'max:150'],
            'status' => ['required', 'string', Rule::in(array_keys(config('bookings.statuses', [])))],
            'requestedon' => ['nullable', 'date'],
            'confirmedon' => ['nullable', 'date'],
            'startdate' => ['nullable', 'date'],
            'enddate' => ['nullable', 'date', 'after_or_equal:startdate'],
            'notes' => ['nullable', 'string'],
            'estimatedcost' => ['nullable', 'numeric', 'min:0'],
            'actualcost' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', Rule::in(array_keys(config('bookings.currencies', [])))],
            'paymentstatus' => ['nullable', 'string', Rule::in(array_keys(config('bookings.payment_statuses', [])))],
            'paymentnotes' => ['nullable', 'string'],
        ]);
    }
}