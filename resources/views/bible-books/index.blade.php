<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $pageTitle ?? 'Bible Books' }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <form method="GET"
                          action="{{ route('bible-books.index') }}"
                          id="bible-books-filter-form"
                          class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                                Search
                            </label>
                            <input type="text"
                                   name="search"
                                   id="search"
                                   value="{{ $filters['search'] ?? '' }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm"
                                   placeholder="Book code or name">
                        </div>

                        <div>
                            <label for="testament" class="block text-sm font-medium text-gray-700 mb-1">
                                Testament
                            </label>
                            <select name="testament"
                                    id="testament"
                                    class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">All</option>
                                @foreach($testamentOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['testament'] ?? '') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Filter
                            </button>

                            <a href="{{ route('bible-books.index') }}"
                               class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <form method="POST"
                      action="{{ route('bible-books.bulk-save') }}"
                      id="bible-books-form">
                    @csrf

                    <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                    <input type="hidden" name="testament" value="{{ $filters['testament'] ?? '' }}">

                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Book Code</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Book Name</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Testament</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sort Order</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Chapter Count</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($rows as $row)
                                    <tr>
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $row->id }}][bookcode]"
                                                   value="{{ old("existing.{$row->id}.bookcode", $row->bookcode) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   maxlength="20"
                                                   required>
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $row->id }}][bookname]"
                                                   value="{{ old("existing.{$row->id}.bookname", $row->bookname) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   maxlength="100"
                                                   required>
                                        </td>

                                        <td class="px-3 py-2">
                                            <select name="existing[{{ $row->id }}][testament]"
                                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                    required>
                                                @foreach($testamentOptions as $value => $label)
                                                    <option value="{{ $value }}" @selected(old("existing.{$row->id}.testament", $row->testament) === $value)>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="number"
                                                   name="existing[{{ $row->id }}][sortorder]"
                                                   value="{{ old("existing.{$row->id}.sortorder", $row->sortorder) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   min="1"
                                                   required>
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="number"
                                                   name="existing[{{ $row->id }}][chaptercount]"
                                                   value="{{ old("existing.{$row->id}.chaptercount", $row->chaptercount) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   min="1"
                                                   placeholder="Optional">
                                        </td>

                                        <td class="px-3 py-2 text-center whitespace-nowrap">
                                            <button type="button"
                                                    class="px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm delete-bible-book-btn"
                                                    data-id="{{ $row->id }}"
                                                    data-name="{{ $row->bookcode }} - {{ $row->bookname }}"
                                                    data-action="{{ route('bible-books.destroy', $row->id) }}">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-3 py-6 text-center text-sm text-gray-500">
                                            No Bible books found.
                                        </td>
                                    </tr>
                                @endforelse

                                <tr class="bg-blue-50">
                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[bookcode]"
                                               value="{{ old('new.bookcode') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               maxlength="20"
                                               placeholder="GEN">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[bookname]"
                                               value="{{ old('new.bookname') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               maxlength="100"
                                               placeholder="Genesis">
                                    </td>

                                    <td class="px-3 py-2">
                                        <select name="new[testament]"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                            <option value="">Select testament</option>
                                            @foreach($testamentOptions as $value => $label)
                                                <option value="{{ $value }}" @selected(old('new.testament') === $value)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number"
                                               name="new[sortorder]"
                                               value="{{ old('new.sortorder') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               min="1"
                                               placeholder="1">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number"
                                               name="new[chaptercount]"
                                               value="{{ old('new.chaptercount') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               min="1"
                                               placeholder="50">
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
                            Edit rows above, add a new Bible book at the bottom, then save once.
                        </p>

                        <button type="submit"
                                class="px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            Save Bible Books
                        </button>
                    </div>
                </form>

                @include('partials.admin.compact-delete-form', [
                    'formId' => 'delete-bible-book-form',
                    'query' => request()->only(['search', 'testament']),
                ])
            </div>
        </div>
    </div>

    @include('partials.admin.dirty-form-script', [
        'formId' => 'bible-books-form',
        'filterFormId' => 'bible-books-filter-form',
        'deleteFormId' => 'delete-bible-book-form',
        'deleteButtonSelector' => '.delete-bible-book-btn',
        'dirtyMessage' => 'You have unsaved changes in the Bible Books table. Continue and lose those changes?',
        'deleteDirtyMessage' => 'You have unsaved changes in the Bible Books table. Delete anyway and lose those changes?',
        'deleteConfirmPrefix' => 'Delete Bible book',
        'deleteConfirmSuffix' => 'This cannot be undone.',
    ])
</x-app-layout>