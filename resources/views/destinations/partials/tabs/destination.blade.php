<form method="POST"
      action="{{ route('destinations.update', $destination) }}"
      id="destination-edit-form"
      class="space-y-6">
    @csrf
    @method('PUT')
    <input type="hidden" name="return_to" value="{{ $returnTo }}">
    <input type="hidden" name="tab" value="{{ $activeTab }}" data-active-tab-input>

    <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-5">
        <h3 class="text-lg font-medium text-gray-900">Core Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <div>
                <label for="placeid" class="block text-sm font-medium text-gray-700 mb-1">Linked Place</label>
                <select name="placeid" id="placeid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">None</option>
                    @foreach ($places as $place)
                        <option value="{{ $place->id }}" @selected((string) old('placeid', $destination->placeid) === (string) $place->id)>{{ $place->placename }}</option>
                    @endforeach
                </select>
            </div>
            <div class="xl:col-span-2">
                <label for="destinationname" class="block text-sm font-medium text-gray-700 mb-1">Destination Name</label>
                <input type="text" name="destinationname" id="destinationname"
                       value="{{ old('destinationname', $destination->destinationname) }}"
                       maxlength="200" required class="w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label for="destinationtype" class="block text-sm font-medium text-gray-700 mb-1">Destination Type</label>
                <select name="destinationtype" id="destinationtype" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    @foreach ($typeOptions as $type)
                        <option value="{{ $type }}" @selected(old('destinationtype', $destination->destinationtype) === $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="bestseason" class="block text-sm font-medium text-gray-700 mb-1">Best Season</label>
                <input type="text" name="bestseason" id="bestseason" maxlength="100"
                       value="{{ old('bestseason', $destination->bestseason) }}"
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label for="revisitinterestlevel" class="block text-sm font-medium text-gray-700 mb-1">Revisit Interest Level</label>
                @php
                    $revisitOptions = $revisitOptions ?? [
                        'very_likely' => 'Very Likely', 'likely' => 'Likely',
                        'neutral' => 'Neutral', 'unlikely' => 'Unlikely',
                        'very_unlikely' => 'Very Unlikely',
                    ];
                @endphp
                <select name="revisitinterestlevel" id="revisitinterestlevel" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">Select revisit interest</option>
                    @foreach ($revisitOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('revisitinterestlevel', $destination->revisitinterestlevel) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-4">
            @foreach (['isfeatured' => 'Featured destination', 'hasvisited' => 'Visited destination'] as $field => $label)
                <label class="inline-flex items-center gap-2">
                    <input type="hidden" name="{{ $field }}" value="0">
                    <input type="checkbox" name="{{ $field }}" value="1"
                           @checked(old($field, $destination->getAttribute($field)))
                           class="rounded border-gray-300 text-blue-600 shadow-sm">
                    <span class="text-sm text-gray-700">{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="bg-amber-50 border border-amber-200 shadow-sm sm:rounded-lg p-6 space-y-4">
        <h3 class="text-lg font-medium text-amber-950">Our Travel Interest</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="visitinterestlevel" class="block text-sm font-medium text-gray-700 mb-1">Travel-interest priority</label>
                <select name="visitinterestlevel" id="visitinterestlevel" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">Not specifically on our travel-interest list</option>
                    @foreach ($visitInterestOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('visitinterestlevel', $destination->visitinterestlevel) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="visitedat" class="block text-sm font-medium text-gray-700 mb-1">Visited date</label>
                <input type="date" name="visitedat" id="visitedat"
                       value="{{ old('visitedat', optional($destination->visitedat)->format('Y-m-d')) }}"
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <p class="mt-1 text-xs text-gray-500">Optional; leave blank if no date is recorded.</p>
            </div>
        </div>
        <div>
            <label for="visitinterestnotes" class="block text-sm font-medium text-gray-700 mb-1">Why do we want to go?</label>
            <textarea name="visitinterestnotes" id="visitinterestnotes" rows="4"
                      class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                      placeholder="Reasons to visit, things to investigate, or future trip ideas...">{{ old('visitinterestnotes', $destination->visitinterestnotes) }}</textarea>
        </div>
    </div>

    @php
        $editorSections = [
            'Editorial Overview' => [
                'overview' => ['Overview', 6, 'Write an editorial overview for this destination...'],
                'travelnotes' => ['Travel Notes', 6, 'Add travel notes, route thoughts, timing, or practical commentary...'],
            ],
            'Suitability and Access' => [
                'suitability' => ['Suitability', 5, 'Describe who this destination suits...'],
                'accessnotes' => ['Access Notes', 5, 'Record access conditions, roads, gates, turning areas, or cautions...'],
            ],
            'Personal Commentary' => [
                'personalcommentary' => ['Personal Commentary', 7, 'Add personal reflections, impressions, and revisit thoughts...'],
            ],
        ];
    @endphp

    @foreach ($editorSections as $heading => $editors)
        <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-5">
            <h3 class="text-lg font-medium text-gray-900">{{ $heading }}</h3>
            @foreach ($editors as $field => $settings)
                <x-forms.markdown-display-editor
                    :name="$field"
                    :id="$field"
                    :value="old($field, $destination->getAttribute($field))"
                    :label="$settings[0]"
                    :rows="$settings[1]"
                    :placeholder="$settings[2]"
                    help="Markdown supported. Click Edit to change the content."
                />
            @endforeach
        </div>
    @endforeach

    <div class="flex flex-wrap items-center justify-end gap-3">
        <a href="{{ $returnTo }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">Cancel</a>
        <button type="submit" class="px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700">Save Destination</button>
    </div>
</form>
