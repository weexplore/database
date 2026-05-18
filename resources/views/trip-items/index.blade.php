<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Trip Items
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $trip->tripname }}
                </p>
                <p class="mt-1 text-sm text-gray-500">
                    Status: {{ ucfirst($trip->tripstatus) }} ·
                    Start: {{ optional($trip->startdate)->format('d M Y') ?? '—' }} ·
                    End: {{ optional($trip->enddate)->format('d M Y') ?? '—' }}
                </p>
            </div>

            <div class="flex items-center gap-2">

                <a href="{{ route('trips.items.create', ['trip' => $trip->id]) }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                    Add Trip Item
                </a>
                <a href="{{ route('trips.edit', ['trip' => $trip, 'tab' => 'workflow']) }}"
                class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                    Back to Trip
                </a>

            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @if(session('success'))
                <div class="rounded-md bg-green-50 p-4 text-green-800 border border-green-200">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-md bg-red-50 p-4 text-red-800 border border-red-200">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="rounded-md bg-red-50 p-4 text-red-800 border border-red-200">
                    <div class="font-medium mb-2">Please fix the following:</div>
                    <ul class="list-disc pl-5 space-y-1 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <form method="GET" action="{{ route('trips.items.index', $trip) }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <div>
                            <label for="itemtype" class="block text-sm font-medium text-gray-700">Item type</label>
                            <select id="itemtype" name="itemtype" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All</option>
                                @foreach($itemTypeOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(request('itemtype') === (string) $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="status" name="status" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All</option>
                                @foreach($itemStatusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(request('status') === (string) $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="tripleg_id" class="block text-sm font-medium text-gray-700">Trip leg</label>
                            <select id="tripleg_id" name="tripleg_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All</option>
                                @foreach($tripLegs as $tripLeg)
                                    <option value="{{ $tripLeg->id }}" @selected((string) request('tripleg_id') === (string) $tripLeg->id)>
                                        Leg {{ $tripLeg->legnumber }}{{ $tripLeg->title ? ' - ' . $tripLeg->title : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="itemdate_from" class="block text-sm font-medium text-gray-700">From date</label>
                            <input type="date" id="itemdate_from" name="itemdate_from" value="{{ request('itemdate_from') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label for="itemdate_to" class="block text-sm font-medium text-gray-700">To date</label>
                            <input type="date" id="itemdate_to" name="itemdate_to" value="{{ request('itemdate_to') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                            <input type="text" id="search" name="search" value="{{ request('search') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm" placeholder="Title or description">
                        </div>

                        <div class="md:col-span-6 flex flex-wrap items-center justify-between gap-3 pt-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-900 text-sm">
                                    Filter
                                </button>
                                <a href="{{ route('trips.items.index', $trip) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                @if($showCreate)
                    <div class="p-6 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Add Trip Item</h3>

                        @php($tripItem = null)

                        <form method="POST" action="{{ route('trips.items.store', $trip) }}" class="space-y-6">
                            @csrf

                            @include('trip-items._form')

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                                <a href="{{ route('trips.items.index', $trip) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                                    Cancel
                                </a>

                                <button type="submit" class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                    Save Trip Item
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-700">Date</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-700">Title</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-700">Type</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-700">Status</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-700">Leg / Stay</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-700">Destination / Place</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-700">Costs</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($items as $item)
                                <tr>
                                    <td class="px-4 py-3 align-top">
                                        <div class="text-gray-900">{{ optional($item->itemdate)->format('d M Y') }}</div>
                                        @if($item->startdatetime)
                                            <div class="text-xs text-gray-500">{{ $item->startdatetime->format('d M Y H:i') }}</div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 align-top">
                                        <div class="font-medium text-gray-900">{{ $item->title }}</div>
                                        @if($item->description)
                                            <div class="text-xs text-gray-500 mt-1">{{ \Illuminate\Support\Str::limit($item->description, 100) }}</div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 align-top">
                                        <div class="text-gray-900">{{ $itemTypeOptions[$item->itemtype] ?? $item->itemtype }}</div>
                                        @if($item->priority)
                                            <div class="text-xs text-gray-500 mt-1">Priority: {{ $priorityOptions[$item->priority] ?? ucfirst($item->priority) }}</div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 align-top">
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $itemStatusOptions[$item->status] ?? $item->status }}
                                        </span>
                                        @if($item->isfullday)
                                            <div class="text-xs text-gray-500 mt-1">Full day</div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 align-top">
                                        @if($item->tripleg)
                                            <div class="text-gray-900">
                                                {{ $item->tripleg->title ?: ('Leg ' . $item->tripleg->legnumber) }}
                                            </div>

                                            @if($item->tripleg->title && $item->tripleg->legnumber)
                                                <div class="text-xs text-gray-500 mt-1">
                                                    Leg {{ $item->tripleg->legnumber }}
                                                </div>
                                            @endif
                                        @endif

                                        @if($item->stay)
                                            <div class="text-xs text-gray-500 mt-1">{{ $item->stay->stayname }}</div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 align-top">
                                        @if($item->destination)
                                            <div class="text-gray-900">{{ $item->destination->destinationname }}</div>
                                        @endif
                                        @if($item->destinationItem)
                                            <div class="text-xs text-gray-500 mt-1">{{ $item->destinationItem->itemname }}</div>
                                        @endif
                                        @if($item->place)
                                            <div class="text-xs text-gray-500 mt-1">{{ $item->place->placename }}</div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 align-top">
                                        @if(!is_null($item->estimatedtotalcost))
                                            <div class="text-gray-900">Est: ${{ number_format((float) $item->estimatedtotalcost, 2) }}</div>
                                        @endif
                                        @if(!is_null($item->actualcost))
                                            <div class="text-xs text-gray-500 mt-1">Act: ${{ number_format((float) $item->actualcost, 2) }}</div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 align-top text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('trips.items.edit', ['trip' => $trip->id, 'tripItem' => $item->id]) }}"
                                               class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 rounded hover:bg-blue-100 text-xs">
                                                Edit
                                            </a>

                                            <form method="POST"
                                                  action="{{ route('trips.items.destroy', ['trip' => $trip->id, 'tripItem' => $item->id]) }}"
                                                  onsubmit="return confirm('Delete this trip item?');">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-700 rounded hover:bg-red-100 text-xs">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                        No trip items found for this trip.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>