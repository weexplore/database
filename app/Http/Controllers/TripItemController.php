<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Destination;
use App\Models\DestinationItem;
use App\Models\Place;
use App\Models\Trip;
use App\Models\TripItem;
use App\Models\TripLeg;
use App\Models\TripStay;
use Illuminate\Http\Request;

class TripItemController extends Controller
{
    private function itemTypeOptions(): array
    {
        return [
            'activity' => 'Activity',
            'task' => 'Task',
            'event' => 'Event',
            'booking' => 'Booking',
            'meal' => 'Meal',
            'drive' => 'Drive',
            'shopping' => 'Shopping',
            'sightseeing' => 'Sightseeing',
            'other' => 'Other',
        ];
    }

    private function itemStatusOptions(): array
    {
        return [
            'planned' => 'Planned',
            'booked' => 'Booked',
            'inprogress' => 'In Progress',
            'done' => 'Done',
            'cancelled' => 'Cancelled',
            'skipped' => 'Skipped',
        ];
    }

    private function priorityOptions(): array
    {
        return [
            'low' => 'Low',
            'normal' => 'Normal',
            'high' => 'High',
            'urgent' => 'Urgent',
        ];
    }

    public function index(Request $request, Trip $trip)
    {
        $query = TripItem::with([
                'tripleg',
                'stay',
                'destination',
                'destinationItem',
                'place',
                'booking',
            ])
            ->where('tripid', $trip->id);

        if ($request->filled('itemtype')) {
            $query->where('itemtype', $request->string('itemtype')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('tripleg_id')) {
            $query->where('triplegid', $request->integer('tripleg_id'));
        }

        if ($request->filled('tripstay_id')) {
            $query->where('tripstayid', $request->integer('tripstay_id'));
        }

        if ($request->filled('destination_id')) {
            $query->where('destinationid', $request->integer('destination_id'));
        }

        if ($request->filled('place_id')) {
            $query->where('placeid', $request->integer('place_id'));
        }

        if ($request->filled('booking_id')) {
            $query->where('bookingid', $request->integer('booking_id'));
        }

        $items = $query
            ->orderBy('itemdate')
            ->orderBy('startdatetime')
            ->orderBy('sortorder')
            ->orderBy('id')
            ->get();

        $tripLegs = TripLeg::where('tripid', $trip->id)
            ->orderBy('legnumber')
            ->orderBy('sortorder')
            ->get();

        $tripStays = TripStay::where('tripid', $trip->id)
            ->orderBy('checkindate')
            ->orderBy('id')
            ->get();

        $destinations = Destination::orderBy('destinationname')->get();
        $destinationItems = DestinationItem::orderBy('itemname')->get();
        $places = Place::orderBy('placename')->get();

        $bookings = Booking::where('tripid', $trip->id)
            ->orderBy('id')
            ->get();

        $itemTypeOptions = $this->itemTypeOptions();
        $itemStatusOptions = $this->itemStatusOptions();
        $priorityOptions = $this->priorityOptions();
        $showCreate = $request->boolean('show_create');

        return view('trip-items.index', compact(
            'trip',
            'items',
            'tripLegs',
            'tripStays',
            'destinations',
            'destinationItems',
            'places',
            'bookings',
            'itemTypeOptions',
            'itemStatusOptions',
            'priorityOptions',
            'showCreate'
        ));
    }

    public function create(Trip $trip)
    {
        return redirect()->route('trips.items.index', [
            'trip' => $trip->id,
            'show_create' => 1,
        ]);
    }

    public function store(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'triplegid' => ['nullable', 'integer', 'exists:triplegs,id'],
            'tripstayid' => ['nullable', 'integer', 'exists:tripstays,id'],
            'destinationid' => ['nullable', 'integer', 'exists:destinations,id'],
            'destinationitemid' => ['nullable', 'integer', 'exists:destinationitems,id'],
            'placeid' => ['nullable', 'integer', 'exists:places,id'],
            'bookingid' => ['nullable', 'integer', 'exists:bookings,id'],
            'itemdate' => ['nullable', 'date'],
            'startdatetime' => ['nullable', 'date'],
            'enddatetime' => ['nullable', 'date', 'after_or_equal:startdatetime'],
            'itemtype' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'priority' => ['nullable', 'string', 'max:50'],
            'isfullday' => ['nullable', 'boolean'],
            'peoplecount' => ['nullable', 'integer', 'min:0'],
            'estimatedcostperperson' => ['nullable', 'numeric', 'min:0'],
            'estimatedtotalcost' => ['nullable', 'numeric', 'min:0'],
            'actualcost' => ['nullable', 'numeric', 'min:0'],
            'allocateasdailycost' => ['nullable', 'boolean'],
            'notesinternal' => ['nullable', 'string'],
            'sortorder' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['tripid'] = $trip->id;
        $validated['isfullday'] = $request->boolean('isfullday');
        $validated['allocateasdailycost'] = $request->boolean('allocateasdailycost');

        TripItem::create($validated);

        return redirect()
            ->route('trips.items.index', $trip)
            ->with('success', 'Trip item created successfully.');
    }

    public function edit(Trip $trip, TripItem $tripItem)
    {
        abort_unless((int) $tripItem->tripid === (int) $trip->id, 404);

        $tripItem->load([
            'tripleg',
            'stay',
            'destination',
            'destinationItem',
            'place',
            'booking',
        ]);

        $tripLegs = TripLeg::where('tripid', $trip->id)
            ->orderBy('legnumber')
            ->orderBy('sortorder')
            ->get();

        $tripStays = TripStay::where('tripid', $trip->id)
            ->orderBy('checkindate')
            ->orderBy('id')
            ->get();

        $destinations = Destination::orderBy('destinationname')->get();
        $destinationItems = DestinationItem::orderBy('itemname')->get();
        $places = Place::orderBy('placename')->get();

        $bookings = Booking::where('tripid', $trip->id)
            ->orderBy('id')
            ->get();

        $itemTypeOptions = $this->itemTypeOptions();
        $itemStatusOptions = $this->itemStatusOptions();
        $priorityOptions = $this->priorityOptions();

        return view('trip-items.edit', compact(
            'trip',
            'tripItem',
            'tripLegs',
            'tripStays',
            'destinations',
            'destinationItems',
            'places',
            'bookings',
            'itemTypeOptions',
            'itemStatusOptions',
            'priorityOptions'
        ));
    }

    public function update(Request $request, Trip $trip, TripItem $tripItem)
    {
        abort_unless((int) $tripItem->tripid === (int) $trip->id, 404);

        $validated = $request->validate([
            'triplegid' => ['nullable', 'integer', 'exists:triplegs,id'],
            'tripstayid' => ['nullable', 'integer', 'exists:tripstays,id'],
            'destinationid' => ['nullable', 'integer', 'exists:destinations,id'],
            'destinationitemid' => ['nullable', 'integer', 'exists:destinationitems,id'],
            'placeid' => ['nullable', 'integer', 'exists:places,id'],
            'bookingid' => ['nullable', 'integer', 'exists:bookings,id'],
            'itemdate' => ['nullable', 'date'],
            'startdatetime' => ['nullable', 'date'],
            'enddatetime' => ['nullable', 'date', 'after_or_equal:startdatetime'],
            'itemtype' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'priority' => ['nullable', 'string', 'max:50'],
            'isfullday' => ['nullable', 'boolean'],
            'peoplecount' => ['nullable', 'integer', 'min:0'],
            'estimatedcostperperson' => ['nullable', 'numeric', 'min:0'],
            'estimatedtotalcost' => ['nullable', 'numeric', 'min:0'],
            'actualcost' => ['nullable', 'numeric', 'min:0'],
            'allocateasdailycost' => ['nullable', 'boolean'],
            'notesinternal' => ['nullable', 'string'],
            'sortorder' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['isfullday'] = $request->boolean('isfullday');
        $validated['allocateasdailycost'] = $request->boolean('allocateasdailycost');

        $tripItem->update($validated);

        return redirect()
            ->route('trips.items.index', $trip)
            ->with('success', 'Trip item updated successfully.');
    }

    public function destroy(Trip $trip, TripItem $tripItem)
    {
        abort_unless((int) $tripItem->tripid === (int) $trip->id, 404);

        try {
            $tripItem->delete();

            return redirect()
                ->route('trips.items.index', $trip)
                ->with('success', 'Trip item deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('trips.items.index', $trip)
                ->with('error', 'This trip item could not be deleted.');
        }
    }
}