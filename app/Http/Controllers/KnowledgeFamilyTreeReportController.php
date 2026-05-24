<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class KnowledgeFamilyTreeReportController extends Controller
{
    public function show(Request $request)
    {
        $focusItemId = (int) $request->input('knowledge_item_id');
        abort_if($focusItemId <= 0, 404, 'No focus person selected.');

        $focusPerson = KnowledgeItem::query()
            ->with([
                'primaryCategory',
                'personFacts' => function ($query) {
                    $query->with('place')
                        ->orderByDesc('ispreferred')
                        ->orderBy('sortorder')
                        ->orderBy('datefrom')
                        ->orderBy('id');
                },
                'outgoingRelationships',
                'outgoingRelationships.toItem.primaryCategory',
                'outgoingRelationships.toItem.personFacts' => function ($query) {
                    $query->with('place')
                        ->orderByDesc('ispreferred')
                        ->orderBy('sortorder')
                        ->orderBy('datefrom')
                        ->orderBy('id');
                },
                'outgoingRelationships.toItem.outgoingRelationships',
                'outgoingRelationships.toItem.incomingRelationships',
                'outgoingRelationships.toItem.outgoingRelationships.toItem.primaryCategory',
                'outgoingRelationships.toItem.incomingRelationships.fromItem.primaryCategory',
                'outgoingRelationships.toItem.outgoingRelationships.toItem.personFacts' => function ($query) {
                    $query->with('place')
                        ->orderByDesc('ispreferred')
                        ->orderBy('sortorder')
                        ->orderBy('datefrom')
                        ->orderBy('id');
                },
                'outgoingRelationships.toItem.incomingRelationships.fromItem.personFacts' => function ($query) {
                    $query->with('place')
                        ->orderByDesc('ispreferred')
                        ->orderBy('sortorder')
                        ->orderBy('datefrom')
                        ->orderBy('id');
                },
                'outgoingRelationships.relationshipFacts' => function ($query) {
                    $query->with('place')
                        ->orderByDesc('ispreferred')
                        ->orderBy('sortorder')
                        ->orderBy('datefrom')
                        ->orderBy('id');
                },
                'incomingRelationships',
                'incomingRelationships.fromItem.primaryCategory',
                'incomingRelationships.fromItem.personFacts' => function ($query) {
                    $query->with('place')
                        ->orderByDesc('ispreferred')
                        ->orderBy('sortorder')
                        ->orderBy('datefrom')
                        ->orderBy('id');
                },
                'incomingRelationships.relationshipFacts' => function ($query) {
                    $query->with('place')
                        ->orderByDesc('ispreferred')
                        ->orderBy('sortorder')
                        ->orderBy('datefrom')
                        ->orderBy('id');
                },
                'incomingRelationships.fromItem.outgoingRelationships',
                'incomingRelationships.fromItem.incomingRelationships',
                'incomingRelationships.fromItem.outgoingRelationships.toItem.primaryCategory',
                'incomingRelationships.fromItem.incomingRelationships.fromItem.primaryCategory',
                'incomingRelationships.fromItem.outgoingRelationships.toItem.personFacts' => function ($query) {
                    $query->with('place')
                        ->orderByDesc('ispreferred')
                        ->orderBy('sortorder')
                        ->orderBy('datefrom')
                        ->orderBy('id');
                },
                'incomingRelationships.fromItem.incomingRelationships.fromItem.personFacts' => function ($query) {
                    $query->with('place')
                        ->orderByDesc('ispreferred')
                        ->orderBy('sortorder')
                        ->orderBy('datefrom')
                        ->orderBy('id');
                },
            ])
            ->findOrFail($focusItemId);

        $focusPerson = $this->decoratePerson($focusPerson);

        $spouseRelationships = $this->spouseRelationships($focusPerson);
        $spouseRelationship = $spouseRelationships->first();    

        $spouse = $spouseRelationship?->toItem && (int) $spouseRelationship->fromitemid === (int) $focusPerson->id
            ? $spouseRelationship->toItem
            : ($spouseRelationship?->fromItem && (int) $spouseRelationship->toitemid === (int) $focusPerson->id
                ? $spouseRelationship->fromItem
                : null);

        if ($spouse) {
            $spouse = $this->decoratePerson($spouse);
        }

        $otherSpouses = $spouseRelationships
            ->slice(1)
            ->map(function ($relationship) use ($focusPerson) {
                $spouse = (int) $relationship->fromitemid === (int) $focusPerson->id
                    ? $relationship->toItem
                    : $relationship->fromItem;

                return [
                    'relationship' => $relationship,
                    'person' => $spouse ? $this->decoratePerson($spouse) : null,
                ];
            })
            ->filter(fn ($entry) => $entry['person'])
            ->values();

        $focusParents = $this->findParents($focusPerson);
        $spouseParents = $spouse ? $this->findParents($spouse) : collect();
        $children = $this->findChildrenWithSpouses($focusPerson, $spouse);
        $spouseChildGroups = $this->findSpouseChildGroups($focusPerson);

        return view('reports.knowledge.family-tree.show', [
            'focusPerson' => $focusPerson,
            'spouse' => $spouse,
            'spouseRelationship' => $spouseRelationship,
            'focusParents' => $focusParents,
            'spouseParents' => $spouseParents,
            'spouseChildGroups' => $spouseChildGroups,
            'returnTo' => $request->input('return_to', url()->previous()),
            'reportTitle' => 'Family Tree – ' . $focusPerson->tree_name,
        ]);
    }

    protected function selectPrimarySpouseRelationship($person)
    {
        return $this->sortRelationshipsForPerson(
            $person->outgoingRelationships
                ->merge($person->incomingRelationships)
                ->filter(fn ($relationship) => $this->normaliseRelationshipType($relationship->relationshiptype) === 'married'),
            $person
        )->first();
    }

    protected function findParents($person): Collection
{
    $parentsFromOutgoingChildOf = $this->sortRelationshipsForPerson(
        $person->outgoingRelationships
            ->filter(fn ($relationship) => $this->normaliseRelationshipType($relationship->relationshiptype) === 'child-of'),
        $person
    )->map(fn ($relationship) => $relationship->toItem);

    $parentsFromIncomingParentOf = $this->sortRelationshipsForPerson(
        $person->incomingRelationships
            ->filter(fn ($relationship) => $this->normaliseRelationshipType($relationship->relationshiptype) === 'parent-of'),
        $person
    )->map(fn ($relationship) => $relationship->fromItem);

    $allParentRelationships = $person->outgoingRelationships
        ->merge($person->incomingRelationships)
        ->filter(function ($relationship) use ($person) {
            $type = $this->normaliseRelationshipType($relationship->relationshiptype);

            if (!in_array($type, ['child-of', 'parent-of'], true)) {
                return false;
            }

            $relatedId = (int) $relationship->fromitemid === (int) $person->id
                ? (int) $relationship->toitemid
                : (int) $relationship->fromitemid;

            return $relatedId > 0;
        });

    return $parentsFromOutgoingChildOf
        ->merge($parentsFromIncomingParentOf)
        ->filter()
        ->unique('id')
        ->sortBy([
            [
                function ($parent) use ($allParentRelationships, $person) {
                    $relationship = $allParentRelationships->first(function ($relationship) use ($parent, $person) {
                        $relatedId = (int) $relationship->fromitemid === (int) $person->id
                            ? (int) $relationship->toitemid
                            : (int) $relationship->fromitemid;

                        return $relatedId === (int) $parent->id;
                    });

                    return $relationship?->sortOrderFor($person) ?? 999999;
                },
                'asc',
            ],
            [fn ($parent) => mb_strtolower($parent->itemname ?? ''), 'asc'],
            ['id', 'asc'],
        ])
        ->take(2)
        ->values()
        ->map(fn ($parent) => $this->decoratePerson($parent));
}

    protected function findChildrenWithSpouses($focusPerson, $spouse = null): Collection
{
    $candidateParents = collect([$focusPerson, $spouse])->filter();

    $children = $candidateParents
        ->flatMap(function ($parent) {
            $childrenFromOutgoingParentOf = $this->sortRelationshipsForPerson(
                $parent->outgoingRelationships
                    ->filter(fn ($relationship) => $this->normaliseRelationshipType($relationship->relationshiptype) === 'parent-of'),
                $parent
            )->map(fn ($relationship) => $relationship->toItem);

            $childrenFromIncomingChildOf = $this->sortRelationshipsForPerson(
                $parent->incomingRelationships
                    ->filter(fn ($relationship) => $this->normaliseRelationshipType($relationship->relationshiptype) === 'child-of'),
                $parent
            )->map(fn ($relationship) => $relationship->fromItem);

            return $childrenFromOutgoingParentOf->merge($childrenFromIncomingChildOf);
        })
        ->filter()
        ->unique('id')
        ->reject(fn ($person) => (int) $person->id === (int) $focusPerson->id)
        ->reject(fn ($person) => $spouse && (int) $person->id === (int) $spouse->id)
        ->sortBy([
            [
                function ($child) use ($focusPerson, $spouse) {
                    $focusRelationship = $focusPerson->outgoingRelationships
                        ->merge($focusPerson->incomingRelationships)
                        ->first(function ($relationship) use ($child, $focusPerson) {
                            $relatedId = (int) $relationship->fromitemid === (int) $focusPerson->id
                                ? (int) $relationship->toitemid
                                : (int) $relationship->fromitemid;

                            return $relatedId === (int) $child->id
                                && in_array(
                                    $this->normaliseRelationshipType($relationship->relationshiptype),
                                    ['parent-of', 'child-of'],
                                    true
                                );
                        });

                    $focusSort = $focusRelationship?->sortOrderFor($focusPerson);

                    if ($focusSort !== null) {
                        return $focusSort;
                    }

                    if ($spouse) {
                        $spouseRelationship = $spouse->outgoingRelationships
                            ->merge($spouse->incomingRelationships)
                            ->first(function ($relationship) use ($child, $spouse) {
                                $relatedId = (int) $relationship->fromitemid === (int) $spouse->id
                                    ? (int) $relationship->toitemid
                                    : (int) $relationship->fromitemid;

                                return $relatedId === (int) $child->id
                                    && in_array(
                                        $this->normaliseRelationshipType($relationship->relationshiptype),
                                        ['parent-of', 'child-of'],
                                        true
                                    );
                            });

                        $spouseSort = $spouseRelationship?->sortOrderFor($spouse);

                        if ($spouseSort !== null) {
                            return $spouseSort;
                        }
                    }

                    return 999999;
                },
                'asc',
            ],
            [fn ($child) => mb_strtolower($child->itemname ?? ''), 'asc'],
            ['id', 'asc'],
        ])
        ->values();

    return $children->map(function ($child) {
        $child = $this->decoratePerson($child);

        $spouseRelationship = $this->sortRelationshipsForPerson(
            $child->outgoingRelationships
                ->merge($child->incomingRelationships)
                ->filter(fn ($relationship) => $this->normaliseRelationshipType($relationship->relationshiptype) === 'married'),
            $child
        )->first();

        $childSpouse = null;

        if ($spouseRelationship) {
            $childSpouse = (int) $spouseRelationship->fromitemid === (int) $child->id
                ? $spouseRelationship->toItem
                : $spouseRelationship->fromItem;

            if ($childSpouse) {
                $childSpouse = $this->decoratePerson($childSpouse);
            }
        }

        return [
            'person' => $child,
            'spouse' => $childSpouse,
            'spouseRelationship' => $spouseRelationship,
        ];
    });
}

    protected function sortRelationshipsForPerson(Collection $relationships, $person): Collection
    {
        return $relationships
            ->filter(fn ($relationship) => $relationship)
            ->sortBy([
                [fn ($relationship) => $relationship->sortOrderFor($person) ?? 999999, 'asc'],
                ['id', 'asc'],
            ])
            ->values();
    }

    protected function decoratePerson($person)
    {
        $sortedFacts = collect($person->personFacts ?? [])
            ->sortBy([
                ['ispreferred', 'desc'],
                ['sortorder', 'asc'],
                ['datefrom', 'asc'],
                ['id', 'asc'],
            ])
            ->values();

        $birthFact = $sortedFacts->first(fn ($fact) =>
            in_array(strtolower((string) $fact->facttype), ['birth', 'born', 'baptism', 'christening'], true)
        );

        $deathFact = $sortedFacts->first(fn ($fact) =>
            in_array(strtolower((string) $fact->facttype), ['death', 'died', 'burial', 'funeral'], true)
        );

        $person->tree_name = $this->personDisplayName($person);
        $person->tree_birth = $birthFact;
        $person->tree_death = $deathFact;

        return $person;
    }

    protected function personDisplayName($person): string
    {
        return trim((string) (
            $person->primaryCategory?->categoryname
            ?: $person->itemname
            ?: 'Unnamed person'
        ));
    }

    protected function normaliseRelationshipType(?string $type): string
    {
        return strtolower(trim((string) $type));
    }
    protected function spouseRelationships($person): Collection
    {
        return $this->sortRelationshipsForPerson(
            $person->outgoingRelationships
                ->merge($person->incomingRelationships)
                ->filter(fn ($relationship) => $this->normaliseRelationshipType($relationship->relationshiptype) === 'married'),
            $person
        );
    }

    protected function findSpouseChildGroups($focusPerson): Collection
{
    $spouseRelationships = $this->sortRelationshipsForPerson(
        $focusPerson->outgoingRelationships
            ->merge($focusPerson->incomingRelationships)
            ->filter(fn ($relationship) => $this->normaliseRelationshipType($relationship->relationshiptype) === 'married'),
        $focusPerson
    );

    return $spouseRelationships
        ->map(function ($spouseRelationship) use ($focusPerson) {
            $spouse = (int) $spouseRelationship->fromitemid === (int) $focusPerson->id
                ? $spouseRelationship->toItem
                : $spouseRelationship->fromItem;

            if (! $spouse) {
                return null;
            }

            $spouse = $this->decoratePerson($spouse);

            return [
                'spouse' => $spouse,
                'spouseRelationship' => $spouseRelationship,
                'spouseParents' => $this->findParents($spouse),
                'children' => $this->findChildrenForCouple($focusPerson, $spouse),
            ];
        })
        ->filter()
        ->values();
}

protected function findChildrenForCouple($focusPerson, $spouse): Collection
{
    $focusChildren = $this->findChildrenForParent($focusPerson);
    $spouseChildren = $this->findChildrenForParent($spouse);

    $sharedChildIds = $focusChildren->pluck('id')
        ->intersect($spouseChildren->pluck('id'))
        ->values();

    return $focusChildren
        ->filter(fn ($child) => $sharedChildIds->contains($child->id))
        ->values()
        ->map(function ($child) {
            $child = $this->decoratePerson($child);

            $childSpouseRelationship = $this->sortRelationshipsForPerson(
                $child->outgoingRelationships
                    ->merge($child->incomingRelationships)
                    ->filter(fn ($relationship) => $this->normaliseRelationshipType($relationship->relationshiptype) === 'married'),
                $child
            )->first();

            $childSpouse = null;

            if ($childSpouseRelationship) {
                $childSpouse = (int) $childSpouseRelationship->fromitemid === (int) $child->id
                    ? $childSpouseRelationship->toItem
                    : $childSpouseRelationship->fromItem;

                if ($childSpouse) {
                    $childSpouse = $this->decoratePerson($childSpouse);
                }
            }

            return [
                'person' => $child,
                'spouse' => $childSpouse,
                'spouseRelationship' => $childSpouseRelationship,
            ];
        });
}

    protected function findChildrenForParent($parent): Collection
    {
        return $parent->outgoingRelationships
            ->merge($parent->incomingRelationships)
            ->filter(function ($relationship) use ($parent) {
                return in_array(
                    $this->normaliseRelationshipType($relationship->relationshiptype),
                    ['parent-of', 'child-of'],
                    true
                );
            })
            ->map(function ($relationship) use ($parent) {
                return (int) $relationship->fromitemid === (int) $parent->id
                    ? $relationship->toItem
                    : $relationship->fromItem;
            })
            ->filter()
            ->unique('id')
            ->sortBy([
                [
                    function ($child) use ($parent) {
                        $relationship = $parent->outgoingRelationships
                            ->merge($parent->incomingRelationships)
                            ->first(function ($relationship) use ($child, $parent) {
                                $relatedId = (int) $relationship->fromitemid === (int) $parent->id
                                    ? (int) $relationship->toitemid
                                    : (int) $relationship->fromitemid;

                                return $relatedId === (int) $child->id
                                    && in_array(
                                        $this->normaliseRelationshipType($relationship->relationshiptype),
                                        ['parent-of', 'child-of'],
                                        true
                                    );
                            });

                        return $relationship?->sortOrderFor($parent) ?? 999999;
                    },
                    'asc',
                ],
                [fn ($child) => mb_strtolower($child->itemname ?? ''), 'asc'],
                ['id', 'asc'],
            ])
            ->values();
    }
}