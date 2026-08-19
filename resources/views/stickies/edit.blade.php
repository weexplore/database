<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Sticky
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            @php
                $returnUrl = request('return');
            @endphp

            <button type="button"
                    onclick="window.location.href='{{ $returnUrl ?: route('stickies.index') }}'"
                    class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded border border-gray-300 hover:bg-gray-200">
                ← Back to Stickies
            </button>

            <div class="bg-white shadow-sm rounded-lg p-6 space-y-6">
                <form method="POST"
                    action="{{ route('stickies.update', $sticky) }}"
                    id="sticky-edit-form">
                    @csrf
                    @method('PATCH')

                    <input type="hidden" name="return_url" value="{{ $returnUrl }}">

                    <x-forms.markdown-field
                        name="stickytext"
                        id="stickytext"
                        label="Text"
                        :value="old('stickytext', $sticky->stickytext ?? '')"
                        rows="8"
                        min-rows="4"
                        placeholder="Write your sticky in Markdown..."
                        help="Markdown supported, including headings, lists, emphasis, links, and tables."
                        :startCollapsed="false"
                    />

                    <div class="mt-4 flex items-center gap-3">
                        <label class="text-xs font-medium text-gray-700">Colour</label>
                        <input type="color"
                            name="colourhex"
                            value="{{ old('colourhex', $sticky->colourhex ?? '#FEF08A') }}"
                            class="w-12 h-8 border-gray-300 rounded-md shadow-sm">
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit"
                                class="px-5 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                            Save Sticky
                        </button>
                    </div>
                </form>
            </div>

            @include('partials.markdown.markdown-styles')
            @include('partials.forms.markdown-field-scripts')

            @include('partials.admin.dirty-form-script', [
                'formId' => 'sticky-edit-form',
                'dirtyMessage' => 'You have unsaved changes on this sticky. Continue and lose those changes?',
            ])
        </div>
    </div>
</x-app-layout>