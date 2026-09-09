<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Trip Expenses
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $trip->tripname }}
                </p>
            </div>

            <a
                href="{{ route('trips.edit', $trip) }}"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
            >
                Back to Trip
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto space-y-6 px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12">

            @if (session('success'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <div class="font-semibold">
                        Please correct the following:
                    </div>

                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <nav class="flex flex-wrap gap-2 border-b border-gray-200 pb-4" aria-label="Trip sections">
                <a
                    href="{{ route('trips.edit', ['trip' => $trip, 'tab' => 'details']) }}"
                    class="inline-flex items-center rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 hover:text-gray-900"
                >
                    Detail
                </a>

                <a
                    href="{{ route('trips.edit', ['trip' => $trip, 'tab' => 'notes']) }}"
                    class="inline-flex items-center rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 hover:text-gray-900"
                >
                    Notes
                </a>

                <a
                    href="{{ route('trips.edit', ['trip' => $trip, 'tab' => 'budget']) }}"
                    class="inline-flex items-center rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 hover:text-gray-900"
                >
                    Budget
                </a>

                <a
                    href="{{ route('trips.expenses.index', $trip) }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm"
                >
                    Expenses
                </a>

                <a
                    href="{{ route('trips.edit', ['trip' => $trip, 'tab' => 'vehicles']) }}"
                    class="inline-flex items-center rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 hover:text-gray-900"
                >
                    Vehicles
                </a>

                <a
                    href="{{ route('trips.edit', ['trip' => $trip, 'tab' => 'travellers']) }}"
                    class="inline-flex items-center rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 hover:text-gray-900"
                >
                    Travellers
                </a>

                <a
                    href="{{ route('trips.edit', ['trip' => $trip, 'tab' => 'workflow']) }}"
                    class="inline-flex items-center rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 hover:text-gray-900"
                >
                    Workflow
                </a>
            </nav>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                        Food Actual
                    </div>

                    <div class="mt-1 text-xl font-bold tabular-nums text-green-800">
                        ${{ number_format((float) ($totals['food_actual'] ?? 0), 2) }}
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                        Misc Actual
                    </div>

                    <div class="mt-1 text-xl font-bold tabular-nums text-green-800">
                        ${{ number_format((float) ($totals['misc_actual'] ?? 0), 2) }}
                    </div>
                </div>

                <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 shadow-sm">
                    <div class="text-xs font-medium uppercase tracking-wide text-blue-700">
                        Total Actual Expenses
                    </div>

                    <div class="mt-1 text-xl font-bold tabular-nums text-blue-950">
                        ${{ number_format((float) ($totals['overall_actual'] ?? 0), 2) }}
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-4 py-4 sm:px-6">
                    <h3 class="text-base font-semibold text-gray-900">
                        Add Expense
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Record actual food and miscellaneous spending. Stays, trip items, and fuel are entered in their own trip sections.
                    </p>
                </div>

                <form
                    method="POST"
                    action="{{ route('trips.expenses.store', $trip) }}"
                    class="p-4 sm:p-6"
                >
                    @csrf

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-7">
                        <div>
                            <label for="expensedate" class="block text-sm font-medium text-gray-700">
                                Date
                            </label>

                            <input
                                id="expensedate"
                                name="expensedate"
                                type="date"
                                required
                                value="{{ old('expensedate', now()->toDateString()) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>

                        <div>
                            <label for="expensecategory" class="block text-sm font-medium text-gray-700">
                                Category
                            </label>

                            <select
                                id="expensecategory"
                                name="expensecategory"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                @foreach ($expenseCategories as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @selected(old('expensecategory', 'food') === $value)
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="xl:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">
                                Description
                            </label>

                            <input
                                id="description"
                                name="description"
                                type="text"
                                required
                                maxlength="200"
                                value="{{ old('description') }}"
                                placeholder="e.g. Groceries, bakery lunch, laundry"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>

                        <div>
                            <label for="payee" class="block text-sm font-medium text-gray-700">
                                Payee
                            </label>

                            <input
                                id="payee"
                                name="payee"
                                type="text"
                                maxlength="200"
                                value="{{ old('payee') }}"
                                placeholder="Optional"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>

                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700">
                                Amount $
                            </label>

                            <input
                                id="amount"
                                name="amount"
                                type="number"
                                min="0.01"
                                max="999999.99"
                                step="0.01"
                                required
                                value="{{ old('amount') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>

                        <div>
                            <label for="currency" class="block text-sm font-medium text-gray-700">
                                Currency
                            </label>

                            <select
                                id="currency"
                                name="currency"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="AUD" @selected(old('currency', 'AUD') === 'AUD')>
                                    AUD
                                </option>

                                <option value="NZD" @selected(old('currency') === 'NZD')>
                                    NZD
                                </option>

                                <option value="USD" @selected(old('currency') === 'USD')>
                                    USD
                                </option>

                                <option value="GBP" @selected(old('currency') === 'GBP')>
                                    GBP
                                </option>

                                <option value="EUR" @selected(old('currency') === 'EUR')>
                                    EUR
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <div>
                            <label for="paymentmethod" class="block text-sm font-medium text-gray-700">
                                Payment Method
                            </label>

                            <select
                                id="paymentmethod"
                                name="paymentmethod"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Not recorded</option>

                                @foreach ($paymentMethods as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @selected(old('paymentmethod') === $value)
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="triplegid" class="block text-sm font-medium text-gray-700">
                                Trip Leg
                            </label>

                            <select
                                id="triplegid"
                                name="triplegid"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Not linked to a leg</option>

                                @foreach ($tripLegs as $tripLeg)
                                    <option
                                        value="{{ $tripLeg->id }}"
                                        @selected((string) old('triplegid') === (string) $tripLeg->id)
                                    >
                                        Leg {{ $tripLeg->legnumber }}
                                        @if ($tripLeg->title)
                                            — {{ $tripLeg->title }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="tripstayid" class="block text-sm font-medium text-gray-700">
                                Trip Stay
                            </label>

                            <select
                                id="tripstayid"
                                name="tripstayid"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Not linked to a stay</option>

                                @foreach ($tripStays as $tripStay)
                                    <option
                                        value="{{ $tripStay->id }}"
                                        @selected((string) old('tripstayid') === (string) $tripStay->id)
                                    >
                                        {{ $tripStay->stayname }}
                                        @if ($tripStay->checkindate)
                                            — {{ $tripStay->checkindate->format('d M Y') }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="subcategory" class="block text-sm font-medium text-gray-700">
                                Subcategory
                            </label>

                            <input
                                id="subcategory"
                                name="subcategory"
                                type="text"
                                maxlength="100"
                                value="{{ old('subcategory') }}"
                                placeholder="Optional"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>

                        <div class="xl:col-span-2">
                            <label for="receiptreference" class="block text-sm font-medium text-gray-700">
                                Receipt Reference
                            </label>

                            <input
                                id="receiptreference"
                                name="receiptreference"
                                type="text"
                                maxlength="150"
                                value="{{ old('receiptreference') }}"
                                placeholder="Optional receipt number or file reference"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>

                        <div class="md:col-span-2 xl:col-span-4">
                            <label for="notes" class="block text-sm font-medium text-gray-700">
                                Notes
                            </label>

                            <textarea
                                id="notes"
                                name="notes"
                                rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end">
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            Add Expense
                        </button>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-4 py-4 sm:px-6">
                    <h3 class="text-base font-semibold text-gray-900">
                        Recorded Expenses
                    </h3>
                </div>

                @if ($expenses->isEmpty())
                    <div class="px-4 py-10 text-center text-sm text-gray-500">
                        No expenses have been entered for this trip yet.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                        Date
                                    </th>

                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                        Category
                                    </th>

                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                        Description
                                    </th>

                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                        Payee
                                    </th>

                                    <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                                        Amount
                                    </th>

                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                        Payment
                                    </th>

                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                        Notes
                                    </th>

                                    <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($expenses as $expense)
                                    <tr>
                                        <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-700">
                                            {{ $expense->expensedate?->format('d M Y') ?? '—' }}
                                        </td>

                                        <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-700">
                                            {{ $expense->category_label }}
                                        </td>

                                        <td class="px-3 py-3 text-sm font-medium text-gray-900">
                                            <div>
                                                {{ $expense->description }}
                                            </div>

                                            @if ($expense->subcategory)
                                                <div class="mt-1 text-xs text-gray-500">
                                                    {{ $expense->subcategory }}
                                                </div>
                                            @endif
                                        </td>

                                        <td class="px-3 py-3 text-sm text-gray-700">
                                            {{ $expense->payee ?: '—' }}
                                        </td>

                                        <td class="whitespace-nowrap px-3 py-3 text-right text-sm font-semibold tabular-nums text-green-800">
                                            {{ $expense->currency ?: 'AUD' }}
                                            ${{ number_format((float) $expense->amount, 2) }}
                                        </td>

                                        <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-700">
                                            {{ $expense->payment_method_label ?: '—' }}
                                        </td>

                                        <td class="max-w-md px-3 py-3 text-sm text-gray-600">
                                            @if ($expense->notes)
                                                <div>{{ $expense->notes }}</div>
                                            @endif

                                            @if ($expense->receiptreference)
                                                <div class="mt-1 text-xs text-gray-500">
                                                    Receipt: {{ $expense->receiptreference }}
                                                </div>
                                            @endif

                                            @if (! $expense->notes && ! $expense->receiptreference)
                                                —
                                            @endif
                                        </td>

                                        <td class="whitespace-nowrap px-3 py-3 text-right text-sm">
                                            <div class="flex justify-end gap-3">
                                                <button
                                                    type="button"
                                                    class="text-blue-700 underline decoration-blue-300 underline-offset-2 hover:text-blue-900"
                                                    onclick="document.getElementById('edit-expense-{{ $expense->id }}').classList.toggle('hidden');"
                                                >
                                                    Edit
                                                </button>

                                                <button
                                                    type="button"
                                                    class="text-red-700 underline decoration-red-300 underline-offset-2 hover:text-red-900"
                                                    onclick="if (confirm('Delete this expense?')) document.getElementById('delete-expense-{{ $expense->id }}').submit();"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr
                                        id="edit-expense-{{ $expense->id }}"
                                        class="hidden bg-blue-50"
                                    >
                                        <td colspan="8" class="px-4 py-4">
                                            <form
                                                method="POST"
                                                action="{{ route('trips.expenses.update', [$trip, $expense]) }}"
                                                class="space-y-4"
                                            >
                                                @csrf
                                                @method('PUT')

                                                <div class="flex flex-wrap items-center justify-between gap-3">
                                                    <div>
                                                        <h4 class="text-sm font-semibold text-gray-900">
                                                            Edit Expense
                                                        </h4>

                                                        <p class="mt-1 text-xs text-gray-600">
                                                            Update the recorded trip expense, then save the changes.
                                                        </p>
                                                    </div>

                                                    <button
                                                        type="button"
                                                        class="text-sm font-medium text-gray-600 underline decoration-gray-300 underline-offset-2 hover:text-gray-900"
                                                        onclick="document.getElementById('edit-expense-{{ $expense->id }}').classList.add('hidden');"
                                                    >
                                                        Cancel
                                                    </button>
                                                </div>

                                                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-7">
                                                    <div>
                                                        <label
                                                            for="edit-expensedate-{{ $expense->id }}"
                                                            class="block text-sm font-medium text-gray-700"
                                                        >
                                                            Date
                                                        </label>

                                                        <input
                                                            id="edit-expensedate-{{ $expense->id }}"
                                                            name="expensedate"
                                                            type="date"
                                                            required
                                                            value="{{ old('expensedate', $expense->expensedate?->format('Y-m-d')) }}"
                                                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                        >
                                                    </div>

                                                    <div>
                                                        <label
                                                            for="edit-expensecategory-{{ $expense->id }}"
                                                            class="block text-sm font-medium text-gray-700"
                                                        >
                                                            Category
                                                        </label>

                                                        <select
                                                            id="edit-expensecategory-{{ $expense->id }}"
                                                            name="expensecategory"
                                                            required
                                                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                        >
                                                            @foreach ($expenseCategories as $value => $label)
                                                                <option
                                                                    value="{{ $value }}"
                                                                    @selected(old('expensecategory', $expense->expensecategory) === $value)
                                                                >
                                                                    {{ $label }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="xl:col-span-2">
                                                        <label
                                                            for="edit-description-{{ $expense->id }}"
                                                            class="block text-sm font-medium text-gray-700"
                                                        >
                                                            Description
                                                        </label>

                                                        <input
                                                            id="edit-description-{{ $expense->id }}"
                                                            name="description"
                                                            type="text"
                                                            required
                                                            maxlength="200"
                                                            value="{{ old('description', $expense->description) }}"
                                                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                        >
                                                    </div>

                                                    <div>
                                                        <label
                                                            for="edit-payee-{{ $expense->id }}"
                                                            class="block text-sm font-medium text-gray-700"
                                                        >
                                                            Payee
                                                        </label>

                                                        <input
                                                            id="edit-payee-{{ $expense->id }}"
                                                            name="payee"
                                                            type="text"
                                                            maxlength="200"
                                                            value="{{ old('payee', $expense->payee) }}"
                                                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                        >
                                                    </div>

                                                    <div>
                                                        <label
                                                            for="edit-amount-{{ $expense->id }}"
                                                            class="block text-sm font-medium text-gray-700"
                                                        >
                                                            Amount $
                                                        </label>

                                                        <input
                                                            id="edit-amount-{{ $expense->id }}"
                                                            name="amount"
                                                            type="number"
                                                            min="0.01"
                                                            max="999999.99"
                                                            step="0.01"
                                                            required
                                                            value="{{ old('amount', $expense->amount) }}"
                                                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                        >
                                                    </div>

                                                    <div>
                                                        <label
                                                            for="edit-currency-{{ $expense->id }}"
                                                            class="block text-sm font-medium text-gray-700"
                                                        >
                                                            Currency
                                                        </label>

                                                        <select
                                                            id="edit-currency-{{ $expense->id }}"
                                                            name="currency"
                                                            required
                                                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                        >
                                                            @foreach (['AUD', 'NZD', 'USD', 'GBP', 'EUR'] as $currency)
                                                                <option
                                                                    value="{{ $currency }}"
                                                                    @selected(old('currency', $expense->currency ?: 'AUD') === $currency)
                                                                >
                                                                    {{ $currency }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                                    <div>
                                                        <label
                                                            for="edit-paymentmethod-{{ $expense->id }}"
                                                            class="block text-sm font-medium text-gray-700"
                                                        >
                                                            Payment Method
                                                        </label>

                                                        <select
                                                            id="edit-paymentmethod-{{ $expense->id }}"
                                                            name="paymentmethod"
                                                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                        >
                                                            <option value="">Not recorded</option>

                                                            @foreach ($paymentMethods as $value => $label)
                                                                <option
                                                                    value="{{ $value }}"
                                                                    @selected(old('paymentmethod', $expense->paymentmethod) === $value)
                                                                >
                                                                    {{ $label }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <label
                                                            for="edit-triplegid-{{ $expense->id }}"
                                                            class="block text-sm font-medium text-gray-700"
                                                        >
                                                            Trip Leg
                                                        </label>

                                                        <select
                                                            id="edit-triplegid-{{ $expense->id }}"
                                                            name="triplegid"
                                                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                        >
                                                            <option value="">Not linked to a leg</option>

                                                            @foreach ($tripLegs as $tripLeg)
                                                                <option
                                                                    value="{{ $tripLeg->id }}"
                                                                    @selected((string) old('triplegid', $expense->triplegid) === (string) $tripLeg->id)
                                                                >
                                                                    Leg {{ $tripLeg->legnumber }}
                                                                    @if ($tripLeg->title)
                                                                        — {{ $tripLeg->title }}
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <label
                                                            for="edit-tripstayid-{{ $expense->id }}"
                                                            class="block text-sm font-medium text-gray-700"
                                                        >
                                                            Trip Stay
                                                        </label>

                                                        <select
                                                            id="edit-tripstayid-{{ $expense->id }}"
                                                            name="tripstayid"
                                                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                        >
                                                            <option value="">Not linked to a stay</option>

                                                            @foreach ($tripStays as $tripStay)
                                                                <option
                                                                    value="{{ $tripStay->id }}"
                                                                    @selected((string) old('tripstayid', $expense->tripstayid) === (string) $tripStay->id)
                                                                >
                                                                    {{ $tripStay->stayname }}
                                                                    @if ($tripStay->checkindate)
                                                                        — {{ $tripStay->checkindate->format('d M Y') }}
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <label
                                                            for="edit-subcategory-{{ $expense->id }}"
                                                            class="block text-sm font-medium text-gray-700"
                                                        >
                                                            Subcategory
                                                        </label>

                                                        <input
                                                            id="edit-subcategory-{{ $expense->id }}"
                                                            name="subcategory"
                                                            type="text"
                                                            maxlength="100"
                                                            value="{{ old('subcategory', $expense->subcategory) }}"
                                                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                        >
                                                    </div>

                                                    <div class="xl:col-span-2">
                                                        <label
                                                            for="edit-receiptreference-{{ $expense->id }}"
                                                            class="block text-sm font-medium text-gray-700"
                                                        >
                                                            Receipt Reference
                                                        </label>

                                                        <input
                                                            id="edit-receiptreference-{{ $expense->id }}"
                                                            name="receiptreference"
                                                            type="text"
                                                            maxlength="150"
                                                            value="{{ old('receiptreference', $expense->receiptreference) }}"
                                                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                        >
                                                    </div>

                                                    <div class="md:col-span-2 xl:col-span-4">
                                                        <label
                                                            for="edit-notes-{{ $expense->id }}"
                                                            class="block text-sm font-medium text-gray-700"
                                                        >
                                                            Notes
                                                        </label>

                                                        <textarea
                                                            id="edit-notes-{{ $expense->id }}"
                                                            name="notes"
                                                            rows="2"
                                                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                        >{{ old('notes', $expense->notes) }}</textarea>
                                                    </div>
                                                </div>

                                                <div class="flex justify-end gap-3 border-t border-blue-100 pt-4">
                                                    <button
                                                        type="button"
                                                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                                                        onclick="document.getElementById('edit-expense-{{ $expense->id }}').classList.add('hidden');"
                                                    >
                                                        Cancel
                                                    </button>

                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                                    >
                                                        Save Changes
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr class="border-t-2 border-blue-700 bg-blue-50 text-gray-900">
                                    <th
                                        colspan="4"
                                        scope="row"
                                        class="px-3 py-3 text-left text-sm font-bold text-blue-950"
                                    >
                                        Expense Total
                                    </th>

                                    <th class="border-l border-blue-200 px-3 py-3 text-right text-sm font-bold tabular-nums text-green-800">
                                        ${{ number_format((float) ($totals['overall_actual'] ?? 0), 2) }}
                                    </th>

                                    <th colspan="3" class="px-3 py-3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Separate forms prevent invalid nested forms inside the table. --}}
                    @foreach ($expenses as $expense)
                        <form
                            id="delete-expense-{{ $expense->id }}"
                            method="POST"
                            action="{{ route('trips.expenses.destroy', [$trip, $expense]) }}"
                            class="hidden"
                        >
                            @csrf
                            @method('DELETE')
                        </form>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</x-app-layout>