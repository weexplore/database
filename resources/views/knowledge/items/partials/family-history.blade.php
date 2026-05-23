<div class="space-y-6">
    <div class="bg-white shadow-sm sm:rounded-lg">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Person Facts</h3>
                <p class="mt-1 text-sm text-slate-600">
                    Structured birth, death, burial, residence, and other personal facts.
                </p>
            </div>

            <a href="{{ route('knowledge.items.edit', [
                    'knowledgeItem' => $knowledgeItem,
                    'tab' => 'family-history',
                    'show_add_person_fact' => 1,
                    'return_to' => request('return_to'),
                ]) }}"
               class="inline-flex items-center rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800">
                Add Person Fact
            </a>
        </div>

        @if (!empty($showAddPersonFact))
            <form method="POST"
                  action="{{ route('knowledge.items.person-facts.store', ['knowledgeItem' => $knowledgeItem]) }}"
                  class="border-b border-slate-200 px-6 py-4 space-y-4">
                @csrf

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                    <div>
                        <label for="person_facttype" class="block text-sm font-medium text-slate-700">Fact Type</label>
                        <select name="facttype" id="person_facttype" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select fact type</option>
                            @foreach ($personFactTypeOptions as $value => $label)
                                <option value="{{ $value }}" {{ old('facttype') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="person_datetext" class="block text-sm font-medium text-slate-700">Date Text</label>
                        <input type="text"
                               name="datetext"
                               id="person_datetext"
                               value="{{ old('datetext') }}"
                               placeholder="About 1832"
                               class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="person_datequalifier" class="block text-sm font-medium text-slate-700">Date Qualifier</label>
                        <select name="datequalifier" id="person_datequalifier" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">None</option>
                            @foreach ($dateQualifierOptions as $value => $label)
                                <option value="{{ $value }}" {{ old('datequalifier') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="person_placeid" class="block text-sm font-medium text-slate-700">Place</label>
                        <select name="placeid" id="person_placeid" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select place</option>
                            @foreach ($places as $place)
                                <option value="{{ $place->id }}" {{ (string) old('placeid') === (string) $place->id ? 'selected' : '' }}>
                                    {{ $place->placename }}@if($place->locality), {{ $place->locality }}@endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="person_sortorder" class="block text-sm font-medium text-slate-700">Sort Order</label>
                        <input type="number"
                               name="sortorder"
                               id="person_sortorder"
                               value="{{ old('sortorder', 0) }}"
                               min="0"
                               class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="person_datefrom" class="block text-sm font-medium text-slate-700">Date From</label>
                        <input type="date"
                               name="datefrom"
                               id="person_datefrom"
                               value="{{ old('datefrom') }}"
                               class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="person_dateto" class="block text-sm font-medium text-slate-700">Date To</label>
                        <input type="date"
                               name="dateto"
                               id="person_dateto"
                               value="{{ old('dateto') }}"
                               class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="person_proofstatus" class="block text-sm font-medium text-slate-700">Proof Status</label>
                        <select name="proofstatus" id="person_proofstatus" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">None</option>
                            @foreach ($proofStatusOptions as $value => $label)
                                <option value="{{ $value }}" {{ old('proofstatus') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="ispreferred" value="1" {{ old('ispreferred') ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            Preferred fact
                        </label>
                    </div>
                </div>

                <div>
                    <label for="person_notes" class="block text-sm font-medium text-slate-700">Notes</label>
                    <textarea name="notes" id="person_notes" rows="3"
                              class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <a href="{{ route('knowledge.items.edit', [
                            'knowledgeItem' => $knowledgeItem,
                            'tab' => 'family-history',
                            'return_to' => request('return_to'),
                        ]) }}"
                       class="inline-flex items-center rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                        Cancel
                    </a>

                    <button type="submit"
                            class="inline-flex items-center rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                        Save Person Fact
                    </button>
                </div>
            </form>
        @endif

        <form method="POST"
              action="{{ route('knowledge.items.person-facts.reorder', ['knowledgeItem' => $knowledgeItem]) }}"
              id="person-facts-reorder-form">
            @csrf
            <input type="hidden" name="ordered_ids" id="person-facts-ordered-ids">

            <div id="person-facts-list" class="divide-y divide-slate-100">
                @forelse ($knowledgeItem->personFacts->sortBy([
                    ['sortorder', 'asc'],
                    ['datefrom', 'asc'],
                    ['id', 'asc'],
                ]) as $fact)
                    <div class="px-6 py-4 person-fact-row" draggable="true" data-id="{{ $fact->id }}">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex min-w-0 flex-1 items-start gap-3">
                                <button type="button"
                                        class="mt-1 cursor-move rounded-md border border-slate-200 bg-slate-50 px-2 py-1 text-xs text-slate-500 hover:bg-slate-100"
                                        title="Drag to reorder">
                                    ↕
                                </button>

                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-medium text-slate-900">
                                        {{ $personFactTypeOptions[$fact->facttype] ?? ucfirst($fact->facttype) }}
                                        @if ($fact->ispreferred)
                                            <span class="ml-2 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">
                                                Preferred
                                            </span>
                                        @endif
                                    </div>

                                    <div class="mt-1 text-sm text-slate-600">
                                        {{ $fact->datetext ?: 'No date text' }}
                                        @if ($fact->place)
                                            · {{ $fact->place->placename }}
                                        @endif
                                        @if ($fact->datequalifier)
                                            · {{ $dateQualifierOptions[$fact->datequalifier] ?? ucfirst($fact->datequalifier) }}
                                        @endif
                                        @if ($fact->proofstatus)
                                            · {{ $proofStatusOptions[$fact->proofstatus] ?? ucfirst($fact->proofstatus) }}
                                        @endif
                                        · Sort {{ $fact->sortorder ?? 0 }}
                                    </div>

                                    @if ($fact->notes)
                                        <div class="mt-2 text-sm text-slate-700">{{ $fact->notes }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('knowledge.items.person-facts.edit', [
                                        'knowledgeItem' => $knowledgeItem,
                                        'knowledgePersonFact' => $fact,
                                        'tab' => 'family-history',
                                        'return_to' => request('return_to'),
                                    ]) }}"
                                   class="inline-flex items-center rounded-md bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100">
                                    Edit
                                </a>

                                <form method="POST"
                                      action="{{ route('knowledge.items.person-facts.destroy', [
                                          'knowledgeItem' => $knowledgeItem,
                                          'knowledgePersonFact' => $fact,
                                      ]) }}"
                                      onsubmit="return confirm('Delete this person fact?');">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="inline-flex items-center rounded-md bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>

                        @if ((int) $editingPersonFactId === (int) $fact->id && !empty($editingPersonFact))
                            <form method="POST"
                                  action="{{ route('knowledge.items.person-facts.update', [
                                      'knowledgeItem' => $knowledgeItem,
                                      'knowledgePersonFact' => $fact,
                                  ]) }}"
                                  class="mt-4 border-t border-slate-200 pt-4 space-y-4">
                                @csrf
                                @method('PUT')

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Fact Type</label>
                                        <select name="facttype" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            @foreach ($personFactTypeOptions as $value => $label)
                                                <option value="{{ $value }}" {{ old('facttype', $editingPersonFact->facttype) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Date Text</label>
                                        <input type="text"
                                               name="datetext"
                                               value="{{ old('datetext', $editingPersonFact->datetext) }}"
                                               class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Date Qualifier</label>
                                        <select name="datequalifier" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">None</option>
                                            @foreach ($dateQualifierOptions as $value => $label)
                                                <option value="{{ $value }}" {{ old('datequalifier', $editingPersonFact->datequalifier) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Place</label>
                                        <select name="placeid" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">Select place</option>
                                            @foreach ($places as $place)
                                                <option value="{{ $place->id }}" {{ (string) old('placeid', $editingPersonFact->placeid) === (string) $place->id ? 'selected' : '' }}>
                                                    {{ $place->placename }}@if($place->locality), {{ $place->locality }}@endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Sort Order</label>
                                        <input type="number"
                                               name="sortorder"
                                               value="{{ old('sortorder', $editingPersonFact->sortorder ?? 0) }}"
                                               min="0"
                                               class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Date From</label>
                                        <input type="date"
                                               name="datefrom"
                                               value="{{ old('datefrom', optional($editingPersonFact->datefrom)->format('Y-m-d')) }}"
                                               class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Date To</label>
                                        <input type="date"
                                               name="dateto"
                                               value="{{ old('dateto', optional($editingPersonFact->dateto)->format('Y-m-d')) }}"
                                               class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Proof Status</label>
                                        <select name="proofstatus" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">None</option>
                                            @foreach ($proofStatusOptions as $value => $label)
                                                <option value="{{ $value }}" {{ old('proofstatus', $editingPersonFact->proofstatus) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="flex items-end">
                                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                            <input type="checkbox"
                                                   name="ispreferred"
                                                   value="1"
                                                   {{ old('ispreferred', $editingPersonFact->ispreferred) ? 'checked' : '' }}
                                                   class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                            Preferred fact
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Notes</label>
                                    <textarea name="notes" rows="3"
                                              class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes', $editingPersonFact->notes) }}</textarea>
                                </div>

                                <div class="flex items-center justify-between gap-3">
                                    <a href="{{ route('knowledge.items.edit', [
                                            'knowledgeItem' => $knowledgeItem,
                                            'tab' => 'family-history',
                                            'return_to' => request('return_to'),
                                        ]) }}"
                                       class="inline-flex items-center rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                                        Cancel
                                    </a>

                                    <button type="submit"
                                            class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                        Update Person Fact
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="px-6 py-8 text-sm text-slate-500">
                        No person facts recorded.
                    </div>
                @endforelse
            </div>

            @if ($knowledgeItem->personFacts->isNotEmpty())
                <div class="border-t border-slate-200 px-6 py-4">
                    <button type="submit"
                            class="inline-flex items-center rounded-md bg-slate-100 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">
                        Save Person Fact Order
                    </button>
                </div>
            @endif
        </form>
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="text-base font-semibold text-slate-900">Relationship Facts</h3>
            <p class="mt-1 text-sm text-slate-600">
                Marriage, separation, divorce, and other facts attached to family relationships.
            </p>
        </div>

        @php
            $allRelationships = $knowledgeItem->outgoingRelationships
                ->merge($knowledgeItem->incomingRelationships)
                ->sortBy('sortorder')
                ->values();
        @endphp

        <div class="divide-y divide-slate-100">
            @forelse ($allRelationships as $relationship)
                @php
                    $isOutgoing = (int) $relationship->fromitemid === (int) $knowledgeItem->id;
                    $relatedItem = $isOutgoing ? $relationship->toItem : $relationship->fromItem;
                    $displayRelationshipLabel = $isOutgoing
                        ? $relationship->relationshipTypeLabel()
                        : $relationship->inverseRelationshipTypeLabel();
                    $baseRelationshipTypeLabel = $relationshipTypeOptions[$relationship->relationshiptype] ?? ucfirst($relationship->relationshiptype);
                @endphp

                <div class="px-6 py-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0 flex-1 space-y-2">
                            <div class="text-sm font-medium text-slate-900">
                                {{ $relatedItem?->itemname ?? 'Unknown item' }}: {{ $relatedItem?->primaryCategory?->categoryname ?? 'Uncategorised' }}
                                · {{ $displayRelationshipLabel }}
                            </div>

                            <div class="flex flex-wrap gap-2 text-xs text-slate-600">
                                <span class="rounded-full bg-slate-100 px-2 py-1">
                                    Type: {{ $baseRelationshipTypeLabel }}
                                </span>

                                <span class="rounded-full bg-slate-100 px-2 py-1">
                                    Direction: {{ $isOutgoing ? 'Outgoing' : 'Incoming' }}
                                </span>

                                @if ($relationship->effective_date)
                                    <span class="rounded-full bg-slate-100 px-2 py-1">
                                        Effective: {{ $relationship->effective_date->format('j M Y') }}
                                    </span>
                                @endif

                                <span class="rounded-full bg-slate-100 px-2 py-1">
                                    Sort: {{ $relationship->sortorder ?? 0 }}
                                </span>
                            </div>

                            @if ($relationship->notes)
                                <div class="text-sm text-slate-700">
                                    {{ $relationship->notes }}
                                </div>
                            @endif
                        </div>

                        <a href="{{ route('knowledge.items.edit', [
                                'knowledgeItem' => $knowledgeItem,
                                'tab' => 'family-history',
                                'show_add_relationship_fact_for' => $relationship->id,
                                'return_to' => request('return_to'),
                            ]) }}"
                           class="inline-flex items-center rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800">
                            Add Relationship Fact
                        </a>
                    </div>

                    @if ((int) $showAddRelationshipFactFor === (int) $relationship->id)
                        <form method="POST"
                              action="{{ route('knowledge.items.relationship-facts.store', [
                                  'knowledgeItem' => $knowledgeItem,
                                  'knowledgeRelationship' => $relationship,
                              ]) }}"
                              class="mt-4 border-t border-slate-200 pt-4 space-y-4">
                            @csrf

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Fact Type</label>
                                    <select name="facttype" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select fact type</option>
                                        @foreach ($relationshipFactTypeOptions as $value => $label)
                                            <option value="{{ $value }}" {{ old('facttype') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Date Text</label>
                                    <input type="text"
                                           name="datetext"
                                           value="{{ old('datetext') }}"
                                           class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Date Qualifier</label>
                                    <select name="datequalifier" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">None</option>
                                        @foreach ($dateQualifierOptions as $value => $label)
                                            <option value="{{ $value }}" {{ old('datequalifier') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Place</label>
                                    <select name="placeid" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select place</option>
                                        @foreach ($places as $place)
                                            <option value="{{ $place->id }}" {{ (string) old('placeid') === (string) $place->id ? 'selected' : '' }}>
                                                {{ $place->placename }}@if($place->locality), {{ $place->locality }}@endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Sort Order</label>
                                    <input type="number"
                                           name="sortorder"
                                           value="{{ old('sortorder', 0) }}"
                                           min="0"
                                           class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Date From</label>
                                    <input type="date"
                                           name="datefrom"
                                           value="{{ old('datefrom') }}"
                                           class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Date To</label>
                                    <input type="date"
                                           name="dateto"
                                           value="{{ old('dateto') }}"
                                           class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Proof Status</label>
                                    <select name="proofstatus" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">None</option>
                                        @foreach ($proofStatusOptions as $value => $label)
                                            <option value="{{ $value }}" {{ old('proofstatus') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="flex items-end">
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                        <input type="checkbox"
                                               name="ispreferred"
                                               value="1"
                                               {{ old('ispreferred') ? 'checked' : '' }}
                                               class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                        Preferred fact
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Notes</label>
                                <textarea name="notes" rows="3"
                                          class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes') }}</textarea>
                            </div>

                            <div class="flex items-center justify-between gap-3">
                                <a href="{{ route('knowledge.items.edit', [
                                        'knowledgeItem' => $knowledgeItem,
                                        'tab' => 'family-history',
                                        'return_to' => request('return_to'),
                                    ]) }}"
                                   class="inline-flex items-center rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                                    Cancel
                                </a>

                                <button type="submit"
                                        class="inline-flex items-center rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                                    Save Relationship Fact
                                </button>
                            </div>
                        </form>
                    @endif

                    <form method="POST"
                          action="{{ route('knowledge.items.relationship-facts.reorder', [
                              'knowledgeItem' => $knowledgeItem,
                              'knowledgeRelationship' => $relationship,
                          ]) }}"
                          class="mt-3">
                        @csrf
                        <input type="hidden" name="ordered_ids" class="relationship-facts-ordered-ids">

                        <div class="relationship-facts-list space-y-3" data-relationship-id="{{ $relationship->id }}">
                            @forelse ($relationship->relationshipFacts->sortBy([
                                ['sortorder', 'asc'],
                                ['datefrom', 'asc'],
                                ['id', 'asc'],
                            ]) as $fact)
                                <div class="rounded-md bg-slate-50 px-4 py-3 relationship-fact-row"
                                     draggable="true"
                                     data-id="{{ $fact->id }}">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex min-w-0 flex-1 items-start gap-3">
                                            <button type="button"
                                                    class="mt-1 cursor-move rounded-md border border-slate-200 bg-white px-2 py-1 text-xs text-slate-500 hover:bg-slate-100"
                                                    title="Drag to reorder">
                                                ↕
                                            </button>

                                            <div class="text-sm text-slate-700">
                                                <div class="font-medium text-slate-900">
                                                    {{ $relationshipFactTypeOptions[$fact->facttype] ?? ucfirst($fact->facttype) }}
                                                    @if ($fact->ispreferred)
                                                        <span class="ml-2 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">
                                                            Preferred
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="mt-1">
                                                    @if ($fact->datetext)
                                                        {{ $fact->datetext }}
                                                    @else
                                                        No date text
                                                    @endif
                                                    @if ($fact->place)
                                                        · {{ $fact->place->placename }}
                                                    @endif
                                                    @if ($fact->datequalifier)
                                                        · {{ $dateQualifierOptions[$fact->datequalifier] ?? ucfirst($fact->datequalifier) }}
                                                    @endif
                                                    @if ($fact->proofstatus)
                                                        · {{ $proofStatusOptions[$fact->proofstatus] ?? ucfirst($fact->proofstatus) }}
                                                    @endif
                                                    · Sort {{ $fact->sortorder ?? 0 }}
                                                </div>

                                                @if ($fact->notes)
                                                    <div class="mt-2">{{ $fact->notes }}</div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('knowledge.items.relationship-facts.edit', [
                                                    'knowledgeItem' => $knowledgeItem,
                                                    'knowledgeRelationship' => $relationship,
                                                    'knowledgeRelationshipFact' => $fact,
                                                    'tab' => 'family-history',
                                                    'return_to' => request('return_to'),
                                                ]) }}"
                                               class="inline-flex items-center rounded-md bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100">
                                                Edit
                                            </a>

                                            <form method="POST"
                                                  action="{{ route('knowledge.items.relationship-facts.destroy', [
                                                      'knowledgeItem' => $knowledgeItem,
                                                      'knowledgeRelationship' => $relationship,
                                                      'knowledgeRelationshipFact' => $fact,
                                                  ]) }}"
                                                  onsubmit="return confirm('Delete this relationship fact?');">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="inline-flex items-center rounded-md bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    @if ((int) $editingRelationshipFactId === (int) $fact->id && !empty($editingRelationshipFact))
                                        <form method="POST"
                                              action="{{ route('knowledge.items.relationship-facts.update', [
                                                  'knowledgeItem' => $knowledgeItem,
                                                  'knowledgeRelationship' => $relationship,
                                                  'knowledgeRelationshipFact' => $fact,
                                              ]) }}"
                                              class="mt-4 border-t border-slate-200 pt-4 space-y-4">
                                            @csrf
                                            @method('PUT')

                                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700">Fact Type</label>
                                                    <select name="facttype" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                        @foreach ($relationshipFactTypeOptions as $value => $label)
                                                            <option value="{{ $value }}" {{ old('facttype', $editingRelationshipFact->facttype) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700">Date Text</label>
                                                    <input type="text"
                                                           name="datetext"
                                                           value="{{ old('datetext', $editingRelationshipFact->datetext) }}"
                                                           class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700">Date Qualifier</label>
                                                    <select name="datequalifier" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                        <option value="">None</option>
                                                        @foreach ($dateQualifierOptions as $value => $label)
                                                            <option value="{{ $value }}" {{ old('datequalifier', $editingRelationshipFact->datequalifier) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700">Place</label>
                                                    <select name="placeid" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                        <option value="">Select place</option>
                                                        @foreach ($places as $place)
                                                            <option value="{{ $place->id }}" {{ (string) old('placeid', $editingRelationshipFact->placeid) === (string) $place->id ? 'selected' : '' }}>
                                                                {{ $place->placename }}@if($place->locality), {{ $place->locality }}@endif
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700">Sort Order</label>
                                                    <input type="number"
                                                           name="sortorder"
                                                           value="{{ old('sortorder', $editingRelationshipFact->sortorder ?? 0) }}"
                                                           min="0"
                                                           class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700">Date From</label>
                                                    <input type="date"
                                                           name="datefrom"
                                                           value="{{ old('datefrom', optional($editingRelationshipFact->datefrom)->format('Y-m-d')) }}"
                                                           class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700">Date To</label>
                                                    <input type="date"
                                                           name="dateto"
                                                           value="{{ old('dateto', optional($editingRelationshipFact->dateto)->format('Y-m-d')) }}"
                                                           class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700">Proof Status</label>
                                                    <select name="proofstatus" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                        <option value="">None</option>
                                                        @foreach ($proofStatusOptions as $value => $label)
                                                            <option value="{{ $value }}" {{ old('proofstatus', $editingRelationshipFact->proofstatus) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="flex items-end">
                                                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                                        <input type="checkbox"
                                                               name="ispreferred"
                                                               value="1"
                                                               {{ old('ispreferred', $editingRelationshipFact->ispreferred) ? 'checked' : '' }}
                                                               class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                                        Preferred fact
                                                    </label>
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-slate-700">Notes</label>
                                                <textarea name="notes" rows="3"
                                                          class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes', $editingRelationshipFact->notes) }}</textarea>
                                            </div>

                                            <div class="flex items-center justify-between gap-3">
                                                <a href="{{ route('knowledge.items.edit', [
                                                        'knowledgeItem' => $knowledgeItem,
                                                        'tab' => 'family-history',
                                                        'return_to' => request('return_to'),
                                                    ]) }}"
                                                   class="inline-flex items-center rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                                                    Cancel
                                                </a>

                                                <button type="submit"
                                                        class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                                    Update Relationship Fact
                                                </button>
                                            </div>
                                        </form>
                                    @endif
                                </div>
                            @empty
                                <div class="text-sm text-slate-500">
                                    No relationship facts recorded for this relationship.
                                </div>
                            @endforelse
                        </div>

                        @if ($relationship->relationshipFacts->isNotEmpty())
                            <div class="mt-3">
                                <button type="submit"
                                        class="inline-flex items-center rounded-md bg-slate-100 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">
                                    Save Relationship Fact Order
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            @empty
                <div class="px-6 py-8 text-sm text-slate-500">
                    No relationships available.
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function enableDragSort(containerSelector, hiddenInputSelector) {
        const container = document.querySelector(containerSelector);
        const hiddenInput = document.querySelector(hiddenInputSelector);

        if (!container || !hiddenInput) {
            return;
        }

        let dragged = null;

        function refreshOrder() {
            const ids = Array.from(container.querySelectorAll('[data-id]')).map(el => el.dataset.id);
            hiddenInput.value = JSON.stringify(ids);
        }

        container.querySelectorAll('[data-id]').forEach((item) => {
            item.addEventListener('dragstart', () => {
                dragged = item;
                item.classList.add('opacity-50');
            });

            item.addEventListener('dragend', () => {
                item.classList.remove('opacity-50');
                dragged = null;
                refreshOrder();
            });

            item.addEventListener('dragover', (e) => {
                e.preventDefault();
            });

            item.addEventListener('drop', (e) => {
                e.preventDefault();

                if (!dragged || dragged === item) {
                    return;
                }

                const rect = item.getBoundingClientRect();
                const offset = e.clientY - rect.top;
                const midpoint = rect.height / 2;

                if (offset < midpoint) {
                    item.parentNode.insertBefore(dragged, item);
                } else {
                    item.parentNode.insertBefore(dragged, item.nextSibling);
                }

                refreshOrder();
            });
        });

        refreshOrder();
    }

    enableDragSort('#person-facts-list', '#person-facts-ordered-ids');

    document.querySelectorAll('.relationship-facts-list').forEach((container) => {
        const hiddenInput = container.closest('form').querySelector('.relationship-facts-ordered-ids');

        if (!hiddenInput) {
            return;
        }

        let dragged = null;

        function refreshOrder() {
            const ids = Array.from(container.querySelectorAll('[data-id]')).map(el => el.dataset.id);
            hiddenInput.value = JSON.stringify(ids);
        }

        container.querySelectorAll('[data-id]').forEach((item) => {
            item.addEventListener('dragstart', () => {
                dragged = item;
                item.classList.add('opacity-50');
            });

            item.addEventListener('dragend', () => {
                item.classList.remove('opacity-50');
                dragged = null;
                refreshOrder();
            });

            item.addEventListener('dragover', (e) => {
                e.preventDefault();
            });

            item.addEventListener('drop', (e) => {
                e.preventDefault();

                if (!dragged || dragged === item) {
                    return;
                }

                const rect = item.getBoundingClientRect();
                const offset = e.clientY - rect.top;
                const midpoint = rect.height / 2;

                if (offset < midpoint) {
                    item.parentNode.insertBefore(dragged, item);
                } else {
                    item.parentNode.insertBefore(dragged, item.nextSibling);
                }

                refreshOrder();
            });
        });

        refreshOrder();
    });
});
</script>
@endpush