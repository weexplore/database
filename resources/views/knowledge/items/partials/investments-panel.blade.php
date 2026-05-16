@php
    $instrument = $knowledgeItem->instrument;
    $isEdit = (bool) $instrument;
@endphp

<form method="POST"
      action="{{ $isEdit
            ? route('knowledge.items.instrument.update', ['knowledgeItem' => $knowledgeItem, 'instrument' => $instrument])
            : route('knowledge.items.instrument.store', $knowledgeItem) }}"
      class="space-y-6">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">
                        {{ $isEdit ? 'Instrument Profile' : 'Create Instrument Profile' }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Attach an investment instrument record to this knowledge item.
                    </p>
                </div>

                @if($isEdit)
                    <div class="text-right text-xs text-gray-500 whitespace-nowrap">
                        <div>Instrument ID: {{ $instrument->id }}</div>
                        <div>Updated: {{ optional($instrument->updatedat)->format('d M Y H:i') ?? '—' }}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div>
                    <label for="instrumenttypeid" class="block text-sm font-medium text-gray-700 mb-1">Instrument Type</label>
                    <select id="instrumenttypeid" name="instrumenttypeid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">Select</option>
                        @foreach($instrumentTypes as $type)
                            <option value="{{ $type->id }}" @selected((string) old('instrumenttypeid', $instrument?->instrumenttypeid) === (string) $type->id)>
                                {{ $type->typename }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="exchangeid" class="block text-sm font-medium text-gray-700 mb-1">Exchange</label>
                    <select id="exchangeid" name="exchangeid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">Select</option>
                        @foreach($exchanges as $exchange)
                            <option value="{{ $exchange->id }}" @selected((string) old('exchangeid', $instrument?->exchangeid) === (string) $exchange->id)>
                                {{ $exchange->exchangename }}{{ $exchange->exchangecode ? ' (' . $exchange->exchangecode . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="symbol" class="block text-sm font-medium text-gray-700 mb-1">Symbol</label>
                    <input type="text" id="symbol" name="symbol" value="{{ old('symbol', $instrument?->symbol) }}" class="w-full rounded-md border-gray-300 shadow-sm text-sm" maxlength="30" required>
                </div>

                <div>
                    <label for="currencycode" class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                    <input type="text" id="currencycode" name="currencycode" value="{{ old('currencycode', $instrument?->currencycode) }}" class="w-full rounded-md border-gray-300 shadow-sm text-sm uppercase" maxlength="3">
                </div>

                <div class="md:col-span-2">
                    <label for="instrumentname" class="block text-sm font-medium text-gray-700 mb-1">Instrument Name</label>
                    <input type="text" id="instrumentname" name="instrumentname" value="{{ old('instrumentname', $instrument?->instrumentname ?? $knowledgeItem->itemname) }}" class="w-full rounded-md border-gray-300 shadow-sm text-sm" maxlength="255" required>
                </div>

                <div>
                    <label for="isin" class="block text-sm font-medium text-gray-700 mb-1">ISIN</label>
                    <input type="text" id="isin" name="isin" value="{{ old('isin', $instrument?->isin) }}" class="w-full rounded-md border-gray-300 shadow-sm text-sm" maxlength="20">
                </div>

                <div>
                    <label for="apiric" class="block text-sm font-medium text-gray-700 mb-1">API RIC</label>
                    <input type="text" id="apiric" name="apiric" value="{{ old('apiric', $instrument?->apiric) }}" class="w-full rounded-md border-gray-300 shadow-sm text-sm" maxlength="50">
                </div>

                <div>
                    <label for="fundmanager" class="block text-sm font-medium text-gray-700 mb-1">Fund Manager</label>
                    <input type="text" id="fundmanager" name="fundmanager" value="{{ old('fundmanager', $instrument?->fundmanager) }}" class="w-full rounded-md border-gray-300 shadow-sm text-sm" maxlength="150">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <input type="text" id="status" name="status" value="{{ old('status', $instrument?->status ?? 'active') }}" class="w-full rounded-md border-gray-300 shadow-sm text-sm" maxlength="30">
                </div>

                <div>
                    <label for="sector" class="block text-sm font-medium text-gray-700 mb-1">Sector</label>
                    <input type="text" id="sector" name="sector" value="{{ old('sector', $instrument?->sector) }}" class="w-full rounded-md border-gray-300 shadow-sm text-sm" maxlength="100">
                </div>

                <div>
                    <label for="industry" class="block text-sm font-medium text-gray-700 mb-1">Industry</label>
                    <input type="text" id="industry" name="industry" value="{{ old('industry', $instrument?->industry) }}" class="w-full rounded-md border-gray-300 shadow-sm text-sm" maxlength="100">
                </div>

                <div>
                    <label for="domicilecountrycode" class="block text-sm font-medium text-gray-700 mb-1">Domicile Country</label>
                    <input type="text" id="domicilecountrycode" name="domicilecountrycode" value="{{ old('domicilecountrycode', $instrument?->domicilecountrycode) }}" class="w-full rounded-md border-gray-300 shadow-sm text-sm uppercase" maxlength="2">
                </div>

                <div class="md:col-span-2 xl:col-span-4">
                    <label for="website" class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                    <input type="url" id="website" name="website" value="{{ old('website', $instrument?->website) }}" class="w-full rounded-md border-gray-300 shadow-sm text-sm" maxlength="255">
                </div>

                <div class="md:col-span-2 xl:col-span-4">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea id="notes" name="notes" rows="4" class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('notes', $instrument?->notes) }}</textarea>
                </div>

                <div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="hidden" name="isactive" value="0">
                        <input type="checkbox"
                               name="isactive"
                               value="1"
                               class="rounded border-gray-300 text-blue-600 shadow-sm"
                               @checked(old('isactive', $instrument?->isactive ?? true))>
                        Active
                    </label>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between gap-3">
            <p class="text-sm text-gray-500">
                Save the linked instrument profile for this knowledge item.
            </p>

            <button type="submit"
                    class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                {{ $isEdit ? 'Save Instrument' : 'Create Instrument' }}
            </button>
        </div>
    </div>
</form>

@if($instrument)
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Aliases</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Alternative identifiers or labels for this instrument.
                    </p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            @if($instrument->aliases->isEmpty())
                <div class="rounded-md bg-gray-50 px-4 py-3 text-sm text-gray-500">
                    No aliases recorded yet.
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Alias Value</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Alias Type</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($instrument->aliases as $alias)
                            <tr>
                                <td class="px-3 py-2">
                                    <form method="POST"
                                          action="{{ route('knowledge.items.instrument.aliases.update', [
                                              'knowledgeItem' => $knowledgeItem,
                                              'instrument' => $instrument,
                                              'alias' => $alias,
                                          ]) }}"
                                          class="flex items-center gap-2">
                                        @csrf
                                        @method('PUT')

                                        <input type="text"
                                               name="aliasvalue"
                                               value="{{ old("aliasvalue.{$alias->id}", $alias->aliasvalue) }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               maxlength="100"
                                               required>
                                </td>
                                <td class="px-3 py-2">
                                        <input type="text"
                                               name="aliastype"
                                               value="{{ old("aliastype.{$alias->id}", $alias->aliastype) }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               maxlength="50"
                                               placeholder="ticker, ric, sedol, internal">
                                </td>
                                <td class="px-3 py-2 text-center whitespace-nowrap">
                                        <button type="submit"
                                                class="px-3 py-1.5 bg-green-100 text-green-700 rounded hover:bg-green-200 text-sm">
                                            Save
                                        </button>
                                    </form>

                                    <form method="POST"
                                          action="{{ route('knowledge.items.instrument.aliases.destroy', [
                                              'knowledgeItem' => $knowledgeItem,
                                              'instrument' => $instrument,
                                              'alias' => $alias,
                                          ]) }}"
                                          class="inline-block mt-2"
                                          onsubmit="return confirm('Delete this alias?');">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach

                        <tr class="bg-blue-50">
                            <td class="px-3 py-2">
                                <form method="POST"
                                      action="{{ route('knowledge.items.instrument.aliases.store', [
                                          'knowledgeItem' => $knowledgeItem,
                                          'instrument' => $instrument,
                                      ]) }}"
                                      class="contents">
                                    @csrf

                                    <input type="text"
                                           name="aliasvalue"
                                           value="{{ old('aliasvalue') }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                           maxlength="100"
                                           placeholder="New alias"
                                           required>
                            </td>
                            <td class="px-3 py-2">
                                    <input type="text"
                                           name="aliastype"
                                           value="{{ old('aliastype') }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                           maxlength="50"
                                           placeholder="ticker, ric, sedol, internal">
                            </td>
                            <td class="px-3 py-2 text-center whitespace-nowrap">
                                    <button type="submit"
                                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                        Add Alias
                                    </button>
                                </form>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

@if($instrument)
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Price Observations</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Record manual or imported market prices for this instrument.
                    </p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            @if($instrument->priceObservations->isEmpty())
                <div class="rounded-md bg-gray-50 px-4 py-3 text-sm text-gray-500">
                    No price observations recorded yet.
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Observed</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Open</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">High</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Low</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Close</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Adj Close</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Volume</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Currency</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($instrument->priceObservations as $observation)
                            <tr>
                                <form method="POST"
                                      action="{{ route('knowledge.items.instrument.price-observations.update', [
                                          'knowledgeItem' => $knowledgeItem,
                                          'instrument' => $instrument,
                                          'priceObservation' => $observation,
                                      ]) }}">
                                    @csrf
                                    @method('PUT')

                                    <td class="px-3 py-2">
                                        <input type="date"
                                               name="observedon"
                                               value="{{ old('observedon', optional($observation->observedon)->format('Y-m-d')) }}"
                                               class="w-36 rounded-md border-gray-300 shadow-sm text-sm"
                                               required>
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number" step="0.000001" min="0"
                                               name="priceopen"
                                               value="{{ old('priceopen', $observation->priceopen) }}"
                                               class="w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number" step="0.000001" min="0"
                                               name="pricehigh"
                                               value="{{ old('pricehigh', $observation->pricehigh) }}"
                                               class="w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number" step="0.000001" min="0"
                                               name="pricelow"
                                               value="{{ old('pricelow', $observation->pricelow) }}"
                                               class="w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number" step="0.000001" min="0"
                                               name="priceclose"
                                               value="{{ old('priceclose', $observation->priceclose) }}"
                                               class="w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number" step="0.000001" min="0"
                                               name="adjustedclose"
                                               value="{{ old('adjustedclose', $observation->adjustedclose) }}"
                                               class="w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number" min="0"
                                               name="volume"
                                               value="{{ old('volume', $observation->volume) }}"
                                               class="w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="currencycode"
                                               value="{{ old('currencycode', $observation->currencycode ?? $instrument->currencycode) }}"
                                               class="w-20 rounded-md border-gray-300 shadow-sm text-sm uppercase"
                                               maxlength="3">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="pricesource"
                                               value="{{ old('pricesource', $observation->pricesource) }}"
                                               class="w-32 rounded-md border-gray-300 shadow-sm text-sm"
                                               maxlength="100">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="observationnotes"
                                               value="{{ old('observationnotes', $observation->observationnotes) }}"
                                               class="w-48 rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2 text-center whitespace-nowrap">
                                        <button type="submit"
                                                class="px-3 py-1.5 bg-green-100 text-green-700 rounded hover:bg-green-200 text-sm">
                                            Save
                                        </button>
                                </form>

                                <form method="POST"
                                      action="{{ route('knowledge.items.instrument.price-observations.destroy', [
                                          'knowledgeItem' => $knowledgeItem,
                                          'instrument' => $instrument,
                                          'priceObservation' => $observation,
                                      ]) }}"
                                      class="mt-2"
                                      onsubmit="return confirm('Delete this price observation?');">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm">
                                        Delete
                                    </button>
                                </form>
                                    </td>
                            </tr>
                        @endforeach

                        <tr class="bg-blue-50">
                            <form method="POST"
                                  action="{{ route('knowledge.items.instrument.price-observations.store', [
                                      'knowledgeItem' => $knowledgeItem,
                                      'instrument' => $instrument,
                                  ]) }}">
                                @csrf

                                <td class="px-3 py-2">
                                    <input type="date"
                                           name="observedon"
                                           value="{{ old('observedon') }}"
                                           class="w-36 rounded-md border-gray-300 shadow-sm text-sm"
                                           required>
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.000001" min="0" name="priceopen" value="{{ old('priceopen') }}" class="w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.000001" min="0" name="pricehigh" value="{{ old('pricehigh') }}" class="w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.000001" min="0" name="pricelow" value="{{ old('pricelow') }}" class="w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.000001" min="0" name="priceclose" value="{{ old('priceclose') }}" class="w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.000001" min="0" name="adjustedclose" value="{{ old('adjustedclose') }}" class="w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" min="0" name="volume" value="{{ old('volume') }}" class="w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="text"
                                           name="currencycode"
                                           value="{{ old('currencycode', $instrument->currencycode) }}"
                                           class="w-20 rounded-md border-gray-300 shadow-sm text-sm uppercase"
                                           maxlength="3">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="text" name="pricesource" value="{{ old('pricesource') }}" class="w-32 rounded-md border-gray-300 shadow-sm text-sm" maxlength="100">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="text" name="observationnotes" value="{{ old('observationnotes') }}" class="w-48 rounded-md border-gray-300 shadow-sm text-sm">
                                </td>

                                <td class="px-3 py-2 text-center whitespace-nowrap">
                                    <button type="submit"
                                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                        Add
                                    </button>
                                </td>
                            </form>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
@if($instrument)
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Corporate Actions</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Record splits, consolidations, mergers, renames, and similar structural events.
                    </p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            @if($instrument->corporateActions->isEmpty())
                <div class="rounded-md bg-gray-50 px-4 py-3 text-sm text-gray-500">
                    No corporate actions recorded yet.
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ratio From</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ratio To</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Old Value</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">New Value</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($instrument->corporateActions as $corporateAction)
                            <tr>
                                <form method="POST"
                                      action="{{ route('knowledge.items.instrument.corporate-actions.update', [
                                          'knowledgeItem' => $knowledgeItem,
                                          'instrument' => $instrument,
                                          'corporateAction' => $corporateAction,
                                      ]) }}">
                                    @csrf
                                    @method('PUT')

                                    <td class="px-3 py-2">
                                        <input type="date"
                                               name="actiondate"
                                               value="{{ old('actiondate', optional($corporateAction->actiondate)->format('Y-m-d')) }}"
                                               class="w-36 rounded-md border-gray-300 shadow-sm text-sm"
                                               required>
                                    </td>

                                    <td class="px-3 py-2">
                                        <select name="actiontype"
                                                class="w-40 rounded-md border-gray-300 shadow-sm text-sm"
                                                required>
                                            <option value="">Select</option>
                                            @foreach($corporateActionTypeOptions as $value => $label)
                                                <option value="{{ $value }}"
                                                    @selected(old('actiontype', $corporateAction->actiontype) === $value)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number"
                                               step="0.000001"
                                               min="0"
                                               name="ratiofrom"
                                               value="{{ old('ratiofrom', $corporateAction->ratiofrom) }}"
                                               class="w-24 rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number"
                                               step="0.000001"
                                               min="0"
                                               name="ratioto"
                                               value="{{ old('ratioto', $corporateAction->ratioto) }}"
                                               class="w-24 rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="oldvalue"
                                               value="{{ old('oldvalue', $corporateAction->oldvalue) }}"
                                               class="w-32 rounded-md border-gray-300 shadow-sm text-sm"
                                               maxlength="255">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="newvalue"
                                               value="{{ old('newvalue', $corporateAction->newvalue) }}"
                                               class="w-32 rounded-md border-gray-300 shadow-sm text-sm"
                                               maxlength="255">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="notes"
                                               value="{{ old('notes', $corporateAction->notes) }}"
                                               class="w-48 rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2 text-center whitespace-nowrap">
                                        <button type="submit"
                                                class="px-3 py-1.5 bg-green-100 text-green-700 rounded hover:bg-green-200 text-sm">
                                            Save
                                        </button>
                                </form>

                                <form method="POST"
                                      action="{{ route('knowledge.items.instrument.corporate-actions.destroy', [
                                          'knowledgeItem' => $knowledgeItem,
                                          'instrument' => $instrument,
                                          'corporateAction' => $corporateAction,
                                      ]) }}"
                                      class="mt-2"
                                      onsubmit="return confirm('Delete this corporate action?');">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm">
                                        Delete
                                    </button>
                                </form>
                                    </td>
                            </tr>
                        @endforeach

                        <tr class="bg-blue-50">
                            <form method="POST"
                                  action="{{ route('knowledge.items.instrument.corporate-actions.store', [
                                      'knowledgeItem' => $knowledgeItem,
                                      'instrument' => $instrument,
                                  ]) }}">
                                @csrf

                                <td class="px-3 py-2">
                                    <input type="date"
                                           name="actiondate"
                                           value="{{ old('actiondate') }}"
                                           class="w-36 rounded-md border-gray-300 shadow-sm text-sm"
                                           required>
                                </td>

                                <td class="px-3 py-2">
                                    <select name="actiontype"
                                            class="w-40 rounded-md border-gray-300 shadow-sm text-sm"
                                            required>
                                        <option value="">Select</option>
                                        @foreach($corporateActionTypeOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('actiontype') === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.000001" min="0" name="ratiofrom" value="{{ old('ratiofrom') }}" class="w-24 rounded-md border-gray-300 shadow-sm text-sm">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.000001" min="0" name="ratioto" value="{{ old('ratioto') }}" class="w-24 rounded-md border-gray-300 shadow-sm text-sm">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="text" name="oldvalue" value="{{ old('oldvalue') }}" class="w-32 rounded-md border-gray-300 shadow-sm text-sm" maxlength="255">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="text" name="newvalue" value="{{ old('newvalue') }}" class="w-32 rounded-md border-gray-300 shadow-sm text-sm" maxlength="255">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="text" name="notes" value="{{ old('notes') }}" class="w-48 rounded-md border-gray-300 shadow-sm text-sm">
                                </td>

                                <td class="px-3 py-2 text-center whitespace-nowrap">
                                    <button type="submit"
                                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                        Add
                                    </button>
                                </td>
                            </form>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
@if($instrument)
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Transactions</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Record portfolio buy, sell, transfer, and related instrument transactions.
                    </p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            @if($instrument->transactions->isEmpty())
                <div class="rounded-md bg-gray-50 px-4 py-3 text-sm text-gray-500">
                    No transactions recorded yet.
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Portfolio</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Trade Date</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Settlement</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Price/Unit</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Gross</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Brokerage</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fees</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Net Cash</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Currency</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">FX AUD</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($instrument->transactions as $transaction)
                            <tr>
                                <form method="POST"
                                      action="{{ route('knowledge.items.instrument.transactions.update', [
                                          'knowledgeItem' => $knowledgeItem,
                                          'instrument' => $instrument,
                                          'transaction' => $transaction,
                                      ]) }}">
                                    @csrf
                                    @method('PUT')

                                    <td class="px-3 py-2">
                                        <select name="portfolioid"
                                                class="w-40 rounded-md border-gray-300 shadow-sm text-sm"
                                                required>
                                            <option value="">Select</option>
                                            @foreach($portfolios as $portfolio)
                                                <option value="{{ $portfolio->id }}"
                                                    @selected((string) old('portfolioid', $transaction->portfolioid) === (string) $portfolio->id)>
                                                    {{ $portfolio->portfolioname }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="date"
                                               name="transactiondate"
                                               value="{{ old('transactiondate', optional($transaction->transactiondate)->format('Y-m-d')) }}"
                                               class="w-36 rounded-md border-gray-300 shadow-sm text-sm"
                                               required>
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="date"
                                               name="settlementdate"
                                               value="{{ old('settlementdate', optional($transaction->settlementdate)->format('Y-m-d')) }}"
                                               class="w-36 rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2">
                                        <select name="transactiontype"
                                                class="w-40 rounded-md border-gray-300 shadow-sm text-sm"
                                                required>
                                            <option value="">Select</option>
                                            @foreach($transactionTypeOptions as $value => $label)
                                                <option value="{{ $value }}"
                                                    @selected(old('transactiontype', $transaction->transactiontype) === $value)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number" step="0.000001" min="0"
                                               name="quantity"
                                               value="{{ old('quantity', $transaction->quantity) }}"
                                               class="w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number" step="0.000001" min="0"
                                               name="priceperunit"
                                               value="{{ old('priceperunit', $transaction->priceperunit) }}"
                                               class="w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01"
                                               name="grossamount"
                                               value="{{ old('grossamount', $transaction->grossamount) }}"
                                               class="w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01"
                                               name="brokerage"
                                               value="{{ old('brokerage', $transaction->brokerage) }}"
                                               class="w-24 rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01"
                                               name="taxesandfees"
                                               value="{{ old('taxesandfees', $transaction->taxesandfees) }}"
                                               class="w-24 rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01"
                                               name="netcashamount"
                                               value="{{ old('netcashamount', $transaction->netcashamount) }}"
                                               class="w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="currencycode"
                                               value="{{ old('currencycode', $transaction->currencycode ?? $instrument->currencycode) }}"
                                               class="w-20 rounded-md border-gray-300 shadow-sm text-sm uppercase"
                                               maxlength="3">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number" step="0.00000001" min="0"
                                               name="fxrateaud"
                                               value="{{ old('fxrateaud', $transaction->fxrateaud) }}"
                                               class="w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="externalreference"
                                               value="{{ old('externalreference', $transaction->externalreference) }}"
                                               class="w-32 rounded-md border-gray-300 shadow-sm text-sm"
                                               maxlength="100">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="notes"
                                               value="{{ old('notes', $transaction->notes) }}"
                                               class="w-48 rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2 text-center whitespace-nowrap">
                                        <button type="submit"
                                                class="px-3 py-1.5 bg-green-100 text-green-700 rounded hover:bg-green-200 text-sm">
                                            Save
                                        </button>
                                </form>

                                <form method="POST"
                                      action="{{ route('knowledge.items.instrument.transactions.destroy', [
                                          'knowledgeItem' => $knowledgeItem,
                                          'instrument' => $instrument,
                                          'transaction' => $transaction,
                                      ]) }}"
                                      class="mt-2"
                                      onsubmit="return confirm('Delete this transaction?');">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm">
                                        Delete
                                    </button>
                                </form>
                                    </td>
                            </tr>
                        @endforeach

                        <tr class="bg-blue-50">
                            <form method="POST"
                                  action="{{ route('knowledge.items.instrument.transactions.store', [
                                      'knowledgeItem' => $knowledgeItem,
                                      'instrument' => $instrument,
                                  ]) }}">
                                @csrf

                                <td class="px-3 py-2">
                                    <select name="portfolioid"
                                            class="w-40 rounded-md border-gray-300 shadow-sm text-sm"
                                            required>
                                        <option value="">Select</option>
                                        @foreach($portfolios as $portfolio)
                                            <option value="{{ $portfolio->id }}"
                                                @selected((string) old('portfolioid') === (string) $portfolio->id)>
                                                {{ $portfolio->portfolioname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-3 py-2">
                                    <input type="date"
                                           name="transactiondate"
                                           value="{{ old('transactiondate') }}"
                                           class="w-36 rounded-md border-gray-300 shadow-sm text-sm"
                                           required>
                                </td>

                                <td class="px-3 py-2">
                                    <input type="date"
                                           name="settlementdate"
                                           value="{{ old('settlementdate') }}"
                                           class="w-36 rounded-md border-gray-300 shadow-sm text-sm">
                                </td>

                                <td class="px-3 py-2">
                                    <select name="transactiontype"
                                            class="w-40 rounded-md border-gray-300 shadow-sm text-sm"
                                            required>
                                        <option value="">Select</option>
                                        @foreach($transactionTypeOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('transactiontype') === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.000001" min="0" name="quantity" value="{{ old('quantity') }}" class="w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.000001" min="0" name="priceperunit" value="{{ old('priceperunit') }}" class="w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" name="grossamount" value="{{ old('grossamount') }}" class="w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" name="brokerage" value="{{ old('brokerage') }}" class="w-24 rounded-md border-gray-300 shadow-sm text-sm">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" name="taxesandfees" value="{{ old('taxesandfees') }}" class="w-24 rounded-md border-gray-300 shadow-sm text-sm">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" name="netcashamount" value="{{ old('netcashamount') }}" class="w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="text"
                                           name="currencycode"
                                           value="{{ old('currencycode', $instrument->currencycode) }}"
                                           class="w-20 rounded-md border-gray-300 shadow-sm text-sm uppercase"
                                           maxlength="3">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="number" step="0.00000001" min="0" name="fxrateaud" value="{{ old('fxrateaud') }}" class="w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="text" name="externalreference" value="{{ old('externalreference') }}" class="w-32 rounded-md border-gray-300 shadow-sm text-sm" maxlength="100">
                                </td>

                                <td class="px-3 py-2">
                                    <input type="text" name="notes" value="{{ old('notes') }}" class="w-48 rounded-md border-gray-300 shadow-sm text-sm">
                                </td>

                                <td class="px-3 py-2 text-center whitespace-nowrap">
                                    <button type="submit"
                                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                        Add
                                    </button>
                                </td>
                            </form>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif