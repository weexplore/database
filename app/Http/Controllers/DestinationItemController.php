<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\DestinationItem;
use App\Models\DestinationItemType;
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

    $itemTypes = DestinationItemType::query()
        ->where('isactive', 1)
->orderBy('typename')
        ->orderBy('sortorder')
        ->get();

    $query = DestinationItem::with([
        'destination',
        'place',
        'itemTypes',
    ]);

    if ($request->filled('destination_id')) {
        $query->where('destinationid', (int) $request->destination_id);
    }

    if ($request->filled('place_id')) {
        $query->where('placeid', (int) $request->place_id);
    }

    if ($request->filled('itemtype_id')) {
        $itemTypeId = (int) $request->itemtype_id;

        $query->whereHas('itemTypes', function ($q) use ($itemTypeId) {
            $q->where('destination_item_types.id', $itemTypeId);
        });
    }

    if ($request->filled('status')) {
        $query->where('isactive', (int) $request->status);
    }

    if ($request->filled('search')) {
        $search = trim((string) $request->search);

        $query->where(function ($q) use ($search) {
            $q->where('itemname', 'like', "%{$search}%")
                ->orWhere('shortdescription', 'like', "%{$search}%")
                ->orWhereHas('itemTypes', function ($typeQuery) use ($search) {
                    $typeQuery->where('destination_item_types.typename', 'like', "%{$search}%");
                });
        });
    }

    $items = $query
        ->orderBy('itemname')    
        ->orderByRaw('COALESCE(sortorder, 999999)')
        ->paginate(25)
        ->withQueryString();

    $showCreate = $request->boolean('show_create', false);
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
    $destinations = Destination::orderBy('destinationname')->get();
    $places = Place::orderBy('placename')->get();

    $itemTypes = DestinationItemType::query()
        ->where('isactive', 1)
        ->orderBy('typename')
        ->orderBy('sortorder')
        ->get();

    $destinationItem = new DestinationItem();

    $returnTo = $request->input('return_to', route('destinations.index'));

    return view('destination-items.create', compact(
        'destinationItem',
        'destinations',
        'places',
        'itemTypes',
        'returnTo'
    ));
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
        'itemtype_ids' => ['nullable', 'array'],
        'itemtype_ids.*' => ['integer', 'exists:destination_item_types,id'],
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

    // Sync item types AFTER the item exists
    $item->itemTypes()->sync($data['itemtype_ids'] ?? []);

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
    $destinationItem->load('itemTypes');

    $destinations = Destination::orderBy('destinationname')->get();
    $places = Place::orderBy('placename')->get();

    $itemTypes = DestinationItemType::query()
        ->where('isactive', 1)
        ->orderBy('typename')
        ->orderBy('sortorder')
        ->get();

    $returnTo = $request->input('return_to', route('destinations.edit', $destinationItem->destinationid));

    return view('destination-items.edit', compact(
        'destinationItem',
        'destinations',
        'places',
        'itemTypes',
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
        'itemtype_ids' => ['nullable', 'array'],
        'itemtype_ids.*' => ['integer', 'exists:destination_item_types,id'],
        'shortdescription' => ['nullable', 'string'],
        'notes' => ['nullable', 'string'],
        'estimatedcostperperson' => ['nullable', 'numeric', 'min:0'],
        'estimatedtotalcost' => ['nullable', 'numeric', 'min:0'],
        'bookingrequired' => ['nullable', 'boolean'],
        'caravanaccessnotes' => ['nullable', 'string'],
        'disabilityaccessnotes' => ['nullable', 'string'],
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
    'shortdescription' => $data['shortdescription'] ?? null,
    'notes' => $data['notes'] ?? null,
    'estimatedcostperperson' => $data['estimatedcostperperson'] ?? null,
    'estimatedtotalcost' => $data['estimatedtotalcost'] ?? null,
    'bookingrequired' => !empty($data['bookingrequired']),
    'caravanaccessnotes' => $data['caravanaccessnotes'] ?? null,
    'disabilityaccessnotes' => $data['disabilityaccessnotes'] ?? null,
    'recommendedstayminutes' => $data['recommendedstayminutes'] ?? null,
    'sortorder' => $data['sortorder'] ?? null,
    'isactive' => !empty($data['isactive']),
]);

$destinationItem->itemTypes()->sync($data['itemtype_ids'] ?? []);

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

    public function createFromDestination(Request $request, Destination $destination)
{
    $returnTo = $request->input('return_to');

    $item = DestinationItem::create([
        'destinationid' => $destination->id,
        'placeid' => $destination->placeid,
        'itemname' => 'New Destination Item',
        'isactive' => true,
    ]);

    return redirect()
        ->route('destination-items.edit', [
            'destinationItem' => $item,
            'return_to' => $returnTo,
        ])
        ->with('success', 'Destination item created. You can now complete the details.');
}
}