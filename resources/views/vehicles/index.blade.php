{{-- resources/views/vehicles/index.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Vehicles
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                {{-- Filters --}}
                <div class="p-6 border-b border-gray-200">
                    <form method="GET"
                          action="{{ route('vehicles.index') }}"
                          id="vehicles-filter-form"
                          class="grid grid-cols-1 md:grid-cols-4 gap-4">

                        <div>
                            <label for="vehicletype" class="block text-sm font-medium text-gray-700 mb-1">
                                Vehicle type
                            </label>
                            <select name="vehicletype"
                                    id="vehicletype"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All</option>
                                @foreach($vehicleTypes as $type)
                                    <option value="{{ $type }}"
                                        @selected(request('vehicletype') === $type)>
                                        {{ ucfirst($type) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                                Search
                            </label>
                            <input type="text"
                                   name="search"
                                   id="search"
                                   value="{{ request('search') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                   placeholder="Search by name, rego, make or model">
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                Status
                            </label>
                            <select name="status"
                                    id="status"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All</option>
                                <option value="1" @selected(request('status') === '1')>Active</option>
                                <option value="0" @selected(request('status') === '0')>Inactive</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                Filter
                            </button>

                            <a href="{{ route('vehicles.index') }}"
                               class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm"
                               id="vehicles-reset-link">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                {{-- Bulk edit form --}}
                <form method="POST"
                      action="{{ route('vehicles.bulk-save') }}"
                      id="vehicles-form">
                    @csrf

                    {{-- Preserve filters on post-back --}}
                    <input type="hidden" name="vehicletype" value="{{ request('vehicletype') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">

                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Name
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Type
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Registration
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Make
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Model
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Fuel type
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        L/100km
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Tank litres
                                    </th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                        Active
                                    </th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                        Actions
                                    </th>

                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($vehicles as $vehicle)
                                    <tr>
                                        {{-- Vehicle name --}}
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $vehicle->id }}][vehiclename]"
                                                   value="{{ old("existing.{$vehicle->id}.vehiclename", $vehicle->vehiclename) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   required>
                                        </td>

                                        {{-- Vehicle type --}}
                                        <td class="px-3 py-2">
                                            <select name="existing[{{ $vehicle->id }}][vehicletype]"
                                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                @foreach($vehicleTypes as $type)
                                                    <option value="{{ $type }}"
                                                        @selected(old("existing.{$vehicle->id}.vehicletype", $vehicle->vehicletype) === $type)>
                                                        {{ ucfirst($type) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        {{-- Registration number --}}
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $vehicle->id }}][registrationnumber]"
                                                   value="{{ old("existing.{$vehicle->id}.registrationnumber", $vehicle->registrationnumber) }}"
                                                   class="w-32 rounded-md border-gray-300 shadow-sm text-sm"
                                                   placeholder="Rego">
                                        </td>

                                        {{-- Make --}}
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $vehicle->id }}][make]"
                                                   value="{{ old("existing.{$vehicle->id}.make", $vehicle->make) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   placeholder="Make">
                                        </td>

                                        {{-- Model --}}
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $vehicle->id }}][model]"
                                                   value="{{ old("existing.{$vehicle->id}.model", $vehicle->model) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   placeholder="Model">
                                        </td>

                                        <td class="px-3 py-2">
                                            <select name="existing[{{ $vehicle->id }}][fueltype]"
                                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                <option value="">-</option>
                                                @foreach($fuelTypes as $fuelType)
                                                    <option value="{{ $fuelType }}"
                                                        @selected(old("existing.{$vehicle->id}.fueltype", $vehicle->fueltype) === $fuelType)>
                                                        {{ ucfirst($fuelType) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="number"
                                                name="existing[{{ $vehicle->id }}][defaultfuelconsumptionlper100km]"
                                                value="{{ old("existing.{$vehicle->id}.defaultfuelconsumptionlper100km", $vehicle->defaultfuelconsumptionlper100km) }}"
                                                class="w-28 rounded-md border-gray-300 shadow-sm text-sm"
                                                step="0.0001"
                                                min="0"
                                                placeholder="e.g. 12.5000">
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="number"
                                                name="existing[{{ $vehicle->id }}][fueltankcapacitylitres]"
                                                value="{{ old("existing.{$vehicle->id}.fueltankcapacitylitres", $vehicle->fueltankcapacitylitres) }}"
                                                class="w-28 rounded-md border-gray-300 shadow-sm text-sm"
                                                step="0.01"
                                                min="0"
                                                placeholder="e.g. 80.00">
                                        </td>


                                        {{-- Active --}}
                                        <td class="px-3 py-2 text-center">
                                            <input type="hidden" name="existing[{{ $vehicle->id }}][isactive]" value="0">
                                            <input type="checkbox"
                                                   name="existing[{{ $vehicle->id }}][isactive]"
                                                   value="1"
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                   @checked(old("existing.{$vehicle->id}.isactive", $vehicle->isactive))>
                                        </td>

                                        {{-- Actions --}}
                                        <td class="px-3 py-2 text-center whitespace-nowrap">
                                            <button type="button"
                                                    class="px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm delete-vehicle-btn"
                                                    data-id="{{ $vehicle->id }}"
                                                    data-name="{{ $vehicle->vehiclename }}"
                                                    data-action="{{ route('vehicles.destroy', $vehicle->id) }}">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-3 py-6 text-center text-sm text-gray-500">
                                            No vehicles found.
                                        </td>
                                    </tr>
                                @endforelse

                                {{-- New row --}}
                                <tr class="bg-blue-50">
                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[vehiclename]"
                                               value="{{ old('new.vehiclename') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               placeholder="New vehicle name">
                                    </td>

                                    <td class="px-3 py-2">
                                        <select name="new[vehicletype]"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                            <option value="">Select type</option>
                                            @foreach($vehicleTypes as $type)
                                                <option value="{{ $type }}"
                                                    @selected(old('new.vehicletype') === $type)>
                                                    {{ ucfirst($type) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[registrationnumber]"
                                               value="{{ old('new.registrationnumber') }}"
                                               class="w-32 rounded-md border-gray-300 shadow-sm text-sm"
                                               placeholder="Rego">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[make]"
                                               value="{{ old('new.make') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               placeholder="Make">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[model]"
                                               value="{{ old('new.model') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               placeholder="Model">
                                    </td>

                                    <td class="px-3 py-2">
                                        <select name="new[fueltype]"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                            <option value="">Select fuel</option>
                                            @foreach($fuelTypes as $fuelType)
                                                <option value="{{ $fuelType }}" @selected(old('new.fueltype') === $fuelType)>
                                                    {{ ucfirst($fuelType) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number"
                                            name="new[defaultfuelconsumptionlper100km]"
                                            value="{{ old('new.defaultfuelconsumptionlper100km') }}"
                                            class="w-28 rounded-md border-gray-300 shadow-sm text-sm"
                                            step="0.0001"
                                            min="0"
                                            placeholder="L/100km">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number"
                                            name="new[fueltankcapacitylitres]"
                                            value="{{ old('new.fueltankcapacitylitres') }}"
                                            class="w-28 rounded-md border-gray-300 shadow-sm text-sm"
                                            step="0.01"
                                            min="0"
                                            placeholder="Tank L">
                                    </td>

                                    <td class="px-3 py-2 text-center">
                                        <input type="hidden" name="new[isactive]" value="0">
                                        <input type="checkbox"
                                               name="new[isactive]"
                                               value="1"
                                               class="rounded border-gray-300 text-blue-600 shadow-sm"
                                               @checked(old('new.isactive', true))>
                                    </td>

                                    <td class="px-3 py-2 text-center text-sm text-gray-400 whitespace-nowrap">
                                        New row
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 border-t border-gray-200 flex items-center justify-between">
                        <p class="text-sm text-gray-500">
                            Edit rows above, add a new vehicle at the bottom, then save once.
                        </p>

                        <button type="submit"
                                class="px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                                id="vehicles-save-button">
                            Save Vehicles
                        </button>
                    </div>
                </form>

                @include('partials.admin.compact-delete-form', [
                    'formId' => 'delete-vehicle-form',
                    'query' => request()->only(['vehicletype', 'search', 'status']),
                ])
            </div>
        </div>
    </div>

    @include('partials.admin.dirty-form-script', [
        'formId' => 'vehicles-form',
        'filterFormId' => 'vehicles-filter-form',
        'deleteFormId' => 'delete-vehicle-form',
        'deleteButtonSelector' => '.delete-vehicle-btn',
        'dirtyMessage' => 'You have unsaved changes in the Vehicles table. Continue and lose those changes?',
        'deleteDirtyMessage' => 'You have unsaved changes in the Vehicles table. Delete anyway and lose those changes?',
        'deleteConfirmPrefix' => 'Delete vehicle',
        'deleteConfirmSuffix' => 'This cannot be undone.',
    ])
</x-app-layout>