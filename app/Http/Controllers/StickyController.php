<?php

namespace App\Http\Controllers;

use App\Models\Sticky;
use Illuminate\Http\Request;
use Illuminate\Mail\Markdown;

class StickyController extends Controller
{
    public function index()
    {
        $stickies = Sticky::query()
            ->orderByDesc('ispinned')
            ->orderByDesc('updatedat')
            ->get();

        return view('stickies.index', compact('stickies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'stickytext' => ['required', 'string'],
            'colourhex' => ['nullable', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'positionx' => ['nullable', 'integer', 'min:0'],
            'positiony' => ['nullable', 'integer', 'min:0'],
        ]);

        $sticky = Sticky::create([
            'stickytext' => $data['stickytext'],
            'colourhex' => $data['colourhex'] ?? '#FEF08A',
            'positionx' => $data['positionx'] ?? 24,
            'positiony' => $data['positiony'] ?? 24,
        ]);

        return response()->json([
            'sticky' => $this->stickyPayload($sticky),
        ], 201);
    }

    public function update(Request $request, Sticky $sticky)
    {
        $data = $request->validate([
            'stickytext' => ['required', 'string'],
            'colourhex' => ['nullable', 'regex:/^#[A-Fa-f0-9]{6}$/'],
        ]);

        $sticky->update([
            'stickytext' => $data['stickytext'],
            'colourhex' => $data['colourhex'] ?? $sticky->colourhex,
        ]);

        return response()->json([
            'sticky' => $this->stickyPayload($sticky->fresh()),
        ]);
    }

    public function destroy(Sticky $sticky)
    {
        $sticky->delete();

        return response()->noContent();
    }

    public function updatePosition(Request $request, Sticky $sticky)
    {
        $data = $request->validate([
            'x' => ['required', 'integer', 'min:0'],
            'y' => ['required', 'integer', 'min:0'],
        ]);

        $sticky->update([
            'positionx' => $data['x'],
            'positiony' => $data['y'],
        ]);

        return response()->noContent();
    }

    private function stickyPayload(Sticky $sticky): array
    {
        return [
            'id' => $sticky->id,
            'stickytext' => $sticky->stickytext ?? '',
            'colourhex' => $sticky->colourhex ?? '#FEF08A',
            'positionx' => (int) ($sticky->positionx ?? 24),
            'positiony' => (int) ($sticky->positiony ?? 24),
            'html' => app(Markdown::class)
                ->parse($sticky->stickytext ?? '')
                ->toHtml(),
        ];
    }
}