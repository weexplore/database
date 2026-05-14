<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Research
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">


            <div class="bg-white shadow sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <p class="text-sm text-gray-600">Research maintenance and reference dashboards.</p>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    @foreach ($cards as $card)
                        <a href="{{ $card['route'] }}" class="block rounded-lg border border-gray-200 p-5 hover:bg-gray-50">
                            <h3 class="text-base font-semibold text-gray-900">{{ $card['title'] }}</h3>
                            <p class="mt-2 text-sm text-gray-600">{{ $card['description'] }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
