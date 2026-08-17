<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Budget Lines — {{ $budget->year_label }}
                @php
                    $statusColour = match($budget->status) {
                        'draft'   => 'bg-gray-100 text-gray-700',
                        'adopted' => 'bg-blue-100 text-blue-700',
                        'revised' => 'bg-yellow-100 text-yellow-700',
                        'closed'  => 'bg-green-100 text-green-700',
                        default   => 'bg-gray-100 text-gray-700',
                    };
                @endphp
                <span class="ml-2 px-2 py-0.5 rounded text-xs font-semibold {{ $statusColour }}">
                    {{ ucfirst($budget->status) }}
                </span>
            </h2>
            <a href="{{ route('cashbook.budgets.index') }}"
               class="text-sm text-blue-600 hover:underline">← Back to Budgets</a>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ viewMode: 'all', showNextYear: false }">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            {{-- Flash --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- Account summary --}}
            @php
                $uniqueAccountIds = $lines->pluck('accountid')->unique();
            @endphp
            @if ($uniqueAccountIds->count() === 1)
                @php $accountName = optional($lines->first()->account)->accountname; @endphp
                <div class="bg-white shadow rounded-lg px-6 py-3">
                    <div class="text-sm text-gray-700">
                        <span class="font-semibold">Account:</span>
                        {{ $accountName ?? 'Unknown' }}
                    </div>
                </div>
            @else
                <div class="bg-white shadow rounded-lg px-6 py-3">
                    <div class="text-sm text-gray-700">
                        <span class="font-semibold">Account:</span>
                        Multiple accounts
                    </div>
                </div>
            @endif

            {{-- View Controls --}}
            <div class="bg-white shadow rounded-lg px-6 py-4">
                <div class="flex flex-wrap items-center gap-6">
                    <div>
                        <span class="text-sm font-medium text-gray-700 mr-3">Show:</span>
                        @foreach (['all' => 'All', 'adopted' => 'Adopted', 'revised' => 'Revised', 'actuals' => 'Actuals'] as $val => $label)
                            <label class="inline-flex items-center mr-4 cursor-pointer">
                                <input type="radio" name="viewmode" value="{{ $val }}"
                                       x-model="viewMode"
                                       class="text-blue-600 focus:ring-blue-500 mr-1">
                                <span class="text-sm text-gray-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>

                    @if ($nextYearHeader)
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="showNextYear"
                                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <span class="text-sm text-gray-700">
                                Show Next Year ({{ 'FY' . ($nextYearHeader->financialyear - 1) . '-' . substr($nextYearHeader->financialyear, -2) }})
                            </span>
                        </label>
                    @endif
                </div>
            </div>

            {{-- Budget Lines Grid --}}
            <div class="bg-white shadow rounded-lg overflow-x-auto">
                <table class="min-w-full text-xs border-collapse">
                    <thead>
                        <tr class="bg-gray-100 border-b border-gray-300">
                            <th class="px-3 py-2 text-left font-semibold text-gray-700 w-64">
                                Category
                            </th>
                            <th class="px-3 py-2 text-right font-semibold text-gray-700 w-24">
                                Total
                            </th>
                            @foreach ($monthLabels as $no => $label)
                                <th class="px-2 py-2 text-right font-semibold text-gray-700 w-20">{{ $label }}</th>
                            @endforeach
                            @if (!$budget->isClosed())
                                <th class="px-3 py-2 text-center font-semibold text-gray-700 w-20">Actions</th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($lines as $line)
                            @php
                                $months       = $line->months->keyBy('monthno');
                                $adoptedTotal = $line->adoptedTotal();
                                $revisedTotal = $line->revisedTotal();
                            @endphp

                            {{-- Adopted row --}}
                            <tr class="bg-white border-t border-gray-200"
                                x-show="viewMode === 'all' || viewMode === 'adopted'">
                                <td class="px-3 py-1.5 text-gray-700 border-r border-gray-100">
                                    <div class="text-sm text-gray-800">
                                        {{ $line->category->categoryname }}
                                        <span class="ml-1 text-gray-400 text-xs">Adopted</span>
                                    </div>
                                </td>
                                <td class="px-3 py-1.5 text-right font-semibold text-gray-800">
                                    {{ number_format($adoptedTotal, 2) }}
                                </td>
                                @foreach ($monthLabels as $no => $label)
                                    @php $m = $months->get($no); @endphp
                                    <td class="px-2 py-1.5 text-right text-gray-700 w-20">
                                        @if ($m && !$budget->isAdoptedLocked())
                                            <input type="number" step="0.01" min="0"
                                                   name="adopted[{{ $line->id }}][{{ $no }}]"
                                                   value="{{ $m ? number_format($m->adoptedamount, 2, '.', '') : '0.00' }}"
                                                   class="w-full text-right border border-transparent hover:border-gray-300 focus:border-blue-400 rounded px-1 py-0.5 bg-transparent">
                                        @else
                                            {{ $m ? number_format($m->adoptedamount, 2) : '0.00' }}
                                        @endif
                                    </td>
                                @endforeach
                                @if (!$budget->isClosed())
                                    <td class="px-3 py-1.5 text-center">
                                        @if (!$budget->isAdoptedLocked())
                                            <button type="button"
                                                    class="text-blue-600 hover:underline text-xs"
                                                    onclick="window.location='{{ route('cashbook.budgets.lines.index', [$budget, 'line' => $line->id, 'field' => 'adopted']) }}'">
                                                Method
                                            </button>
                                        @endif
                                    </td>
                                @endif
                            </tr>

                            {{-- Revised row --}}
                            <tr class="bg-blue-50 border-t border-blue-100"
                                x-show="viewMode === 'all' || viewMode === 'revised'">
                                <td class="px-3 py-1.5 text-gray-700 border-r border-blue-100">
                                    <div class="text-sm text-gray-800">
                                        {{ $line->category->categoryname }}
                                        <span class="ml-1 text-blue-700 text-xs font-medium">Revised</span>
                                    </div>
                                </td>
                                <td class="px-3 py-1.5 text-right font-semibold text-blue-800">
                                    {{ number_format($revisedTotal, 2) }}
                                </td>
                                @foreach ($monthLabels as $no => $label)
                                    @php $m = $months->get($no); @endphp
                                    <td class="px-2 py-1.5 text-right text-blue-800 w-20">
                                        @if ($m && $budget->isRevised())
                                            <input type="number" step="0.01" min="0"
                                                   name="revised[{{ $line->id }}][{{ $no }}]"
                                                   value="{{ $m ? number_format($m->effectiveRevisedAmount(), 2, '.', '') : '0.00' }}"
                                                   class="w-full text-right border border-transparent hover:border-blue-300 focus:border-blue-500 rounded px-1 py-0.5 bg-transparent {{ $m && $m->revisedisactual ? 'text-amber-600 font-semibold' : '' }}"
                                                   title="{{ $m && $m->revisedisactual ? 'Set from actuals' : '' }}">
                                        @else
                                            <span class="{{ $m && $m->revisedisactual ? 'text-amber-600 font-semibold' : '' }}"
                                                  title="{{ $m && $m->revisedisactual ? 'Set from actuals' : '' }}">
                                                {{ $m ? number_format($m->effectiveRevisedAmount(), 2) : '0.00' }}
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                                @if (!$budget->isClosed())
                                    <td class="px-3 py-1.5 text-center">
                                        @if ($budget->isRevised())
                                            <button type="button"
                                                    class="text-blue-600 hover:underline text-xs"
                                                    onclick="window.location='{{ route('cashbook.budgets.lines.index', [$budget, 'line' => $line->id, 'field' => 'revised']) }}'">
                                                Method
                                            </button>
                                        @endif
                                    </td>
                                @endif
                            </tr>

                            {{-- Actuals row --}}
                            <tr class="bg-yellow-50 border-t border-yellow-100"
                                x-show="viewMode === 'all' || viewMode === 'actuals'">
                                <td class="px-3 py-1.5 text-gray-700 border-r border-yellow-100">
                                    <div class="text-xs text-gray-500">
                                        {{ $line->category->categoryname }}
                                        <span class="ml-1 text-yellow-700 text-xs font-medium">Actuals</span>
                                    </div>
                                </td>
                                <td class="px-3 py-1.5 text-right font-semibold text-yellow-800">
                                    {{ isset($lineActuals[$line->id]) ? number_format(array_sum($lineActuals[$line->id]), 2) : '—' }}
                                </td>
                                @foreach ($monthLabels as $no => $label)
                                    <td class="px-2 py-1.5 text-right text-yellow-800 w-20">
                                        {{ isset($lineActuals[$line->id][$no]) ? number_format($lineActuals[$line->id][$no], 2) : '—' }}
                                    </td>
                                @endforeach
                                @if (!$budget->isClosed())
                                    <td class="px-3 py-1.5"></td>
                                @endif
                            </tr>

                            {{-- Next Year row --}}
                            @if ($nextYearHeader)
                                @php
                                    $nyLine   = $nextYearHeader->budgetLines
                                        ->where('accountid', $line->accountid)
                                        ->where('categoryid', $line->categoryid)
                                        ->first();
                                    $nyMonths = $nyLine ? $nyLine->months->keyBy('monthno') : collect();
                                    $nyTotal  = $nyLine ? $nyLine->adoptedTotal() : 0;
                                @endphp
                                <tr class="bg-green-50 border-t border-green-100"
                                    x-show="showNextYear">
                                    <td class="px-3 py-1.5 text-gray-700 border-r border-green-100">
                                        <div class="text-sm text-gray-800">
                                            {{ $line->category->categoryname }}
                                            <span class="ml-1 text-green-700 text-xs font-medium">Next Year Budget</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-1.5 text-right font-semibold text-green-800">
                                        {{ number_format($nyTotal, 2) }}
                                    </td>
                                    @foreach ($monthLabels as $no => $label)
                                        @php $nym = $nyMonths->get($no); @endphp
                                        <td class="px-2 py-1.5 text-right text-green-800 w-20">
                                            {{ $nym ? number_format($nym->adoptedamount, 2) : '—' }}
                                        </td>
                                    @endforeach
                                    @if (!$budget->isClosed())
                                        <td class="px-3 py-1.5"></td>
                                    @endif
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="16" class="px-4 py-6 text-center text-gray-400">
                                    No budget lines yet. Add a line below.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Apply Preparation Method panel (server-driven) --}}
            @if (!empty($methodLineId))
                @php
                    $methodLine = $lines->firstWhere('id', (int) $methodLineId);
                @endphp

                @if ($methodLine)
                    <div class="bg-white shadow rounded-lg px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-800 mb-2">
                            Apply Preparation Method —
                            {{ $methodLine->account->accountname }} / {{ $methodLine->category->categoryname }}
                            <span class="ml-2 text-xs text-gray-500">
                                ({{ ucfirst($methodField ?? 'adopted') }})
                            </span>
                        </h3>
                        <p class="text-xs text-gray-600 mb-4">
                            Select a method to distribute the annual total across 12 months.
                            Individual months can be adjusted manually afterwards.
                        </p>

                        <form method="POST"
                              action="{{ route('cashbook.budgets.lines.applyMethod', [$budget, $methodLine]) }}"
                              class="space-y-4">
                            @csrf
                            <input type="hidden" name="field" value="{{ $methodField ?? 'adopted' }}">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Method</label>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="method" value="equal"
                                               class="text-blue-600"
                                               {{ old('method', 'equal') === 'equal' ? 'checked' : '' }}>
                                        <span class="text-sm">Equal — divide total equally across all 12 months</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="method" value="proportioned"
                                               class="text-blue-600"
                                               {{ old('method') === 'proportioned' ? 'checked' : '' }}>
                                        <span class="text-sm">Proportioned — distribute based on prior year actuals</span>
                                    </label>
                                    @if (($methodField ?? 'adopted') === 'revised')
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="method" value="lock_actuals"
                                                   class="text-blue-600"
                                                   {{ old('method') === 'lock_actuals' ? 'checked' : '' }}>
                                            <span class="text-sm">
                                                Lock Actuals to date — replace revised with actuals up to a chosen month
                                            </span>
                                        </label>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Annual Total
                                </label>
                                <input type="number" name="total" step="0.01" min="0"
                                       value="{{ old('total') }}"
                                       class="w-40 border-gray-300 rounded shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            @if (($methodField ?? 'adopted') === 'revised')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Lock actuals through
                                    </label>
                                    <select name="lock_thru_month"
                                            class="w-40 border-gray-300 rounded shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                                        @foreach ($monthLabels as $no => $label)
                                            <option value="{{ $no }}" {{ (int) old('lock_thru_month') === (int) $no ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="flex items-center gap-3 pt-2">
                                <button type="submit"
                                        class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700">
                                    Apply
                                </button>
                                <a href="{{ route('cashbook.budgets.lines.index', $budget) }}"
                                   class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded hover:bg-gray-200">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                @endif
            @endif

            {{-- Add Line Form (draft only) --}}
            @if ($budget->isDraft())
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-700">Add Budget Line</h3>
                    </div>
                    <form method="POST" action="{{ route('cashbook.budgets.lines.store', $budget) }}"
                          class="px-6 py-4 flex flex-wrap items-end gap-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                Account <span class="text-red-500">*</span>
                            </label>
                            <select name="accountid" required
                                    class="border-gray-300 rounded shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500 w-56">
                                <option value="">— select —</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->accountname }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <select name="categoryid" required
                                    class="border-gray-300 rounded shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500 w-56">
                                <option value="">— select —</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->categoryname }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700">
                                Add Line
                            </button>
                        </div>
                    </form>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>