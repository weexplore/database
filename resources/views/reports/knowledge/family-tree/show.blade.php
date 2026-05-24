@php
    $title = $reportTitle ?? 'Family Tree Report';

    $formatFact = function ($fact) {
        if (! $fact) {
            return null;
        }

        $text = $fact->datetext ?: optional($fact->datefrom)->format('d M Y') ?: 'Date unknown';

        if ($fact->place) {
            $text .= ' · ' . $fact->place->placename . ($fact->place->locality ? ', ' . $fact->place->locality : '');
        }

        return $text;
    };
@endphp

<x-report-layout :title="$title">
    <div class="screen-only flex items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ $title }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                Family tree grouped by marriage and children.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button type="button"
                    onclick="window.print()"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded text-sm">
                Print
            </button>

            @if(!empty($returnTo))
                <a href="{{ $returnTo }}"
                   class="button-link inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded text-sm">
                    Back
                </a>
            @endif
        </div>
    </div>

    <div class="print-only mb-6">
        <h1 class="text-xl font-semibold text-gray-900">{{ $title }}</h1>
        <div class="mt-1 text-sm text-gray-600">
            Printed {{ now()->format('j M Y g:i A') }}
        </div>
    </div>

    <div class="tree-grid">
        <div class="tree-row">
            <div class="span-3">
                @if($focusParents->get(0))
                    <div class="tree-card">
                        <div class="tree-label">Parent</div>
                        <div class="tree-name">{{ $focusParents[0]->tree_name }}</div>
                        @if($formatFact($focusParents[0]->tree_birth))
                            <div class="tree-meta" style="margin-top: .5rem;">B: {{ $formatFact($focusParents[0]->tree_birth) }}</div>
                        @endif
                        @if($formatFact($focusParents[0]->tree_death))
                            <div class="tree-meta">D: {{ $formatFact($focusParents[0]->tree_death) }}</div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="span-3">
                @if($focusParents->get(1))
                    <div class="tree-card">
                        <div class="tree-label">Parent</div>
                        <div class="tree-name">{{ $focusParents[1]->tree_name }}</div>
                        @if($formatFact($focusParents[1]->tree_birth))
                            <div class="tree-meta" style="margin-top: .5rem;">B: {{ $formatFact($focusParents[1]->tree_birth) }}</div>
                        @endif
                        @if($formatFact($focusParents[1]->tree_death))
                            <div class="tree-meta">D: {{ $formatFact($focusParents[1]->tree_death) }}</div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="span-6"></div>
        </div>

        <div class="tree-row">
            <div class="span-6">
                <div class="tree-card">
                    <div class="tree-label">Focus Person</div>
                    <div class="tree-name">{{ $focusPerson->tree_name }}</div>
                    @if($formatFact($focusPerson->tree_birth ?? null))
                        <div class="tree-meta" style="margin-top: .5rem;">B: {{ $formatFact($focusPerson->tree_birth) }}</div>
                    @endif
                    @if($formatFact($focusPerson->tree_death ?? null))
                        <div class="tree-meta">D: {{ $formatFact($focusPerson->tree_death) }}</div>
                    @endif
                </div>
            </div>

            <div class="span-6"></div>
        </div>

        @forelse($spouseChildGroups as $index => $group)
            @php
                $marriageFact = $group['spouseRelationship']?->relationshipFacts
                    ?->first(fn ($fact) => in_array(strtolower((string) $fact->facttype), ['marriage', 'married'], true));

                $spouseParents = $group['spouseParents'] ?? collect();
            @endphp

            <div style="border-top: 1px solid #e2e8f0; padding-top: 1.5rem;">
                <div class="mb-4">
                    <div class="tree-label">Marriage {{ $index + 1 }}</div>
                </div>

                @if($spouseParents->isNotEmpty())
                    <div class="tree-row" style="margin-bottom: .75rem;">
                        <div class="span-3"></div>

                        <div class="span-5">
                            <div class="tree-two-col">
                                @foreach($spouseParents as $spouseParent)
                                    <div class="tree-card">
                                        <div class="tree-label">Spouse Parent</div>
                                        <div class="tree-name">{{ $spouseParent->tree_name }}</div>
                                        @if($formatFact($spouseParent->tree_birth ?? null))
                                            <div class="tree-meta" style="margin-top: .5rem;">B: {{ $formatFact($spouseParent->tree_birth) }}</div>
                                        @endif
                                        @if($formatFact($spouseParent->tree_death ?? null))
                                            <div class="tree-meta">D: {{ $formatFact($spouseParent->tree_death) }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="span-4"></div>
                    </div>
                @endif

                <div class="tree-row">
                    <div class="span-3">
                        <div class="tree-marriage">
                            <div class="tree-label">Marriage</div>
                            @if($marriageFact)
                                <div class="tree-meta" style="margin-top: .5rem;">{{ $formatFact($marriageFact) }}</div>
                            @else
                                <div class="tree-muted" style="margin-top: .5rem;">No marriage fact recorded</div>
                            @endif
                        </div>
                    </div>

                    <div class="span-5">
                        <div class="tree-card">
                            <div class="tree-label">Spouse</div>
                            <div class="tree-name">{{ $group['spouse']->tree_name }}</div>
                            @if($formatFact($group['spouse']->tree_birth ?? null))
                                <div class="tree-meta" style="margin-top: .5rem;">B: {{ $formatFact($group['spouse']->tree_birth) }}</div>
                            @endif
                            @if($formatFact($group['spouse']->tree_death ?? null))
                                <div class="tree-meta">D: {{ $formatFact($group['spouse']->tree_death) }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="span-4"></div>
                </div>

                <div style="margin-top: 1rem;">
                    <div class="tree-label" style="margin-bottom: .5rem;">Children from this marriage</div>

                    <div class="tree-children-grid">
                        @forelse($group['children'] as $childEntry)
                            @php
                                $child = $childEntry['person'];
                                $childSpouse = $childEntry['spouse'];
                                $childMarriageFact = $childEntry['spouseRelationship']?->relationshipFacts
                                    ?->first(fn ($fact) => in_array(strtolower((string) $fact->facttype), ['marriage', 'married'], true));
                            @endphp

                            <div class="tree-card">
                                <div>
                                    <div class="tree-label">Child</div>
                                    <div class="tree-name">{{ $child->tree_name }}</div>
                                    @if($formatFact($child->tree_birth ?? null))
                                        <div class="tree-meta" style="margin-top: .5rem;">B: {{ $formatFact($child->tree_birth) }}</div>
                                    @endif
                                    @if($formatFact($child->tree_death ?? null))
                                        <div class="tree-meta">D: {{ $formatFact($child->tree_death) }}</div>
                                    @endif
                                </div>

                                @if($childSpouse)
                                    <div style="border-top: 1px solid #e5e7eb; padding-top: .75rem; margin-top: .75rem;">
                                        <div class="tree-label">Spouse</div>
                                        <div class="tree-name">{{ $childSpouse->tree_name }}</div>
                                        @if($formatFact($childSpouse->tree_birth ?? null))
                                            <div class="tree-meta" style="margin-top: .5rem;">B: {{ $formatFact($childSpouse->tree_birth) }}</div>
                                        @endif
                                        @if($formatFact($childSpouse->tree_death ?? null))
                                            <div class="tree-meta">D: {{ $formatFact($childSpouse->tree_death) }}</div>
                                        @endif
                                        <div class="tree-muted" style="margin-top: .5rem;">
                                            Marriage: {{ $childMarriageFact ? $formatFact($childMarriageFact) : 'No marriage fact recorded' }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="tree-card span-12">
                                <div class="tree-muted">No children found for this marriage.</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @empty
            <div class="tree-card">
                <div class="tree-muted">No spouse groups or children found.</div>
            </div>
        @endforelse
    </div>
</x-report-layout>