<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Stickies
        </h2>
    </x-slot>

    @php
        $stickyPayload = $stickies
            ->map(function ($sticky) {
                return [
                    'id' => $sticky->id,
                    'stickytext' => $sticky->stickytext ?? '',
                    'colourhex' => $sticky->colourhex ?? '#FEF08A',
                    'positionx' => (int) ($sticky->positionx ?? 40),
                    'positiony' => (int) ($sticky->positiony ?? 40),
                    'html' => app(\Illuminate\Mail\Markdown::class)
                        ->parse($sticky->stickytext ?? '')
                        ->toHtml(),
                ];
            })
            ->values();
    @endphp

    {{-- Keep JSON out of HTML attributes so quote escaping cannot break x-data. --}}
    <script id="stickies-initial-data" type="application/json">
        {!! json_encode([
            'stickies' => $stickyPayload,
            'storeUrl' => route('stickies.store'),
            'baseUrl' => url('/stickies'),
            'csrfToken' => csrf_token(),
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6"
             x-data="stickiesEditor()">

            @include('partials.admin.flash-messages')

            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-gray-600">
                    Select a sticky to edit it. Saving does not reload the page.
                </p>

                <button type="button"
                        @click="newSticky()"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    + New Sticky
                </button>
            </div>

            <template x-if="errorMessage">
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
                     x-text="errorMessage">
                </div>
            </template>

            <div x-ref="board"
                class="relative overflow-auto rounded-lg border border-gray-300 bg-yellow-50"
                :style="`min-height: ${boardHeight}px;`">

                <template x-for="sticky in stickies" :key="sticky.key">
                    <article class="absolute w-72 max-w-[calc(100%-2rem)] rounded-lg border border-yellow-300 p-3 shadow-md "
                            :style="`
                                left: ${sticky.positionx}px;
                                top: ${sticky.positiony}px;
                                background-color: ${sticky.colourhex || '#FEF08A'};
                                z-index: ${sticky.zIndex || 1};
                            `">

                        <template x-if="!sticky.editing">
                            <div>
                                <div class="mb-2 flex items-center justify-between gap-2 border-b border-black/10 pb-2">
                                    <button type="button"
                                            title="Drag to move"
                                            @pointerdown.stop.prevent="startDrag($event, sticky)"
                                            @click.stop
                                            class="touch-none select-none cursor-grab active:cursor-grabbing text-left text-xs font-semibold text-gray-700"
                                            style="touch-action: none; -webkit-user-select: none; user-select: none;">
                                        ⠿ Drag
                                    </button>

                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] text-gray-600">
                                            #<span x-text="sticky.id"></span>
                                        </span>

                                        <button type="button"
                                                @click="editSticky(sticky)"
                                                class="rounded border border-gray-400/50 bg-white/70 px-2 py-0.5 text-[11px] font-medium text-gray-700 hover:bg-white">
                                            Edit
                                        </button>
                                    </div>
                                </div>

                                <div class="sticky-markdown prose prose-sm max-w-none break-words text-gray-800"
                                    x-html="sticky.html || '<p class=&quot;text-gray-500 italic&quot;>Empty sticky</p>'">
                                </div>

                                <p class="mt-3 text-[11px] text-gray-500">
                                    Position:
                                    <span x-text="sticky.positionx"></span>,
                                    <span x-text="sticky.positiony"></span>
                                </p>
                            </div>
                        </template>

                        <template x-if="sticky.editing">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between gap-2 border-b border-black/10 pb-2">
                                    <span class="text-xs font-semibold text-gray-700"
                                        x-text="sticky.isNew ? 'New Sticky' : 'Edit Sticky'">
                                    </span>

                                    <button type="button"
                                            @click="cancelEdit(sticky)"
                                            class="rounded border border-gray-400/50 bg-white/70 px-2 py-0.5 text-[11px] text-gray-700 hover:bg-white">
                                        Cancel
                                    </button>
                                </div>

                                <textarea x-model="sticky.draftText"
                                        rows="7"
                                        placeholder="Write your sticky..."
                                        class="w-full rounded-md border-gray-400 bg-white/80 text-sm shadow-sm focus:border-green-500 focus:ring-green-500">
                                </textarea>

                                <div class="flex items-center justify-between gap-3">
                                    <label class="flex items-center gap-2 text-xs font-medium text-gray-700">
                                        Colour

                                        <input type="color"
                                            x-model="sticky.draftColour"
                                            class="h-8 w-12 rounded border border-gray-400 bg-white">
                                    </label>

                                    <button type="button"
                                            @click="saveSticky(sticky)"
                                            :disabled="sticky.saving"
                                            class="rounded bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60">
                                        <span x-show="!sticky.saving">Save</span>
                                        <span x-show="sticky.saving">Saving…</span>
                                    </button>
                                </div>

                                <div class="border-t border-black/10 pt-3 text-right">
                                    <button type="button"
                                            @click="deleteSticky(sticky)"
                                            :disabled="sticky.saving"
                                            class="text-xs font-medium text-red-700 hover:text-red-900 disabled:cursor-not-allowed disabled:opacity-60">
                                        Delete sticky
                                    </button>
                                </div>
                            </div>
                        </template>
                    </article>
                </template>

                <template x-if="stickies.length === 0">
                    <p class="p-5 text-sm text-gray-500">
                        No stickies yet. Select “New Sticky” to add one.
                    </p>
                </template>
            </div>
        </div>
    </div>

    <style>
        .sticky-markdown {
            font-size: 0.875rem;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }

        .sticky-markdown > :first-child {
            margin-top: 0;
        }

        .sticky-markdown > :last-child {
            margin-bottom: 0;
        }

        .sticky-markdown h1,
        .sticky-markdown h2,
        .sticky-markdown h3,
        .sticky-markdown h4,
        .sticky-markdown h5,
        .sticky-markdown h6 {
            margin: 0.15rem 0 0.45rem;
            font-size: 0.95rem;
            line-height: 1.25;
            font-weight: 700;
        }

        .sticky-markdown p {
            margin: 0.35rem 0;
        }

        .sticky-markdown ul,
        .sticky-markdown ol {
            margin: 0.35rem 0;
            padding-left: 1.25rem;
        }

        .sticky-markdown ul {
            list-style-type: disc;
        }

        .sticky-markdown ol {
            list-style-type: decimal;
        }

        .sticky-markdown li {
            margin: 0.15rem 0;
        }

        .sticky-markdown li > ul,
        .sticky-markdown li > ol {
            margin-top: 0.15rem;
            margin-bottom: 0.15rem;
        }

        .sticky-markdown a {
            color: #1d4ed8;
            text-decoration: underline;
        }

        .sticky-markdown blockquote {
            margin: 0.4rem 0;
            border-left: 3px solid rgba(55, 65, 81, 0.35);
            padding-left: 0.65rem;
            color: #374151;
        }

        .sticky-markdown pre,
        .sticky-markdown code {
            overflow-wrap: anywhere;
        }
    </style>
    @include('partials.markdown.markdown-styles')

    <script>
        function stickiesEditor() {
            return {
                status: 'Loading…',
                stickies: [],
                storeUrl: '',
                baseUrl: '',
                csrfToken: '',
                errorMessage: '',
                boardHeight: 600,
                drag: null,
                highestZIndex: 1,   

                init() {
                    try {
                        const dataElement = document.getElementById('stickies-initial-data');
                        const data = JSON.parse(dataElement.textContent);

                        this.stickies = (data.stickies || []).map((sticky, index) => ({
                            ...sticky,
                            key: `saved-${sticky.id ?? index}`,
                            editing: false,
                            saving: false,
                            isNew: false,
                            draftText: sticky.stickytext ?? '',
                            draftColour: sticky.colourhex ?? '#FEF08A',
                            zIndex: index + 1,
                        }));

                        this.storeUrl = data.storeUrl;
                        this.baseUrl = data.baseUrl;
                        this.csrfToken = data.csrfToken;
                        this.status = `Working — ${this.stickies.length} sticky note(s) loaded`;
                        this.refreshBoardHeight();
                        this.highestZIndex = this.stickies.length;
                        this.onPointerMove = this.onPointerMove.bind(this);
                        this.endDrag = this.endDrag.bind(this);

                        console.log('Stickies editor initialised', data);
                    } catch (error) {
                        this.status = 'Failed to load';
                        this.errorMessage = error.message || 'Unable to initialise the Stickies board.';
                        console.error('Stickies editor initialisation failed:', error);
                    }
                },
                refreshBoardHeight() {
                    const lowestPosition = this.stickies.reduce((lowest, sticky) => {
                        const y = Number(sticky.positiony) || 0;

                        return Math.max(lowest, y);
                    }, 0);

                    this.boardHeight = Math.max(600, lowestPosition + 340);
                },

                newSticky() {
                    this.errorMessage = '';

                    const positionY = this.nextPositionY();

                    this.stickies.push({
                        id: null,
                        key: `new-${Date.now()}`,
                        stickytext: '',
                        colourhex: '#FEF08A',
                        positionx: 24,
                        positiony: positionY,
                        editing: true,
                        saving: false,
                        isNew: true,
                        draftText: '',
                        draftColour: '#FEF08A',
                    });

                    this.refreshBoardHeight();
                },

                nextPositionY() {
                    if (this.stickies.length === 0) {
                        return 24;
                    }

                    return Math.max(
                        ...this.stickies.map(sticky => Number(sticky.positiony) || 0)
                    ) + 220;
                },

                editSticky(sticky) {
                    this.errorMessage = '';
                    sticky.draftText = sticky.stickytext ?? '';
                    sticky.draftColour = sticky.colourhex ?? '#FEF08A';
                    sticky.editing = true;
                },

                cancelEdit(sticky) {
                    if (sticky.isNew) {
                        this.stickies = this.stickies.filter(item => item !== sticky);
                        return;
                    }

                    sticky.draftText = sticky.stickytext ?? '';
                    sticky.draftColour = sticky.colourhex ?? '#FEF08A';
                    sticky.editing = false;
                },

                async saveSticky(sticky) {
                    this.errorMessage = '';

                    if (!sticky.draftText.trim()) {
                        this.errorMessage = 'Enter text before saving the sticky.';
                        return;
                    }

                    sticky.saving = true;

                    try {
                        const isNew = !sticky.id;

                        const response = await fetch(
                            isNew ? this.storeUrl : `${this.baseUrl}/${sticky.id}`,
                            {
                                method: isNew ? 'POST' : 'PATCH',
                                headers: {
                                    'X-CSRF-TOKEN': this.csrfToken,
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    stickytext: sticky.draftText,
                                    colourhex: sticky.draftColour,
                                    ...(isNew ? {
                                        positionx: sticky.positionx,
                                        positiony: sticky.positiony,
                                    } : {}),
                                }),
                            }
                        );

                        if (!response.ok) {
                            throw new Error(await this.responseMessage(response));
                        }

                        const data = await response.json();

                        Object.assign(sticky, {
                            ...data.sticky,
                            key: sticky.key,
                            editing: false,
                            saving: false,
                            isNew: false,
                            draftText: data.sticky.stickytext,
                            draftColour: data.sticky.colourhex,
                        });
                    } catch (error) {
                        sticky.saving = false;
                        this.errorMessage = error.message || 'Unable to save the sticky.';
                        console.error('Sticky save failed:', error);
                    }
                },
                bringToFront(sticky) {
                    sticky.zIndex = ++this.highestZIndex;
                },

                startDrag(event, sticky) {
                    if (!sticky.id || sticky.editing) {
                        return;
                    }

                    const board = this.$refs.board;
                    const boardRect = board.getBoundingClientRect();

                    this.bringToFront(sticky);

                    this.drag = {
                        sticky,
                        pointerId: event.pointerId,
                        offsetX: event.clientX - boardRect.left + board.scrollLeft - sticky.positionx,
                        offsetY: event.clientY - boardRect.top + board.scrollTop - sticky.positiony,
                    };
                    
                    document.body.style.overflow = 'hidden';

                    event.currentTarget.setPointerCapture?.(event.pointerId);

                    window.addEventListener('pointermove', this.onPointerMove, { passive: false });
                    window.addEventListener('pointerup', this.endDrag, { once: true });
                    window.addEventListener('pointercancel', this.endDrag, { once: true });
                },

                onPointerMove(event) {
                    if (!this.drag) {
                        return;
                    }

                    event.preventDefault();

                    const board = this.$refs.board;
                    const boardRect = board.getBoundingClientRect();

                    const x = event.clientX
                        - boardRect.left
                        + board.scrollLeft
                        - this.drag.offsetX;

                    const y = event.clientY
                        - boardRect.top
                        + board.scrollTop
                        - this.drag.offsetY;

                    this.drag.sticky.positionx = Math.max(0, Math.round(x));
                    this.drag.sticky.positiony = Math.max(0, Math.round(y));

                    this.refreshBoardHeight();
                },

                async endDrag() {

                    document.body.style.overflow = '';

                    window.removeEventListener('pointermove', this.onPointerMove);

                    const drag = this.drag;
                    this.drag = null;

                    if (!drag?.sticky?.id) {
                        return;
                    }

                    try {
                        const response = await fetch(
                            `${this.baseUrl}/${drag.sticky.id}/position`,
                            {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': this.csrfToken,
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    x: drag.sticky.positionx,
                                    y: drag.sticky.positiony,
                                }),
                            }
                        );

                        if (!response.ok) {
                            throw new Error(await this.responseMessage(response));
                        }
                    } catch (error) {
                        this.errorMessage = error.message || 'The new sticky position could not be saved.';
                        console.error('Sticky position save failed:', error);
                    }
                },

                async responseMessage(response) {
                    const data = await response.json().catch(() => null);

                    if (data?.message) {
                        return data.message;
                    }

                    if (data?.errors) {
                        return Object.values(data.errors).flat().join(' ');
                    }

                    return 'The sticky could not be saved.';
                },
                async deleteSticky(sticky) {
                    if (sticky.isNew || !sticky.id) {
                        this.stickies = this.stickies.filter(item => item !== sticky);
                        return;
                    }

                    if (!window.confirm('Delete this sticky? This cannot be undone.')) {
                        return;
                    }

                    this.errorMessage = '';
                    sticky.saving = true;

                    try {
                        const response = await fetch(`${this.baseUrl}/${sticky.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json',
                            },
                        });

                        if (!response.ok) {
                            throw new Error(await this.responseMessage(response));
                        }

                        this.stickies = this.stickies.filter(item => item.id !== sticky.id);
                    } catch (error) {
                        sticky.saving = false;
                        this.errorMessage = error.message || 'Unable to delete the sticky.';
                        console.error('Sticky delete failed:', error);
                    }
                },
            };
        }
    </script>
</x-app-layout>