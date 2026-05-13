<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Attachment
        </h2>
    </x-slot>

    @php
        $returnTo = $returnTo ?? route('attachments.index');
    @endphp

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @if(session('success'))
                <div class="rounded-md bg-green-50 p-4 text-green-800 border border-green-200">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-md bg-red-50 p-4 text-red-800 border border-red-200">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="rounded-md bg-red-50 p-4 text-red-800 border border-red-200">
                    <div class="font-medium mb-2">Please fix the following:</div>
                    <ul class="list-disc pl-5 space-y-1 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div class="xl:col-span-2">
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <form method="POST" action="{{ route('attachments.update', $attachment) }}" class="space-y-6">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="return_to" value="{{ $returnTo }}">

                            @include('attachments._form', ['attachment' => $attachment])

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                                <a href="{{ $returnTo }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                                    Cancel
                                </a>

                                <button type="submit" class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                    Save Attachment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white shadow-sm sm:rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-900">File Actions</h3>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('attachments.view', $attachment) }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-sm">
                                View
                            </a>
                            <a href="{{ route('attachments.download', $attachment) }}"
                               class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-sm">
                                Download
                            </a>
                        </div>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Delete Attachment</h3>
                        <form method="POST" action="{{ route('attachments.destroy', $attachment) }}" class="mt-4" onsubmit="return confirm('Delete this attachment?');">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="return_to" value="{{ $returnTo }}">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>