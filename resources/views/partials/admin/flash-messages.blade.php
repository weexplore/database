@if (session('success'))
    <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded">
        {{ session('error') }}
    </div>
@endif