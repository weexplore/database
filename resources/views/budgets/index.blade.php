<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Budgets
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            {{-- Flash messages --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow rounded-lg">

                {{-- Filters --}}
                <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Legal entity</label>
                        <form method="GET" action="{{ route('cashbook.budgets.index') }}" class="flex items-end gap-3">
                            <select name="legalentityid" class="border-gray-300 rounded shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500 w-64">
                                <option value="">All</option>
                                @foreach ($legalEntities as $entity)
                                    <option value="{{ $entity->id }}" {{ (string) $selectedEntityId === (string) $entity->id ? 'selected' : '' }}>
                                        {{ $entity->entitycode ?? '' }}{{ $entity->entitycode ? ' — ' : '' }}{{ $entity->entityname ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">Filter</button>
                            <a href="{{ route('cashbook.budgets.index') }}" class="px-3 py-2 bg-gray-100 text-gray-700 text-xs rounded hover:bg-gray-200">Reset</a>
                        </form>
                    </div>
                    <div class="ml-auto">
                        <a href="{{ route('cashbook.budgets.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700">
                            + New Budget
                        </a>
                    </div>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Financial Year</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Legal Entity</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Adopted Date</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Revised Date</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Prepared By</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Lines</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($headers as $header)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium">
                                        <a href="{{ route('cashbook.budgets.lines.index', $header) }}" class="text-blue-600 hover:underline">
                                            {{ $header->year_label }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ optional($header->legalEntity)->code }} {{ optional($header->legalEntity)->name }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $statusColour = match($header->status) {
                                                'draft'    => 'bg-gray-100 text-gray-700',
                                                'adopted'  => 'bg-blue-100 text-blue-700',
                                                'revised'  => 'bg-yellow-100 text-yellow-700',
                                                'closed'   => 'bg-green-100 text-green-700',
                                                default    => 'bg-gray-100 text-gray-700',
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $statusColour }}">
                                            {{ ucfirst($header->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $header->adopteddate?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $header->reviseddate?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $header->preparedby ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $header->budget_lines_count ?? $header->budgetLines->count() }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <a href="{{ route('cashbook.budgets.lines.index', $header) }}" class="text-blue-600 hover:underline text-xs">Lines</a>
                                            <a href="{{ route('cashbook.budgets.edit', $header) }}" class="text-gray-600 hover:underline text-xs">Edit</a>
                                            @if ($header->isDraft())
                                                <form method="POST" action="{{ route('cashbook.budgets.adopt', $header) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" onclick="return confirm('Adopt this budget? Adopted amounts will be locked.')" class="text-blue-700 hover:underline text-xs">Adopt</button>
                                                </form>
                                                <form method="POST" action="{{ route('cashbook.budgets.destroy', $header) }}" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" onclick="return confirm('Delete this draft budget?')" class="text-red-600 hover:underline text-xs">Delete</button>
                                                </form>
                                            @endif
                                            @if ($header->isAdopted())
                                                <form method="POST" action="{{ route('cashbook.budgets.revise', $header) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-yellow-700 hover:underline text-xs">Revise</button>
                                                </form>
                                                <form method="POST" action="{{ route('cashbook.budgets.reopen', $header) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" onclick="return confirm('Reopen this budget to draft? The adopted date will be cleared.')" class="text-gray-500 hover:underline text-xs">Reopen</button>
                                                </form>
                                                <form method="POST" action="{{ route('cashbook.budgets.close', $header) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" onclick="return confirm('Close this budget?')" class="text-green-700 hover:underline text-xs">Close</button>
                                                </form>
                                            @endif
                                            @if ($header->isRevised())
                                                <form method="POST" action="{{ route('cashbook.budgets.close', $header) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" onclick="return confirm('Close this budget?')" class="text-green-700 hover:underline text-xs">Close</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-gray-400">No budgets found. Create one to get started.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
