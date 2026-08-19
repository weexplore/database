<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\TaskRecurrence;
use App\Models\Label;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;


class TaskController extends Controller
{
    
    public function index(Project $project, Request $request)
    {
        $hideClosed = $request->boolean('hideclosed', true);

        $tasksQuery = $project->tasks()
            ->with([
                'status',
                'assignee',
                'labels',
                'parentTask',
                'recurrence' => function ($q) {
                    $q->where('isactive', 1);
                },
            ])
            ->withCount('subtasks');

        if ($hideClosed) {
            $tasksQuery->where(function (Builder $q) {
                $q->whereHas('status', function (Builder $q2) {
                    $q2->where('iscompletedstatus', false);
                })
                ->orWhereNull('statusid');
            });
        }

        $tasks = $tasksQuery
            ->orderByRaw('duedate IS NULL, duedate ASC')
            ->get()
            ->groupBy('statusid');

        $statuses = $project->taskStatuses()->orderBy('sortorder')->get();

        return view('tasks.index', compact('project', 'tasks', 'statuses', 'hideClosed'));
    }

    public function show(Task $task)
    {
        $task->load([
            'project',
            'status',
            'assignee',
            'labels',
            'recurrence',
            'subtasks.status',
            'subtasks.labels',
            'comments.user',
            'dependencies.dependsOnTask.status',
        ]);

        $projects = Project::orderBy('projectname')->get();

        $statuses = $task->project
            ->taskStatuses()
            ->orderBy('sortorder')
            ->get();

        $labels = Label::orderBy('labelname')->get();

        $projectTasks = $task->project
            ->tasks()
            ->where('id', '!=', $task->id)
            ->orderBy('tasktitle')
            ->get();

        return view('tasks.show', compact(
            'task',
            'projects',
            'statuses',
            'labels',
            'projectTasks'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'projectid'    => ['required', 'exists:projects,id'],
            'parenttaskid' => ['nullable', 'exists:tasks,id'],
            'statusid'     => ['required', 'exists:task_statuses,id'],
            'title'        => ['required', 'string', 'max:200'],
            'description'  => ['nullable', 'string'],
            'priority'     => ['nullable', 'in:low,medium,high,urgent'],
            'assigneeid'   => ['nullable', 'exists:users,id'],
            'startdate'    => ['nullable', 'date'],
            'duedate'      => ['nullable', 'date'],
            'labelids'     => ['nullable', 'array'],
            'labelids.*'   => ['integer', 'exists:labels,id'],
        ]);

        if (!empty($data['parenttaskid'])) {
            $parentTask = Task::findOrFail($data['parenttaskid']);

            if ((int) $parentTask->projectid !== (int) $data['projectid']) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'parenttaskid' => 'A sub-task must belong to the same project as its parent task.',
                    ]);
            }
        }

        $data['tasktitle'] = $data['title'];
        unset($data['title']);

        $data['assignedto'] = $data['assigneeid'] ?? null;
        unset($data['assigneeid']);

        // labelids belongs to the pivot relation, not the tasks table.
        $labelIds = $data['labelids'] ?? [];
        unset($data['labelids']);

        $task = Task::create($data);

        $task->labels()->sync($labelIds);

        if (!empty($task->parenttaskid)) {
            return redirect()
                ->route('tasks.show', [
                    'task' => $task->parenttaskid,
                    'from' => $request->input('from'),
                    'return' => $request->input('return_url'),
                ])
                ->with('success', 'Sub-task created.');
        }

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Task created.');
    }

    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'parenttaskid' => ['nullable', 'exists:tasks,id'],
            'statusid'     => ['required', 'exists:task_statuses,id'],
            'title'        => ['required', 'string', 'max:200'],
            'description'  => ['nullable', 'string'],
            'priority'     => ['nullable', 'in:low,medium,high,urgent'],
            'assigneeid'   => ['nullable', 'exists:users,id'],
            'startdate'    => ['nullable', 'date'],
            'duedate'      => ['nullable', 'date'],
            'labelids'     => ['nullable', 'array'],
            'labelids.*'   => ['integer', 'exists:labels,id'],
        ]);

        if (!empty($data['parenttaskid'])) {
            if ((int) $data['parenttaskid'] === (int) $task->id) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'parenttaskid' => 'A task cannot be its own parent.',
                    ]);
            }

            $parentTask = Task::findOrFail($data['parenttaskid']);

            if ((int) $parentTask->projectid !== (int) $task->projectid) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'parenttaskid' => 'A sub-task must belong to the same project as its parent task.',
                    ]);
            }
        }

        $data['tasktitle'] = $data['title'];
        unset($data['title']);

        $data['assignedto'] = $data['assigneeid'] ?? null;
        unset($data['assigneeid']);

        $labelIds = $data['labelids'] ?? [];
        unset($data['labelids']);

        $task->update($data);
        $task->labels()->sync($labelIds);

        $from = $request->input('from');
        $returnUrl = $request->input('return_url');

        if ($returnUrl) {
            return redirect($returnUrl)->with('success', 'Task updated.');
        }

        if ($from === 'alltasks') {
            return redirect()
                ->route('tasksall.all')
                ->with('success', 'Task updated.');
        }

        return redirect()
            ->route('tasks.index', $task->projectid)
            ->with('success', 'Task updated.');
    }

    public function destroy(Task $task)
    {
        if ($task->subtasks()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete a task that still has sub-tasks.');
        }

        $task->delete();

        return redirect()->route('tasks.index', $task->projectid)->with('success', 'Task deleted.');
    }

    public function moveStatus(Request $request, Task $task)
    {
        $data = $request->validate([
            'statusid' => 'required|exists:task_statuses,id',
        ]);

        $task->statusid = $data['statusid'];
        $task->save();

        // Optionally adjust sortorder here if you want ordering in each column

        return response()->noContent();
    }

    public function moveProject(Request $request, Task $task)
    {
        $data = $request->validate([
            'projectid' => 'required|exists:projects,id',
        ]);

        $newProject = Project::findOrFail($data['projectid']);

        // Optional: remap status by code so the task lands in an equivalent column
        if ($task->statusid) {
            $oldStatus = TaskStatus::find($task->statusid);

            if ($oldStatus) {
                $matchingStatus = TaskStatus::forProject($newProject->id)
                    ->where('statuscode', $oldStatus->statuscode)
                    ->first();

                $task->statusid = $matchingStatus?->id;
            }
        }

        $task->projectid = $newProject->id;
        $task->save();

        return redirect()
            ->route('tasks.index', $newProject)
            ->with('success', 'Task moved to project: '.$newProject->projectname);
    }
    public function updateRecurrence(Request $request, Task $task)
    {
        $data = $request->validate([
            'frequency'         => ['nullable', 'in:daily,weekly,monthly,yearly'],
            'intervalcount'     => ['nullable', 'integer', 'min:1'],
            'leaddaysbeforedue' => ['nullable', 'integer', 'min:0'],
            'startsonoccurrence'=> ['nullable', 'date'],
            'endsonoccurrence'  => ['nullable', 'date', 'after_or_equal:startsonoccurrence'],
            'maxoccurrences'    => ['nullable', 'integer', 'min:1'],
            'isactive'          => ['boolean'],

            'monthlypattern'    => ['nullable', 'in:day_of_month,nth_weekday,last_day'],
            'monthday'          => ['nullable', 'integer', 'between:1,31'],
            'monthweeknumber'   => ['nullable', 'integer', 'in:1,2,3,4,-1'],
            'monthweekday'      => ['nullable', 'integer', 'between:0,6'],
        ]);

        $task->isrecurringtemplate = filled($data['frequency']);
        $task->save();

        TaskRecurrence::updateOrCreate(
            ['tasktemplateid' => $task->id],
            [
                'frequency'         => $data['frequency'],
                'intervalcount'     => $data['intervalcount'] ?? 1,
                'leaddaysbeforedue' => $data['leaddaysbeforedue'] ?? 0,
                'startsonoccurrence'=> $data['startsonoccurrence'] ?? null,
                'endsonoccurrence'  => $data['endsonoccurrence'] ?? null,
                'maxoccurrences'    => $data['maxoccurrences'] ?? null,
                'isactive'          => $data['isactive'] ?? false,

                'monthlypattern'    => $data['monthlypattern'] ?? null,
                'monthday'          => $data['monthday'] ?? null,
                'monthweeknumber'   => $data['monthweeknumber'] ?? null,
                'monthweekday'      => $data['monthweekday'] ?? null,
            ]
        );

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Recurring settings updated.');
    }

    public function allIndex(Request $request)
{
    $projectId = $request->input('projectid');
    $labelId   = $request->input('labelid');
    $search    = trim((string) $request->input('search', ''));
    $hideClosed = $request->boolean('hideclosed', true);

    $allowedSorts = ['tasktitle', 'startdate', 'duedate', 'projectname'];
    $sort = $request->input('sort', 'duedate');
    if (! in_array($sort, $allowedSorts, true)) {
        $sort = 'duedate';
    }

    $dir = $request->input('dir', 'asc');
    if (! in_array($dir, ['asc', 'desc'], true)) {
        $dir = 'asc';
    }

    $tasksQuery = Task::query()
        ->with([
            'project',
            'labels',
            'status',
            'parentTask',
            'recurrence' => function ($q) {
                $q->where('isactive', 1);
            },
        ])
        ->withCount('subtasks')
        ->when($projectId, function (Builder $q) use ($projectId) {
            $q->where('projectid', $projectId);
        })
        ->when($labelId, function (Builder $q) use ($labelId) {
            $q->whereHas('labels', function (Builder $q2) use ($labelId) {
                $q2->where('labels.id', $labelId);
            });
        })
        ->when($search, function (Builder $q) use ($search) {
            $q->where(function (Builder $q2) use ($search) {
                $q2->where('tasktitle', 'like', '%'.$search.'%')
                   ->orWhere('description', 'like', '%'.$search.'%');
            });
        })
        ->when($hideClosed, function (Builder $q) {
            $q->where(function (Builder $q2) {
                $q2->whereHas('status', function (Builder $q3) {
                    $q3->where('iscompletedstatus', false);
                })
                ->orWhereNull('statusid');
            });
        });

    if ($sort === 'projectname') {
        $tasksQuery->join('projects', 'tasks.projectid', '=', 'projects.id')
            ->select('tasks.*')
            ->orderBy('projects.projectname', $dir);
    } elseif ($sort === 'duedate') {
        $tasksQuery->orderByRaw('duedate IS NULL')
            ->orderBy('duedate', $dir);
    } else {
        $tasksQuery->orderBy($sort, $dir);
    }

    $tasks = $tasksQuery->paginate(25)->withQueryString();

    $projects = Project::orderBy('projectname')->get();
    $labels   = Label::orderBy('labelname')->get();

    return view('tasks.all-index', [
        'tasks'       => $tasks,
        'projects'    => $projects,
        'labels'      => $labels,
        'currentSort' => $sort,
        'currentDir'  => $dir,
        'projectId'   => $projectId,
        'labelId'     => $labelId,
        'hideClosed'  => $hideClosed,
        'search'      => $search,
    ]);
}

    public function bulkUpdate(Request $request)
    {
        $data = $request->validate([
            'tasks' => ['required', 'array'],
            'tasks.*.statusid'  => ['nullable', 'exists:task_statuses,id'],
            'tasks.*.startdate' => ['nullable', 'date'],
            'tasks.*.duedate'   => ['nullable', 'date'],
            'tasks.*.priority'  => ['nullable', 'string'],
        ]);

        foreach ($data['tasks'] as $taskId => $taskData) {
            $task = Task::find($taskId);
            if (! $task) {
                continue;
            }

            $update = [];

            if (array_key_exists('statusid', $taskData)) {
                $update['statusid'] = $taskData['statusid'];
            }
            if (array_key_exists('priority', $taskData)) {
                $update['priority'] = $taskData['priority'];
            }
            if (array_key_exists('startdate', $taskData)) {
                $update['startdate'] = $taskData['startdate'] ?: null;
            }
            if (array_key_exists('duedate', $taskData)) {
                $update['duedate'] = $taskData['duedate'] ?: null;
            }

            if ($update) {
                $task->update($update);
            }
        }

        return redirect()
            ->route('tasksall.all', $request->only(['projectid', 'labelid', 'sort', 'dir']))
            ->with('success', 'Tasks updated.');
    }
}