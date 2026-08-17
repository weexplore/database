<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Budget — {{ $budget->year_label }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @if ($errors->any())
                <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center gap-3">
                    <h3 class="text-lg font-medium text-gray-900">Budget Details</h3>
                    @php
                        $statusColour = match($budget->status) {
                            'draft'   => 'bg-gray-100 text-gray-700',
                            'adopted' => 'bg-blue-100 text-blue-700',
                            'revised' => 'bg-yellow-100 text-yellow-700',
                            'closed'  => 'bg-green-100 text-green-700',
                            default   => 'bg-gray-100 text-gray-700',
                        };
                    @endphp
                    <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $statusColour }}">
                        {{ ucfirst($budget->status) }}
                    </span>
                </div>

                <form method="POST" action="{{ route('cashbook.budgets.update', $budget) }}" class="px-6 py-6 space-y-5">
                    @csrf @method('PUT')

                    {{-- Prepared By --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prepared By</label>
                        <input type="text" name="preparedby"
                               value="{{ old('preparedby', $budget->preparedby) }}"
                               maxlength="150"
                               class="w-80 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>

                    {{-- Adopted Notes --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adopted Budget Notes</label>
                        <textarea name="adoptednotes" rows="3"
                                  class="w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">{{ old('adoptednotes', $budget->adoptednotes) }}</textarea>
                    </div>

                    {{-- Revised Notes --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Revised Budget Notes</label>
                        <textarea name="revisednotes" rows="3"
                                  class="w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">{{ old('revisednotes', $budget->revisednotes) }}</textarea>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit"
                                class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700">
                            Save Changes
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
