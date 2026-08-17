<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            New Budget
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            {{-- Validation errors --}}
            @if ($errors->any())
                <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Create Budget</h3>
                </div>

                <form method="POST" action="{{ route('cashbook.budgets.store') }}" class="px-6 py-6 space-y-5">
                    @csrf
                    {{-- Legal Entity --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Legal Entity <span class="text-red-500">*</span>
                        </label>
                        <select name="legalentityid"
                                class="w-80 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                                required>
                            <option value="">— select —</option>
                            @foreach ($legalEntities as $entity)
                                <option value="{{ $entity->id }}"
                                    {{ (string) old('legalentityid', $selectedEntityId) === (string) $entity->id ? 'selected' : '' }}>
                                    {{ $entity->entitycode ?? '' }}{{ $entity->entitycode ? ' — ' : '' }}{{ $entity->entityname ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Financial Year --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Financial Year <span class="text-red-500">*</span>
                        </label>
                        <select name="financialyear"
                                class="w-48 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                                required>
                            @for ($y = now()->year - 1; $y <= now()->year + 3; $y++)
                                @php $label = 'FY' . ($y - 1) . '-' . substr($y, -2); @endphp
                                <option value="{{ $y }}"
                                    {{ old('financialyear', $suggestedYear) == $y ? 'selected' : '' }}
                                    {{ in_array($y, $existingYears) ? 'disabled' : '' }}>
                                    {{ $label }}{{ in_array($y, $existingYears) ? ' (exists)' : '' }}
                                </option>
                            @endfor
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Year ending 30 June — e.g. FY2025-26 = year ending June 2026.</p>
                    </div>

                    {{-- Default Budget Account --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Default Budget Account <span class="text-red-500">*</span>
                        </label>
                        <select name="default_accountid"
                                class="w-80 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                                required>
                            <option value="">— select —</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}"
                                    {{ (string) old('default_accountid') === (string) $account->id ? 'selected' : '' }}>
                                    {{ $account->accountname }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Used to seed one budget line per category. You can change accounts later per line if needed.
                        </p>
                    </div>

                    {{-- Prepared By --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prepared By</label>
                        <input type="text" name="preparedby" value="{{ old('preparedby') }}"
                               maxlength="150"
                               class="w-80 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>

                    {{-- Adopted Notes --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adopted Budget Notes</label>
                        <textarea name="adoptednotes" rows="3"
                                  class="w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">{{ old('adoptednotes') }}</textarea>
                    </div>

                    {{-- Revised Notes --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Revised Budget Notes</label>
                        <textarea name="revisednotes" rows="3"
                                  class="w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">{{ old('revisednotes') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Optional — can be completed when the revision is prepared.</p>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit"
                                class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700">
                            Create Budget
                        </button>
                        <a href="{{ route('cashbook.budgets.index') }}"
                           class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded hover:bg-gray-200">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
