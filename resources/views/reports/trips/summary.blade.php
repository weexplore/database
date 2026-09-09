<x-app-layout>
    <x-report-print-styles orientation="landscape" />

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Trip Summary Report
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Planned and actual trip distance and cost totals for the selected date range.
                </p>
            </div>

            <div class="print-hide flex shrink-0 items-center gap-3">
                <button
                    type="button"
                    onclick="window.print()"
                    class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700"
                >
                    Print / Save PDF
                </button>

                <a
                    href="{{ route('trips.index') }}"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                >
                    Back to Trips
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @if ($errors->any())
                <div class="print-hide rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <div class="font-medium">Please fix the following:</div>

                    <ul class="mt-2 list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="print-hide rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <form
                    method="GET"
                    action="{{ route('trips.summary-report') }}"
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4"
                >
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700">
                            Date from
                        </label>

                        <input
                            type="date"
                            name="date_from"
                            id="date_from"
                            value="{{ $dateFrom }}"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm"
                        >
                    </div>

                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700">
                            Date to
                        </label>

                        <input
                            type="date"
                            name="date_to"
                            id="date_to"
                            value="{{ $dateTo }}"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm"
                        >
                    </div>

                    <div>
                        <label for="trip_status" class="block text-sm font-medium text-gray-700">
                            Trip status
                        </label>

                        <select
                            name="trip_status"
                            id="trip_status"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm"
                        >
                            <option value="">All statuses</option>

                            @foreach ($tripStatusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($tripStatus === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-3">
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                        >
                            Run report
                        </button>

                        <a
                            action="{{ route('trips.summary-report') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            @php
                $dateRangeLabel = match (true) {
                    filled($dateFrom) && filled($dateTo) =>
                        \Carbon\Carbon::parse($dateFrom)->format('d M Y')
                        . ' to '
                        . \Carbon\Carbon::parse($dateTo)->format('d M Y'),

                    filled($dateFrom) =>
                        'From ' . \Carbon\Carbon::parse($dateFrom)->format('d M Y'),

                    filled($dateTo) =>
                        'To ' . \Carbon\Carbon::parse($dateTo)->format('d M Y'),

                    default => 'All trip dates',
                };
            @endphp

            <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
                <div class="report-selection-grid grid grid-cols-1 gap-x-8 gap-y-3 text-sm text-gray-700 sm:grid-cols-2 xl:grid-cols-3">
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Date range
                        </div>

                        <div class="mt-0.5 font-semibold text-gray-900">
                            {{ $dateRangeLabel }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Trip status
                        </div>

                        <div class="mt-0.5 font-semibold text-gray-900">
                            {{ $tripStatusOptions[$tripStatus] ?? 'All statuses' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Trips included
                        </div>

                        <div class="mt-0.5 font-semibold text-gray-900">
                            {{ number_format($reportTotals['trip_count']) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 md:grid-cols-4 xl:grid-cols-7">
                <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                        Trips
                    </div>

                    <div class="mt-1 text-xl font-semibold text-gray-900">
                        {{ number_format($reportTotals['trip_count']) }}
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                        Trip days
                    </div>

                    <div class="mt-1 text-xl font-semibold text-gray-900">
                        {{ number_format($reportTotals['trip_days']) }}
                    </div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                        Stay nights
                    </div>

                    <div class="mt-1 text-xl font-semibold text-gray-900">
                        {{ number_format($reportTotals['stay_nights']) }}
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                        Planned km
                    </div>

                    <div class="mt-1 text-xl font-semibold text-gray-900">
                        {{ number_format($reportTotals['planned_distance_km'], 1) }}
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                        Actual km
                    </div>

                    <div class="mt-1 text-xl font-semibold text-gray-900">
                        {{ number_format($reportTotals['actual_distance_km'], 1) }}
                    </div>
                </div>

                <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 shadow-sm">
                    <div class="text-xs font-medium uppercase tracking-wide text-blue-700">
                        Estimated costs
                    </div>

                    <div class="mt-1 text-xl font-semibold text-blue-950">
                        ${{ number_format($reportTotals['overall_planned'], 2) }}
                    </div>
                </div>

                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 shadow-sm">
                    <div class="text-xs font-medium uppercase tracking-wide text-green-700">
                        Actual costs
                    </div>

                    <div class="mt-1 text-xl font-semibold text-green-950">
                        ${{ number_format($reportTotals['overall_actual'], 2) }}
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white shadow-sm overflow-x-auto">
                <table class="trip-summary-table min-w-full table-fixed border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-gray-300 bg-gray-100 text-gray-700">
                            <th rowspan="2" class="px-3 py-2 text-left font-semibold">
                                Trip
                            </th>

                            <th rowspan="2" class="px-2 py-2 text-left font-semibold">
                                Dates
                            </th>

                            <th colspan="2" class="border-l border-gray-300 px-2 py-2 text-center font-semibold">
                                Distance km
                            </th>

                            <th colspan="2" class="border-l border-gray-300 px-2 py-2 text-center font-semibold">
                                Food
                            </th>

                            <th colspan="2" class="border-l border-gray-300 px-2 py-2 text-center font-semibold">
                                Misc
                            </th>

                            <th colspan="2" class="border-l border-gray-300 px-2 py-2 text-center font-semibold">
                                Stays
                            </th>

                            <th colspan="2" class="border-l border-gray-300 px-2 py-2 text-center font-semibold">
                                Items
                            </th>

                            <th colspan="2" class="border-l border-gray-300 px-2 py-2 text-center font-semibold">
                                Fuel
                            </th>

                            <th colspan="2" class="border-l border-gray-300 px-2 py-2 text-center font-semibold">
                                Overall
                            </th>
                        </tr>

                        <tr class="border-b border-gray-300 bg-gray-50 text-gray-600">
                            @foreach (range(1, 7) as $group)
                                <th class="border-l border-gray-200 px-2 py-1.5 text-right font-medium">
                                    Plan
                                </th>

                                <th class="px-2 py-1.5 text-right font-medium">
                                    Actual
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($reportRows as $row)
                            @php
                                $trip = $row['trip'];

                                $statusColour = match ($trip->tripstatus) {
                                    'planned' => 'bg-gray-100 text-gray-700',
                                    'active' => 'bg-blue-100 text-blue-700',
                                    'completed' => 'bg-green-100 text-green-700',
                                    'archived' => 'bg-slate-100 text-slate-700',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                            @endphp

                            <tr class="border-b border-gray-200 align-top">
                                <td class="px-3 py-2">
                                    <a
                                        href="{{ route('trips.edit', $trip) }}"
                                        class="font-semibold text-blue-700 hover:underline"
                                    >
                                        {{ $trip->tripname }}
                                    </a>

                                    <div class="mt-1">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium {{ $statusColour }}">
                                            {{ ucfirst($trip->tripstatus) }}
                                        </span>
                                    </div>

                                    @if ($trip->travellers->isNotEmpty())
                                        <div class="mt-1 text-[10px] text-gray-500">
                                            {{ $trip->travellers->pluck('displayname')->join(', ') }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-2 py-2 text-gray-700 whitespace-nowrap">
                                    @if ($trip->startdate)
                                        {{ $trip->startdate->format('d M Y') }}
                                    @else
                                        —
                                    @endif

                                    <br>

                                    @if ($trip->enddate)
                                        {{ $trip->enddate->format('d M Y') }}
                                    @else
                                        —
                                    @endif

                                    <div class="mt-1 text-[10px] text-gray-500">
                                        @if ($row['trip_days'] !== null)
                                            <div>
                                                {{ $row['trip_days'] }}
                                                {{ \Illuminate\Support\Str::plural('day', $row['trip_days']) }}
                                            </div>
                                        @endif

                                        <div>
                                            {{ $row['stay_nights'] }}
                                            {{ \Illuminate\Support\Str::plural('night', $row['stay_nights']) }}
                                        </div>
                                    </div>
                                </td>

                                <td class="border-l border-gray-100 px-2 py-2 text-right tabular-nums">
                                    {{ $row['planned_distance_km'] !== null ? number_format($row['planned_distance_km'], 1) : '—' }}
                                </td>

                                <td class="px-2 py-2 text-right tabular-nums">
                                    {{ $row['actual_distance_km'] !== null ? number_format($row['actual_distance_km'], 1) : '—' }}
                                </td>

                                <td class="border-l border-gray-100 px-2 py-2 text-right tabular-nums">
                                    {{ $row['food_planned'] !== null ? number_format($row['food_planned'], 2) : '—' }}
                                </td>

                                <td class="px-2 py-2 text-right tabular-nums">
                                    {{ number_format($row['food_actual'], 2) }}
                                </td>

                                <td class="border-l border-gray-100 px-2 py-2 text-right tabular-nums">
                                    {{ $row['misc_planned'] !== null ? number_format($row['misc_planned'], 2) : '—' }}
                                </td>

                                <td class="px-2 py-2 text-right tabular-nums">
                                    {{ number_format($row['misc_actual'], 2) }}
                                </td>

                                <td class="border-l border-gray-100 px-2 py-2 text-right tabular-nums">
                                    {{ number_format($row['stay_planned'], 2) }}
                                </td>

                                <td class="px-2 py-2 text-right tabular-nums">
                                    {{ number_format($row['stay_actual'], 2) }}
                                </td>

                                <td class="border-l border-gray-100 px-2 py-2 text-right tabular-nums">
                                    {{ number_format($row['item_planned'], 2) }}
                                </td>

                                <td class="px-2 py-2 text-right tabular-nums">
                                    {{ number_format($row['item_actual'], 2) }}
                                </td>

                                <td class="border-l border-gray-100 px-2 py-2 text-right tabular-nums">
                                    {{ $row['fuel_planned'] !== null ? number_format($row['fuel_planned'], 2) : '—' }}
                                </td>

                                <td class="px-2 py-2 text-right tabular-nums">
                                    {{ number_format($row['fuel_actual'], 2) }}
                                </td>

                                <td class="border-l border-gray-100 px-2 py-2 text-right font-semibold tabular-nums text-blue-800">
                                    {{ number_format($row['overall_planned'], 2) }}
                                </td>

                                <td class="px-2 py-2 text-right font-semibold tabular-nums text-green-800">
                                    {{ number_format($row['overall_actual'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="16" class="px-4 py-8 text-center text-sm text-gray-500">
                                    No trips match the selected date range and status filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    <tfoot>
    <tr class="trip-summary-total-row border-t-2 border-blue-700 bg-blue-50 text-gray-900">
        <th
            scope="row"
            colspan="2"
            class="px-3 py-2 text-left font-bold text-blue-950"
        >
            Selected Date Range Totals
        </th>

        {{-- Distance --}}
        <th class="border-l border-blue-200 px-2 py-2 text-right font-bold tabular-nums">
            {{ number_format($reportTotals['planned_distance_km'], 1) }}
        </th>

        <th class="px-2 py-2 text-right font-bold tabular-nums">
            {{ number_format($reportTotals['actual_distance_km'], 1) }}
        </th>

        {{-- Food --}}
        <th class="border-l border-blue-200 px-2 py-2 text-right font-bold tabular-nums">
            {{ number_format($reportTotals['food_planned'], 2) }}
        </th>

        <th class="px-2 py-2 text-right font-bold tabular-nums">
            {{ number_format($reportTotals['food_actual'], 2) }}
        </th>

        {{-- Misc --}}
        <th class="border-l border-blue-200 px-2 py-2 text-right font-bold tabular-nums">
            {{ number_format($reportTotals['misc_planned'], 2) }}
        </th>

        <th class="px-2 py-2 text-right font-bold tabular-nums">
            {{ number_format($reportTotals['misc_actual'], 2) }}
        </th>

        {{-- Trip Stays --}}
        <th class="border-l border-blue-200 px-2 py-2 text-right font-bold tabular-nums">
            {{ number_format($reportTotals['stay_planned'], 2) }}
        </th>

        <th class="px-2 py-2 text-right font-bold tabular-nums">
            {{ number_format($reportTotals['stay_actual'], 2) }}
        </th>

        {{-- Trip Items --}}
        <th class="border-l border-blue-200 px-2 py-2 text-right font-bold tabular-nums">
            {{ number_format($reportTotals['item_planned'], 2) }}
        </th>

        <th class="px-2 py-2 text-right font-bold tabular-nums">
            {{ number_format($reportTotals['item_actual'], 2) }}
        </th>

        {{-- Fuel --}}
        <th class="border-l border-blue-200 px-2 py-2 text-right font-bold tabular-nums">
            {{ number_format($reportTotals['fuel_planned'], 2) }}
        </th>

        <th class="px-2 py-2 text-right font-bold tabular-nums">
            {{ number_format($reportTotals['fuel_actual'], 2) }}
        </th>

        {{-- Overall --}}
        <th class="border-l border-blue-300 px-2 py-2 text-right font-bold tabular-nums text-blue-900">
            {{ number_format($reportTotals['overall_planned'], 2) }}
        </th>

        <th class="px-2 py-2 text-right font-bold tabular-nums text-green-800">
            {{ number_format($reportTotals['overall_actual'], 2) }}
        </th>
    </tr>
</tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>