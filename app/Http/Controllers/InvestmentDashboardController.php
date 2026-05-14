<?php

namespace App\Http\Controllers;

class InvestmentDashboardController extends Controller
{
    public function index()
    {
        return view('investments.index', [
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
        ]);
    }
}
