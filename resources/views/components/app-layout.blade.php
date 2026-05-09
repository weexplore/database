<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? 'Caravan Travel Planner' }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen">
        {{-- Header --}}
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        {{-- Top navigation --}}
        <nav class="bg-blue-700 border-b border-blue-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex space-x-8">
                    <a href="{{ url('/') }}" 
                       class="inline-flex items-center px-1 pt-3 pb-2.5 text-sm font-medium
                              {{ request()->is('/') ? 'border-b-2 border-white text-white' : 'text-blue-100 hover:text-white hover:border-b-2 hover:border-blue-300' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('travellers.index') }}" 
                       class="inline-flex items-center px-1 pt-3 pb-2.5 text-sm font-medium
                              {{ request()->is('travellers*') ? 'border-b-2 border-white text-white' : 'text-blue-100 hover:text-white hover:border-b-2 hover:border-blue-300' }}">
                        Travellers
                    </a>
                    <a href="{{ route('trips.index') }}" 
                       class="inline-flex items-center px-1 pt-3 pb-2.5 text-sm font-medium
                              {{ request()->is('trips*') ? 'border-b-2 border-white text-white' : 'text-blue-100 hover:text-white hover:border-b-2 hover:border-blue-300' }}">
                        Trips
                    </a>
                    <a href="{{ route('places.index') }}" 
                       class="inline-flex items-center px-1 pt-3 pb-2.5 text-sm font-medium
                              {{ request()->is('places*') ? 'border-b-2 border-white text-white' : 'text-blue-100 hover:text-white hover:border-b-2 hover:border-blue-300' }}">
                        Places
                    </a>
                    <a href="{{ route('regions.index') }}" 
                       class="inline-flex items-center px-1 pt-3 pb-2.5 text-sm font-medium
                              {{ request()->is('regions*') ? 'border-b-2 border-white text-white' : 'text-blue-100 hover:text-white hover:border-b-2 hover:border-blue-300' }}">
                        Regions
                    </a>
                </div>
            </div>
        </nav>

        {{-- Page Content --}}
        <main>
            {{ $slot }}
        </main>
    </div>

    @stack('scripts')
</body>
</html>