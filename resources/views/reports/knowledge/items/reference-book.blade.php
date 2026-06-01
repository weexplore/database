{{-- resources/views/reports/places/reference-book.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $reportTitle ?? 'Place Reference Book' }}
                </h2>

                @if(!empty($reportSubtitle))
                    <p class="mt-1 text-sm text-gray-500">{{ $reportSubtitle }}</p>
                @endif
            </div>

            <div class="flex items-center gap-2">
                @if(!empty($returnTo))
                    <a href="{{ $returnTo }}"
                       class="inline-flex items-center rounded-md bg-gray-200 px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-300">
                        Back
                    </a>
                @else
                    <button type="button"
                            onclick="window.close(); setTimeout(() => history.back(), 150);"
                            class="inline-flex items-center rounded-md bg-gray-200 px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-300">
                        Close
                    </button>
                @endif

                <button type="button"
                        onclick="window.print()"
                        class="inline-flex items-center rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                    Print
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            @if(!empty($filters) && collect($filters)->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg border border-slate-200">
                    <div class="px-6 py-4">
                        <h3 class="text-sm font-semibold text-slate-900">Applied Filters</h3>

                        <div class="mt-3 flex flex-wrap gap-2 text-xs text-slate-600">
                            @if(!empty($filters['search']))
                                <span class="rounded-full bg-slate-100 px-2.5 py-1">
                                    Search: {{ $filters['search'] }}
                                </span>
                            @endif

                            @if(array_key_exists('country_id', $filters) && filled($filters['country_id']))
                                <span class="rounded-full bg-slate-100 px-2.5 py-1">
                                    Country ID: {{ $filters['country_id'] }}
                                </span>
                            @endif

                            @if(array_key_exists('state_id', $filters) && filled($filters['state_id']))
                                <span class="rounded-full bg-slate-100 px-2.5 py-1">
                                    State ID: {{ $filters['state_id'] }}
                                </span>
                            @endif

                            @if(array_key_exists('region_id', $filters) && filled($filters['region_id']))
                                <span class="rounded-full bg-slate-100 px-2.5 py-1">
                                    Region ID: {{ $filters['region_id'] }}
                                </span>
                            @endif

                            @if(!empty($filters['placetype']))
                                <span class="rounded-full bg-blue-50 px-2.5 py-1 text-blue-700">
                                    Type: {{ $filters['placetype'] }}
                                </span>
                            @endif

                            @if(isset($filters['status']) && $filters['status'] !== '')
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-emerald-700">
                                    Status: {{ (string) $filters['status'] === '1' ? 'Active' : ((string) $filters['status'] === '0' ? 'Inactive' : $filters['status']) }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @forelse($places as $place)
                @php
                    $destinationCount = $place->destinations->count();
                    $destinationItemCount = $place->destinations->sum(fn ($destination) => $destination->items->count());
                    $fuelStopCount = $place->fuelStops->count();
                    $tripStayCount = $place->tripStays->count();
                @endphp

                <div class="bg-white shadow-sm sm:rounded-lg border border-slate-200 overflow-hidden">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h1 class="text-2xl font-semibold text-slate-900">
                                    {{ $place->placename }}
                                </h1>

                                <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-600">
                                    @if($place->placetype)
                                        <span class="rounded-full bg-blue-50 px-2.5 py-1 text-blue-700">
                                            Type: {{ ucwords(str_replace('_', ' ', $place->placetype)) }}
                                        </span>
                                    @endif

                                    <span class="rounded-full {{ $place->isactive ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }} px-2.5 py-1">
                                        Status: {{ $place->isactive ? 'Active' : 'Inactive' }}
                                    </span>

                                    @if($place->country)
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1">
                                            Country: {{ $place->country->countryname }}
                                        </span>
                                    @endif

                                    @if($place->state)
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1">
                                            State: {{ $place->state->statename }}
                                        </span>
                                    @endif

                                    @if($place->region)
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1">
                                            Region: {{ $place->region->regionname }}
                                        </span>
                                    @endif

                                    @if($place->locality)
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1">
                                            Locality: {{ $place->locality }}
                                        </span>
                                    @endif

                                    @if($place->postcode)
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1">
                                            Postcode: {{ $place->postcode }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="shrink-0">
                                <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600">
                                    <div>Destinations: {{ $destinationCount }}</div>
                                    <div class="mt-1">Destination Items: {{ $destinationItemCount }}</div>
                                    <div class="mt-1">Fuel Stops: {{ $fuelStopCount }}</div>
                                    <div class="mt-1">Trip Stays: {{ $tripStayCount }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-5 space-y-8">
                        <section>
                            <h3 class="text-base font-semibold text-slate-900">Place Details</h3>

                            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-700">
                                <div class="rounded-lg border border-slate-200 px-4 py-3">
                                    <dl class="space-y-2">
                                        <div class="flex justify-between gap-4">
                                            <dt class="text-slate-500">Place ID</dt>
                                            <dd class="text-slate-900">{{ $place->id }}</dd>
                                        </div>

                                        <div class="flex justify-between gap-4">
                                            <dt class="text-slate-500">Type</dt>
                                            <dd class="text-slate-900">{{ $place->placetype ? ucwords(str_replace('_', ' ', $place->placetype)) : '—' }}</dd>
                                        </div>

                                        <div class="flex justify-between gap-4">
                                            <dt class="text-slate-500">Status</dt>
                                            <dd class="text-slate-900">{{ $place->isactive ? 'Active' : 'Inactive' }}</dd>
                                        </div>

                                        <div class="flex justify-between gap-4">
                                            <dt class="text-slate-500">Source Quality</dt>
                                            <dd class="text-slate-900">{{ $place->sourcequality ?: '—' }}</dd>
                                        </div>
                                    </dl>
                                </div>

                                <div class="rounded-lg border border-slate-200 px-4 py-3">
                                    <dl class="space-y-2">
                                        <div class="flex justify-between gap-4">
                                            <dt class="text-slate-500">Latitude</dt>
                                            <dd class="text-slate-900">{{ $place->latitude ?? '—' }}</dd>
                                        </div>

                                        <div class="flex justify-between gap-4">
                                            <dt class="text-slate-500">Longitude</dt>
                                            <dd class="text-slate-900">{{ $place->longitude ?? '—' }}</dd>
                                        </div>

                                        <div class="flex justify-between gap-4">
                                            <dt class="text-slate-500">Created</dt>
                                            <dd class="text-slate-900">{{ optional($place->createdat)->format('j M Y') ?: '—' }}</dd>
                                        </div>

                                        <div class="flex justify-between gap-4">
                                            <dt class="text-slate-500">Updated</dt>
                                            <dd class="text-slate-900">{{ optional($place->updatedat)->format('j M Y') ?: '—' }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </section>

                        @if($place->addressline1 || $place->addressline2 || $place->locality || $place->postcode)
                            <section>
                                <h3 class="text-base font-semibold text-slate-900">Address</h3>

                                <div class="mt-3 rounded-lg border border-slate-200 px-4 py-4 text-sm text-slate-700 space-y-1">
                                    @if($place->addressline1)
                                        <div>{{ $place->addressline1 }}</div>
                                    @endif

                                    @if($place->addressline2)
                                        <div>{{ $place->addressline2 }}</div>
                                    @endif

                                    <div>
                                        {{ collect([$place->locality, $place->postcode])->filter()->join(' ') ?: '—' }}
                                    </div>

                                    <div>
                                        {{ collect([
                                            $place->state?->statename,
                                            $place->country?->countryname,
                                        ])->filter()->join(', ') ?: '—' }}
                                    </div>
                                </div>
                            </section>
                        @endif

                        @if(filled($place->accessnotes))
                            <section>
                                <h3 class="text-base font-semibold text-slate-900">Access Notes</h3>
                                <div class="mt-2 text-sm leading-6 text-slate-700 markdown-content">
                                    @include('partials.markdown.rendered-block', [
                                        'content' => $place->accessnotes,
                                    ])
                                </div>
                            </section>
                        @endif

                        @if(filled($place->generalnotes))
                            <section>
                                <h3 class="text-base font-semibold text-slate-900">General Notes</h3>
                                <div class="mt-2 text-sm leading-6 text-slate-700 markdown-content">
                                    @include('partials.markdown.rendered-block', [
                                        'content' => $place->generalnotes,
                                    ])
                                </div>
                            </section>
                        @endif

                        @if($place->destinations->isNotEmpty())
                            <section>
                                <h3 class="text-base font-semibold text-slate-900">Destinations</h3>

                                <div class="mt-3 space-y-4">
                                    @foreach($place->destinations as $destination)
                                        <div class="rounded-lg border border-slate-200 px-4 py-4">
                                            <div class="flex items-start justify-between gap-4">
                                                <div>
                                                    <div class="text-sm font-semibold text-slate-900">
                                                        {{ $destination->destinationname ?: 'Unnamed destination' }}
                                                    </div>

                                                    <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-600">
                                                        @if($destination->destinationtype)
                                                            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-blue-700 border border-blue-200">
                                                                Type: {{ ucwords(str_replace('_', ' ', $destination->destinationtype)) }}
                                                            </span>
                                                        @endif

                                                        <span class="rounded-full {{ !empty($destination->hasvisited) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }} px-2.5 py-1">
                                                            Has Visited: {{ !empty($destination->hasvisited) ? 'Yes' : 'No' }}
                                                        </span>

                                                        @if(!empty($destination->isactive) || (string) ($destination->isactive ?? '') === '0')
                                                            <span class="rounded-full {{ $destination->isactive ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }} px-2.5 py-1">
                                                                Status: {{ $destination->isactive ? 'Active' : 'Inactive' }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="shrink-0 text-xs text-slate-500">
                                                    {{ $destination->items->count() }} item{{ $destination->items->count() === 1 ? '' : 's' }}
                                                </div>
                                            </div>

                                            @if(filled($destination->summary))
                                                <div class="mt-3">
                                                    <div class="text-xs font-medium text-slate-500 mb-1">Summary</div>
                                                    <div class="text-sm text-slate-700 markdown-content">
                                                        @include('partials.markdown.rendered-block', [
                                                            'content' => $destination->summary,
                                                        ])
                                                    </div>
                                                </div>
                                            @endif

                                            @if(filled($destination->description))
                                                <div class="mt-3">
                                                    <div class="text-xs font-medium text-slate-500 mb-1">Description</div>
                                                    <div class="text-sm text-slate-700 markdown-content">
                                                        @include('partials.markdown.rendered-block', [
                                                            'content' => $destination->description,
                                                        ])
                                                    </div>
                                                </div>
                                            @endif

                                            @if(filled($destination->notes))
                                                <div class="mt-3">
                                                    <div class="text-xs font-medium text-slate-500 mb-1">Notes</div>
                                                    <div class="text-sm text-slate-700 markdown-content">
                                                        @include('partials.markdown.rendered-block', [
                                                            'content' => $destination->notes,
                                                        ])
                                                    </div>
                                                </div>
                                            @endif

                                            @if($destination->items->isNotEmpty())
                                                <div class="mt-4">
                                                    <div class="text-xs font-medium text-slate-500 mb-2">Destination Items</div>

                                                    <div class="space-y-2">
                                                        @foreach($destination->items as $item)
                                                            <div class="rounded-md bg-slate-50 border border-slate-200 px-3 py-3">
                                                                <div class="flex items-start justify-between gap-3">
                                                                    <div>
                                                                        <div class="text-sm font-medium text-slate-900">
                                                                            {{ $item->itemname ?: 'Unnamed item' }}
                                                                        </div>

                                                                        @if($item->itemTypes->isNotEmpty())
                                                                            <div class="mt-1 text-xs text-slate-500">
                                                                                Type: {{ $item->itemTypes->pluck('typename')->filter()->join(', ') }}
                                                                            </div>
                                                                        @endif
                                                                    </div>

                                                                    @if(!empty($item->isactive) || (string) ($item->isactive ?? '') === '0')
                                                                        <div class="text-xs {{ $item->isactive ? 'text-emerald-700' : 'text-rose-700' }}">
                                                                            {{ $item->isactive ? 'Active' : 'Inactive' }}
                                                                        </div>
                                                                    @endif
                                                                </div>

                                                                @if(filled($item->itemsummary))
                                                                    <div class="mt-2 text-sm text-slate-700 markdown-content">
                                                                        @include('partials.markdown.rendered-block', [
                                                                            'content' => $item->itemsummary,
                                                                        ])
                                                                    </div>
                                                                @endif

                                                                @if(filled($item->description))
                                                                    <div class="mt-2 text-sm text-slate-700 markdown-content">
                                                                        @include('partials.markdown.rendered-block', [
                                                                            'content' => $item->description,
                                                                        ])
                                                                    </div>
                                                                @endif

                                                                @if(filled($item->notes))
                                                                    <div class="mt-2 text-sm text-slate-700 markdown-content">
                                                                        @include('partials.markdown.rendered-block', [
                                                                            'content' => $item->notes,
                                                                        ])
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @else
                                                <div class="mt-4 text-sm text-slate-500">
                                                    No destination items linked to this destination.
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        @if($place->fuelStops->isNotEmpty())
                            <section>
                                <h3 class="text-base font-semibold text-slate-900">Fuel Stops</h3>

                                <div class="mt-3 space-y-3">
                                    @foreach($place->fuelStops as $fuelStop)
                                        <div class="rounded-lg border border-slate-200 px-4 py-3">
                                            <div class="text-sm font-medium text-slate-900">
                                                {{ $fuelStop->stopname ?: 'Fuel stop' }}
                                            </div>

                                            <div class="mt-1 flex flex-wrap gap-3 text-xs text-slate-500">
                                                <span>Brand: {{ $fuelStop->brandname ?: '—' }}</span>

                                                @if(!is_null($fuelStop->isactive))
                                                    <span>Status: {{ $fuelStop->isactive ? 'Active' : 'Inactive' }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        @if($place->tripStays->isNotEmpty())
                            <section>
                                <h3 class="text-base font-semibold text-slate-900">Trip Stays</h3>

                                <div class="mt-3 space-y-3">
                                    @foreach($place->tripStays as $tripStay)
                                        @php
                                            $checkIn = $tripStay->checkindate
                                                ? \Illuminate\Support\Carbon::parse($tripStay->checkindate)->format('j M Y')
                                                : null;

                                            $checkOut = $tripStay->checkoutdate
                                                ? \Illuminate\Support\Carbon::parse($tripStay->checkoutdate)->format('j M Y')
                                                : null;
                                        @endphp

                                        <div class="rounded-lg border border-slate-200 px-4 py-3">
                                            <div class="text-sm font-medium text-slate-900">
                                                {{ $tripStay->stayname ?: 'Trip stay' }}
                                            </div>

                                            <div class="mt-1 flex flex-wrap gap-3 text-xs text-slate-500">
                                                <span>Trip: {{ $tripStay->trip?->tripname ?: '—' }}</span>
                                                <span>Stay: {{ $checkIn ?: '—' }}{{ $checkOut ? ' to ' . $checkOut : '' }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-sm sm:rounded-lg border border-slate-200">
                    <div class="px-6 py-10 text-center">
                        <h3 class="text-base font-semibold text-slate-900">No places found</h3>
                        <p class="mt-2 text-sm text-slate-500">
                            No places matched the current report filters.
                        </p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    @include('partials.markdown.markdown-styles')
</x-app-layout>