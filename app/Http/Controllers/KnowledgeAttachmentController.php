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
            'uploadedby' => ['nullable', 'string', 'max:100'],
            'isprimary' => ['nullable', 'boolean'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'return_to' => ['nullable', 'string'],
        ]);

        $file = $request->file('file');
        $storedPath = $file->store('knowledge-attachments');

        if (!empty($data['isprimary'])) {
            KnowledgeAttachment::query()
                ->where('knowledgeitemid', $knowledgeItem->id)
                ->update(['isprimary' => false]);
        }

        KnowledgeAttachment::create([
            'knowledgeitemid' => $knowledgeItem->id,
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

        return redirect($data['return_to'] ?: route('knowledge.items.edit', [
            'knowledgeItem' => $knowledgeItem,
            'tab' => 'attachments',
        ]))->with('success', 'Attachment uploaded successfully.');
    }

    public function edit(Request $request, KnowledgeAttachment $knowledgeAttachment): View
    {
        $knowledgeAttachment->load('item.primaryCategory');

        $knowledgeItem = $knowledgeAttachment->item;
        $returnTo = $request->input('return_to', route('knowledge.items.edit', [
            'knowledgeItem' => $knowledgeItem,
            'tab' => 'attachments',
        ]));

        $attachments = KnowledgeAttachment::query()
            ->where('knowledgeitemid', $knowledgeItem->id)
            ->orderByDesc('isprimary')
            ->orderByDesc('uploadedat')
            ->orderByDesc('id')
            ->get();

        return view('knowledge-attachments.edit', [
            'knowledgeAttachment' => $knowledgeAttachment,
            'knowledgeItem' => $knowledgeItem,
            'attachmentTypeOptions' => $this->attachmentTypeOptions(),
            'returnTo' => $returnTo,
            'attachments' => $attachments,
        ]);
    }

    public function update(Request $request, KnowledgeAttachment $knowledgeAttachment): RedirectResponse
    {
        $attachmentTypeOptions = array_keys($this->attachmentTypeOptions());

        $data = $request->validate([
            'attachmenttype' => ['nullable', Rule::in($attachmentTypeOptions)],
            'description' => ['nullable', 'string'],
            'uploadedby' => ['nullable', 'string', 'max:100'],
            'isprimary' => ['nullable', 'boolean'],
            'return_to' => ['nullable', 'string'],
        ]);

        if (!empty($data['isprimary'])) {
            KnowledgeAttachment::query()
                ->where('knowledgeitemid', $knowledgeAttachment->knowledgeitemid)
                ->where('id', '!=', $knowledgeAttachment->id)
                ->update(['isprimary' => false]);
        }

        $knowledgeAttachment->update([
            'attachmenttype' => $data['attachmenttype'] ?? $knowledgeAttachment->attachmenttype,
            'description' => $data['description'] ?? null,
            'uploadedby' => $data['uploadedby'] ?? null,
            'isprimary' => !empty($data['isprimary']),
        ]);

        return redirect($data['return_to'] ?: route('knowledge.attachments.edit', $knowledgeAttachment))
            ->with('success', 'Attachment updated successfully.');
    }

    public function destroy(Request $request, KnowledgeAttachment $knowledgeAttachment): RedirectResponse
    {
        $returnTo = $request->input('return_to', route('knowledge.items.edit', [
            'knowledgeItem' => $knowledgeAttachment->knowledgeitemid,
            'tab' => 'attachments',
        ]));

        try {
            if ($knowledgeAttachment->filename && Storage::exists($knowledgeAttachment->filename)) {
                Storage::delete($knowledgeAttachment->filename);
            }

            $knowledgeAttachment->delete();

            return redirect($returnTo)->with('success', 'Attachment deleted successfully.');
        } catch (\Throwable $e) {
            return redirect($returnTo)->with('error', 'This attachment could not be deleted.');
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
}