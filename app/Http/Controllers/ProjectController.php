<?php
namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::with('owner')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->ownerid, fn($q) => $q->where('ownerid', $request->ownerid))
            ->orderBy('projectname')
            ->get();

        return view('projects.index', compact('projects'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'projects' => 'array',
            'projects.*.projectname' => 'required|string|max:150',
            'projects.*.status' => 'required|string',
            'projects.*.startdate' => 'nullable|date',
            'projects.*.targetdate' => 'nullable|date',
            'projects.*.colourhex' => 'required|string|max:7',
            'projects.*.ownerid' => 'nullable|exists:users,id',

            'new.projectname' => 'nullable|string|max:150',
            'new.status' => 'nullable|string',
            'new.startdate' => 'nullable|date',
            'new.targetdate' => 'nullable|date',
            'new.colourhex' => 'nullable|string|max:7',
            'new.ownerid' => 'nullable|exists:users,id',
        ]);

        // Update existing projects
        foreach ($data['projects'] ?? [] as $id => $row) {
            Project::where('id', $id)->update([
                'projectname' => $row['projectname'],
                'status' => $row['status'],
                'startdate' => $row['startdate'] ?? null,
                'targetdate' => $row['targetdate'] ?? null,
                'colourhex' => $row['colourhex'],
                'ownerid' => $row['ownerid'] ?? null,
            ]);
        }

        // Optional new project from bottom row
        if (!empty($data['new']['projectname']) && !empty($data['new']['status'])) {
            $project = Project::create([
                'projectname' => $data['new']['projectname'],
                'status' => $data['new']['status'],
                'startdate' => $data['new']['startdate'] ?? null,
                'targetdate' => $data['new']['targetdate'] ?? null,
                'colourhex' => $data['new']['colourhex'] ?? '#6366F1',
                'ownerid' => $data['new']['ownerid'] ?? null,
            ]);

            // Seed per-project statuses from global defaults
            $project->cloneDefaultStatuses();
        }

        return redirect()->route('projects.index')->with('success', 'Projects updated.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'projectname' => 'required|string|max:150',
            'status' => 'required|string',
            'startdate' => 'nullable|date',
            'targetdate' => 'nullable|date',
            'colourhex' => 'required|string|max:7',
            'ownerid' => 'nullable|exists:users,id',
        ]);

        $project = Project::create($data);
        $project->cloneDefaultStatuses();

        return redirect()->route('projects.index')->with('success', 'Project created.');
    }

    public function destroy(Project $project)
    {
        if ($project->tasks()->exists()) {
            return redirect()->route('projects.index')
                ->with('error', 'Cannot delete a project that still has tasks.');
        }

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted.');
    }
}