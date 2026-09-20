<section class="overflow-hidden border border-teal-200 bg-white shadow-sm sm:rounded-lg">
    <div class="border-b border-teal-200 bg-teal-50 px-4 py-3">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-sm font-semibold text-teal-900">
                    Today’s Travel Maintenance
                </h2>

                <p class="mt-1 text-xs text-teal-700">
                    Places, destinations, and destination items created or updated today.
                </p>
            </div>

            <a
                href="{{ route('places.index') }}"
                class="inline-flex items-center rounded-md border border-teal-300 bg-white px-3 py-1.5 text-xs font-medium text-teal-800 hover:bg-teal-100"
            >
                Places
            </a>
        </div>
    </div>

    @if ($todayTravelActivity->isEmpty())
        <div class="px-4 py-5 text-sm text-gray-500">
            No travel-maintenance activity recorded today.
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach ($todayTravelActivity as $activity)
                @php
                    $record = $activity['record'];

                    $typeStyles = match ($activity['type']) {
                        'place' => [
                            'badge' => 'bg-teal-100 text-teal-800',
                            'label' => 'Place',
                        ],
                        'destination' => [
                            'badge' => 'bg-indigo-100 text-indigo-800',
                            'label' => 'Destination',
                        ],
                        'destination_item' => [
                            'badge' => 'bg-sky-100 text-sky-800',
                            'label' => 'Destination item',
                        ],
                        default => [
                            'badge' => 'bg-gray-100 text-gray-800',
                            'label' => 'Travel activity',
                        ],
                    };

                    $routeName = match ($activity['type']) {
                        'place' => 'places.edit',
                        'destination' => 'destinations.edit',
                        'destination_item' => 'destination-items.edit',
                        default => null,
                    };

                    $routeParameters = $routeName
                        ? [$record, 'return_to' => request()->fullUrl()]
                        : null;
                @endphp

                @if ($routeName)
                    <a
                        href="{{ route($routeName, $routeParameters) }}"
                        class="block px-4 py-3 hover:bg-teal-50"
                    >
                @else
                    <div class="px-4 py-3">
                @endif

                    <div class="flex flex-wrap items-start gap-3">
                        <span
                            class="inline-flex shrink-0 items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $typeStyles['badge'] }}"
                        >
                            {{ $typeStyles['label'] }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                                <span class="font-medium text-gray-900">
                                    {{ $activity['title'] }}
                                </span>

                                @if (filled($activity['detail']))
                                    <span class="text-sm text-gray-600">
                                        {{ \Illuminate\Support\Str::limit($activity['detail'], 180) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="shrink-0 whitespace-nowrap text-right text-xs text-gray-500">
                            @if (($activity['createdToday'] ?? false) === true)
                                <span class="font-medium text-emerald-700">
                                    Created today
                                </span>

                                <span class="text-gray-400">·</span>
                            @endif

                            <span>Updated</span>

                            @if ($activity['updatedAt'])
                                <time datetime="{{ $activity['updatedAt']->toIso8601String() }}">
                                    {{ $activity['updatedAt']->shiftTimezone('Australia/Sydney')->format('g:i A') }}
                                </time>
                            @endif
                        </div>
                    </div>

                @if ($routeName)
                    </a>
                @else
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</section>