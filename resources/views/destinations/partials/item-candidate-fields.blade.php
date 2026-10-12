<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
    <label class="block text-xs font-medium text-gray-700">
        Item name
        <input name="itemname" type="text" maxlength="200" required class="mt-1 block w-full rounded-md border-gray-300 text-sm">
    </label>
    <label class="block text-xs font-medium text-gray-700">
        Suggested type
        <select name="itemtype" data-candidate-type class="mt-1 block w-full rounded-md border-gray-300 text-sm"><option value="">Not specified</option></select>
    </label>
    <label class="block text-xs font-medium text-gray-700 md:col-span-2">
        Original description
        <textarea name="shortdescription" rows="4" maxlength="16000" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></textarea>
    </label>
    <label class="block text-xs font-medium text-gray-700 md:col-span-2">
        Reviewer-edited description
        <textarea name="editeddescription" rows="4" maxlength="16000" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></textarea>
    </label>
    <label class="block text-xs font-medium text-gray-700 md:col-span-2">
        Website / reference URL
        <input name="website" type="url" maxlength="500" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
        <span class="mt-1 block font-normal text-gray-500">Defaults to the source URL when selected text is used. Check whether this is the item website or a reference page.</span>
    </label>
    @foreach ([
        'addressline1' => ['Address', 'text', 200],
        'addressline2' => ['Address', 'text', 200],
        'addressline3' => ['Address', 'text', 200],
        'postcode' => ['Postcode', 'text', 20],
        'telephone' => ['Telephone', 'text', 50],
    ] as $field => $config)
        <label class="block text-xs font-medium text-gray-700">{{ $config[0] }}
            <input name="{{ $field }}" type="{{ $config[1] }}" maxlength="{{ $config[2] }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
        </label>
    @endforeach
    <label class="block text-xs font-medium text-gray-700">Recommended visit minutes
        <input name="recommendedstayminutes" type="number" min="0" max="65535" step="1" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
    </label>
    <label class="block text-xs font-medium text-gray-700">Latitude
        <input name="latitude" type="number" min="-90" max="90" step="0.00000001" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
    </label>
    <label class="block text-xs font-medium text-gray-700">Longitude
        <input name="longitude" type="number" min="-180" max="180" step="0.00000001" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
    </label>
    <label class="block text-xs font-medium text-gray-700">Confidence
        <select name="confidence" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
            <option value="">Not rated</option>
            @foreach (range(1, 5) as $rating)<option value="{{ $rating }}">{{ $rating }}</option>@endforeach
        </select>
    </label>
    <label class="block text-xs font-medium text-gray-700">Sort order
        <input name="sortorder" type="number" value="0" min="0" max="2147483647" step="1" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
    </label>
    <label class="flex items-center gap-2 text-sm text-gray-700 md:col-span-2">
        <input name="bookingrequired" type="checkbox" class="rounded border-gray-300">Booking required (only tick if supported by the source)
    </label>
    @foreach (['caravanaccessnotes' => 'Caravan access notes', 'disabilityaccessnotes' => 'Disability access notes', 'reviewnotes' => 'Review notes / caveats'] as $field => $label)
        <label class="block text-xs font-medium text-gray-700 md:col-span-2">{{ $label }}
            <textarea name="{{ $field }}" rows="2" maxlength="16000" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></textarea>
        </label>
    @endforeach
</div>
