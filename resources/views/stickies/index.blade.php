
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Stickies
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            {{-- New sticky form --}}
            <div class="bg-white shadow-sm rounded-lg p-4 space-y-3">
                <h3 class="text-sm font-semibold text-gray-900">New sticky</h3>

                <form method="POST" action="{{ route('stickies.store') }}" class="space-y-2">
                    @csrf

                    <div>
                        <label class="block text-xs font-medium text-gray-600">Text</label>
                        <textarea name="stickytext" rows="3" required
                                  class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm bg-yellow-50"></textarea>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label class="block text-xs font-medium text-gray-600">Colour</label>
                            <input type="color" name="colourhex" value="#FEF08A"
                                class="mt-1 w-12 h-8 border-gray-300 rounded-md shadow-sm">
                        </div>

                        <button type="submit"
                                class="px-4 py-1.5 bg-green-600 text-white text-xs font-semibold rounded hover:bg-green-700">
                            Save sticky
                        </button>
                    </div>
                </form>
            </div>

            {{-- Stickies board --}}
            <div class="border border-gray-300 rounded-lg bg-yellow-50 min-h-[300px] p-4 flex flex-wrap gap-4">
                @forelse ($stickies as $sticky)
                    <div class="w-64 rounded shadow p-2 text-sm flex flex-col gap-2"
                        style="background: {{ $sticky->colourhex ?? '#FEF08A' }}">

                        <form method="POST" action="{{ route('stickies.update', $sticky) }}" class="space-y-2">
                            @csrf
                            @method('PATCH')

                            <textarea name="stickytext" rows="3" required
                                    class="w-full border-gray-300 rounded-md shadow-sm text-xs bg-yellow-50">{{ $sticky->stickytext }}</textarea>

                            <div class="flex items-center justify-between">
                                <input type="color" name="colourhex"
                                    value="{{ $sticky->colourhex ?? '#FEF08A' }}"
                                    class="w-12 h-8 border-gray-300 rounded-md shadow-sm">

                                <button type="submit"
                                        class="px-3 py-1 bg-blue-600 text-white text-[11px] font-semibold rounded hover:bg-blue-700">
                                    Save
                                </button>
                            </div>
                        </form>

                        <form method="POST"
                              action="{{ route('stickies.destroy', $sticky) }}"
                              onsubmit="return confirm('Delete this sticky?');">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="mt-1 px-3 py-1 bg-red-600 text-white text-[11px] font-semibold rounded hover:bg-red-700">
                                Delete
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-xs text-gray-500">
                        No stickies yet. Use the form above to create one.
                    </p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>