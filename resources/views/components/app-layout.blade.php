<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? 'WeExplore Database' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen">
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <nav class="bg-blue-700 border-b border-blue-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap gap-x-8">
                    <a href="{{ url('/') }}"
                       class="inline-flex items-center px-1 pt-3 pb-2.5 text-sm font-medium
                            {{ request()->routeIs('home')
                                ? 'border-b-2 border-white text-white'
                                : 'text-blue-100 hover:text-white hover:border-b-2 hover:border-blue-300' }}">
                        Home
                    </a>

                    <a href="{{ route('trips.index') }}"
                       class="inline-flex items-center px-1 pt-3 pb-2.5 text-sm font-medium
                            {{ request()->routeIs('trips.*')
                                ? 'border-b-2 border-white text-white'
                                : 'text-blue-100 hover:text-white hover:border-b-2 hover:border-blue-300' }}">
                        Trips
                    </a>

                    <a href="{{ route('places.index') }}"
                       class="inline-flex items-center px-1 pt-3 pb-2.5 text-sm font-medium
                            {{ request()->routeIs('places.*')
                                ? 'border-b-2 border-white text-white'
                                : 'text-blue-100 hover:text-white hover:border-b-2 hover:border-blue-300' }}">
                        Places
                    </a>

                    <a href="{{ route('knowledge-categories.index', ['domainid' => 0, 'categoryid' => 0]) }}"
                       class="inline-flex items-center px-1 pt-3 pb-2.5 text-sm font-medium
                            {{ request()->routeIs('knowledge-categories.*')
                                || request()->routeIs('knowledge.items.*')
                                || request()->routeIs('knowledge.attachments.*')
                                || request()->routeIs('knowledge.sources.*')
                                || request()->routeIs('knowledge.relationships.*')
                                || request()->routeIs('knowledge.notes.*')
                                || request()->routeIs('knowledge.reports.*')
                                    ? 'border-b-2 border-white text-white'
                                    : 'text-blue-100 hover:text-white hover:border-b-2 hover:border-blue-300' }}">
                        Knowledge
                    </a>
                    <a href="{{ route('cashbook-transactions.index') }}"
                        class="inline-flex items-center px-1 pt-3 pb-2.5 text-sm font-medium
                        {{ request()->routeIs('cashbook-transactions.*')
                            ? 'border-b-2 border-white text-white'
                            : 'text-blue-100 hover:text-white hover:border-b-2 hover:border-blue-300' }}">
                        Cashbook Transactions
                    </a>
                    <a href="{{ route('projects.index') }}"
                        class="inline-flex items-center px-1 pt-3 pb-2.5 text-sm font-medium
                        {{ request()->routeIs('projects.*')
                            ? 'border-b-2 border-white text-white'
                            : 'text-blue-100 hover:text-white hover:border-b-2 hover:border-blue-300' }}">
                        Projects
                    </a>
                    <a href="{{ route('tasksall.all') }}"
                        class="inline-flex items-center px-1 pt-3 pb-2.5 text-sm font-medium
                        {{ request()->routeIs('tasksall.*')
                            ? 'border-b-2 border-white text-white'
                            : 'text-blue-100 hover:text-white hover:border-b-2 hover:border-blue-300' }}">
                        All Tasks
                    </a>
                    <a href="{{ route('stickies.index') }}"
                        class="inline-flex items-center px-1 pt-3 pb-2.5 text-sm font-medium
                        {{ request()->routeIs('stickies.*')
                            ? 'border-b-2 border-white text-white'
                            : 'text-blue-100 hover:text-white hover:border-b-2 hover:border-blue-300' }}">
                        Stickies
                    </a>
                </div>
            </div>
        </nav>

        <main>
            {{ $slot }}
        </main>
    </div>

    @stack('scripts')
</body>
</html>