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
            'positionx'  => 20,
            'positiony'  => 20,
        ]);

        return redirect()
            ->route('stickies.index')
            ->with('success', 'Sticky created.');
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
}