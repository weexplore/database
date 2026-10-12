<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\DestinationItem;
use App\Models\DestinationItemType;
use App\Models\DestinationSource;
use App\Models\DestinationSourceItemCandidate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DestinationItemCandidateController extends Controller
{
    public function index(Destination $destination): JsonResponse
    {
        $sources = DestinationSource::query()->where('destinationid', $destination->id)
            ->orderBy('sourcetitle')->orderBy('id')->get();
        $candidates = DestinationSourceItemCandidate::query()
            ->whereHas('source', fn ($query) => $query->where('destinationid', $destination->id))
            ->with(['source', 'destinationItem'])
            ->orderBy('sortorder')->orderBy('id')->get();
        $types = DestinationItemType::query()->where('isactive', true)
            ->orderBy('typename')->orderBy('id')->get();
        $items = DestinationItem::query()->where('destinationid', $destination->id)
            ->orderBy('itemname')->get(['id', 'itemname']);
        return response()->json([
            'sources' => $sources->map(fn ($source) => [
                'id' => $source->id, 'title' => $source->sourcetitle ?: 'Source',
                'website' => $source->sourceurl, 'status' => $source->importstatus,
            ])->values(),
            'types' => $types->map(fn ($type) => [
                'id' => $type->id, 'slug' => $type->slug, 'label' => $type->typename,
            ])->values(),
            'items' => $items,
            'candidates' => $candidates->map(fn ($candidate) => $this->payload($candidate))->values(),
        ]);
    }

    public function store(Request $request, Destination $destination): JsonResponse
    {
        $sourceId = $request->validate([
            'destinationsourceid' => ['required', 'integer', 'exists:destinationsources,id'],
        ])['destinationsourceid'];
        $data = $this->validatedData($request);
        $candidate = DB::transaction(function () use ($destination, $sourceId, $data) {
            $source = DestinationSource::query()->lockForUpdate()->findOrFail($sourceId);
            $this->ensureSource($source, $destination);
            $this->ensureUsableSource($source);
            return $source->itemCandidates()->create([
                ...$data, 'candidatestatus' => 'pendingreview',
                'destinationitemid' => null, 'appliedon' => null,
            ]);
        }, 3);
        return response()->json([
            'message' => 'Item candidate saved for review.',
            'candidate' => $this->payload($candidate->fresh(['source', 'destinationItem'])),
        ], 201);
    }

    public function update(
        Request $request, Destination $destination,
        DestinationSourceItemCandidate $candidate
    ): JsonResponse {
        $data = $this->validatedData($request);
        $updated = $this->mutate($destination, $candidate, function ($locked, $source) use ($data) {
            $this->ensureUnresolved($locked);
            $locked->update([
                ...$data, 'candidatestatus' => 'pendingreview', 'appliedon' => null,
            ]);
        });
        return $this->candidateResponse('Candidate updated and returned to pending review.', $updated);
    }

    public function approve(Destination $destination, DestinationSourceItemCandidate $candidate): JsonResponse
    {
        $updated = $this->mutate($destination, $candidate, function ($locked, $source) {
            $this->ensureUnresolved($locked);
            $this->ensureUsableSource($source);
            if (trim((string) $locked->applicable_description) === '') {
                throw ValidationException::withMessages([
                    'shortdescription' => ['Add a description before approving this candidate.'],
                ]);
            }
            $locked->update(['candidatestatus' => 'approved']);
        });
        return $this->candidateResponse('Item candidate approved.', $updated);
    }

    public function reject(Destination $destination, DestinationSourceItemCandidate $candidate): JsonResponse
    {
        $updated = $this->mutate($destination, $candidate, function ($locked, $source) {
            $this->ensureUnresolved($locked);
            $locked->update(['candidatestatus' => 'rejected']);
        });
        return $this->candidateResponse('Item candidate rejected.', $updated);
    }

    public function destroy(Destination $destination, DestinationSourceItemCandidate $candidate)
    {
        $this->mutate($destination, $candidate, function ($locked, $source) {
            $this->ensureUnresolved($locked);
            $locked->delete();
        }, false);
        return response()->noContent();
    }

    public function createItem(
        Request $request, Destination $destination,
        DestinationSourceItemCandidate $candidate
    ): JsonResponse {
        $data = $request->validate([
            'itemtype_ids' => ['present', 'array', 'max:30'],
            'itemtype_ids.*' => [
                'integer', 'distinct',
                Rule::exists('destination_item_types', 'id')->where('isactive', true),
            ],
            'expected_updatedat' => ['required', 'date_format:Y-m-d H:i:s'],
            'confirmed' => ['required', 'accepted'],
        ]);
        $updated = $this->mutate($destination, $candidate, function ($locked, $source) use ($destination, $data) {
            $this->ensureApproved($locked, $source, $data['expected_updatedat']);
            // Serialise candidate creation for this Destination before checking names.
            $lockedDestination = Destination::query()
                ->lockForUpdate()
                ->findOrFail($destination->id);
            $exists = DestinationItem::query()->where('destinationid', $destination->id)
                ->whereRaw('LOWER(TRIM(itemname)) = ?', [mb_strtolower(trim($locked->itemname))])
                ->exists();
            if ($exists) {
                throw ValidationException::withMessages([
                    'itemname' => ['An item with this name already exists in this Destination. Use Link Existing Item, or edit the candidate name if it is genuinely a different item.'],
                ]);
            }
            $item = DestinationItem::create([
                'destinationid' => $lockedDestination->id,
                'placeid' => $lockedDestination->placeid,
                'itemname' => trim($locked->itemname),
                'shortdescription' => $locked->applicable_description,
                'notes' => $locked->reviewnotes,
                'addressline1' => $locked->addressline1,
                'addressline2' => $locked->addressline2,
                'addressline3' => $locked->addressline3,
                'postcode' => $locked->postcode,
                'telephone' => $locked->telephone,
                'website' => $locked->website,
                'latitude' => $locked->latitude,
                'longitude' => $locked->longitude,
                'bookingrequired' => (bool) $locked->bookingrequired,
                'caravanaccessnotes' => $locked->caravanaccessnotes,
                'disabilityaccessnotes' => $locked->disabilityaccessnotes,
                'recommendedstayminutes' => $locked->recommendedstayminutes,
                'sortorder' => $locked->sortorder,
                'isactive' => true,
                'hasvisited' => false,
                'stillwanttovisit' => false,
                'visitinterestlevel' => null,
            ]);
            $item->itemTypes()->sync($data['itemtype_ids']);
            $locked->update([
                'destinationitemid' => $item->id,
                'candidatestatus' => 'created',
                'appliedon' => now(),
            ]);
        });
        return $this->resolvedResponse('Destination Item created.', $destination, $updated);
    }

    public function linkItem(
        Request $request, Destination $destination,
        DestinationSourceItemCandidate $candidate
    ): JsonResponse {
        $data = $request->validate([
            'destinationitemid' => ['required', 'integer', 'exists:destinationitems,id'],
            'expected_updatedat' => ['required', 'date_format:Y-m-d H:i:s'],
            'confirmed' => ['required', 'accepted'],
        ]);
        $updated = $this->mutate($destination, $candidate, function ($locked, $source) use ($destination, $data) {
            $this->ensureApproved($locked, $source, $data['expected_updatedat']);
            $item = DestinationItem::query()->lockForUpdate()->findOrFail($data['destinationitemid']);
            if ((int) $item->destinationid !== (int) $destination->id) {
                throw ValidationException::withMessages([
                    'destinationitemid' => ['Choose an item belonging to this Destination.'],
                ]);
            }
            // Linking does not change the existing Destination Item.
            $locked->update([
                'destinationitemid' => $item->id,
                'candidatestatus' => 'linked',
                'appliedon' => now(),
            ]);
        });
        return $this->resolvedResponse('Candidate linked. The existing item was not changed.', $destination, $updated);
    }

    private function mutate(Destination $destination, DestinationSourceItemCandidate $candidate, callable $callback, bool $refresh = true)
    {
        return DB::transaction(function () use ($destination, $candidate, $callback, $refresh) {
            $source = DestinationSource::query()->lockForUpdate()
                ->findOrFail($candidate->destinationsourceid);
            $locked = DestinationSourceItemCandidate::query()->lockForUpdate()->findOrFail($candidate->id);
            abort_unless((int) $locked->destinationsourceid === (int) $source->id, 404);
            $this->ensureSource($source, $destination);
            $callback($locked, $source);
            return $refresh ? $locked->fresh(['source', 'destinationItem']) : null;
        }, 3);
    }

    private function ensureSource(DestinationSource $source, Destination $destination): void
    {
        abort_unless((int) $source->destinationid === (int) $destination->id, 404);
    }

    private function ensureUsableSource(DestinationSource $source): void
    {
        if (in_array($source->importstatus, ['rejected', 'archived'], true)) {
            throw ValidationException::withMessages([
                'source' => ['This source is rejected or archived. It cannot be used to create or approve item candidates.'],
            ]);
        }
    }

    private function ensureUnresolved(DestinationSourceItemCandidate $candidate): void
    {
        if (in_array($candidate->candidatestatus, ['created', 'linked'], true)) {
            throw ValidationException::withMessages([
                'candidate' => ['A created or linked candidate is read-only and cannot be deleted.'],
            ]);
        }
    }

    private function ensureApproved(DestinationSourceItemCandidate $candidate, DestinationSource $source, string $expected): void
    {
        $this->ensureUnresolved($candidate);
        $this->ensureUsableSource($source);
        if ($candidate->candidatestatus !== 'approved') {
            throw ValidationException::withMessages(['candidate' => ['Approve this candidate first.']]);
        }
        if ($candidate->updatedat?->format('Y-m-d H:i:s') !== $expected) {
            throw ValidationException::withMessages(['candidate' => ['The candidate changed. Refresh candidates before creating or linking an item.']]);
        }
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'itemname' => ['required', 'string', 'max:200'],
            'itemtype' => ['nullable', 'string', 'max:50', 'exists:destination_item_types,slug'],
            'shortdescription' => ['nullable', 'string', 'max:16000'],
            'editeddescription' => ['nullable', 'string', 'max:16000'],
            'addressline1' => ['nullable', 'string', 'max:200'],
            'addressline2' => ['nullable', 'string', 'max:200'],
            'addressline3' => ['nullable', 'string', 'max:200'],
            'postcode' => ['nullable', 'string', 'max:20'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url:http,https', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'bookingrequired' => ['required', 'boolean'],
            'caravanaccessnotes' => ['nullable', 'string', 'max:16000'],
            'disabilityaccessnotes' => ['nullable', 'string', 'max:16000'],
            'recommendedstayminutes' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'confidence' => ['nullable', 'integer', 'min:1', 'max:5'],
            'reviewnotes' => ['nullable', 'string', 'max:16000'],
            'sortorder' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
        ]);
        foreach (['itemname', 'itemtype', 'shortdescription', 'editeddescription', 'addressline1', 'addressline2',
    'addressline3','postcode', 'telephone', 'website', 'caravanaccessnotes', 'disabilityaccessnotes', 'reviewnotes'] as $field) {
            $value = trim((string) ($data[$field] ?? ''));
            $data[$field] = $value === '' ? null : $value;
        }
        if ($data['itemname'] === null) {
            throw ValidationException::withMessages(['itemname' => ['An item name is required.']]);
        }
        $data['sortorder'] = $data['sortorder'] ?? 0;
        return $data;
    }

    private function payload(DestinationSourceItemCandidate $candidate): array
    {
        $data = $candidate->only([
            'id', 'destinationsourceid', 'itemname', 'itemtype', 'shortdescription',
            'editeddescription', 'addressline1', 'addressline2','addressline3','postcode', 'telephone', 'website',
            'latitude', 'longitude', 'bookingrequired', 'caravanaccessnotes',
            'disabilityaccessnotes', 'recommendedstayminutes', 'confidence',
            'reviewnotes', 'sortorder', 'candidatestatus', 'destinationitemid',
        ]);
        $data['source_title'] = $candidate->source?->sourcetitle ?: 'Source';
        $data['status_label'] = DestinationSourceItemCandidate::statusOptions()[$candidate->candidatestatus] ?? $candidate->candidatestatus;
        $data['applicable_description'] = $candidate->applicable_description;
        $data['updatedat'] = $candidate->updatedat?->format('Y-m-d H:i:s');
        $data['appliedon_display'] = $candidate->appliedon?->format('d M Y H:i');
        $data['item_name'] = $candidate->destinationItem?->itemname;
        $data['item_edit_url'] = $candidate->destinationitemid && $candidate->destinationItem
            ? route('destination-items.edit', [
                'destinationItem' => $candidate->destinationitemid,
                'return_to' => route('destinations.edit', [
                    'destination' => $candidate->destinationItem->destinationid,
                    'tab' => 'destination-items',
                ]),
            ]) : null;
        return $data;
    }

    private function candidateResponse(string $message, DestinationSourceItemCandidate $candidate): JsonResponse
    {
        return response()->json(['message' => $message, 'candidate' => $this->payload($candidate)]);
    }

    private function resolvedResponse(string $message, Destination $destination, DestinationSourceItemCandidate $candidate): JsonResponse
    {
        $destination->load(['items' => fn ($query) => $query->with('itemTypes')->orderBy('itemname')]);
        $returnUrl = route('destinations.edit', ['destination' => $destination, 'tab' => 'destination-items']);
        return response()->json([
            'message' => $message,
            'candidate' => $this->payload($candidate),
            'item_count' => $destination->items->count(),
            'items' => $destination->items->map(fn ($item) => ['id' => $item->id, 'itemname' => $item->itemname])->values(),
            'items_html' => view('destinations.partials.tabs.destination-items', [
                'destination' => $destination,
                'tabReturnUrls' => ['destination-items' => $returnUrl],
            ])->render(),
        ]);
    }
}
