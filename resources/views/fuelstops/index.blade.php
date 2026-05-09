<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Fuel Stops
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @if (session('success'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <div class="font-semibold mb-2">Please fix the following:</div>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="p-4 border-b border-gray-200">
                    <form method="GET" action="{{ route('fuel-stops.index') }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input
                                type="text"
                                name="search"
                                id="search"
                                value="{{ request('search') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Stop, brand, fuel type">
                        </div>

                        <div>
                            <label for="place_id" class="block text-sm font-medium text-gray-700 mb-1">Place</label>
                            <select
                                name="place_id"
                                id="place_id"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All places</option>
                                @foreach ($places as $place)
                                    <option value="{{ $place->id }}" @selected((string) request('place_id') === (string) $place->id)>
                                        {{ $place->placename }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="brand" class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                            <input
                                type="text"
                                name="brand"
                                id="brand"
                                value="{{ request('brand') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Brand">
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select
                                name="status"
                                id="status"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All</option>
                                <option value="1" @selected(request('status') === '1')>Active</option>
                                <option value="0" @selected(request('status') === '0')>Inactive</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Filter
                            </button>
                            <a href="{{ route('fuel-stops.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <div class="p-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">Add Fuel Stop</h3>

                    <form method="POST" action="{{ route('fuel-stops.store') }}" class="space-y-4" data-dirty-form>
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                            <div>
                                <label for="placeid" class="block text-sm font-medium text-gray-700 mb-1">Place</label>
                                <select
                                    name="placeid"
                                    id="placeid"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required>
                                    <option value="">Select place</option>
                                    @foreach ($places as $place)
                                        <option value="{{ $place->id }}" @selected(old('placeid') == $place->id)>
                                            {{ $place->placename }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="stopname" class="block text-sm font-medium text-gray-700 mb-1">Stop Name</label>
                                <input
                                    type="text"
                                    name="stopname"
                                    id="stopname"
                                    value="{{ old('stopname') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    maxlength="200"
                                    required>
                            </div>

                            <div>
                                <label for="brandname" class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                                <input
                                    type="text"
                                    name="brandname"
                                    id="brandname"
                                    value="{{ old('brandname') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    maxlength="100">
                            </div>
                        </div>

                        <div>
                            <span class="block text-sm font-medium text-gray-700 mb-2">Fuel Types Available</span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                @foreach ($fuelTypes as $value => $label)
                                    <label class="inline-flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            name="fueltypesavailable[]"
                                            value="{{ $value }}"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm"
                                            @checked(in_array($value, old('fueltypesavailable', [])))>
                                        <span class="text-sm text-gray-700">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="hashighflowdiesel" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm" @checked(old('hashighflowdiesel'))>
                                <span class="text-sm text-gray-700">High-flow diesel</span>
                            </label>

                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="hasadblue" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm" @checked(old('hasadblue'))>
                                <span class="text-sm text-gray-700">AdBlue</span>
                            </label>

                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="hascarwash" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm" @checked(old('hascarwash'))>
                                <span class="text-sm text-gray-700">Car wash</span>
                            </label>

                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="hasairwater" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm" @checked(old('hasairwater'))>
                                <span class="text-sm text-gray-700">Air / water</span>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                            <div>
                                <label for="caravanaccessnotes" class="block text-sm font-medium text-gray-700 mb-1">Caravan Access Notes</label>
                                <textarea
                                    name="caravanaccessnotes"
                                    id="caravanaccessnotes"
                                    rows="3"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('caravanaccessnotes') }}</textarea>
                            </div>

                            <div>
                                <label for="openingnotes" class="block text-sm font-medium text-gray-700 mb-1">Opening Notes</label>
                                <textarea
                                    name="openingnotes"
                                    id="openingnotes"
                                    rows="3"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('openingnotes') }}</textarea>
                            </div>

                            <div>
                                <label for="generalnotes" class="block text-sm font-medium text-gray-700 mb-1">General Notes</label>
                                <textarea
                                    name="generalnotes"
                                    id="generalnotes"
                                    rows="3"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('generalnotes') }}</textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="isactive" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm" @checked(old('isactive', true))>
                                <span class="text-sm text-gray-700">Active</span>
                            </label>

                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Add Fuel Stop
                            </button>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Stop</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Place</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Brand</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Fuel Types</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Facilities</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($fuelStops as $fuelStop)
                                <tr>
                                    <td class="px-4 py-3 align-top">
                                        <div class="font-medium text-gray-900">{{ $fuelStop->stopname }}</div>
                                        @if ($fuelStop->caravanaccessnotes)
                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ \Illuminate\Support\Str::limit($fuelStop->caravanaccessnotes, 80) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700">
                                        {{ $fuelStop->place?->placename ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700">
                                        {{ $fuelStop->brandname ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700">
                                        @if (count($fuelStop->fuel_types_array))
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($fuelStop->fuel_types_array as $fuelType)
                                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">
                                                        {{ $fuelTypes[$fuelType] ?? $fuelType }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700">
                                        <div class="flex flex-wrap gap-1">
                                            @if ($fuelStop->hashighflowdiesel)
                                                <span class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">High-flow</span>
                                            @endif
                                            @if ($fuelStop->hasadblue)
                                                <span class="inline-flex rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-800">AdBlue</span>
                                            @endif
                                            @if ($fuelStop->hascarwash)
                                                <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">Car wash</span>
                                            @endif
                                            @if ($fuelStop->hasairwater)
                                                <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Air / water</span>
                                            @endif
                                            @if (
                                                !$fuelStop->hashighflowdiesel &&
                                                !$fuelStop->hasadblue &&
                                                !$fuelStop->hascarwash &&
                                                !$fuelStop->hasairwater
                                            )
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        @if ($fuelStop->isactive)
                                            <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Active</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 align-top text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('fuel-stops.edit', $fuelStop) }}"
                                               class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50">
                                                Edit
                                            </a>

                                            <form method="POST" action="{{ route('fuel-stops.destroy', $fuelStop) }}" onsubmit="return confirm('Delete this fuel stop?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 border border-red-300 rounded-md text-xs font-medium text-red-700 bg-white hover:bg-red-50">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                                        No fuel stops found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($fuelStops->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200">
                        {{ $fuelStops->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        (() => {
            const forms = document.querySelectorAll('[data-dirty-form]');
            forms.forEach((form) => {
                let isDirty = false;

                form.querySelectorAll('input, select, textarea').forEach((field) => {
                    field.addEventListener('change', () => isDirty = true);
                    field.addEventListener('input', () => isDirty = true);
                });

                form.addEventListener('submit', () => isDirty = false);

                window.addEventListener('beforeunload', (event) => {
                    if (!isDirty) return;
                    event.preventDefault();
                    event.returnValue = '';
                });
            });
        })();
    </script>
</x-app-layout>