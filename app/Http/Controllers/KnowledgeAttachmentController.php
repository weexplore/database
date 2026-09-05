<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeAttachment;
use App\Models\KnowledgeItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KnowledgeAttachmentController extends Controller
{
    private function attachmentTypeOptions(): array
    {
        return [
            'document' => 'Document',
            'image' => 'Image',
            'invoice' => 'Invoice',
            'receipt' => 'Receipt',
            'map' => 'Map',
            'other' => 'Other',
        ];
    }

    public function store(Request $request, KnowledgeItem $knowledgeItem): RedirectResponse
{
    $attachmentTypeOptions = array_keys($this->attachmentTypeOptions());

    $data = $request->validate([
        'attachmenttype' => ['nullable', Rule::in($attachmentTypeOptions)],
        'description' => ['nullable', 'string'],
        'expirydate' => ['nullable', 'date'],
        'uploadedby' => ['nullable', 'string', 'max:100'],
        'isprimary' => ['nullable', 'boolean'],
        'sortorder' => ['nullable', 'integer', 'min:0'],
        'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        'return_to' => ['nullable', 'string'],
    ]);

    $file = $request->file('file');
    $storedPath = $file->store('knowledge-attachments');

    $attachment = KnowledgeAttachment::create([
        'attachmenttype' => $data['attachmenttype'] ?? 'document',
        'filename' => $storedPath,
        'originalfilename' => $file->getClientOriginalName(),
        'mimetype' => $file->getClientMimeType(),
        'filesizebytes' => $file->getSize(),
        'uploadedat' => now(),
        'uploadedby' => $data['uploadedby'] ?? (auth()->user()->name ?? null),
    ]);

    if (!empty($data['isprimary'])) {
        $knowledgeItem->attachments()->updateExistingPivot(
            $knowledgeItem->attachments()->pluck('knowledgeattachments.id')->all(),
            ['isprimary' => false]
        );
    }

    $knowledgeItem->attachments()->attach($attachment->id, [
        'description' => $data['description'] ?? null,
        'expirydate' => $data['expirydate'] ?? null,
        'isprimary' => !empty($data['isprimary']),
        'sortorder' => $data['sortorder'] ?? 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);


    return redirect($data['return_to'] ?: route('knowledge.items.edit', [
        'knowledgeItem' => $knowledgeItem,
        'tab' => 'attachments',
    ]))->with('success', 'Attachment uploaded successfully.');
}

    public function edit(
        Request $request,
        KnowledgeItem $knowledgeItem,
        KnowledgeAttachment $knowledgeAttachment
    ): View {
        $knowledgeAttachment = $knowledgeItem->attachments()
            ->where('knowledgeattachments.id', $knowledgeAttachment->id)
            ->firstOrFail();

        $returnTo = $request->input('return_to', route('knowledge.items.edit', [
            'knowledgeItem' => $knowledgeItem,
            'tab' => 'attachments',
        ]));

        $attachments = $knowledgeItem->attachments()
            ->orderByDesc('knowledgeitem_attachments.isprimary')
            ->orderByRaw(
                'CASE
                    WHEN COALESCE(knowledgeitem_attachments.sortorder, 0) > 0 THEN 0
                    ELSE 1
                END'
            )
            ->orderByRaw(
                'CASE
                    WHEN COALESCE(knowledgeitem_attachments.sortorder, 0) > 0
                        THEN knowledgeitem_attachments.sortorder
                    ELSE 999999
                END'
            )
            ->orderByDesc('knowledgeattachments.uploadedat')
            ->orderByDesc('knowledgeattachments.id')
            ->get();

        return view('knowledge-attachments.edit', [
            'knowledgeAttachment' => $knowledgeAttachment,
            'knowledgeItem' => $knowledgeItem,
            'attachmentTypeOptions' => $this->attachmentTypeOptions(),
            'returnTo' => $returnTo,
            'attachments' => $attachments,
        ]);
    }

    public function update(
    Request $request,
    KnowledgeItem $knowledgeItem,
    KnowledgeAttachment $knowledgeAttachment
): RedirectResponse {
    abort_unless(
        $knowledgeItem->attachments()
            ->where('knowledgeattachments.id', $knowledgeAttachment->id)
            ->exists(),
        404
    );

    $attachmentTypeOptions = array_keys($this->attachmentTypeOptions());

    $data = $request->validate([
        'attachmenttype' => ['nullable', Rule::in($attachmentTypeOptions)],
        'description' => ['nullable', 'string'],
        'expirydate' => ['nullable', 'date_format:Y-m-d'],
        'uploadedby' => ['nullable', 'string', 'max:100'],
        'isprimary' => ['nullable', 'boolean'],
        'sortorder' => ['nullable', 'integer', 'min:0'],
        'return_to' => ['nullable', 'string'],
    ]);

    if (!empty($data['isprimary'])) {
        \DB::table('knowledgeitem_attachments')
            ->where('knowledgeitemid', $knowledgeItem->id)
            ->where('knowledgeattachmentid', '!=', $knowledgeAttachment->id)
            ->update([
                'isprimary' => false,
                'updated_at' => now(),
            ]);
    }

    $knowledgeAttachment->update([
        'attachmenttype' => $data['attachmenttype'] ?? $knowledgeAttachment->attachmenttype,
        'uploadedby' => $data['uploadedby'] ?? null,
    ]);

    $expiryDate = $request->filled('expirydate')
        ? $request->input('expirydate')
        : null;

    $knowledgeItem->attachments()->updateExistingPivot(
        $knowledgeAttachment->id,
        [
            'description' => $data['description'] ?? null,
            'expirydate' => $expiryDate,
            'isprimary' => $request->boolean('isprimary'),
            'sortorder' => $data['sortorder'] ?? 0,
            'updated_at' => now(),
        ]
    );

    return redirect($data['return_to'] ?: route('knowledge.items.edit', [
        'knowledgeItem' => $knowledgeItem,
        'tab' => 'attachments',
    ]))->with('success', 'Attachment updated successfully.');
}

    public function destroy(Request $request, KnowledgeItem $knowledgeItem, KnowledgeAttachment $knowledgeAttachment): RedirectResponse
{
    $returnTo = $request->input('return_to', route('knowledge.items.edit', [
        'knowledgeItem' => $knowledgeItem,
        'tab' => 'attachments',
    ]));

    try {
        $knowledgeItem->attachments()->detach($knowledgeAttachment->id);

        $remainingLinks = $knowledgeAttachment->items()->count();

        if ($remainingLinks === 0) {
            if ($knowledgeAttachment->filename && Storage::exists($knowledgeAttachment->filename)) {
                Storage::delete($knowledgeAttachment->filename);
            }

            $knowledgeAttachment->delete();
        }

        return redirect($returnTo)->with('success', 'Attachment removed successfully.');
    } catch (\Throwable $e) {
        return redirect($returnTo)->with('error', 'This attachment could not be removed.');
    }
}

    public function download(KnowledgeAttachment $knowledgeAttachment)
    {
        abort_unless($knowledgeAttachment->filename && Storage::exists($knowledgeAttachment->filename), 404);

        return Storage::download(
            $knowledgeAttachment->filename,
            $knowledgeAttachment->originalfilename,
            ['Content-Type' => $knowledgeAttachment->mimetype]
        );
    }

    public function view(KnowledgeAttachment $knowledgeAttachment)
    {
        abort_unless($knowledgeAttachment->filename && Storage::exists($knowledgeAttachment->filename), 404);

        return Storage::response($knowledgeAttachment->filename, $knowledgeAttachment->originalfilename, [
            'Content-Type' => $knowledgeAttachment->mimetype,
            'Content-Disposition' => 'inline; filename="' . $knowledgeAttachment->originalfilename . '"',
        ]);
    }

    public function attachExisting(Request $request, KnowledgeItem $knowledgeItem): RedirectResponse
{
    $data = $request->validate([
        'knowledgeattachmentid' => ['required', 'integer', Rule::exists('knowledgeattachments', 'id')],
        'description' => ['nullable', 'string'],
        'expirydate' => ['nullable', 'date'],
        'isprimary' => ['nullable', 'boolean'],
        'sortorder' => ['nullable', 'integer', 'min:0'],
        'return_to' => ['nullable', 'string'],
    ]);

    $attachmentId = (int) $data['knowledgeattachmentid'];

    $alreadyLinked = $knowledgeItem->attachments()
        ->where('knowledgeattachments.id', $attachmentId)
        ->exists();

    if ($alreadyLinked) {
        return redirect($data['return_to'] ?: route('knowledge.items.edit', [
            'knowledgeItem' => $knowledgeItem,
            'tab' => 'attachments',
        ]))->with('error', 'That attachment is already linked to this knowledge item.');
    }

    if (!empty($data['isprimary'])) {
        \DB::table('knowledgeitem_attachments')
            ->where('knowledgeitemid', $knowledgeItem->id)
            ->update(['isprimary' => false]);
    }

   $knowledgeItem->attachments()->attach($attachmentId, [
        'description' => $data['description'] ?? null,
        'expirydate' => $data['expirydate'] ?? null,
        'isprimary' => !empty($data['isprimary']),
        'sortorder' => $data['sortorder'] ?? 0,
    ]);

    return redirect($data['return_to'] ?: route('knowledge.items.edit', [
        'knowledgeItem' => $knowledgeItem,
        'tab' => 'attachments',
    ]))->with('success', 'Existing attachment linked successfully.');
}
}