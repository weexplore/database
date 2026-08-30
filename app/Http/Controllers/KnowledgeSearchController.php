<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KnowledgeDomain;
use App\Models\KnowledgeItem;
use App\Models\KnowledgeNote;
use App\Models\KnowledgeSource;
use App\Models\KnowledgeReviewLog; // or your review/log model
use App\Models\KnowledgeCategory;
use App\Models\KnowledgeRelationship;
use App\Models\BibleReference;

class KnowledgeSearchController extends Controller
{
    public function index(Request $request)
    {
        $domainId   = $request->input('domainid');
        $q          = trim((string) $request->input('q'));
        $typeFilter = (array) $request->input('types', []);

        $domains = KnowledgeDomain::orderBy('domainname')->get();

        $selectedDomain = $domainId
            ? $domains->firstWhere('id', (int) $domainId)
            : null;

        $hasBibleTools = (bool) ($selectedDomain?->hasbibletools ?? false);

        $items   = collect();
        $notes   = collect();
        $sources = collect();
        $reviews = collect();
        $categories    = collect();
        $relationships = collect();
        $bibleReferences = collect();

        if ($domainId && $q !== '') {
            // Basic pattern: one domain at a time, grouped by type. [cite:9]
            // Categories
            if (empty($typeFilter) || in_array('categories', $typeFilter, true)) {
                $categories = KnowledgeCategory::query()
                    ->where('domainid', $domainId)
                    ->where(function ($query) use ($q) {
                        $query->where('categoryname', 'like', "%{$q}%")
                            ->orWhere('description', 'like', "%{$q}%");
                    })
                    ->orderBy('categoryname')
                    ->limit(100)
                    ->get();
            }

            // Knowledge Items
            if (empty($typeFilter) || in_array('items', $typeFilter, true)) {
                $items = KnowledgeItem::query()
                    ->with([
                        'primaryCategory',
                    ])
                    ->whereHas('primaryCategory', function ($query) use ($domainId) {
                        $query->where('domainid', $domainId);
                    })
                    ->where(function ($query) use ($q) {
                        $query->where('itemname', 'like', "%{$q}%")
                            ->orWhere('itemtype', 'like', "%{$q}%")
                            ->orWhere('itemstatus', 'like', "%{$q}%")
                            ->orWhere('summary', 'like', "%{$q}%")
                            ->orWhere('detailednotes', 'like', "%{$q}%")
                            ->orWhere('significance', 'like', "%{$q}%")
                            ->orWhere('reviewnotes', 'like', "%{$q}%");
                    })
                    ->orderBy('itemname')
                    ->limit(100)
                    ->get();
            }

            // Notes
            if (empty($typeFilter) || in_array('notes', $typeFilter, true)) {
                $notes = KnowledgeNote::query()
                    ->with([
                        'knowledgeItem.primaryCategory',
                    ])
                    ->whereHas('knowledgeItem.primaryCategory', function ($query) use ($domainId) {
                        $query->where('domainid', $domainId);
                    })
                    ->where(function ($query) use ($q) {
                        $query->where('title', 'like', "%{$q}%")
                            ->orWhere('notetype', 'like', "%{$q}%")
                            ->orWhere('notecontent', 'like', "%{$q}%")
                            ->orWhere('stance', 'like', "%{$q}%");
                    })
                    ->orderByDesc('createdat')
                    ->limit(100)
                    ->get();
            }

        // Relationships
            if (empty($typeFilter) || in_array('relationships', $typeFilter, true)) {
                $baseRelationships = KnowledgeRelationship::query()
                    ->with([
                        'fromItem.primaryCategory',
                        'toItem.primaryCategory',
                    ])
                    ->where(function ($query) use ($domainId) {
                        $query->whereHas('fromItem.primaryCategory', function ($subQuery) use ($domainId) {
                            $subQuery->where('domainid', $domainId);
                        })->orWhereHas('toItem.primaryCategory', function ($subQuery) use ($domainId) {
                            $subQuery->where('domainid', $domainId);
                        });
                    })
                   ->where(function ($query) use ($q) {
                        $query->where('relationshiptype', 'like', "%{$q}%")
                            ->orWhere('notes', 'like', "%{$q}%")
                            ->orWhereHas('fromItem', function ($subQuery) use ($q) {
                                $subQuery->where(function ($itemQuery) use ($q) {
                                    $itemQuery->where('itemname', 'like', "%{$q}%")
                                        ->orWhere('itemtype', 'like', "%{$q}%")
                                        ->orWhere('itemstatus', 'like', "%{$q}%")
                                        ->orWhere('summary', 'like', "%{$q}%")
                                        ->orWhere('detailednotes', 'like', "%{$q}%")
                                        ->orWhere('significance', 'like', "%{$q}%")
                                        ->orWhere('reviewnotes', 'like', "%{$q}%");
                                })->orWhereHas('primaryCategory', function ($categoryQuery) use ($q) {
                                    $categoryQuery->where('categoryname', 'like', "%{$q}%")
                                        ->orWhere('description', 'like', "%{$q}%");
                                });
                            })
                            ->orWhereHas('toItem', function ($subQuery) use ($q) {
                                $subQuery->where(function ($itemQuery) use ($q) {
                                    $itemQuery->where('itemname', 'like', "%{$q}%")
                                        ->orWhere('itemtype', 'like', "%{$q}%")
                                        ->orWhere('itemstatus', 'like', "%{$q}%")
                                        ->orWhere('summary', 'like', "%{$q}%")
                                        ->orWhere('detailednotes', 'like', "%{$q}%")
                                        ->orWhere('significance', 'like', "%{$q}%")
                                        ->orWhere('reviewnotes', 'like', "%{$q}%");
                                })->orWhereHas('primaryCategory', function ($categoryQuery) use ($q) {
                                    $categoryQuery->where('categoryname', 'like', "%{$q}%")
                                        ->orWhere('description', 'like', "%{$q}%");
                                });
                            });
                    })
                    ->limit(200)
                    ->get();

                $twoWayTypes = ['parent-of', 'child-of', 'married'];

            $relationships = $baseRelationships
                ->flatMap(function ($relationship) use ($twoWayTypes) {
                    $fromItem = $relationship->fromItem;
                    $toItem   = $relationship->toItem;

                    $fromLabel = trim(collect([
                        $fromItem?->primaryCategory?->categoryname,
                        $fromItem?->itemname,
                    ])->filter()->implode(': '));

                    $toLabel = trim(collect([
                        $toItem?->primaryCategory?->categoryname,
                        $toItem?->itemname,
                    ])->filter()->implode(': '));

                    $fromText = $fromLabel !== '' ? $fromLabel : ('Item #' . $relationship->fromitemid);
                    $toText   = $toLabel   !== '' ? $toLabel   : ('Item #' . $relationship->toitemid);

                    $rows = collect([
                        [
                            'relationship'      => $relationship,
                            'direction'         => 'outgoing',
                            'displayTypeLabel'  => $relationship->relationshipTypeLabel(),
                            'fromText'          => $fromText,
                            'toText'            => $toText,
                            'targetItem'        => $fromItem ?: $toItem,
                            'sort_name'         => mb_strtolower($fromLabel . ' - ' . $toLabel),
                        ],
                    ]);

                    if (in_array($relationship->relationshiptype, $twoWayTypes, true)) {
                        $revFromText = $toText;
                        $revToText   = $fromText;

                        $rows->push([
                            'relationship'      => $relationship,
                            'direction'         => 'incoming',
                            'displayTypeLabel'  => $relationship->inverseRelationshipTypeLabel(),
                            'fromText'          => $revFromText,
                            'toText'            => $revToText,
                            'targetItem'        => $toItem ?: $fromItem,
                            'sort_name'         => mb_strtolower($toLabel . ' - ' . $fromLabel),
                        ]);
                    }

                    return $rows;
                })
                ->sortBy('sort_name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values();
            }

            // Sources
            if (empty($typeFilter) || in_array('sources', $typeFilter, true)) {
                $sources = KnowledgeSource::query()
                    ->with([
                        'knowledgeItem.primaryCategory',
                    ])
                    ->whereHas('knowledgeItem.primaryCategory', function ($query) use ($domainId) {
                        $query->where('domainid', $domainId);
                    })
                    ->where(function ($query) use ($q) {
                        $query->where('sourcetype', 'like', "%{$q}%")
                            ->orWhere('sourceurl', 'like', "%{$q}%")
                            ->orWhere('sourcetitle', 'like', "%{$q}%")
                            ->orWhere('sourcepublisher', 'like', "%{$q}%")
                            ->orWhere('importedsummary', 'like', "%{$q}%")
                            ->orWhere('importednotes', 'like', "%{$q}%")
                            ->orWhere('importstatus', 'like', "%{$q}%")
                            ->orWhere('reviewedby', 'like', "%{$q}%")
                            ->orWhere('internalnotes', 'like', "%{$q}%");
                    })
                    ->orderByDesc('retrievedon')
                    ->limit(100)
                    ->get();
            }

            // Review logs / reviews
            if (empty($typeFilter) || in_array('reviews', $typeFilter, true)) {
                $reviews = KnowledgeReviewLog::query()
                    ->with([
                        'knowledgeItem.primaryCategory',
                    ])
                    ->whereHas('knowledgeItem.primaryCategory', function ($query) use ($domainId) {
                        $query->where('domainid', $domainId);
                    })
                    ->where(function ($query) use ($q) {
                        $query->where('reviewtype', 'like', "%{$q}%")
                            ->orWhere('outcome', 'like', "%{$q}%")
                            ->orWhere('summary', 'like', "%{$q}%");
                    })
                    ->orderByDesc('reviewdate')
                    ->limit(100)
                    ->get();
            }

            // Bible References
            //
            // Deliberately excludes cachedpassagetext and cachedreferencetext.
            // Search only the user-maintained reference label and notes.
            if (empty($typeFilter) || in_array('bible-references', $typeFilter, true)) {
                $bibleReferences = BibleReference::query()
                    ->with([
                        'knowledgeItem.primaryCategory',
                        'book',
                        'version',
                    ])
                    ->whereHas('knowledgeItem.primaryCategory', function ($query) use ($domainId) {
                        $query->where('domainid', $domainId);
                    })
                    ->where(function ($query) use ($q) {
                        $query->where('referencelabel', 'like', "%{$q}%")
                            ->orWhere('notes', 'like', "%{$q}%");
                    })
                    ->orderByDesc('updatedat')
                    ->limit(100)
                    ->get();
            }
        }

        return view('knowledge.search', [
            'domains' => $domains,
            'selectedDomainId' => $domainId,
            'selectedDomain' => $selectedDomain,
            'hasBibleTools' => $hasBibleTools,
            'q' => $q,
            'typeFilter' => $typeFilter,
            'categories' => $categories,
            'items' => $items,
            'notes' => $notes,
            'sources' => $sources,
            'reviews' => $reviews,
            'relationships' => $relationships,
            'bibleReferences' => $bibleReferences,
        ]);
    }
}