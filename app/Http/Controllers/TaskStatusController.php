<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TaskStatus;
use Illuminate\Http\Request;

class TaskStatusController extends Controller
{
    public function defaults()
    {
        $statuses = TaskStatus::globalDefaults()->orderBy('sortorder')->get();
        $project = null;

        return view('task-statuses.index', compact('statuses', 'project'));
    }

    public function index(Project $project)
    {
        $statuses = TaskStatus::forProject($project->id)->orderBy('sortorder')->get();

        return view('task-statuses.index', compact('project', 'statuses'));
    }

    public function update(Request $request, ?Project $project = null)
    {
        $data = $request->validate([
            'statuses' => 'array',
            'statuses.*.statuslabel' => 'required|string|max:100',
            'statuses.*.statuscode' => 'required|string|max:50',
            'statuses.*.colourhex' => 'required|string|max:7',
            'statuses.*.iscompletedstatus' => 'nullable|boolean',
            'statuses.*.sortorder' => 'nullable|integer',
            'statuses.*.isactive' => 'nullable|boolean',

            'new.statuslabel' => 'nullable|string|max:100',
            'new.statuscode' => 'nullable|string|max:50',
            'new.colourhex' => 'nullable|string|max:7',
            'new.iscompletedstatus' => 'nullable|boolean',
            'new.sortorder' => 'nullable|integer',
            'new.isactive' => 'nullable|boolean',
        ]);

        // Update existing statuses
        foreach ($data['statuses'] ?? [] as $id => $row) {
            TaskStatus::where('id', $id)->update([
                'statuslabel' => $row['statuslabel'],
                'statuscode' => $row['statuscode'],
                'colourhex' => $row['colourhex'],
                'iscompletedstatus' => !empty($row['iscompletedstatus']),
                'sortorder' => $row['sortorder'] ?? 0,
                'isactive' => !empty($row['isactive']),
            ]);
        }

        // Optional new status from bottom row
        if (!empty($data['new']['statuslabel']) && !empty($data['new']['statuscode'])) {
            $sort = $data['new']['sortorder']
                ?? (TaskStatus::forProject($project?->id)->max('sortorder') + 1);

            TaskStatus::create([
                'projectid' => $project?->id,
                'statuslabel' => $data['new']['statuslabel'],
                'statuscode' => $data['new']['statuscode'],
                'colourhex' => $data['new']['colourhex'] ?? '#94A3B8',
                'iscompletedstatus' => !empty($data['new']['iscompletedstatus']),
                'sortorder' => $sort,
                'isactive' => !empty($data['new']['isactive']),
            ]);
        }

        return redirect()->back()->with('success', 'Statuses updated.');
    }

    public function store(Request $request, ?Project $project = null)
    {
        $data = $request->validate([
            'statuslabel' => 'required|string|max:100',
            'statuscode' => 'required|string|max:50',
            'colourhex' => 'required|string|max:7',
        ]);

        $data['projectid'] = $project?->id;
        $data['sortorder'] = TaskStatus::forProject($project?->id)->max('sortorder') + 1;

        TaskStatus::create($data);

        return redirect()->back()->with('success', 'Status added.');
    }

    public function destroy(TaskStatus $taskStatus)
    {
        if ($taskStatus->tasks()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete a status that is still in use by tasks.');
        }

        $taskStatus->delete();

        return redirect()->back()->with('success', 'Status deleted.');
    }
}