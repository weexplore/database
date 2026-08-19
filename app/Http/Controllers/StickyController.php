<?php

namespace App\Http\Controllers;

use App\Models\Sticky;
use Illuminate\Http\Request;

class StickyController extends Controller
{
    public function index()
    {
        $stickies = Sticky::orderByDesc('ispinned')
            ->orderByDesc('updatedat')
            ->get();

        return view('stickies.index', compact('stickies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'stickytext' => ['required', 'string'],
            'colourhex'  => ['nullable', 'string', 'max:7'],
        ]);

        Sticky::create([
            'stickytext' => $data['stickytext'],
            'colourhex'  => $data['colourhex'] ?? '#FEF08A',
            'positionx'  => 40,
            'positiony'  => 40,
        ]);

        return redirect()
            ->route('stickies.index')
            ->with('success', 'Sticky created.');
    }

    public function edit(Request $request, Sticky $sticky)
    {
        $return = $request->input('return'); // where to go back after save

        return view('stickies.edit', compact('sticky', 'return'));
    }

    public function update(Request $request, Sticky $sticky)
    {
        $data = $request->validate([
            'stickytext' => ['required', 'string'],
            'colourhex'  => ['nullable', 'string', 'max:7'],
        ]);

        $sticky->update([
            'stickytext' => $data['stickytext'],
            'colourhex'  => $data['colourhex'] ?? $sticky->colourhex,
        ]);

        $returnUrl = $request->input('return_url');

        if ($returnUrl) {
            return redirect($returnUrl)->with('success', 'Sticky updated.');
        }

        return redirect()
            ->route('stickies.index')
            ->with('success', 'Sticky updated.');
    }

    public function destroy(Sticky $sticky)
    {
        $sticky->delete();

        return redirect()
            ->route('stickies.index')
            ->with('success', 'Sticky deleted.');
    }

    public function updatePosition(Request $request, Sticky $sticky)
    {
        $data = $request->validate([
            'x' => ['required', 'integer'],
            'y' => ['required', 'integer'],
        ]);

        $sticky->positionx = $data['x'];
        $sticky->positiony = $data['y'];
        $sticky->save();

        return response()->noContent();
    }

    public function createAndEdit()
    {
        $lastSticky = Sticky::orderByDesc('positiony')
            ->orderByDesc('id')
            ->first();

        $positionX = 40;

        // Start 40px from the top if there are no notes.
        // Otherwise, place the next note 240px below the lowest note.
        $positionY = $lastSticky
            ? ((int) $lastSticky->positiony + 240)
            : 40;

        $sticky = Sticky::create([
            'stickytext' => '',
            'colourhex'  => '#FEF08A',
            'positionx'  => $positionX,
            'positiony'  => $positionY,
        ]);

        return redirect()
            ->route('stickies.edit', [
                'sticky' => $sticky,
                'return' => route('stickies.index'),
            ])
            ->with('success', 'New sticky created. Add its content and save when ready.');
    }
}