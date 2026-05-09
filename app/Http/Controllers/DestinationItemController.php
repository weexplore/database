<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\DestinationItem;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DestinationItemController extends Controller
{
    private function itemTypeOptions(): array
    {
        return DestinationItem::itemTypeOptions();
    }

    public function index(Request $request)
    {
        $destinations = Destination::orderBy('destinationname')->get();
        $places = Place::orderBy('placename')->get();
        $itemTypes = $this->itemTypeOptions();

        $query = DestinationItem::with(['destination', 'place']);

        if ($request->filled('destination_id')) {
            $query->where('destinationid', (int) $request->destination_id);
        }

        if ($request->filled('place_id')) {
            $query->where('placeid', (int) $request->place_id);
        }

        if ($request->filled('itemtype')) {
            $query->where('itemtype', $request->itemtype);
        }

        if ($request->filled('status')) {
            $query->where('isactive', (int) $request->status);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('itemname', 'like', "%{$search}%")
                    ->orWhere('shortdescription', 'like', "%{$search}%")
                    ->orWhere('itemtype', 'like', "%{$search}%");
            });
        }

        $items = $query
            ->orderBy('itemname')
            ->orderByRaw('COALESCE(sortorder, 999999)')
            ->paginate(25)
            ->withQueryString();

        $showCreate = $request->boolean('show_create');
        $selectedDestinationId = $request->integer('destination_id');

        return view('destination-items.index', compact(
            'items',
            'destinations',
            'places',
            'itemTypes',
            'showCreate',
            'selectedDestinationId'
        ));
    }

public function create(Request $request)
{
    $destinationId = $request->filled('destination_id')
        ? $request->integer('destination_id')
        : null;

    $destination = null;
    $placeId = null;

    if ($destinationId) {
        $destination = Destination::findOrFail($destinationId);
        $placeId = $destination->placeid ?: null;
    }

    $item = DestinationItem::create([
        'destinationid' => $destinationId,
        'placeid' => $placeId,
        'itemname' => 'New Destination Item',
        'itemtype' => array_key_first(DestinationItem::itemTypeOptions()),
    ]);

    $returnTo = $request->input('return_to');

    return redirect()->route('destination-items.edit', [
        'destinationItem' => $item,
        'return_to' => $returnTo,
    ])->with('success', 'Destination item created. Update the details below.');
}

    public function store(Request $request)
{
    $itemTypeKeys = array_keys($this->itemTypeOptions());

    $data = $request->validate([
        'destinationid' => ['required', 'integer', 'exists:destinations,id'],
        'placeid' => ['nullable', 'integer', 'exists:places,id'],
        'addressline1' => ['nullable', 'string', 'max:200'],
        'addressline2' => ['nullable', 'string', 'max:200'],
        'addressline3' => ['nullable', 'string', 'max:200'],
        'postcode' => ['nullable', 'string', 'max:20'],
        'telephone' => ['nullable', 'string', 'max:50'],
        'website' => ['nullable', 'url', 'max:500'],
        'latitude' => ['nullable', 'numeric', 'between:-90,90'],
        'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        'internetsearch' => ['nullable', 'string', 'max:500'],
        'itemname' => ['required', 'string', 'max:200'],
        'itemtype' => ['nullable', 'string', 'max:50', Rule::in($itemTypeKeys)],
        'shortdescription' => ['nullable', 'string'],
        'notes' => ['nullable', 'string'],
        'estimatedcostperperson' => ['nullable', 'numeric', 'min:0'],
        'estimatedtotalcost' => ['nullable', 'numeric', 'min:0'],
        'bookingrequired' => ['nullable', 'boolean'],
        'caravanaccessnotes' => ['nullable', 'string'],
        'recommendedstayminutes' => ['nullable', 'integer', 'min:0'],
        'sortorder' => ['nullable', 'integer', 'min:0'],
        'isactive' => ['nullable', 'boolean'],
    ]);

    $item = DestinationItem::create([
        'destinationid' => $data['destinationid'],
        'placeid' => $data['placeid'] ?? null,
        'addressline1' => $data['addressline1'] ?? null,
        'addressline2' => $data['addressline2'] ?? null,
        'addressline3' => $data['addressline3'] ?? null,
        'postcode' => $data['postcode'] ?? null,
        'telephone' => $data['telephone'] ?? null,
        'website' => $data['website'] ?? null,
        'latitude' => $data['latitude'] ?? null,
        'longitude' => $data['longitude'] ?? null,
        'internetsearch' => $data['internetsearch'] ?? null,
        'itemname' => $data['itemname'],
        'itemtype' => $data['itemtype'] ?? null,
        'shortdescription' => $data['shortdescription'] ?? null,
        'notes' => $data['notes'] ?? null,
        'estimatedcostperperson' => $data['estimatedcostperperson'] ?? null,
        'estimatedtotalcost' => $data['estimatedtotalcost'] ?? null,
        'bookingrequired' => !empty($data['bookingrequired']),
        'caravanaccessnotes' => $data['caravanaccessnotes'] ?? null,
        'recommendedstayminutes' => $data['recommendedstayminutes'] ?? null,
        'sortorder' => $data['sortorder'] ?? null,
        'isactive' => array_key_exists('isactive', $data) ? !empty($data['isactive']) : true,
    ]);

    $returnTo = $request->input('return_to');

    if ($returnTo) {
        return redirect($returnTo)->with('success', 'Destination item created successfully.');
    }

    return redirect()
        ->route('destination-items.edit', $item)
        ->with('success', 'Destination item created successfully.');
}


public function edit(Request $request, DestinationItem $destinationItem)
{
    $destinationItem->load([
        'destination.place',
        'place',
    ]);

    $destinations = Destination::orderBy('destinationname')->get();
    $places = Place::orderBy('placename')->get();
    $itemTypeOptions = DestinationItem::itemTypeOptions();
    $returnTo = $request->input('return_to', route('destination-items.index'));

    return view('destination-items.edit', compact(
        'destinationItem',
        'destinations',
        'places',
        'itemTypeOptions',
        'returnTo'
    ));
}

    public function update(Request $request, DestinationItem $destinationItem)
{
    $itemTypeKeys = array_keys($this->itemTypeOptions());

    $data = $request->validate([
        'destinationid' => ['required', 'integer', 'exists:destinations,id'],
        'placeid' => ['nullable', 'integer', 'exists:places,id'],
        'addressline1' => ['nullable', 'string', 'max:200'],
        'addressline2' => ['nullable', 'string', 'max:200'],
        'addressline3' => ['nullable', 'string', 'max:200'],
        'postcode' => ['nullable', 'string', 'max:20'],
        'telephone' => ['nullable', 'string', 'max:50'],
        'website' => ['nullable', 'url', 'max:500'],
        'latitude' => ['nullable', 'numeric', 'between:-90,90'],
        'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        'internetsearch' => ['nullable', 'string', 'max:500'],
        'itemname' => ['required', 'string', 'max:200'],
        'itemtype' => ['nullable', 'string', 'max:50', Rule::in($itemTypeKeys)],
        'shortdescription' => ['nullable', 'string'],
        'notes' => ['nullable', 'string'],
        'estimatedcostperperson' => ['nullable', 'numeric', 'min:0'],
        'estimatedtotalcost' => ['nullable', 'numeric', 'min:0'],
        'bookingrequired' => ['nullable', 'boolean'],
        'caravanaccessnotes' => ['nullable', 'string'],
        'recommendedstayminutes' => ['nullable', 'integer', 'min:0'],
        'sortorder' => ['nullable', 'integer', 'min:0'],
        'isactive' => ['nullable', 'boolean'],
    ]);

    $destinationItem->update([
        'destinationid' => $data['destinationid'],
        'placeid' => $data['placeid'] ?? null,
        'addressline1' => $data['addressline1'] ?? null,
        'addressline2' => $data['addressline2'] ?? null,
        'addressline3' => $data['addressline3'] ?? null,
        'postcode' => $data['postcode'] ?? null,
        'telephone' => $data['telephone'] ?? null,
        'website' => $data['website'] ?? null,
        'latitude' => $data['latitude'] ?? null,
        'longitude' => $data['longitude'] ?? null,
        'internetsearch' => $data['internetsearch'] ?? null,
        'itemname' => $data['itemname'],
        'itemtype' => $data['itemtype'] ?? null,
        'shortdescription' => $data['shortdescription'] ?? null,
        'notes' => $data['notes'] ?? null,
        'estimatedcostperperson' => $data['estimatedcostperperson'] ?? null,
        'estimatedtotalcost' => $data['estimatedtotalcost'] ?? null,
        'bookingrequired' => !empty($data['bookingrequired']),
        'caravanaccessnotes' => $data['caravanaccessnotes'] ?? null,
        'recommendedstayminutes' => $data['recommendedstayminutes'] ?? null,
        'sortorder' => $data['sortorder'] ?? null,
        'isactive' => !empty($data['isactive']),
    ]);

    $returnTo = $request->input('return_to');

    if ($returnTo) {
        return redirect($returnTo)->with('success', 'Destination item updated successfully.');
    }

    return redirect()
        ->route('destination-items.edit', $destinationItem)
        ->with('success', 'Destination item updated successfully.');
}

    public function destroy(Request $request, DestinationItem $destinationItem)
    {
        $returnTo = $request->input('return_to');

        try {
            $destinationItem->delete();

            if ($returnTo) {
                return redirect($returnTo)->with('success', 'Destination item deleted successfully.');
            }

            return redirect()
                ->route('destination-items.index')
                ->with('success', 'Destination item deleted successfully.');
        } catch (\Throwable $e) {
            if ($returnTo) {
                return redirect($returnTo)->with('error', 'This destination item could not be deleted.');
            }

            return redirect()
                ->route('destination-items.index')
                ->with('error', 'This destination item could not be deleted.');
        }
    }
}