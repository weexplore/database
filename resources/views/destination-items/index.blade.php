<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Destination Items
        </h2>
    </x-slot>

    @php
        $returnTo = request()->fullUrl();

        $interestStyles = [
            'must_visit' => [
                'label' => 'Must visit',
                'badge' => 'bg-rose-100 text-rose-800',
                'row' => 'bg-rose-50',
            ],
            'very_interested' => [
                'label' => 'Very interested',
                'badge' => 'bg-amber-100 text-amber-800',
                'row' => 'bg-amber-50',
            ],
            'interested' => [
                'label' => 'Interested',
                'badge' => 'bg-sky-100 text-sky-800',
                'row' => 'bg-sky-50',
            ],
            'if_nearby' => [
                'label' => 'If nearby',
                'badge' => 'bg-slate-100 text-slate-700',
                'row' => '',
            ],
        ];
    @endphp

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <form
                    method="GET"
                    action="{{ route('destination-items.index') }}"
                    class="grid grid-cols-1 gap-4 md:grid-cols-3 xl:grid-cols-6"
                >
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">
                            Search
                        </label>

                        <input
                            type="text"
                            name="search"
                            id="search"
                            value="{{ request('search') }}"
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                        >
                    </div>

                    <div>
                        <label for="destination_id" class="block text-sm font-medium text-gray-700">
                            Destination
                        </label>

                        <select
                            name="destination_id"
                            id="destination_id"
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                        >
                            <option value="">All</option>

                            @foreach ($destinations as $destination)
                                <option
                                    value="{{ $destination->id }}"
                                    @selected((string) request('destination_id') === (string) $destination->id)
                                >
                                    {{ $destination->destinationname }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="place_id" class="block text-sm font-medium text-gray-700">
                            Place
                        </label>

                        <select
                            name="place_id"
                            id="place_id"
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                        >
                            <option value="">All</option>

                            @foreach ($places as $place)
                                <option
                                    value="{{ $place->id }}"
                                    @selected((string) request('place_id') === (string) $place->id)
                                >
                                    {{ $place->placename }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="itemtype_id" class="block text-sm font-medium text-gray-700">
                            Type
                        </label>

                        <select
                            name="itemtype_id"
                            id="itemtype_id"
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                        >
                            <option value="">All</option>

                            @foreach ($itemTypes as $itemType)
                                <option
                                    value="{{ $itemType->id }}"
                                    @selected((string) request('itemtype_id') === (string) $itemType->id)
                                >
                                    {{ $itemType->typename }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">
                            Status
                        </label>

                        <select
                            name="status"
                            id="status"
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                        >
                            <option value="">All</option>
                            <option value="1" @selected(request('status') === '1')>
                                Active
                            </option>
                            <option value="0" @selected(request('status') === '0')>
                                Inactive
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="visitinterestlevel" class="block text-sm font-medium text-gray-700">
                            Travel Interest
                        </label>

                        <select
                            name="visitinterestlevel"
                            id="visitinterestlevel"
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                        >
                            <option value="">All</option>

                            @foreach ($visitInterestOptions as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected(request('visitinterestlevel') === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-2 md:col-span-3 xl:col-span-6">
                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                type="submit"
                                class="px-4 py-2 bg-slate-700 text-white rounded hover:bg-slate-800"
                            >
                                Filter
                            </button>

                            <a
                                href="{{ route('destination-items.index') }}"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
                            >
                                Reset
                            </a>
                        </div>

                        <a
                            href="{{ route('destination-items.index', array_merge(
                                request()->query(),
                                ['show_create' => 1]
                            )) }}"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                        >
                            Add Destination Item
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                @if ($showCreate)
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Add Destination Item
                        </h3>

                        @php
                            $destinationItem = null;
                        @endphp

                        <form
                            method="POST"
                            action="{{ route('destination-items.store') }}"
                            class="space-y-6"
                        >
                            @csrf

                            @include('destination-items._form')

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                                <a
                                    href="{{ route('destination-items.index') }}"
                                    class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm"
                                >
                                    Cancel
                                </a>

                                <button
                                    type="submit"
                                    class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                                >
                                    Save Destination Item
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Item
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Destination
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Type
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Place
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Travel Interest
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Booking
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Active
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Coordinates
                                </th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 bg-white">
                            @if ($items->count() > 0)
                                @foreach ($items as $item)
                                    @php
                                        $interestStyle = $interestStyles[$item->visitinterestlevel] ?? null;
                                    @endphp

                                    <tr class="{{ $interestStyle['row'] ?? '' }}">
                                        <td class="px-4 py-3">
                                            <a
                                                href="{{ route('destination-items.edit', [
                                                    'destinationItem' => $item,
                                                    'return_to' => $returnTo,
                                                ]) }}"
                                                class="block rounded-md border border-gray-200 bg-gray-100 px-3 py-2 font-medium text-gray-900 hover:bg-gray-200"
                                            >
                                                {{ $item->itemname }}
                                            </a>

                                            @if ($item->shortdescription)
                                                <div class="mt-1 text-xs text-gray-500">
                                                    {{ \Illuminate\Support\Str::limit($item->shortdescription, 80) }}
                                                </div>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3">
                                            {{ $item->destination?->destinationname ?? '—' }}
                                        </td>

                                        <td class="px-4 py-3">
                                            {{ $item->itemTypes->pluck('typename')->join(', ') ?: '—' }}
                                        </td>

                                        <td class="px-4 py-3">
                                            {{ $item->place?->placename ?? '—' }}
                                        </td>

                                        <td class="px-4 py-3">
                                            @if ($interestStyle)
                                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ $interestStyle['badge'] }}">
                                                    {{ $visitInterestOptions[$item->visitinterestlevel] ?? $interestStyle['label'] }}
                                                </span>

                                                <div class="mt-1 text-xs text-gray-500">
                                                    @if ($item->hasvisited)
                                                        Visited
                                                    @else
                                                        Not visited
                                                    @endif

                                                    @if ($item->hasvisited && $item->visitedat)
                                                        · {{ $item->visitedat->format('d M Y') }}
                                                    @endif

                                                    @if ($item->stillwanttovisit)
                                                        · Still interested
                                                    @endif
                                                </div>

                                                @if ($item->visitinterestnotes)
                                                    <div class="mt-1 max-w-xs text-xs text-gray-600">
                                                        {{ \Illuminate\Support\Str::limit($item->visitinterestnotes, 100) }}
                                                    </div>
                                                @endif
                                            @else
                                                @if ($item->hasvisited)
                                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-green-100 text-green-800">
                                                        Visited
                                                    </span>

                                                    @if ($item->visitedat)
                                                        <div class="mt-1 text-xs text-gray-500">
                                                            {{ $item->visitedat->format('d M Y') }}
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="text-xs text-gray-400">
                                                        —
                                                    </span>
                                                @endif
                                            @endif
                                        </td>

                                        <td class="px-4 py-3">
                                            {{ $item->bookingrequired ? 'Yes' : 'No' }}
                                        </td>

                                        <td class="px-4 py-3">
                                            {{ $item->isactive ? 'Yes' : 'No' }}
                                        </td>

                                        <td class="px-4 py-3">
                                            @if (! is_null($item->latitude) && ! is_null($item->longitude))
                                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs font-medium">
                                                    Yes
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-medium">
                                                    No
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <a
                                                href="{{ route('destination-items.edit', [
                                                    'destinationItem' => $item,
                                                    'return_to' => $returnTo,
                                                ]) }}"
                                                class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-800 rounded hover:bg-gray-200"
                                            >
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="9" class="px-4 py-6 text-center text-gray-500">
                                        No destination items found.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>