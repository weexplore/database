<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Budget Lines — {{ $budget->year_label }}
                @php
                    $statusColour = match($budget->status) {
                        'draft' => 'bg-gray-100 text-gray-700',
                        'adopted' => 'bg-blue-100 text-blue-700',
                        'revised' => 'bg-yellow-100 text-yellow-700',
                        'closed' => 'bg-green-100 text-green-700',
                        default => 'bg-gray-100 text-gray-700',
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

    <div class="py-6"
         x-data="{
             showAdopted: true,
             showRevised: true,
             showActuals: true,
             showNextYear: false,

             showAll() {
                 this.showAdopted = true;
                 this.showRevised = true;
                 this.showActuals = true;
             },

             showAdoptedActuals() {
                 this.showAdopted = true;
                 this.showRevised = false;
                 this.showActuals = true;
             },

             showRevisedActuals() {
                 this.showAdopted = false;
                 this.showRevised = true;
                 this.showActuals = true;
             }
         }">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow rounded-lg px-6 py-3">
                <div class="text-sm text-gray-700">
                    <span class="font-semibold">Legal Entity:</span>
                    {{ $budget->legalEntity?->entitycode ?? '' }}
                    @if ($budget->legalEntity?->entitycode && $budget->legalEntity?->entityname)
                        —
                    @endif
                    {{ $budget->legalEntity?->entityname ?? 'Unknown' }}
                </div>
            </div>

            <div class="bg-white shadow rounded-lg px-6 py-4">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-sm font-medium text-gray-700 mr-1">Show:</span>

                    <button type="button"
                            @click="showAll()"
                            class="rounded px-3 py-1.5 text-xs font-medium transition"
                            :class="showAdopted && showRevised && showActuals
                                ? 'bg-slate-700 text-white'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                        All
                    </button>

                    <button type="button"
                            @click="showAdopted = !showAdopted"
                            class="rounded px-3 py-1.5 text-xs font-medium transition"
                            :class="showAdopted
                                ? 'bg-gray-700 text-white'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                        Adopted
                    </button>

                    <button type="button"
                            @click="showRevised = !showRevised"
                            class="rounded px-3 py-1.5 text-xs font-medium transition"
                            :class="showRevised
                                ? 'bg-blue-700 text-white'
                                : 'bg-blue-100 text-blue-700 hover:bg-blue-200'">
                        Revised
                    </button>

                    <button type="button"
                            @click="showActuals = !showActuals"
                            class="rounded px-3 py-1.5 text-xs font-medium transition"
                            :class="showActuals
                                ? 'bg-amber-700 text-white'
                                : 'bg-amber-100 text-amber-700 hover:bg-amber-200'">
                        Actuals
                    </button>

                    <span class="mx-1 h-6 border-l border-gray-300"></span>

                    <button type="button"
                            @click="showAdoptedActuals()"
                            class="rounded px-3 py-1.5 text-xs font-medium transition"
                            :class="showAdopted && !showRevised && showActuals
                                ? 'bg-slate-700 text-white'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                        Adopted + Actuals
                    </button>

                    <button type="button"
                            @click="showRevisedActuals()"
                            class="rounded px-3 py-1.5 text-xs font-medium transition"
                            :class="!showAdopted && showRevised && showActuals
                                ? 'bg-blue-700 text-white'
                                : 'bg-blue-100 text-blue-700 hover:bg-blue-200'">
                        Revised + Actuals
                    </button>

                    @if ($nextYearHeader)
                        <span class="mx-1 h-6 border-l border-gray-300"></span>

                        <label class="inline-flex items-center gap-2 cursor-pointer ml-1">
                            <input type="checkbox"
                                   x-model="showNextYear"
                                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <span class="text-sm text-gray-700">
                                Show Next Year ({{ $nextYearHeader->year_label }})
                            </span>
                        </label>
                    @endif
                </div>
            </div>

            <div class="bg-white shadow rounded-lg overflow-x-auto">
                <table class="min-w-[1456px] table-fixed text-xs border-collapse">
                    <colgroup>
                        <col class="w-72">
                        <col class="w-24">
                        @foreach ($monthLabels as $no => $label)
                            <col class="w-20">
                        @endforeach
                        @if (!$budget->isClosed())
                            <col class="w-28">
                        @endif
                    </colgroup>

                    <thead>
                        <tr class="bg-gray-100 border-b border-gray-300">
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">Category</th>
                            <th class="px-3 py-2 text-right font-semibold text-gray-700 tabular-nums">Total</th>
                            @foreach ($monthLabels as $no => $label)
                                <th class="px-2 py-2 text-right font-semibold text-gray-700 tabular-nums">{{ $label }}</th>
                            @endforeach
                            @if (!$budget->isClosed())
                                <th class="px-3 py-2 text-center font-semibold text-gray-700">Actions</th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($lines as $line)
                            @php
                                $months = $line->months->keyBy('monthno');
                                $adoptedTotal = $line->adoptedTotal();
                                $revisedTotal = $line->revisedTotal();

                                $previousLine = $lines->get($loop->index - 1);
                                $nextLine = $lines->get($loop->index + 1);

                                $currentCategoryAncestors = $line->category
                                    ? $line->category->ancestors()
                                    : collect();

                                $previousCategoryAncestors = $previousLine?->category
                                    ? $previousLine->category->ancestors()
                                    : collect();

                                $headingCount = $budget->isClosed() ? 14 : 15;

                                $currentTypeId = $line->category?->categorytypeid;
                                $previousTypeId = $previousLine?->category?->categorytypeid;

                                $categoryTypeChanged = !$previousLine
                                    || $previousTypeId !== $currentTypeId;

                                $currentTypeCode = strtolower(trim((string) (
                                    $line->category?->categoryType?->typecode ?? 'other'
                                )));

                                $nextTypeCode = $nextLine
                                    ? strtolower(trim((string) (
                                        $nextLine->category?->categoryType?->typecode ?? 'other'
                                    )))
                                    : null;

                                $isLastLineInType = !$nextLine || $currentTypeCode !== $nextTypeCode;
                            @endphp

                            @if ($loop->first)
                                <tr class="bg-slate-700"
                                    x-show="showAdopted || showRevised || showActuals">
                                    <td colspan="{{ $headingCount }}"
                                        class="px-3 py-2 text-sm font-semibold text-white">
                                        Legal Entity —
                                        {{ $budget->legalEntity?->entitycode ?? '' }}
                                        @if ($budget->legalEntity?->entitycode && $budget->legalEntity?->entityname)
                                            —
                                        @endif
                                        {{ $budget->legalEntity?->entityname ?? 'Unknown' }}
                                    </td>
                                </tr>
                            @endif

                            @if ($categoryTypeChanged)
                                <tr class="bg-slate-700"
                                    x-show="showAdopted || showRevised || showActuals">
                                    <td colspan="{{ $headingCount }}"
                                        class="px-3 py-2 text-sm font-semibold uppercase tracking-wide text-white">
                                        {{ $line->category?->categoryType?->typename ?? 'Other Categories' }}
                                    </td>
                                </tr>
                            @endif

                            @foreach ($currentCategoryAncestors as $depth => $ancestor)
                                @php
                                    $previousAncestor = $previousCategoryAncestors->get($depth);
                                    $ancestorChanged = !$previousAncestor
                                        || $previousAncestor->id !== $ancestor->id;
                                @endphp

                                @if ($ancestorChanged)
                                    <tr class="{{ $depth === 0
                                        ? 'bg-gray-200 border-y border-gray-300'
                                        : 'bg-gray-50 border-y border-gray-200' }}"
                                        x-show="showAdopted || showRevised || showActuals">
                                        <td colspan="{{ $headingCount }}"
                                            class="px-3 py-1.5 text-sm font-semibold text-gray-700">
                                            <span style="padding-left: {{ $depth * 1.25 }}rem;">
                                                {{ $ancestor->categoryname }}
                                            </span>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach

                            <tr class="bg-white border-t border-gray-200" x-show="showAdopted">
                                <td class="px-3 py-1.5 text-gray-700 border-r border-gray-100">
                                    <div class="text-sm text-gray-800">
                                        {{ $line->category?->categoryname ?? 'Unknown category' }}
                                        <span class="ml-1 text-gray-400 text-xs">Adopted</span>
                                    </div>
                                </td>
                                <td class="px-3 py-1.5 text-right font-semibold text-gray-800 tabular-nums whitespace-nowrap">
                                    {{ number_format($adoptedTotal, 2) }}
                                </td>
                                @foreach ($monthLabels as $no => $label)
                                    @php $m = $months->get($no); @endphp
                                    <td class="px-2 py-1.5 text-right text-gray-700 tabular-nums whitespace-nowrap">
                                        {{ $m ? number_format($m->adoptedamount, 2) : '0.00' }}
                                    </td>
                                @endforeach
                                @if (!$budget->isClosed())
                                    <td class="px-3 py-1.5 text-center whitespace-nowrap">
                                        @if (!$budget->isAdoptedLocked())
                                            <a href="{{ route('cashbook.budgets.lines.index', [
                                                'budget' => $budget,
                                                'edit_line' => $line->id,
                                                'edit_field' => 'adopted',
                                            ]) }}"
                                               class="text-gray-700 hover:underline text-xs">
                                                Edit
                                            </a>
                                            <a href="{{ route('cashbook.budgets.lines.index', [
                                                'budget' => $budget,
                                                'line' => $line->id,
                                                'field' => 'adopted',
                                            ]) }}"
                                               class="ml-2 text-blue-600 hover:underline text-xs">
                                                Method
                                            </a>
                                        @endif
                                    </td>
                                @endif
                            </tr>

                            <tr class="bg-blue-50 border-t border-blue-100" x-show="showRevised">
                                <td class="px-3 py-1.5 text-gray-700 border-r border-blue-100">
                                    <div class="text-sm text-gray-800">
                                        {{ $line->category?->categoryname ?? 'Unknown category' }}
                                        <span class="ml-1 text-blue-700 text-xs font-medium">Revised</span>
                                    </div>
                                </td>
                                <td class="px-3 py-1.5 text-right font-semibold text-blue-800 tabular-nums whitespace-nowrap">
                                    {{ number_format($revisedTotal, 2) }}
                                </td>
                                @foreach ($monthLabels as $no => $label)
                                    @php $m = $months->get($no); @endphp
                                    <td class="px-2 py-1.5 text-right text-blue-800 tabular-nums whitespace-nowrap">
                                        <span class="{{ $m && $m->revisedisactual ? 'text-amber-600 font-semibold' : '' }}"
                                              title="{{ $m && $m->revisedisactual ? 'Set from actuals' : '' }}">
                                            {{ $m ? number_format($m->effectiveRevisedAmount(), 2) : '0.00' }}
                                        </span>
                                    </td>
                                @endforeach
                                @if (!$budget->isClosed())
                                    <td class="px-3 py-1.5 text-center whitespace-nowrap">
                                        @if ($budget->isRevised())
                                            <a href="{{ route('cashbook.budgets.lines.index', [
                                                'budget' => $budget,
                                                'edit_line' => $line->id,
                                                'edit_field' => 'revised',
                                            ]) }}"
                                               class="text-gray-700 hover:underline text-xs">
                                                Edit
                                            </a>
                                            <a href="{{ route('cashbook.budgets.lines.index', [
                                                'budget' => $budget,
                                                'line' => $line->id,
                                                'field' => 'revised',
                                            ]) }}"
                                               class="ml-2 text-blue-600 hover:underline text-xs">
                                                Method
                                            </a>
                                        @endif
                                    </td>
                                @endif
                            </tr>

                            <tr class="bg-yellow-50 border-t border-yellow-100" x-show="showActuals">
                                <td class="px-3 py-1.5 text-gray-700 border-r border-yellow-100">
                                    <div class="text-xs text-gray-500">
                                        {{ $line->category?->categoryname ?? 'Unknown category' }}
                                        <span class="ml-1 text-yellow-700 text-xs font-medium">Actuals</span>
                                    </div>
                                </td>
                                <td class="px-3 py-1.5 text-right font-semibold text-yellow-800 tabular-nums whitespace-nowrap">
                                    {{ isset($lineActuals[$line->id]) ? number_format(array_sum($lineActuals[$line->id]), 2) : '—' }}
                                </td>
                                @foreach ($monthLabels as $no => $label)
                                    <td class="px-2 py-1.5 text-right text-yellow-800 tabular-nums whitespace-nowrap">
                                        {{ isset($lineActuals[$line->id][$no]) ? number_format($lineActuals[$line->id][$no], 2) : '—' }}
                                    </td>
                                @endforeach
                                @if (!$budget->isClosed())
                                    <td class="px-3 py-1.5"></td>
                                @endif
                            </tr>

                            @if ($nextYearHeader)
                                @php
                                    $nyLine = $nextYearHeader->budgetLines
                                        ->where('accountid', $line->accountid)
                                        ->where('categoryid', $line->categoryid)
                                        ->first();
                                    $nyMonths = $nyLine ? $nyLine->months->keyBy('monthno') : collect();
                                    $nyTotal = $nyLine ? $nyLine->adoptedTotal() : 0;
                                @endphp
                                <tr class="bg-green-50 border-t border-green-100" x-show="showNextYear">
                                    <td class="px-3 py-1.5 text-gray-700 border-r border-green-100">
                                        <div class="text-sm text-gray-800">
                                            {{ $line->category?->categoryname ?? 'Unknown category' }}
                                            <span class="ml-1 text-green-700 text-xs font-medium">Next Year Budget</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-1.5 text-right font-semibold text-green-800 tabular-nums whitespace-nowrap">
                                        {{ number_format($nyTotal, 2) }}
                                    </td>
                                    @foreach ($monthLabels as $no => $label)
                                        @php $nym = $nyMonths->get($no); @endphp
                                        <td class="px-2 py-1.5 text-right text-green-800 tabular-nums whitespace-nowrap">
                                            {{ $nym ? number_format($nym->adoptedamount, 2) : '—' }}
                                        </td>
                                    @endforeach
                                    @if (!$budget->isClosed())
                                        <td class="px-3 py-1.5"></td>
                                    @endif
                                </tr>
                            @endif

                            @if ($isLastLineInType && isset($budgetSectionTotals[$currentTypeCode]))
                                @php
                                    $section = $budgetSectionTotals[$currentTypeCode];
                                    $sectionClasses = match ($currentTypeCode) {
                                        'receipt' => 'bg-green-50 border-t border-green-200 text-green-900',
                                        'payment' => 'bg-red-50 border-t border-red-200 text-red-900',
                                        'transfer' => 'bg-blue-50 border-t border-blue-200 text-blue-900',
                                        default => 'bg-gray-50 border-t border-gray-200 text-gray-900',
                                    };
                                @endphp

                                <tr class="{{ $sectionClasses }}" x-show="showAdopted">
                                    <td class="px-3 py-2 font-semibold border-r border-current/10">
                                        Total {{ $section['label'] }} — Adopted
                                    </td>
                                    <td class="px-3 py-2 text-right font-semibold tabular-nums whitespace-nowrap">
                                        {{ number_format(array_sum($section['adopted']), 2) }}
                                    </td>
                                    @foreach ($monthLabels as $monthNo => $label)
                                        <td class="px-2 py-2 text-right font-semibold tabular-nums whitespace-nowrap">
                                            {{ number_format($section['adopted'][$monthNo] ?? 0, 2) }}
                                        </td>
                                    @endforeach
                                    @if (!$budget->isClosed())
                                        <td></td>
                                    @endif
                                </tr>

                                <tr class="{{ $sectionClasses }}" x-show="showRevised">
                                    <td class="px-3 py-2 font-semibold border-r border-current/10">
                                        Total {{ $section['label'] }} — Revised
                                    </td>
                                    <td class="px-3 py-2 text-right font-semibold tabular-nums whitespace-nowrap">
                                        {{ number_format(array_sum($section['revised']), 2) }}
                                    </td>
                                    @foreach ($monthLabels as $monthNo => $label)
                                        <td class="px-2 py-2 text-right font-semibold tabular-nums whitespace-nowrap">
                                            {{ number_format($section['revised'][$monthNo] ?? 0, 2) }}
                                        </td>
                                    @endforeach
                                    @if (!$budget->isClosed())
                                        <td></td>
                                    @endif
                                </tr>

                                <tr class="{{ $sectionClasses }}" x-show="showActuals">
                                    <td class="px-3 py-2 font-semibold border-r border-current/10">
                                        Total {{ $section['label'] }} — Actuals
                                    </td>
                                    <td class="px-3 py-2 text-right font-semibold tabular-nums whitespace-nowrap">
                                        {{ number_format(array_sum($section['actuals']), 2) }}
                                    </td>
                                    @foreach ($monthLabels as $monthNo => $label)
                                        <td class="px-2 py-2 text-right font-semibold tabular-nums whitespace-nowrap">
                                            {{ number_format($section['actuals'][$monthNo] ?? 0, 2) }}
                                        </td>
                                    @endforeach
                                    @if (!$budget->isClosed())
                                        <td></td>
                                    @endif
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="{{ !$budget->isClosed() ? 15 : 14 }}"
                                    class="px-4 py-6 text-center text-gray-400">
                                    No budget lines yet. Add a line below.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    <tfoot>
                        <tr x-show="showAdopted">
                            <th class="px-3 py-2.5 text-left font-semibold border-t border-slate-500"
                                style="background-color: #1e293b; color: #ffffff;">
                                Entity Total — Adopted
                            </th>
                            <th class="px-3 py-2.5 text-right font-semibold tabular-nums whitespace-nowrap"
                                style="background-color: #1e293b; color: #ffffff;">
                                {{ number_format(array_sum($budgetNetMovementTotals['adopted']), 2) }}
                            </th>
                            @foreach ($monthLabels as $monthNo => $label)
                                <th class="px-2 py-2.5 text-right font-semibold tabular-nums whitespace-nowrap"
                                    style="background-color: #1e293b; color: #ffffff;">
                                    {{ number_format($budgetNetMovementTotals['adopted'][$monthNo] ?? 0, 2) }}
                                </th>
                            @endforeach
                            @if (!$budget->isClosed())
                                <th style="background-color: #1e293b; color: #ffffff;"></th>
                            @endif
                        </tr>

                        <tr x-show="showRevised">
                            <th class="bg-slate-700 px-3 py-2.5 text-left font-semibold text-white border-t border-slate-500">
                                Entity Total — Revised
                            </th>
                            <th class="bg-slate-700 px-3 py-2.5 text-right font-semibold text-white tabular-nums whitespace-nowrap">
                                {{ number_format(array_sum($budgetNetMovementTotals['revised']), 2) }}
                            </th>
                            @foreach ($monthLabels as $monthNo => $label)
                                <th class="bg-slate-700 px-2 py-2.5 text-right font-semibold text-white tabular-nums whitespace-nowrap">
                                    {{ number_format($budgetNetMovementTotals['revised'][$monthNo] ?? 0, 2) }}
                                </th>
                            @endforeach
                            @if (!$budget->isClosed())
                                <th class="bg-slate-700"></th>
                            @endif
                        </tr>

                        <tr x-show="showActuals">
                            <th class="bg-slate-900 px-3 py-2.5 text-left font-semibold text-white border-t border-slate-500">
                                Entity Total — Actuals
                            </th>
                            <th class="bg-slate-900 px-3 py-2.5 text-right font-semibold text-white tabular-nums whitespace-nowrap">
                                {{ number_format(array_sum($budgetNetMovementTotals['actuals']), 2) }}
                            </th>
                            @foreach ($monthLabels as $monthNo => $label)
                                <th class="bg-slate-900 px-2 py-2.5 text-right font-semibold text-white tabular-nums whitespace-nowrap">
                                    {{ number_format($budgetNetMovementTotals['actuals'][$monthNo] ?? 0, 2) }}
                                </th>
                            @endforeach
                            @if (!$budget->isClosed())
                                <th class="bg-slate-900"></th>
                            @endif
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if (!empty($editLineId))
                @php
                    $editLine = $lines->firstWhere('id', (int) $editLineId);
                    $editMonths = $editLine?->months->keyBy('monthno');
                    $isRevisedEdit = $editField === 'revised';
                @endphp

                @if ($editLine)
                    <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
                         role="dialog"
                         aria-modal="true"
                         aria-labelledby="budget-edit-title">
                        <a href="{{ route('cashbook.budgets.lines.index', $budget) }}"
                           class="absolute inset-0 bg-gray-900/50"
                           aria-label="Close edit budget form"></a>

                        <div class="relative z-10 w-full max-w-4xl rounded-lg bg-white shadow-xl">
                            <div class="flex items-start justify-between border-b border-gray-200 px-6 py-4">
                                <div>
                                    <h3 id="budget-edit-title" class="text-lg font-semibold text-gray-900">
                                        Edit {{ $isRevisedEdit ? 'Revised' : 'Adopted' }} Budget
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-600">
                                        {{ $editLine->account?->accountname ?? 'Unknown account' }}
                                        <span class="mx-1">—</span>
                                        {{ $editLine->category?->categoryname ?? 'Unknown category' }}
                                    </p>
                                </div>

                                <a href="{{ route('cashbook.budgets.lines.index', $budget) }}"
                                   class="rounded px-2 py-1 text-xl leading-none text-gray-400 hover:bg-gray-100 hover:text-gray-700"
                                   aria-label="Close">
                                    &times;
                                </a>
                            </div>

                            <form method="POST"
                                  action="{{ route('cashbook.budgets.lines.updateMonths', [$budget, $editLine]) }}">
                                @csrf
                                <input type="hidden"
                                       name="field"
                                       value="{{ $isRevisedEdit ? 'revised' : 'adopted' }}">

                                <div class="px-6 py-5">
                                    <p class="mb-4 text-sm text-gray-600">
                                        Enter monthly amounts from July to June. The grid total is recalculated when you save.
                                    </p>

                                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                                        @foreach ($monthLabels as $monthNo => $label)
                                            @php
                                                $month = $editMonths->get($monthNo);
                                                $amount = $isRevisedEdit
                                                    ? $month?->effectiveRevisedAmount()
                                                    : $month?->adoptedamount;
                                            @endphp

                                            <div>
                                                <label class="mb-1 block text-xs font-medium text-gray-600">
                                                    {{ $label }}
                                                </label>
                                                <input type="number"
                                                       name="amounts[]"
                                                       min="0"
                                                       step="0.01"
                                                       required
                                                       value="{{ old('amounts.' . ($monthNo - 1), number_format($amount ?? 0, 2, '.', '')) }}"
                                                       class="w-full rounded border-gray-300 text-right text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4">
                                    <a href="{{ route('cashbook.budgets.lines.index', $budget) }}"
                                       class="rounded bg-white px-4 py-2 text-sm text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-100">
                                        Cancel
                                    </a>
                                    <button type="submit"
                                            class="rounded bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                        Save {{ $isRevisedEdit ? 'Revised' : 'Adopted' }} Budget
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            @endif

            @if (!empty($methodLineId))
            @php
                $methodLine = $lines->firstWhere('id', (int) $methodLineId);
                $isRevisedMethod = ($methodField ?? 'adopted') === 'revised';
            @endphp

            @if ($methodLine)
                <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="budget-method-title">

                    <a href="{{ route('cashbook.budgets.lines.index', $budget) }}"
                    class="absolute inset-0 bg-gray-900/50"
                    aria-label="Close preparation method"></a>

                    <div class="relative z-10 w-full max-w-3xl rounded-lg bg-white shadow-xl"
                        x-data="{ method: '{{ old('method', 'equal') }}' }">

                        <div class="flex items-start justify-between border-b border-gray-200 px-6 py-4">
                            <div>
                                <h3 id="budget-method-title" class="text-lg font-semibold text-gray-900">
                                    Prepare {{ $isRevisedMethod ? 'Revised' : 'Adopted' }} Budget
                                </h3>

                                <p class="mt-1 text-sm text-gray-600">
                                    {{ $methodLine->category?->categoryname ?? 'Unknown category' }}
                                </p>
                            </div>

                            <a href="{{ route('cashbook.budgets.lines.index', $budget) }}"
                            class="rounded px-2 py-1 text-xl leading-none text-gray-400 hover:bg-gray-100 hover:text-gray-700"
                            aria-label="Close">
                                &times;
                            </a>
                        </div>

                        <form method="POST"
                            action="{{ route('cashbook.budgets.lines.applyMethod', [$budget, $methodLine]) }}">
                            @csrf

                            <input type="hidden"
                                name="field"
                                value="{{ $methodField ?? 'adopted' }}">

                            <div class="space-y-5 px-6 py-5">
                                <p class="text-sm text-gray-600">
                                    Select a method to populate July–June. You can then fine-tune individual months using Edit.
                                </p>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">
                                        Preparation method
                                    </label>

                                    <div class="space-y-3">
                                        <label class="flex cursor-pointer items-start gap-2">
                                            <input type="radio"
                                                name="method"
                                                value="equal"
                                                x-model="method"
                                                class="mt-1 text-blue-600">

                                            <span>
                                                <span class="block text-sm font-medium text-gray-800">
                                                    Equal annual allocation
                                                </span>
                                                <span class="block text-xs text-gray-600">
                                                    Divide one annual total equally over July–June. June receives any rounding difference.
                                                </span>
                                            </span>
                                        </label>

                                        <label class="flex cursor-pointer items-start gap-2">
                                            <input type="radio"
                                                name="method"
                                                value="prior_year_adjusted"
                                                x-model="method"
                                                class="mt-1 text-blue-600">

                                            <span>
                                                <span class="block text-sm font-medium text-gray-800">
                                                    Prior-year actuals with adjustment
                                                </span>
                                                <span class="block text-xs text-gray-600">
                                                    Use last year’s monthly pattern, adjusted by a percentage.
                                                </span>
                                            </span>
                                        </label>

                                        <label class="flex cursor-pointer items-start gap-2">
                                            <input type="radio"
                                                name="method"
                                                value="copy_prior_year_actuals"
                                                x-model="method"
                                                class="mt-1 text-blue-600">

                                            <span>
                                                <span class="block text-sm font-medium text-gray-800">
                                                    Copy prior-year actuals
                                                </span>
                                                <span class="block text-xs text-gray-600">
                                                    Copy last year’s July–June actual values without change.
                                                </span>
                                            </span>
                                        </label>

                                        @if ($isRevisedMethod)
                                            <label class="flex cursor-pointer items-start gap-2">
                                                <input type="radio"
                                                    name="method"
                                                    value="lock_actuals"
                                                    x-model="method"
                                                    class="mt-1 text-blue-600">

                                                <span>
                                                    <span class="block text-sm font-medium text-gray-800">
                                                        Lock actuals to date
                                                    </span>
                                                    <span class="block text-xs text-gray-600">
                                                        Replace completed revised months with current-year actuals. Future months remain unchanged.
                                                    </span>
                                                </span>
                                            </label>
                                        @endif
                                    </div>
                                </div>

                                <div x-show="method === 'equal'" x-cloak>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">
                                        Annual total
                                    </label>

                                    <input type="number"
                                        name="total"
                                        step="0.01"
                                        value="{{ old('total') }}"
                                        class="w-48 rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">

                                    <p class="mt-1 text-xs text-gray-500">
                                        Negative values are allowed for planned refunds, rebates, or other adjustments.
                                    </p>
                                </div>

                                <div x-show="method === 'prior_year_adjusted'" x-cloak>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">
                                        Prior-year adjustment (%)
                                    </label>

                                    <input type="number"
                                        name="percentage_adjustment"
                                        step="0.01"
                                        min="-100"
                                        max="1000"
                                        value="{{ old('percentage_adjustment', 0) }}"
                                        class="w-48 rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">

                                    <p class="mt-1 text-xs text-gray-500">
                                        Enter 5 for a 5% increase, -10 for a 10% reduction, or 0 to copy the same annual total using the prior-year pattern.
                                    </p>
                                </div>

                                <div x-show="method === 'copy_prior_year_actuals'"
                                    x-cloak
                                    class="rounded border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                                    The prior financial year’s July–June actual values will be copied unchanged.
                                </div>

                                @if ($isRevisedMethod)
                                    <div x-show="method === 'lock_actuals'" x-cloak>
                                        <label class="mb-1 block text-sm font-medium text-gray-700">
                                            Lock actuals through
                                        </label>

                                        <select name="lock_thru_month"
                                                class="w-48 rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            @foreach ($monthLabels as $no => $label)
                                                <option value="{{ $no }}"
                                                    {{ (int) old('lock_thru_month') === (int) $no ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4">
                                <a href="{{ route('cashbook.budgets.lines.index', $budget) }}"
                                class="rounded bg-white px-4 py-2 text-sm text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-100">
                                    Cancel
                                </a>

                                <button type="submit"
                                        class="cursor-pointer rounded bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                    Apply Method
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        @endif
        </div>
    </div>
</x-app-layout>