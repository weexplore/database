<?php

namespace App\Http\Controllers;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return view('admin.index', [
            'groups' => [
                [
                    'title' => 'Travel Setup',
                    'cards' => [
                        [
                            'title' => 'Countries',
                            'description' => 'Maintain the core country validation list.',
                            'route' => route('countries.index'),
                        ],
                        [
                            'title' => 'States',
                            'description' => 'Maintain states and territories linked to countries.',
                            'route' => route('states.index'),
                        ],
                        [
                            'title' => 'Regions',
                            'description' => 'Manage travel and tourism regions for grouping and filtering.',
                            'route' => route('regions.index'),
                        ],
                        [
                            'title' => 'Travellers',
                            'description' => 'Manage Ian, Heather, and any future travellers.',
                            'route' => route('travellers.index'),
                        ],
                        [
                            'title' => 'Vehicles',
                            'description' => 'Create and manage vehicles.',
                            'route' => route('vehicles.index'),
                        ],
                        [
                            'title' => 'Destinations',
                            'description' => 'Manage editorial destination records linked to places or localities.',
                            'route' => route('destinations.index'),
                        ],
                        [
                            'title' => 'Destination Items',
                            'description' => 'Attractions, walks, dump points, water points, drives, and more.',
                            'route' => route('destination-items.index'),
                        ],
                        [
                            'title' => 'Fuel Stops',
                            'description' => 'Reusable fuel stop records with caravan access notes.',
                            'route' => route('fuel-stops.index'),
                        ],
                        [
                            'title' => 'Fuel Price Observations',
                            'description' => 'Keep price history by stop, date, and fuel type.',
                            'route' => route('fuel-price-observations.index'),
                        ],
                    ],
                ],
                [
                    'title' => 'Research',
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
                            'title' => 'Knowledge Item Types',
                            'description' => 'Maintain knowledge item type reference rows.',
                            'route' => route('knowledge.item-types.index'),
                        ],
                    ],
                ],
                [
                    'title' => 'Investments',
                    'cards' => [
                        [
                            'title' => 'Exchanges',
                            'description' => 'Maintain market and exchange reference data.',
                            'route' => route('exchanges.index'),
                        ],
                        [
                            'title' => 'Instrument Types',
                            'description' => 'Maintain investment classification rows.',
                            'route' => route('instrument-types.index'),
                        ],
                        [
                            'title' => 'Portfolios',
                            'description' => 'Maintain ownership and account groupings.',
                            'route' => route('portfolios.index'),
                        ],
                    ],
                ],
            ],
        ]);
    }
}