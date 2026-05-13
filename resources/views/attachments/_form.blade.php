@php
    $attachment = $attachment ?? null;

    $selectedTripId = $selectedTripId ?? request('trip_id');
    $selectedLinkedType = $selectedLinkedType ?? request('linkedtype');
    $selectedLinkedId = $selectedLinkedId ?? request('linkedid');
    $selectedLinkedLabel = $selectedLinkedLabel ?? request('linked_label');
    $selectedLinkedDisplay = $selectedLinkedDisplay ?? null;

    $currentTripId = old('tripid', $selectedTripId ?? ($attachment->tripid ?? null));
    $currentLinkedType = old('linkedtype', $selectedLinkedType ?? ($attachment->linkedtype ?? ''));
    $currentLinkedId = old('linkedid', $selectedLinkedId ?? ($attachment->linkedid ?? ''));
    $currentAttachmentType = old('attachmenttype', $attachment->attachmenttype ?? 'document');
    $currentDescription = old('description', $attachment->description ?? '');
    $currentUploadedBy = old('uploadedby', $attachment->uploadedby ?? '');
    $currentIsPrimary = old('isprimary', $attachment->isprimary ?? false);
    $linkedTypeOptions = $linkedTypeOptions ?? [];
    $linkedRecordOptions = $linkedRecordOptions ?? [];

    $hasLockedLinkedRecord = filled($currentLinkedType) && filled($currentLinkedId);
@endphp

@if($hasLockedLinkedRecord)
    <input type="hidden" name="linkedtype" value="{{ $currentLinkedType }}">
    <input type="hidden" name="linkedid" value="{{ $currentLinkedId }}">

    @if(filled($currentTripId))
        <input type="hidden" name="tripid" value="{{ $currentTripId }}">
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <div class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700">
            <div class="font-medium text-gray-900">
    {{
        $selectedLinkedLabel
        ?: ($linkedRecordOptions[$currentLinkedId] ?? null)
        ?: 'Linked record'
    }}
</div>
<div class="text-xs text-gray-500 mt-1">
    {{ $selectedLinkedDisplay ?: ($linkedTypeOptions[$currentLinkedType] ?? str_replace('_', ' ', $currentLinkedType)) }}
</div>
        </div>

        <div>
            <label for="attachmenttype" class="block text-sm font-medium text-gray-700 mb-1">Attachment type</label>
            <select id="attachmenttype" name="attachmenttype" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                @foreach($attachmentTypeOptions as $value => $label)
                    <option value="{{ $value }}" @selected((string) $currentAttachmentType === (string) $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div>
            <label for="linkedtype" class="block text-sm font-medium text-gray-700 mb-1">Linked type</label>
            <select id="linkedtype" name="linkedtype" class="w-full rounded-md border-gray-300 shadow-sm text-sm" required>
                <option value="">Select</option>
                @foreach($linkedTypeOptions as $value => $label)
                    <option value="{{ $value }}" @selected((string) $currentLinkedType === (string) $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="tripid" class="block text-sm font-medium text-gray-700 mb-1">Trip ID</label>
            <input type="number" id="tripid" name="tripid" value="{{ $currentTripId }}" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="attachmenttype" class="block text-sm font-medium text-gray-700 mb-1">Attachment type</label>
            <select id="attachmenttype" name="attachmenttype" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                @foreach($attachmentTypeOptions as $value => $label)
                    <option value="{{ $value }}" @selected((string) $currentAttachmentType === (string) $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
@endif

<div class="mt-4">
    @if(!$attachment)
        <label for="file" class="block text-sm font-medium text-gray-700 mb-1">File</label>
        <input
            type="file"
            id="file"
            name="file"
            accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
            class="block w-full text-sm text-gray-700 border border-gray-300 rounded-md bg-white file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200"
            required
        >
        <p class="mt-1 text-xs text-gray-500">Allowed: PDF, JPG, JPEG, PNG. Maximum 10MB.</p>
        @if($errors->has('file'))
            <p class="mt-1 text-xs text-red-600">Please choose the file again after correcting the form.</p>
        @endif
    @else
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-sm">
            <div class="font-medium text-gray-900">{{ $attachment->originalfilename }}</div>
            <div class="mt-1 text-gray-500">{{ $attachment->mimetype }} · {{ number_format(((int) $attachment->filesizebytes) / 1024, 1) }} KB</div>
        </div>
    @endif
</div>

<div class="mt-4">
    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
    <textarea id="description" name="description" rows="3" class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ $currentDescription }}</textarea>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
    <div>
        <label for="uploadedby" class="block text-sm font-medium text-gray-700 mb-1">Uploaded by</label>
        <input type="text" id="uploadedby" name="uploadedby" value="{{ $currentUploadedBy }}" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>

    <div class="flex items-end">
        <label class="inline-flex items-center gap-2">
            <input type="hidden" name="isprimary" value="0">
            <input type="checkbox" name="isprimary" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm" @checked((bool) $currentIsPrimary)>
            <span class="text-sm text-gray-700">Primary attachment</span>
        </label>
    </div>
</div>