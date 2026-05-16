<?php

namespace App\Http\Controllers;

class ResearchDashboardController extends Controller
{
    public function index()
    {
        return view('research.index', [
            'cards' => [
                [
                    'title' => 'Knowledge Domains',
                    'description' => 'Maintain top-level research domains.',
                    'route' => route('knowledge-domains.index'),
                ],
                [
                    'title' => 'Knowledge Tags',
                    'description' => 'Maintain shared research and study tags.',
                    'route' => route('knowledge-tags.index'),
                ],
                [
                    'title' => 'Bible Versions',
                    'description' => 'Maintain Bible version master records.',
                    'route' => route('bible-versions.index'),
                ],
                [
                    'title' => 'Bible Books',
                    'description' => 'Maintain Bible book reference rows.',
                    'route' => route('bible-books.index'),
                ],
                [
                    'title' => 'Knowledge Categories',
                    'description' => 'Maintain hierarchical research categories.',
                    'route' => route('knowledge-categories.index', ['domainid' => 0, 'categoryid' => 0]),
                ],
                [
                    'title' => 'Knowledge Item Types',
                    'description' => 'Maintain hierarchical research categories.',
                    'route' => route('knowledge.item-types.index'),
                ],
            ],
        ]);
    }
}
