<?php
namespace App\Http\Controllers;

use App\Models\Label;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    public function index(Request $request)
    {
        $labels = Label::withCount('taskLabels')
            ->when($request->search, fn($q) => $q->where('labelname', 'like', "%{$request->search}%"))
            ->orderBy('labelname')
            ->get();

        return view('labels.index', compact('labels'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'labels.*.id' => 'required|exists:labels,id',
            'labels.*.labelname' => 'required|string|max:100',
            'labels.*.colourhex' => 'required|string|max:7',
        ]);

        foreach ($data['labels'] as $row) {
            Label::where('id', $row['id'])->update([
                'labelname' => $row['labelname'],
                'colourhex' => $row['colourhex'],
            ]);
        }

        return redirect()->route('labels.index')->with('success', 'Labels updated.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'labelname' => 'required|string|max:100',
            'colourhex' => 'required|string|max:7',
        ]);

        Label::create($data);

        return redirect()->route('labels.index')->with('success', 'Label added.');
    }

    public function destroy(Label $label)
    {
        if ($label->taskLabels()->exists()) {
            return redirect()->route('labels.index')
                ->with('error', 'Cannot delete a label that is still assigned to tasks.');
        }

        $label->delete();

        return redirect()->route('labels.index')->with('success', 'Label deleted.');
    }
    
    public function bulkUpdate(Request $request)
    {
        $data = $request->validate([
            'labels' => 'array',
            'labels.*.labelname' => 'required|string|max:100',
            'labels.*.colourhex' => 'required|string|max:7',
            'new.labelname' => 'nullable|string|max:100',
            'new.colourhex' => 'nullable|string|max:7',
        ]);

        // Update existing labels — use the array key as the ID
        foreach ($data['labels'] ?? [] as $id => $row) {
            Label::where('id', $id)->update([
                'labelname' => $row['labelname'],
                'colourhex' => $row['colourhex'],
            ]);
        }

        // Optional new label from bottom row
        if (!empty($data['new']['labelname'])) {
            Label::create([
                'labelname' => $data['new']['labelname'],
                'colourhex' => $data['new']['colourhex'] ?? '#6B7280',
            ]);
        }

        return redirect()->route('labels.index')->with('success', 'Labels saved.');
    }
}