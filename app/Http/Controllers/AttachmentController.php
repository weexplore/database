<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Trip;
use App\Models\Destination;
use App\Models\DestinationItem;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;

class AttachmentController extends Controller
{
    private function linkedTypeOptions(): array
    {
        return [
            'destination' => 'Destination',
            'destination_item' => 'Destination Item',
            'booking' => 'Booking',
            'review' => 'Review',
        ];
    }

    private function attachmentTypeOptions(): array
    {
        return [
            'document' => 'Document',
            'image' => 'Image',
            'invoice' => 'Invoice',
            'receipt' => 'Receipt',
            'booking_confirmation' => 'Booking Confirmation',
            'map' => 'Map',
            'other' => 'Other',
        ];
    }

    public function index(Request $request)
{
    $returnTo = $request->input('return_to', url()->previous());

    $linkedTypeOptions = $this->linkedTypeOptions();
    $attachmentTypeOptions = $this->attachmentTypeOptions();

    $query = Attachment::query()->with('trip');

    if ($request->filled('trip_id')) {
        $query->where('tripid', (int) $request->trip_id);
    }

    if ($request->filled('linkedtype')) {
        $query->where('linkedtype', $request->linkedtype);
    }

    if ($request->filled('linkedid')) {
        $query->where('linkedid', (int) $request->linkedid);
    }

    if ($request->filled('attachmenttype')) {
        $query->where('attachmenttype', $request->attachmenttype);
    }

    if ($request->filled('search')) {
        $search = trim((string) $request->search);

        $query->where(function ($q) use ($search) {
            $q->where('originalfilename', 'like', "%{$search}%")
                ->orWhere('filename', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('mimetype', 'like', "%{$search}%");
        });
    }

    $attachments = $query
        ->orderByDesc('isprimary')
        ->orderByDesc('uploadedat')
        ->orderByDesc('id')
        ->paginate(25)
        ->withQueryString();

    $attachments->getCollection()->transform(function ($attachment) {
        $record = $this->resolveLinkedRecord($attachment->linkedtype, $attachment->linkedid);

        $attachment->linked_to_label = $this->linkedRecordLabel($attachment->linkedtype, $record)
            ?: (($attachment->linkedtype ?: 'Record') . ' #' . $attachment->linkedid);

        return $attachment;
    });

    $showCreate = $request->boolean('show_create', false);
    $selectedTripId = $request->integer('trip_id') ?: null;

    $linkedContext = $this->buildLinkedContext(
        $request->input('linkedtype'),
        $request->input('linkedid')
    );

    return view('attachments.index', array_merge([
        'attachments' => $attachments,
        'linkedTypeOptions' => $linkedTypeOptions,
        'attachmentTypeOptions' => $attachmentTypeOptions,
        'showCreate' => $showCreate,
        'selectedTripId' => $selectedTripId,
        'returnTo' => $returnTo,
    ], $linkedContext));
}

    public function store(Request $request)
    {
        $linkedTypeOptions = array_keys($this->linkedTypeOptions());
        $attachmentTypeOptions = array_keys($this->attachmentTypeOptions());

        $request->merge([
            'tripid' => filled($request->tripid) && (int) $request->tripid > 0 ? (int) $request->tripid : null,
            'linkedid' => filled($request->linkedid) ? (int) $request->linkedid : null,
        ]);

        $data = $request->validate([
            'tripid' => ['nullable', 'integer', 'exists:trips,id'], 
            'linkedtype' => ['required', Rule::in($linkedTypeOptions)],
            'linkedid' => ['required', 'integer', 'min:1'],
            'attachmenttype' => ['nullable', Rule::in($attachmentTypeOptions)],
            'description' => ['nullable', 'string'],
            'uploadedby' => ['nullable', 'string', 'max:100'],
            'isprimary' => ['nullable', 'boolean'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $file = $request->file('file');
        $storedPath = $file->store('attachments');

        if (!empty($data['isprimary'])) {
            Attachment::query()
                ->where('linkedtype', $data['linkedtype'])
                ->where('linkedid', $data['linkedid'])
                ->update(['isprimary' => false]);
        }

        $attachment = Attachment::create([
            'tripid' => $data['tripid'] ?? null,
            'linkedtype' => $data['linkedtype'],
            'linkedid' => $data['linkedid'],
            'attachmenttype' => $data['attachmenttype'] ?? 'document',
            'filename' => $storedPath,
            'originalfilename' => $file->getClientOriginalName(),
            'mimetype' => $file->getClientMimeType(),
            'filesizebytes' => $file->getSize(),
            'description' => $data['description'] ?? null,
            'uploadedat' => now(),
            'uploadedby' => $data['uploadedby'] ?? (auth()->user()->name ?? null),
            'isprimary' => !empty($data['isprimary']),
        ]);

        $returnTo = $request->input('return_to');

        if ($returnTo) {
            return redirect($returnTo)->with('success', 'Attachment uploaded successfully.');
        }

        return redirect()
            ->route('attachments.edit', $attachment)
            ->with('success', 'Attachment uploaded successfully.');
    }

    public function edit(Request $request, Attachment $attachment)
{
    $linkedTypeOptions = $this->linkedTypeOptions();
    $attachmentTypeOptions = $this->attachmentTypeOptions();

    $returnTo = $request->input('return_to', route('attachments.index'));

    $linkedRecord = $this->resolveLinkedRecord($attachment->linkedtype, $attachment->linkedid);
    $selectedLinkedLabel = $this->linkedRecordLabel($attachment->linkedtype, $linkedRecord);

$selectedLinkedTypeLabel = $linkedTypeOptions[$attachment->linkedtype] ?? str_replace('_', ' ', $attachment->linkedtype);

$selectedLinkedDisplay = $selectedLinkedTypeLabel;

    $linkedAttachments = Attachment::query()
        ->where('linkedtype', $attachment->linkedtype)
        ->where('linkedid', $attachment->linkedid)
        ->orderByDesc('isprimary')
        ->orderByDesc('uploadedat')
        ->orderByDesc('id')
        ->get();

    return view('attachments.edit', compact(
        'attachment',
        'linkedTypeOptions',
        'attachmentTypeOptions',
        'returnTo',
        'selectedLinkedLabel',
        'selectedLinkedDisplay',
        'linkedAttachments'
    ));
}

    public function update(Request $request, Attachment $attachment)
    {
        $linkedTypeOptions = array_keys($this->linkedTypeOptions());
        $attachmentTypeOptions = array_keys($this->attachmentTypeOptions());

        $request->merge([
            'tripid' => filled($request->tripid) && (int) $request->tripid > 0 ? (int) $request->tripid : null,
            'linkedid' => filled($request->linkedid) ? (int) $request->linkedid : null,
        ]);

        $data = $request->validate([
            'tripid' => ['nullable', 'integer', 'exists:trips,id'],
            'linkedtype' => ['required', Rule::in($linkedTypeOptions)],
            'linkedid' => ['required', 'integer', 'min:1'],
            'attachmenttype' => ['nullable', Rule::in($attachmentTypeOptions)],
            'description' => ['nullable', 'string'],
            'uploadedby' => ['nullable', 'string', 'max:100'],
            'isprimary' => ['nullable', 'boolean'],
        ]);

        if (!empty($data['isprimary'])) {
            Attachment::query()
                ->where('linkedtype', $data['linkedtype'])
                ->where('linkedid', $data['linkedid'])
                ->where('id', '!=', $attachment->id)
                ->update(['isprimary' => false]);
        }

        $attachment->update([
            'tripid' => $data['tripid'] ?? null,
            'linkedtype' => $data['linkedtype'],
            'linkedid' => $data['linkedid'],
            'attachmenttype' => $data['attachmenttype'] ?? $attachment->attachmenttype,
            'description' => $data['description'] ?? null,
            'uploadedby' => $data['uploadedby'] ?? null,
            'isprimary' => !empty($data['isprimary']),
        ]);

        $returnTo = $request->input('return_to');

        if ($returnTo) {
            return redirect($returnTo)->with('success', 'Attachment updated successfully.');
        }

        return redirect()
            ->route('attachments.edit', $attachment)
            ->with('success', 'Attachment updated successfully.');
    }

    public function destroy(Request $request, Attachment $attachment)
    {
        $returnTo = $request->input('return_to');

        try {
            if ($attachment->filename && Storage::exists($attachment->filename)) {
                Storage::delete($attachment->filename);
            }

            $attachment->delete();

            if ($returnTo) {
                return redirect($returnTo)->with('success', 'Attachment deleted successfully.');
            }

            return redirect()
                ->route('attachments.index')
                ->with('success', 'Attachment deleted successfully.');
        } catch (\Throwable $e) {
            if ($returnTo) {
                return redirect($returnTo)->with('error', 'This attachment could not be deleted.');
            }

            return redirect()
                ->route('attachments.index')
                ->with('error', 'This attachment could not be deleted.');
        }
    }

    public function download(Attachment $attachment)
    {
        abort_unless($attachment->filename && Storage::exists($attachment->filename), 404);

        return Storage::download(
            $attachment->filename,
            $attachment->originalfilename,
            ['Content-Type' => $attachment->mimetype]
        );
    }

    public function view(Attachment $attachment)
    {
        abort_unless($attachment->filename && Storage::exists($attachment->filename), 404);

        return Storage::response($attachment->filename, $attachment->originalfilename, [
            'Content-Type' => $attachment->mimetype,
            'Content-Disposition' => 'inline; filename="' . $attachment->originalfilename . '"',
        ]);
    }

    private function resolveLinkedRecord(?string $linkedType, $linkedId)
    {
        if (!$linkedType || !$linkedId) {
            return null;
        }

        $normalizedType = ltrim(strtolower($linkedType), '\\');

        return match ($normalizedType) {
            'destination', 'app\\models\\destination' => Destination::find($linkedId),
            'destinationitem', 'destination_item', 'app\\models\\destinationitem' => DestinationItem::find($linkedId),
            'booking', 'app\\models\\booking' => Booking::find($linkedId),
            'review', 'app\\models\\review' => Review::find($linkedId),
            default => null,
        };
    }

    private function linkedRecordLabel(?string $linkedType, $record): ?string
{
    if (! $linkedType || ! $record) {
        return null;
    }

    $normalizedType = ltrim(strtolower($linkedType), '\\');

    return match ($normalizedType) {
        'destination', 'app\\models\\destination' => $record->destinationname ?: ('Destination #' . $record->id),
        'destination_item', 'destinationitem', 'app\\models\\destinationitem' => $record->itemname ?: ('Destination Item #' . $record->id),
        'booking', 'app\\models\\booking' => $this->bookingLinkedLabel($record),
        'review', 'app\\models\\review' => $record->title ?: ('Review #' . $record->id),
        default => null,
    };
}
    private function linkedRecordOptions(?string $linkedType): array
    {
        return match ($linkedType) {
            'destination' => Destination::query()
                ->orderBy('destinationname')
                ->pluck('destinationname', 'id')
                ->toArray(),

            'destination_item' => DestinationItem::query()
                ->orderBy('itemname')
                ->pluck('itemname', 'id')
                ->toArray(),

            'booking' => Booking::query()
                ->orderBy('providername')
                ->orderBy('startdate')
                ->orderBy('id')
                ->get()
                ->mapWithKeys(fn ($booking) => [
                    $booking->id => $this->bookingLinkedLabel($booking),
                ])
                ->toArray(),

            'review' => Review::query()
                ->orderByDesc('id')
                ->get()
                ->mapWithKeys(fn ($review) => [
                    $review->id => $review->title ?: ('Review #' . $review->id)
                ])
                ->toArray(),

            default => [],
        };
    }
    private function buildLinkedContext(?string $linkedType, $linkedId): array
{
    $linkedTypeOptions = $this->linkedTypeOptions();

    $selectedLinkedType = $linkedType ?: null;
    $selectedLinkedId = filled($linkedId) ? (int) $linkedId : null;

    $selectedLinkedTypeLabel = $selectedLinkedType
        ? ($linkedTypeOptions[$selectedLinkedType] ?? $selectedLinkedType)
        : null;

    $selectedLinkedRecord = $this->resolveLinkedRecord($selectedLinkedType, $selectedLinkedId);
    $selectedLinkedLabel = $this->linkedRecordLabel($selectedLinkedType, $selectedLinkedRecord);

    $selectedLinkedDisplay = $selectedLinkedTypeLabel;

    $linkedRecordOptions = $this->linkedRecordOptions($selectedLinkedType);

    return [
        'selectedLinkedType' => $selectedLinkedType,
        'selectedLinkedId' => $selectedLinkedId,
        'selectedLinkedLabel' => $selectedLinkedLabel,
        'selectedLinkedDisplay' => $selectedLinkedDisplay,
        'linkedRecordOptions' => $linkedRecordOptions,
    ];
}
    private function bookingLinkedLabel(Booking $booking): string
{
    $parts = collect([
        $booking->providername ?: null,
        $booking->externalreference ? 'Ref ' . $booking->externalreference : null,
        $booking->startdate ? \Illuminate\Support\Carbon::parse($booking->startdate)->format('d M Y') : null,
    ])->filter()->values();

    if ($parts->isNotEmpty()) {
        return $parts->implode(' - ');
    }

    if (! empty($booking->providercontact)) {
        return $booking->providercontact;
    }

    if (! empty($booking->website)) {
        return $booking->website;
    }

    return 'Booking #' . $booking->id;
}
}