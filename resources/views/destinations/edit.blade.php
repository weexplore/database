<x-app-layout>
    @php
        $returnTo = request('return_to', route('destinations.index', request()->only([
            'placeid', 'destinationtype', 'featured', 'visited',
            'visitinterestlevel', 'search', 'page',
        ])));

        $tabs = [
            'destination' => 'Destination',
            'destination-items' => 'Destination Items',
            'record-information' => 'Record Information',
            'attachments' => 'Attachments',
            'internet-research' => 'Internet Research',
        ];

        $requestedTab = old('tab', request('tab', 'destination'));
        $activeTab = is_string($requestedTab) && array_key_exists($requestedTab, $tabs)
            ? $requestedTab : 'destination';

        if (old('_source_form_action')) {
            $activeTab = 'internet-research';
        }

        $tabCounts = [
            'destination-items' => $destination->items->count(),
            'attachments' => $destination->attachments->count(),
            'internet-research' => $destination->sources->count(),
        ];

        $tabReturnUrls = [];
        foreach ($tabs as $key => $label) {
            $tabReturnUrls[$key] = route('destinations.edit', [
                'destination' => $destination,
                'tab' => $key,
                'return_to' => $returnTo,
            ]);
        }
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Destination</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $destination->destinationname }}</p>
            </div>
            <a href="{{ $returnTo }}"
               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">Back</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6"
             id="destination-tab-workspace"
             data-active-tab="{{ $activeTab }}">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white shadow-sm sm:rounded-lg px-6 py-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Destination</p>
                        <h3 class="mt-1 text-xl font-semibold text-gray-900 break-words">{{ $destination->destinationname }}</h3>
                        <p class="mt-1 text-xs text-gray-500">
                            ID: {{ $destination->id }}
                            · Updated: {{ optional($destination->updatedat)->format('d M Y H:i') ?: '—' }}
                        </p>
                        @if ($destination->place)
                            <p class="mt-2 text-sm text-gray-500">
                                Place:
                                <a href="{{ route('places.edit', [
                                    'place' => $destination->place,
                                    'return_to' => $tabReturnUrls['destination'],
                                ]) }}"
                                   class="text-blue-600 hover:underline">{{ $destination->place->placename }}</a>
                            </p>
                        @endif
                    </div>
                    <span id="destination-active-tab-label"
                          class="shrink-0 inline-flex px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-medium">{{ $tabs[$activeTab] }}</span>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <nav class="flex flex-wrap gap-2 px-4 sm:px-6 py-3"
                     role="tablist" aria-label="Destination sections">
                    @foreach ($tabs as $tabKey => $tabLabel)
                        <a href="{{ $tabReturnUrls[$tabKey] }}"
                           id="destination-tab-{{ $tabKey }}"
                           data-destination-tab="{{ $tabKey }}"
                           data-tab-label="{{ $tabLabel }}"
                           role="tab"
                           aria-controls="destination-panel-{{ $tabKey }}"
                           aria-selected="{{ $activeTab === $tabKey ? 'true' : 'false' }}"
                           tabindex="{{ $activeTab === $tabKey ? '0' : '-1' }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium {{ $activeTab === $tabKey ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            <span>{{ $tabLabel }}</span>
                            @if (array_key_exists($tabKey, $tabCounts))
                                <span data-tab-count="{{ $tabKey }}"
                                      class="inline-flex min-w-5 items-center justify-center rounded-full px-1.5 py-0.5 text-[11px] font-semibold {{ $activeTab === $tabKey ? 'bg-white/20 text-white' : 'bg-white text-gray-700 ring-1 ring-inset ring-gray-300' }}">{{ $tabCounts[$tabKey] }}</span>
                            @endif
                        </a>
                    @endforeach
                </nav>
            </div>

            @foreach ($tabs as $tabKey => $tabLabel)
                <section id="destination-panel-{{ $tabKey }}"
                         data-destination-panel="{{ $tabKey }}"
                         role="tabpanel"
                         aria-labelledby="destination-tab-{{ $tabKey }}"
                         @if ($activeTab !== $tabKey) hidden @endif>
                    @include('destinations.partials.tabs.' . $tabKey)
                </section>
            @endforeach
        </div>
    </div>

    @include('partials.markdown.markdown-styles')
    @include('destinations.partials.tabs-script')
    @include('destinations.partials.internet-sources-script')
    @include('destinations.partials.source-capture-script')
    @include('destinations.partials.compose-fields-script')
    @include('destinations.partials.item-candidates-script')

</x-app-layout>
