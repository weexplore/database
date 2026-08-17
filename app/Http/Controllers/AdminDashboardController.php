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
                        [
                            'title' => 'Destination Item Types',
                            'description' => 'Maintain types for destination items.',
                            'route' => route('destination-item-types.index'),
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
                [
                    'title' => 'Cashbook',
                    'cards' => [
                        [
                            'title' => 'Legal Entities',
                            'description' => 'Maintain legal entities used for ownership, reporting, and cashbook scope.',
                            'route' => route('legal-entities.index'),
                        ],
                        [
                            'title' => 'Cashbook Accounts',
                            'description' => 'Manage bank, cash, and other cashbook accounts for each entity.',
                            'route' => route('cashbook-accounts.index'),
                        ],
                        [
                            'title' => 'Cashbook Categories',
                            'description' => 'Maintain receipt and payment categories, including grouped parent categories.',
                            'route' => route('cashbook-categories.index'),
                        ],
                        [
                            'title' => 'Cashbook Transactions',
                            'description' => 'Capture and review receipts, payments, transfers, and coded transaction lines.',
                            'route' => route('cashbook-transactions.index'),
                        ],
                        [
                            'title' => 'Cashbook Reports',
                            'description' => 'Cashbook Reports.',
                            'route' => route('cashbook-reports.index'),
                        ],
                        [
                            'title' => 'Cashbook Budgets',
                            'description' => 'Cashbook Budgets.',
                            'route' => route('cashbook.budgets.index'),
                        ],
                    ],
                ],
            ],
        ]);
    }
}