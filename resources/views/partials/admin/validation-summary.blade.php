@if ($errors->any())
    <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded">
        <div class="font-semibold mb-2">Please fix the following:</div>
        <ul class="list-disc ml-5 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif