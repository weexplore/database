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
            ],
        ]);
    }
}
