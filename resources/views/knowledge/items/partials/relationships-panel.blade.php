{{-- resources/views/knowledge/items/partials/relationships-panel.blade.php --}}

@php
    $relationshipsPayload = $displayRelationships
        ->map(function ($entry) {
            $relationship = $entry['relationship'];
            $relatedItem = $entry['relatedItem'];
            $direction = $entry['direction'];

            return [
                'id' => $relationship->id,
                'direction' => $direction,
                'relateditemid' => $relatedItem?->id,
                'relateditemname' => $relatedItem?->itemname ?? 'Missing related item',
                'relateditemcategory' => $relatedItem?->primaryCategory?->categoryname
                    ?? 'Uncategorised',
                'relationshiptype' => $relationship->relationshiptype,
                'relationshiptype_label' => $entry['displayTypeLabel']
                    ?? $relationship->relationshipTypeLabel(),
                'effective_date' => $relationship->effective_date?->format('Y-m-d'),
                'effective_date_display' => $relationship->effective_date?->format('d M Y'),
                'notes' => $relationship->notes ?? '',
                'notes_html' => app(\Illuminate\Mail\Markdown::class)
                    ->parse($relationship->notes ?? '')
                    ->toHtml(),
                'sortorder' => (int) ($entry['sortorder'] ?? 0),
            ];
        })
        ->values();

    $relationshipItemsPayload = ($relationshipItems ?? collect())
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->itemname ?? 'Untitled item',
                'category' => $item->primaryCategory?->categoryname ?? 'Uncategorised',
            ];
        })
        ->values();
@endphp

<script id="knowledge-relationships-initial-data" type="application/json">
{!! json_encode([
    'relationships' => $relationshipsPayload,
    'relationshipItems' => $relationshipItemsPayload,
    'relationshipTypes' => $relationshipTypeOptions ?? [],
    'storeUrl' => route('knowledge.items.relationships.store', $knowledgeItem),
    'baseUrl' => url('/knowledge/items/'.$knowledgeItem->id.'/relationships'),
    'reorderUrl' => route('knowledge.items.relationships.reorder', $knowledgeItem),
    'csrfToken' => csrf_token(),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
</script>

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
     x-data="knowledgeRelationshipsPanel()">

    {{-- Header --}}
    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Relationships</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Link this knowledge item to related items in the same domain. Relationships can be viewed from either direction.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-medium">
                    <span x-text="relationships.length"></span> total
                </span>

                <button type="button"
                        @click="startNewRelationship()"
                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                    + Add Relationship
                </button>
            </div>
        </div>
    </div>

    <template x-if="errorMessage">
        <div class="mx-4 sm:mx-6 mt-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
             x-text="errorMessage">
        </div>
    </template>

    {{-- New relationship editor --}}
    <template x-if="newRelationship">
        <div class="m-4 sm:m-6 rounded-lg border border-blue-200 bg-blue-50 p-4 sm:p-5">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <h4 class="text-sm font-semibold text-blue-900">Add Relationship</h4>
                    <p class="mt-1 text-xs text-blue-700">
                        New relationships are created from this item to the selected related item.
                    </p>
                </div>

                <button type="button"
                        @click="cancelNewRelationship()"
                        class="inline-flex shrink-0 items-center px-3 py-1.5 bg-white text-gray-700 border border-gray-300 rounded text-xs hover:bg-gray-50">
                    Cancel
                </button>
            </div>

            @include('knowledge.items.partials.relationship-inline-editor', [
                'draftReference' => 'newRelationship',
                'saveAction' => 'saveNewRelationship()',
                'cancelAction' => 'cancelNewRelationship()',
                'saveLabel' => 'Save New Relationship',
            ])
        </div>
    </template>

    {{-- Relationship list --}}
    <div id="knowledge-relationships-list"
         x-ref="relationshipsList"
         class="divide-y divide-gray-200">

        <template x-for="relationship in relationships" :key="relationship.id">
            <div class="p-4 sm:p-6 knowledge-relationship-row"
                 :data-relationship-id="relationship.id">

                {{-- Display mode --}}
                <template x-if="!relationship.editing">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex min-w-0 flex-1 items-start gap-3">
                            <button type="button"
                                    class="knowledge-relationship-drag-handle inline-flex h-8 w-8 shrink-0 touch-none select-none cursor-grab items-center justify-center text-gray-400 hover:text-gray-600 active:cursor-grabbing"
                                    style="touch-action: none;"
                                    title="Drag to reorder"
                                    aria-label="Drag to reorder">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-5 w-5"
                                     viewBox="0 0 20 20"
                                     fill="currentColor"
                                     aria-hidden="true">
                                    <path d="M3 5h14a1 1 0 110 2H3a1 1 0 110-2Zm0 4h14a1 1 0 110 2H3a1 1 0 110-2Zm0 4h14a1 1 0 110 2H3a1 1 0 110-2Z" />
                                </svg>
                            </button>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="text-sm font-semibold text-gray-900 break-words">
                                        <span x-text="relationship.relateditemcategory"></span>:
                                        <span x-text="relationship.relateditemname"></span>
                                    </h4>

                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700"
                                          x-text="relationship.relationshiptype_label">
                                    </span>

                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium"
                                          :class="relationship.direction === 'outgoing'
                                              ? 'border-blue-200 bg-blue-50 text-blue-700'
                                              : 'border-violet-200 bg-violet-50 text-violet-700'"
                                          x-text="relationship.direction === 'outgoing' ? 'Outgoing' : 'Incoming'">
                                    </span>
                                </div>

                                <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-500">
                                    <template x-if="relationship.effective_date">
                                        <span>
                                            Effective:
                                            <span x-text="relationship.effective_date_display"></span>
                                        </span>
                                    </template>

                                    <span>
                                        Sort:
                                        <span x-text="relationship.sortorder"></span>
                                    </span>
                                </div>

                                <template x-if="relationship.notes_html">
                                    <div class="mt-3 markdown-content prose prose-sm max-w-none text-gray-700"
                                         x-html="relationship.notes_html">
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-2 self-end sm:self-start">
                            <button type="button"
                                    @click="startEditRelationship(relationship)"
                                    class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded text-xs hover:bg-gray-300">
                                Edit
                            </button>

                            <button type="button"
                                    @click="deleteRelationship(relationship)"
                                    class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded text-xs hover:bg-red-700">
                                Delete
                            </button>
                        </div>
                    </div>
                </template>

                {{-- Edit mode --}}
                <template x-if="relationship.editing">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900">Edit Relationship</h4>
                                <p class="mt-1 text-xs text-gray-500">
                                    <span x-show="relationship.direction === 'outgoing'">
                                        This is stored as an outgoing relationship from the current item.
                                    </span>

                                    <span x-show="relationship.direction === 'incoming'">
                                        This is stored as an incoming relationship. The related item is the source side.
                                    </span>
                                </p>
                            </div>

                            <button type="button"
                                    @click="cancelEditRelationship(relationship)"
                                    class="inline-flex shrink-0 items-center px-3 py-1.5 bg-white text-gray-700 border border-gray-300 rounded text-xs hover:bg-gray-50">
                                Cancel
                            </button>
                        </div>

                        @include('knowledge.items.partials.relationship-inline-editor', [
                            'draftReference' => 'relationship.draft',
                            'saveAction' => 'saveExistingRelationship(relationship)',
                            'cancelAction' => 'cancelEditRelationship(relationship)',
                            'saveLabel' => 'Save Relationship',
                        ])
                    </div>
                </template>
            </div>
        </template>

        <template x-if="relationships.length === 0 && !newRelationship">
            <div class="p-6 text-sm text-gray-500">
                No relationships recorded for this knowledge item yet.
            </div>
        </template>
    </div>
</div>

@include('partials.markdown.markdown-styles')

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>

<script>
    function knowledgeRelationshipsPanel() {
        return {
            relationships: [],
            relationshipItems: [],
            relationshipTypes: {},
            storeUrl: '',
            baseUrl: '',
            reorderUrl: '',
            csrfToken: '',
            newRelationship: null,
            saving: false,
            reordering: false,
            errorMessage: '',
            sortable: null,

            init() {
                try {
                    const dataElement = document.getElementById(
                        'knowledge-relationships-initial-data'
                    );

                    const data = JSON.parse(dataElement.textContent);

                    this.relationships = (data.relationships || []).map(
                        relationship => this.prepareRelationship(relationship)
                    );

                    this.relationshipItems = data.relationshipItems || [];
                    this.relationshipTypes = data.relationshipTypes || {};
                    this.storeUrl = data.storeUrl;
                    this.baseUrl = data.baseUrl;
                    this.reorderUrl = data.reorderUrl;
                    this.csrfToken = data.csrfToken;

                    this.sortRelationships();

                    this.$nextTick(() => this.setupSortable());
                } catch (error) {
                    this.errorMessage = error.message || 'Unable to load relationships.';
                    console.error(
                        'Knowledge relationships initialisation failed:',
                        error
                    );
                }
            },

            prepareRelationship(relationship) {
                return {
                    ...relationship,
                    editing: false,
                    draft: null,
                };
            },

            emptyDraft() {
                return {
                    direction: 'outgoing',
                    relateditemid: '',
                    relationshiptype: '',
                    effective_date: '',
                    notes: '',
                    sortorder: this.nextSortOrder(),
                };
            },

            nextSortOrder() {
                if (this.relationships.length === 0) {
                    return 1;
                }

                return Math.max(
                    ...this.relationships.map(
                        relationship => Number(relationship.sortorder) || 0
                    )
                ) + 1;
            },

            startNewRelationship() {
                this.errorMessage = '';
                this.closeAllEditors();
                this.newRelationship = this.emptyDraft();
                this.disableSortable();
            },

            cancelNewRelationship() {
                this.newRelationship = null;
                this.enableSortable();
            },

            startEditRelationship(relationship) {
                this.errorMessage = '';
                this.newRelationship = null;
                this.closeAllEditors();

                relationship.draft = {
                    direction: relationship.direction,
                    relateditemid: relationship.relateditemid || '',
                    relationshiptype: relationship.relationshiptype || '',
                    effective_date: relationship.effective_date || '',
                    notes: relationship.notes || '',
                    sortorder: relationship.sortorder || 0,
                };

                relationship.editing = true;
                this.disableSortable();
            },

            cancelEditRelationship(relationship) {
                relationship.editing = false;
                relationship.draft = null;
                this.enableSortable();
            },

            closeAllEditors() {
                this.relationships.forEach(relationship => {
                    relationship.editing = false;
                    relationship.draft = null;
                });

                this.enableSortable();
            },

            async saveNewRelationship() {
                if (!this.newRelationship) {
                    return;
                }

                await this.saveRelationship(
                    this.newRelationship,
                    null
                );
            },

            async saveExistingRelationship(relationship) {
                await this.saveRelationship(
                    relationship.draft,
                    relationship
                );
            },

            async saveRelationship(payload, existingRelationship) {
                this.errorMessage = '';

                if (!payload.relateditemid || !payload.relationshiptype) {
                    this.errorMessage = 'Related item and relationship type are required.';
                    return;
                }

                this.saving = true;

                try {
                    const isNew = !existingRelationship;

                    const body = {
                        relationshiptype: payload.relationshiptype,
                        effective_date: payload.effective_date || null,
                        notes: payload.notes || null,
                        sortorder: Number(payload.sortorder) || 0,
                    };

                    if (isNew) {
                        body.toitemid = payload.relateditemid;
                    } else {
                        body.relateditemid = payload.relateditemid;
                    }

                    const response = await fetch(
                        isNew
                            ? this.storeUrl
                            : `${this.baseUrl}/${existingRelationship.id}`,
                        {
                            method: isNew ? 'POST' : 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(body),
                        }
                    );

                    if (!response.ok) {
                        throw new Error(await this.responseMessage(response));
                    }

                    const data = await response.json();

                    if (isNew) {
                        this.relationships.push(
                            this.prepareRelationship(data.relationship)
                        );

                        this.newRelationship = null;
                    } else {
                        const index = this.relationships.findIndex(
                            item => Number(item.id) === Number(existingRelationship.id)
                        );

                        if (index !== -1) {
                            this.relationships.splice(
                                index,
                                1,
                                this.prepareRelationship(data.relationship)
                            );
                        }
                    }

                    this.sortRelationships();
                    this.enableSortable();
                } catch (error) {
                    this.errorMessage = error.message
                        || 'Unable to save the relationship.';

                    console.error('Knowledge relationship save failed:', error);
                } finally {
                    this.saving = false;
                }
            },

            async deleteRelationship(relationship) {
                if (!window.confirm(
                    'Delete this relationship? This cannot be undone.'
                )) {
                    return;
                }

                this.errorMessage = '';
                this.saving = true;

                try {
                    const response = await fetch(
                        `${this.baseUrl}/${relationship.id}`,
                        {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json',
                            },
                        }
                    );

                    if (!response.ok) {
                        throw new Error(await this.responseMessage(response));
                    }

                    this.relationships = this.relationships.filter(
                        item => Number(item.id) !== Number(relationship.id)
                    );

                    this.sortRelationships();
                } catch (error) {
                    this.errorMessage = error.message
                        || 'Unable to delete the relationship.';

                    console.error('Knowledge relationship delete failed:', error);
                } finally {
                    this.saving = false;
                }
            },

            sortRelationships() {
                this.relationships.sort((left, right) => {
                    const leftSort = Number(left.sortorder) || 0;
                    const rightSort = Number(right.sortorder) || 0;

                    if (leftSort !== rightSort) {
                        return leftSort - rightSort;
                    }

                    return String(left.relateditemname || '').localeCompare(
                        String(right.relateditemname || '')
                    );
                });
            },

            setupSortable() {
                if (
                    !this.$refs.relationshipsList
                    || typeof Sortable === 'undefined'
                    || this.sortable
                ) {
                    return;
                }

                this.sortable = Sortable.create(
                    this.$refs.relationshipsList,
                    {
                        animation: 150,
                        handle: '.knowledge-relationship-drag-handle',
                        draggable: '.knowledge-relationship-row',
                        ghostClass: 'bg-blue-50',
                        chosenClass: 'bg-slate-50',
                        onEnd: () => this.persistReorder(),
                    }
                );
            },

            disableSortable() {
                if (this.sortable) {
                    this.sortable.option('disabled', true);
                }
            },

            enableSortable() {
                if (this.sortable) {
                    this.sortable.option('disabled', false);
                }
            },

            async persistReorder() {
                if (this.reordering || !this.$refs.relationshipsList) {
                    return;
                }

                const rows = Array.from(
                    this.$refs.relationshipsList.querySelectorAll(
                        '.knowledge-relationship-row'
                    )
                );

                const relationshipOrder = {};

                rows.forEach((row, index) => {
                    const relationshipId = row.dataset.relationshipId;

                    if (relationshipId) {
                        relationshipOrder[relationshipId] = index + 1;
                    }
                });

                this.relationships.forEach(relationship => {
                    if (relationshipOrder[relationship.id]) {
                        relationship.sortorder =
                            relationshipOrder[relationship.id];
                    }
                });

                this.reordering = true;
                this.errorMessage = '';

                try {
                    const response = await fetch(this.reorderUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            relationship_order: relationshipOrder,
                        }),
                    });

                    if (!response.ok) {
                        throw new Error(await this.responseMessage(response));
                    }

                    this.sortRelationships();
                } catch (error) {
                    this.errorMessage = error.message
                        || 'Unable to save relationship order.';

                    console.error(
                        'Knowledge relationship reorder failed:',
                        error
                    );
                } finally {
                    this.reordering = false;
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

                return 'The request could not be completed.';
            },
        };
    }
</script>