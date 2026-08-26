{{-- resources/views/knowledge/items/_form.blade.php --}}
<div class="space-y-8">
    <div>
        <h4 class="text-base font-semibold text-gray-900">Core Details</h4>
        <p class="mt-1 text-sm text-gray-500">
            Define the main category, naming, classification, and general summary information.
        </p>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="primarycategoryid" class="block text-sm font-medium text-gray-700 mb-1">
                    Primary Category
                </label>
                <select name="primarycategoryid"
                        id="primarycategoryid"
                        class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                        required>
                    <option value="">Select category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                            @selected((string) old('primarycategoryid', $knowledgeItem->primarycategoryid ?? '') === (string) $category->id)>
                            {{ $category->categoryname }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="parentitemid" class="block text-sm font-medium text-gray-700 mb-1">
                    Parent Item
                </label>
                <select name="parentitemid"
                        id="parentitemid"
                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">No parent item</option>
                    @foreach($parentItems as $parentItem)
                        <option value="{{ $parentItem->id }}"
                            @selected((string) old('parentitemid', $knowledgeItem->parentitemid ?? '') === (string) $parentItem->id)>
                            {{ $parentItem->itemname }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label for="itemname" class="block text-sm font-medium text-gray-700 mb-1">
                    Item Name
                </label>
                <input type="text"
                       name="itemname"
                       id="itemname"
                       value="{{ old('itemname', $knowledgeItem->itemname ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                       maxlength="255"
                       required>
            </div>

            <div>
                <label for="itemtype" class="block text-sm font-medium text-gray-700 mb-1">
                    Item Type
                </label>
                <select name="itemtype" id="itemtype" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">Select type</option>
                    @foreach($itemTypes as $itemType)
                        <option value="{{ $itemType->id }}"
                            @selected((string) old('itemtype', $knowledgeItem->itemtype ?? '') === (string) $itemType->id)>
                            {{ $itemType->typename }}
                        </option>
                    @endforeach
                </select>

                @error('itemtype')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="itemstatus" class="block text-sm font-medium text-gray-700 mb-1">
                    Item Status
                </label>
                <select name="itemstatus"
                        id="itemstatus"
                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">Select status</option>
                    @foreach($itemStatusOptions as $value => $label)
                        <option value="{{ $value }}"
                            @selected(old('itemstatus', $knowledgeItem->itemstatus ?? 'active') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
               <x-forms.markdown-display-editor
                    name="summary"
                    id="summary"
                    label="Summary"
                    :value="$knowledgeItem->summary ?? ''"
                    rows="4"
                    placeholder="Short overview of the knowledge item"
                    help="A concise overview of this knowledge item."
                />
            </div>
        </div>
    </div>

    <div class="border-t border-gray-200 pt-6">
        <h4 class="text-base font-semibold text-gray-900">Notes and Interpretation</h4>
        <p class="mt-1 text-sm text-gray-500">
            Capture detail, importance, and review commentary for this item.
        </p>

        <div class="mt-4 space-y-5">
            <x-forms.markdown-display-editor
                name="detailednotes"
                id="detailednotes"
                label="Detailed Notes"
                :value="$knowledgeItem->detailednotes ?? ''"
                rows="12"
                placeholder="Full notes, observations, or structured research detail"
                help="Long-form research, observations, or structured background detail."
            />

            <x-forms.markdown-display-editor
                name="significance"
                id="significance"
                label="Significance"
                :value="$knowledgeItem->significance ?? ''"
                rows="6"
                placeholder="Why this item matters"
                help="Why this item matters, implications, and decision relevance."
            />

            <x-forms.markdown-display-editor
                name="reviewnotes"
                id="reviewnotes"
                label="Review Notes"
                :value="$knowledgeItem->reviewnotes ?? ''"
                rows="6"
                placeholder="Review comments, follow-up actions, or quality notes"
                help="Follow-up actions, checks, and quality commentary."
            />
        </div>
    </div>

    <div class="border-t border-gray-200 pt-6">
        <h4 class="text-base font-semibold text-gray-900">Dates and Ordering</h4>
        <p class="mt-1 text-sm text-gray-500">
            Use dates for historical items, review scheduling, and display ordering.
        </p>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
            <div>
                <label for="startdate" class="block text-sm font-medium text-gray-700 mb-1">
                    Start Date
                </label>
                <input type="date"
                       name="startdate"
                       id="startdate"
                       value="{{ old('startdate', isset($knowledgeItem) && $knowledgeItem->startdate ? $knowledgeItem->startdate->format('Y-m-d') : '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>

            <div>
                <label for="enddate" class="block text-sm font-medium text-gray-700 mb-1">
                    End Date
                </label>
                <input type="date"
                       name="enddate"
                       id="enddate"
                       value="{{ old('enddate', isset($knowledgeItem) && $knowledgeItem->enddate ? $knowledgeItem->enddate->format('Y-m-d') : '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>

            <div>
                <label for="nextreviewdate" class="block text-sm font-medium text-gray-700 mb-1">
                    Next Review Date
                </label>
                <input type="date"
                       name="nextreviewdate"
                       id="nextreviewdate"
                       value="{{ old('nextreviewdate', isset($knowledgeItem) && $knowledgeItem->nextreviewdate ? $knowledgeItem->nextreviewdate->format('Y-m-d') : '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>

            <div>
                <label for="sortorder" class="block text-sm font-medium text-gray-700 mb-1">
                    Sort Order
                </label>
                <input type="number"
                       name="sortorder"
                       id="sortorder"
                       value="{{ old('sortorder', $knowledgeItem->sortorder ?? 0) }}"
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                       min="0">
            </div>

            <div class="xl:col-span-2">
                <label for="placeid" class="block text-sm font-medium text-gray-700 mb-1">
                    Place
                </label>
                <select name="placeid"
                        id="placeid"
                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">Select a place</option>
                    @foreach($places as $place)
                        <option value="{{ $place->id }}"
                            @selected((string) old('placeid', $knowledgeItem->placeid ?? '') === (string) $place->id)>
                            {{ $place->placename }}
                            @if($place->locality)
                                — {{ $place->locality }}
                            @endif
                            @if($place->placetype)
                                ({{ $place->placetype }})
                            @endif
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">
                    Optional linked place record.
                </p>
            </div>

            <div class="xl:col-span-2 flex flex-wrap items-center gap-6 pt-6">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="hidden" name="isfeatured" value="0">
                    <input type="checkbox"
                           name="isfeatured"
                           value="1"
                           class="rounded border-gray-300 text-blue-600 shadow-sm"
                           @checked(old('isfeatured', $knowledgeItem->isfeatured ?? false))>
                    Featured
                </label>

                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="hidden" name="isactive" value="0">
                    <input type="checkbox"
                           name="isactive"
                           value="1"
                           class="rounded border-gray-300 text-blue-600 shadow-sm"
                           @checked(old('isactive', $knowledgeItem->isactive ?? true))>
                    Active
                </label>
            </div>
        </div>
    </div>
</div>

@if(($activeTab ?? 'details') === 'details')
    @include('partials.markdown.markdown-styles')

    @include('partials.admin.dirty-form-script', [
        'formId' => 'knowledge-item-form',
        'dirtyMessage' => 'You have unsaved changes on this Knowledge Item. Continue and lose those changes?',
    ])
@endif