<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Caravan Travel Planner') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-100 text-gray-900 antialiased">
    <main class="flex min-h-screen items-center justify-center px-4 py-8">
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-sm sm:p-8">
            {{ $slot }}
        </div>
    </main>
</body>
</html>