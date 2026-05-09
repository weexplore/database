<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Destinations
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <form method="GET"
                          action="{{ route('destinations.index') }}"
                          id="destinations-filter-form"
                          class="grid grid-cols-1 md:grid-cols-4 gap-4">

                        <div>
                            <label for="placeid" class="block text-sm font-medium text-gray-700 mb-1">
                                Place
                            </label>
                            <select name="placeid"
                                    id="placeid"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All</option>
                                @foreach($places as $place)
                                    <option value="{{ $place->id }}"
                                        @selected((string) request('placeid') === (string) $place->id)>
                                        {{ $place->placename }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="destinationtype" class="block text-sm font-medium text-gray-700 mb-1">
                                Type
                            </label>
                            <select name="destinationtype"
                                    id="destinationtype"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All</option>
                                @foreach($typeOptions as $type)
                                    <option value="{{ $type }}"
                                        @selected(request('destinationtype') === $type)>
                                        {{ ucfirst($type) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="featured" class="block text-sm font-medium text-gray-700 mb-1">
                                Featured
                            </label>
                            <select name="featured"
                                    id="featured"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All</option>
                                <option value="1" @selected(request('featured') === '1')>Featured</option>
                                <option value="0" @selected(request('featured') === '0')>Not Featured</option>
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
                                   placeholder="Name, best season, overview">
                        </div>

                        <div class="md:col-span-4 flex items-end gap-2">
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                Filter
                            </button>

                            <a href="{{ route('destinations.index') }}"
                               class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm"
                               id="destinations-reset-link">
                                Reset
                            </a>

                            <span class="ml-auto text-xs text-gray-500">
                                {{ $destinations->total() }} destinations
                            </span>
                        </div>
                    </form>
                </div>

                <form method="POST"
                    action="{{ route('destinations.bulk-save') }}"
                    id="destinations-form">
                    @csrf

                    <input type="hidden" name="return_to" value="{{ url()->full() }}">
                    <input type="hidden" name="placeid" value="{{ request('placeid') }}">
                    <input type="hidden" name="destinationtype" value="{{ request('destinationtype') }}">
                    <input type="hidden" name="featured" value="{{ request('featured') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="page" value="{{ request('page') }}">

                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Place
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Destination
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Type
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Best Season
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Revisit
                                    </th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                        Featured
                                    </th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($destinations as $destination)
                                    <tr>
                                        <td class="px-3 py-2">
                                            <select name="existing[{{ $destination->id }}][placeid]"
                                                    class="w-56 rounded-md border-gray-300 shadow-sm text-sm">
                                                <option value="">None</option>
                                                @foreach($places as $place)
                                                    <option value="{{ $place->id }}"
                                                        @selected((string) old("existing.{$destination->id}.placeid", $destination->placeid) === (string) $place->id)>
                                                        {{ $place->placename }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $destination->id }}][destinationname]"
                                                   value="{{ old("existing.{$destination->id}.destinationname", $destination->destinationname) }}"
                                                   class="w-56 rounded-md border-gray-300 shadow-sm text-sm"
                                                   required>
                                        </td>

                                        <td class="px-3 py-2">
                                            <select name="existing[{{ $destination->id }}][destinationtype]"
                                                    class="w-40 rounded-md border-gray-300 shadow-sm text-sm">
                                                @foreach($typeOptions as $type)
                                                    <option value="{{ $type }}"
                                                        @selected(old("existing.{$destination->id}.destinationtype", $destination->destinationtype) === $type)>
                                                        {{ ucfirst($type) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $destination->id }}][bestseason]"
                                                   value="{{ old("existing.{$destination->id}.bestseason", $destination->bestseason) }}"
                                                   class="w-40 rounded-md border-gray-300 shadow-sm text-sm">
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="number"
                                                   name="existing[{{ $destination->id }}][revisitinterestlevel]"
                                                   value="{{ old("existing.{$destination->id}.revisitinterestlevel", $destination->revisitinterestlevel) }}"
                                                   class="w-24 rounded-md border-gray-300 shadow-sm text-sm"
                                                   min="0"
                                                   max="10">
                                        </td>

                                        <td class="px-3 py-2 text-center">
                                            <input type="hidden" name="existing[{{ $destination->id }}][isfeatured]" value="0">
                                            <input type="checkbox"
                                                   name="existing[{{ $destination->id }}][isfeatured]"
                                                   value="1"
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                   @checked(old("existing.{$destination->id}.isfeatured", $destination->isfeatured))>
                                        </td>

                                        <td class="px-3 py-2 text-center whitespace-nowrap">
<a href="{{ route('destinations.edit', [
        'destination' => $destination,
        'return_to' => url()->full(),
    ]) }}"
   class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-xs">
    Open
</a>

                                            <button type="button"
                                                    class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-xs delete-destination-btn"
                                                    data-id="{{ $destination->id }}"
                                                    data-name="{{ $destination->destinationname }}"
                                                    data-action="{{ route('destinations.destroy', $destination->id) }}">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-3 py-6 text-center text-sm text-gray-500">
                                            No destinations found.
                                        </td>
                                    </tr>
                                @endforelse

                                <tr class="bg-blue-50">
                                    <td class="px-3 py-2">
                                        <select name="new[placeid]"
                                                class="w-56 rounded-md border-gray-300 shadow-sm text-sm">
                                            <option value="">None</option>
                                            @foreach($places as $place)
                                                <option value="{{ $place->id }}"
                                                    @selected((string) old('new.placeid', request('placeid')) === (string) $place->id)>
                                                    {{ $place->placename }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[destinationname]"
                                               value="{{ old('new.destinationname') }}"
                                               class="w-56 rounded-md border-gray-300 shadow-sm text-sm"
                                               placeholder="New destination name">
                                    </td>

                                    <td class="px-3 py-2">
                                        <select name="new[destinationtype]"
                                                class="w-40 rounded-md border-gray-300 shadow-sm text-sm">
                                            <option value="">Select type</option>
                                            @foreach($typeOptions as $type)
                                                <option value="{{ $type }}"
                                                    @selected(old('new.destinationtype') === $type)>
                                                    {{ ucfirst($type) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[bestseason]"
                                               value="{{ old('new.bestseason') }}"
                                               class="w-40 rounded-md border-gray-300 shadow-sm text-sm"
                                               placeholder="Best season">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number"
                                               name="new[revisitinterestlevel]"
                                               value="{{ old('new.revisitinterestlevel') }}"
                                               class="w-24 rounded-md border-gray-300 shadow-sm text-sm"
                                               min="0"
                                               max="10"
                                               placeholder="0-10">
                                    </td>

                                    <td class="px-3 py-2 text-center">
                                        <input type="hidden" name="new[isfeatured]" value="0">
                                        <input type="checkbox"
                                               name="new[isfeatured]"
                                               value="1"
                                               class="rounded border-gray-300 text-blue-600 shadow-sm"
                                               @checked(old('new.isfeatured', false))>
                                    </td>

                                    <td class="px-3 py-2 text-center text-xs text-gray-500">
                                        New row
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 border-t border-gray-200 flex items-center justify-between">
                        <p class="text-sm text-gray-500">
                            Edit summary fields here. Open a destination for overview, travel notes, suitability, access notes, and commentary.
                        </p>

                        <button type="submit"
                                class="px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                                id="destinations-save-button">
                            Save Destinations
                        </button>
                    </div>
                </form>

                @include('partials.admin.compact-delete-form', [
                    'formId' => 'delete-destination-form',
                    'query' => request()->only(['placeid', 'destinationtype', 'featured', 'search', 'page']),
                ])
            </div>

            <div>
                {{ $destinations->links() }}
            </div>
        </div>
    </div>

    @include('partials.admin.dirty-form-script', [
        'formId' => 'destinations-form',
        'filterFormId' => 'destinations-filter-form',
        'deleteFormId' => 'delete-destination-form',
        'deleteButtonSelector' => '.delete-destination-btn',
        'dirtyMessage' => 'You have unsaved changes in the Destinations table. Continue and lose those changes?',
        'deleteDirtyMessage' => 'You have unsaved changes in the Destinations table. Delete anyway and lose those changes?',
        'deleteConfirmPrefix' => 'Delete destination',
        'deleteConfirmSuffix' => 'This cannot be undone.',
    ])
</x-app-layout>