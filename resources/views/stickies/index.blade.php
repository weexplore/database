<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Stickies
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600">
                    Drag notes to arrange them. Select Edit to change a note.
                </p>

                <form method="POST" action="{{ route('stickies.create-and-edit') }}">
                    @csrf

                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        + New Sticky
                    </button>
                </form>
            </div>

            
            {{-- Stickies board --}}
            <div id="stickies-board"
                 class="relative border border-gray-300 rounded-lg bg-yellow-50 min-h-[400px] overflow-auto">

                @forelse ($stickies as $sticky)
                    <div
                        class="sticky-note absolute inline-block max-w-xs rounded shadow p-2 text-sm cursor-move"
                        data-id="{{ $sticky->id }}"
                        style="
                            left: {{ $sticky->positionx ?? 40 }}px;
                            top:  {{ $sticky->positiony ?? 40 }}px;
                            background: {{ $sticky->colourhex ?? '#FEF08A' }};
                        "
                    >
                        {{-- Render markdown as HTML --}}
                        {!! app(\Illuminate\Mail\Markdown::class)->parse($sticky->stickytext ?? '') !!}

                        <div class="mt-2 flex justify-between items-center gap-2 text-[11px]">
                            <span class="text-gray-500">Drag to move</span>

                            <div class="flex gap-1">
                                <a href="{{ route('stickies.edit', [
                                        'sticky' => $sticky,
                                        'return' => request()->fullUrl(),
                                    ]) }}"
                                class="px-2 py-0.5 rounded bg-white border border-gray-300 text-gray-700 hover:bg-gray-100">
                                    Edit
                                </a>

                                <form method="POST"
                                    action="{{ route('stickies.destroy', $sticky) }}"
                                    onsubmit="return confirm('Delete this sticky?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-2 py-0.5 rounded bg-red-600 text-white hover:bg-red-700">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="p-4 text-xs text-gray-500">
                        No stickies yet. Use the editor above to create one.
                    </p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Drag-and-drop behaviour --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const board = document.getElementById('stickies-board');
            if (!board) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            document.querySelectorAll('.sticky-note').forEach(note => {
                note.addEventListener('mousedown', (event) => {
                    // Only drag with left button
                    if (event.button !== 0) return;

                    const boardRect = board.getBoundingClientRect();
                    const noteRect = note.getBoundingClientRect();

                    const offsetX = event.clientX - noteRect.left;
                    const offsetY = event.clientY - noteRect.top;

                    function onMouseMove(e) {
                        const x = e.clientX - boardRect.left - offsetX;
                        const y = e.clientY - boardRect.top - offsetY;

                        note.style.left = x + 'px';
                        note.style.top  = y + 'px';
                    }

                    function onMouseUp() {
                        document.removeEventListener('mousemove', onMouseMove);
                        document.removeEventListener('mouseup', onMouseUp);

                        const payload = {
                            x: parseInt(note.style.left, 10) || 0,
                            y: parseInt(note.style.top, 10) || 0,
                        };

                        fetch(`{{ url('/stickies') }}/${note.dataset.id}/position`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(payload),
                        }).catch(() => {});
                    }

                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', onMouseUp);
                });
            });
        });
    </script>
</x-app-layout>