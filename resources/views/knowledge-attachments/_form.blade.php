@php
    $knowledgeAttachment = $knowledgeAttachment ?? null;
    $knowledgeItem = $knowledgeItem ?? $knowledgeAttachment?->item;

    $currentAttachmentType = old(
        'attachmenttype',
        $knowledgeAttachment->attachmenttype ?? 'document'
    );

    $currentDescription = old(
        'description',
        $knowledgeAttachment?->pivot?->description ?? ''
    );

    $currentExpiryDate = old(
        'expirydate',
        optional($knowledgeAttachment?->pivot?->expirydate)->format('Y-m-d')
    );

    $currentUploadedBy = old(
        'uploadedby',
        $knowledgeAttachment->uploadedby ?? ''
    );

    $currentIsPrimary = old(
        'isprimary',
        $knowledgeAttachment?->pivot?->isprimary ?? false
    );
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    <div class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700">
        <div class="font-medium text-gray-900">
            {{ $knowledgeItem->itemname ?: 'Knowledge item' }}
        </div>
        <div class="text-xs text-gray-500 mt-1">
            Knowledge Item · ID {{ $knowledgeItem->id }}
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

<div class="mt-4">
    @if(!$knowledgeAttachment)
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
            <div class="font-medium text-gray-900">{{ $knowledgeAttachment->originalfilename }}</div>
            <div class="mt-1 text-gray-500">
                {{ $knowledgeAttachment->mimetype }} · {{ number_format(((int) $knowledgeAttachment->filesizebytes) / 1024, 1) }} KB
            </div>
        </div>
    @endif
</div>

<div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
            Description
        </label>

        <textarea id="description"
                  name="description"
                  rows="3"
                  class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ $currentDescription }}</textarea>
    </div>

    <div>
        <label for="expirydate" class="block text-sm font-medium text-gray-700 mb-1">
            Expiry date
        </label>

        <input type="date"
               id="expirydate"
               name="expirydate"
               value="{{ $currentExpiryDate }}"
               class="w-full rounded-md border-gray-300 shadow-sm text-sm">

        <p class="mt-1 text-xs text-gray-500">
            Optional. Appears in Task Outlook within 14 days of expiry.
        </p>
    </div>
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