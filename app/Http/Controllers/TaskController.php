<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\TaskComment;
use App\Models\TaskRecurrence;
use App\Models\Label;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


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
            ->withCount('subtasks')
            ->withCount([
                'subtasks as open_subtasks_count' => function (Builder $query) {
                    $query->where(function (Builder $statusQuery) {
                        $statusQuery->whereHas('status', function (Builder $query) {
                            $query->where('iscompletedstatus', false);
                        })
                        ->orWhereNull('statusid');
                    });
                },
            ]);

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
            'estimatedefforthours' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'actualefforthours' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'taskexpectation' => ['nullable', 'string'],
            'statuscomment' => ['nullable', 'string'],
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

        foreach ([
            'estimatedefforthours',
            'actualefforthours',
        ] as $field) {
            if (($data[$field] ?? null) === '') {
                $data[$field] = null;
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
                    'return_url' => $request->input('return_url'),
                ])
                ->with('success', 'Sub-task created.');
        }

        $returnUrl = $this->safeReturnUrl($request->input('return_url'));

        if ($returnUrl) {
            return redirect($returnUrl)->with('success', 'Task created.');
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
            'estimatedefforthours' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'actualefforthours' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'taskexpectation' => ['nullable', 'string'],
            'statuscomment' => ['nullable', 'string'],
        ]);

        foreach ([
            'estimatedefforthours',
            'actualefforthours',
        ] as $field) {
            if (($data[$field] ?? null) === '') {
                $data[$field] = null;
            }
        }

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

        $newStatus = TaskStatus::find($data['statusid']);

        if ($newStatus?->iscompletedstatus) {
            if (! $task->completedat) {
                $data['completedat'] = now();
            }
        } else {
            $data['completedat'] = null;
        }

        $data['tasktitle'] = $data['title'];
        unset($data['title']);

        $data['assignedto'] = $data['assigneeid'] ?? null;
        unset($data['assigneeid']);

        $labelIds = $data['labelids'] ?? [];
        unset($data['labelids']);

        $task->update($data);
        $task->labels()->sync($labelIds);

        $returnUrl = $this->safeReturnUrl($request->input('return_url'));

        if ($returnUrl) {
            return redirect($returnUrl)->with('success', 'Task updated.');
        }

        if ($request->input('from') === 'alltasks') {
            return redirect()
                ->route('tasksall.all')
                ->with('success', 'Task updated.');
        }

        if ($request->input('from') === 'outlook') {
            return redirect()
                ->route('tasks.outlook')
                ->with('success', 'Task updated.');
        }

        return redirect()
            ->route('tasks.index', $task->projectid)
            ->with('success', 'Task updated.');
    }

    public function destroy(Request $request, Task $task)
    {
        if ($task->subtasks()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete a task that still has sub-tasks.');
        }

        $task->delete();

        $returnUrl = $this->safeReturnUrl($request->input('return_url'));

        if ($returnUrl) {
            return redirect($returnUrl)->with('success', 'Task deleted.');
        }

        return redirect()
            ->route('tasks.index', $task->projectid)
            ->with('success', 'Task deleted.');
    }

    public function moveStatus(Request $request, Task $task)
    {
        $data = $request->validate([
            'statusid' => ['required', 'exists:task_statuses,id'],
        ]);

        $newStatus = TaskStatus::findOrFail($data['statusid']);

        $task->statusid = $newStatus->id;
        $task->completedat = $newStatus->iscompletedstatus
            ? ($task->completedat ?? now())
            : null;

        $task->save();

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

        $returnUrl = $this->safeReturnUrl($request->input('return_url'));

        if ($returnUrl) {
            return redirect($returnUrl)
                ->with('success', 'Task moved to project: '.$newProject->projectname);
        }

        return redirect()
            ->route('tasks.index', $newProject)
            ->with('success', 'Task moved to project: '.$newProject->projectname);
    }
    public function updateRecurrence(Request $request, Task $task)
    {
        $data = $request->validate([
            'frequency' => ['nullable', 'in:daily,weekly,monthly,yearly'],
            'intervalcount' => ['nullable', 'integer', 'min:1'],
            'leaddaysbeforedue' => ['nullable', 'integer', 'min:0'],
            'startsonoccurrence' => ['nullable', 'date'],
            'endsonoccurrence' => ['nullable', 'date', 'after_or_equal:startsonoccurrence'],
            'maxoccurrences' => ['nullable', 'integer', 'min:1'],
            'isactive' => ['nullable', 'boolean'],

            'monthlypattern' => ['nullable', 'in:day_of_month,nth_weekday,last_day'],
            'monthday' => ['nullable', 'integer', 'between:1,31'],
            'monthweeknumber' => ['nullable', 'integer', 'in:1,2,3,4,-1'],
            'monthweekday' => ['nullable', 'integer', 'between:0,6'],
        ]);

        /*
        * A blank frequency is the UI's “None” selection.
        * Remove the recurrence definition entirely rather than storing
        * an invalid/inactive row with a NULL frequency.
        */
        if (blank($data['frequency'] ?? null)) {
            $task->recurrence()->delete();

            $task->isrecurringtemplate = 0;
            $task->save();

           return $this->redirectAfterTaskAction(
                $request,
                $task,
                'Recurring task settings removed.',
                $request->input('from')
            );
        }

        /*
        * These fields are meaningful only for a real recurrence.
        * Require a start date when recurrence is being enabled/updated.
        */
        if (blank($data['startsonoccurrence'] ?? null)) {
            return back()
                ->withInput()
                ->withErrors([
                    'startsonoccurrence' => 'A recurrence start date is required when a frequency is selected.',
                ]);
        }

        $task->isrecurringtemplate = 1;
        $task->save();

        TaskRecurrence::updateOrCreate(
            ['tasktemplateid' => $task->id],
            [
                'frequency' => $data['frequency'],
                'intervalcount' => $data['intervalcount'] ?? 1,
                'leaddaysbeforedue' => $data['leaddaysbeforedue'] ?? 0,
                'startsonoccurrence' => $data['startsonoccurrence'],
                'endsonoccurrence' => $data['endsonoccurrence'] ?? null,
                'maxoccurrences' => $data['maxoccurrences'] ?? null,
                'isactive' => $request->boolean('isactive'),

                'monthlypattern' => $data['monthlypattern'] ?? null,
                'monthday' => $data['monthday'] ?? null,
                'monthweeknumber' => $data['monthweeknumber'] ?? null,
                'monthweekday' => $data['monthweekday'] ?? null,
            ]
        );

        return $this->redirectAfterTaskAction(
            $request,
            $task,
            'Recurring task settings updated.',
            $request->input('from')
        );
    }

    public function allIndex(Request $request)
{
    $projectId = $request->input('projectid');
    $labelId   = $request->input('labelid');
    $search    = trim((string) $request->input('search', ''));
    $hideClosed = $request->boolean('hideclosed', true);
    $templatesOnly = $request->boolean('templatesonly');

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
        ->withCount([
            'subtasks as open_subtasks_count' => function (Builder $query) {
                $query->where(function (Builder $statusQuery) {
                    $statusQuery->whereHas('status', function (Builder $query) {
                        $query->where('iscompletedstatus', false);
                    })
                    ->orWhereNull('statusid');
                });
            },
        ])
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
        ->when($templatesOnly, function (Builder $query) {
            $query->where('isrecurringtemplate', 1);
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
        'templatesOnly' => $templatesOnly,
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
    public function outlook(Request $request)
{
    $today = now()->startOfDay();
    $tomorrow = $today->copy()->addDay();
    $weekEnd = $today->copy()->addDays(7)->endOfDay();

    $openTasks = Task::query()
        ->with([
            'project',
            'status',
            'labels',
            'parentTask',
        ])
        ->where(function (Builder $query) {
            $query->whereHas('status', function (Builder $statusQuery) {
                $statusQuery->where('iscompletedstatus', false);
            })
            ->orWhereNull('statusid');
        });

    $overdueTasks = (clone $openTasks)
        ->whereNotNull('duedate')
        ->whereDate('duedate', '<', $today->toDateString())
        ->orderBy('duedate')
        ->orderBy('priority')
        ->orderBy('tasktitle')
        ->get();

    $dueTodayTasks = (clone $openTasks)
        ->whereDate('duedate', $today->toDateString())
        ->orderBy('priority')
        ->orderBy('tasktitle')
        ->get();

    $inProgressTasks = (clone $openTasks)
        ->whereNotNull('startdate')
        ->whereNotNull('duedate')
        ->whereDate('startdate', '<=', $today->toDateString())
        ->whereDate('duedate', '>', $today->toDateString())
        ->orderBy('duedate')
        ->orderBy('priority')
        ->orderBy('tasktitle')
        ->get();

    $upcomingTasks = (clone $openTasks)
        ->where(function (Builder $query) use ($today, $weekEnd) {
            /*
            * Tasks with a future start date:
            * show them under that future start date.
            */
            $query->where(function (Builder $startDateQuery) use ($today, $weekEnd) {
                $startDateQuery
                    ->whereNotNull('startdate')
                    ->whereDate('startdate', '>', $today->toDateString())
                    ->whereDate('startdate', '<=', $weekEnd->toDateString());
            })

            /*
            * Tasks without a start date:
            * show them under their future due date.
            *
            * Do NOT include startdate <= today here. Those tasks have already
            * started and belong only in Work in Progress, Due Today, or Overdue.
            */
            ->orWhere(function (Builder $dueDateQuery) use ($today, $weekEnd) {
                $dueDateQuery
                    ->whereNull('startdate')
                    ->whereNotNull('duedate')
                    ->whereDate('duedate', '>', $today->toDateString())
                    ->whereDate('duedate', '<=', $weekEnd->toDateString());
            });
        })
        ->orderByRaw("
            CASE
                WHEN startdate IS NOT NULL
                    AND startdate > ?
                THEN startdate
                ELSE duedate
            END ASC
        ", [$today->toDateString()])
        ->orderBy('priority')
        ->orderBy('tasktitle')
        ->get()
        ->groupBy(function (Task $task) use ($today) {
            if (
                $task->startdate
                && $task->startdate->greaterThan($today)
            ) {
                return $task->startdate->toDateString();
            }

            return $task->duedate?->toDateString() ?? 'undated';
        });

    /*
     * Today's activity deliberately includes completed tasks as well as
     * currently open work. It is a historical activity view, not a to-do list.
     */
    $tasksUpdatedToday = Task::query()
        ->with(['project', 'parentTask'])
        ->where('isrecurringtemplate', 0)
        ->whereBetween('updatedat', [
            $today,
            $tomorrow,
        ])
        ->get();

    $tasksCompletedToday = Task::query()
        ->with(['project', 'parentTask'])
        ->whereNotNull('completedat')
        ->whereBetween('completedat', [
            $today,
            $tomorrow,
        ])
        ->get();

    $commentsToday = TaskComment::query()
        ->with([
            'task.project',
            'task.parentTask',
            'user',
        ])
        ->whereBetween('createdat', [
            $today,
            $tomorrow,
        ])
        ->orderBy('createdat')
        ->get();

    /*
     * One activity record per task. A task can carry multiple reasons:
     * Updated, Completed, and Commented.
     */
    $todaysActivity = collect();

    foreach ($tasksUpdatedToday as $task) {
        $todaysActivity->put($task->id, [
            'task' => $task,
            'updated' => true,
            'completed' => false,
            'comments' => collect(),
            'latestActivityAt' => $task->updatedat,
        ]);
    }

    foreach ($tasksCompletedToday as $task) {
        if (! $todaysActivity->has($task->id)) {
            $todaysActivity->put($task->id, [
                'task' => $task,
                'updated' => false,
                'completed' => true,
                'comments' => collect(),
                'latestActivityAt' => $task->completedat,
            ]);

            continue;
        }

        $activity = $todaysActivity->get($task->id);

        $activity['completed'] = true;

        if (
            ! $activity['latestActivityAt']
            || $task->completedat->greaterThan(
                $activity['latestActivityAt']
            )
        ) {
            $activity['latestActivityAt'] = $task->completedat;
        }

        $todaysActivity->put($task->id, $activity);
    }

    foreach ($commentsToday as $comment) {
        if (! $comment->task) {
            continue;
        }

        $task = $comment->task;

        if (! $todaysActivity->has($task->id)) {
            $todaysActivity->put($task->id, [
                'task' => $task,
                'updated' => false,
                'completed' => false,
                'comments' => collect(),
                'latestActivityAt' => $comment->createdat,
            ]);
        }

        $activity = $todaysActivity->get($task->id);

        $activity['comments']->push($comment);

        if (
            ! $activity['latestActivityAt']
            || $comment->createdat->greaterThan(
                $activity['latestActivityAt']
            )
        ) {
            $activity['latestActivityAt'] = $comment->createdat;
        }

        $todaysActivity->put($task->id, $activity);
    }

    $todaysActivity = $todaysActivity
        ->sortByDesc('latestActivityAt')
        ->values();

    $statuses = TaskStatus::query()
        ->orderBy('projectid')
        ->orderBy('sortorder')
        ->get()
        ->groupBy('projectid');

    return view('tasks.outlook', compact(
        'today',
        'weekEnd',
        'overdueTasks',
        'dueTodayTasks',
        'inProgressTasks',
        'upcomingTasks',
        'todaysActivity',
        'statuses'
    ));
}

    public function updateFromOutlook(Request $request, Task $task)
    {
        $data = $request->validate([
            'duedate' => ['nullable', 'date'],
            'statusid' => ['required', 'exists:task_statuses,id'],
            'statuscomment' => ['nullable', 'string'],
            'actualefforthours' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
        ]);

        $status = TaskStatus::findOrFail($data['statusid']);

        $task->update([
            'duedate' => $data['duedate'] ?: null,
            'statusid' => $status->id,
            'statuscomment' => $data['statuscomment'] ?? null,
            'actualefforthours' => $data['actualefforthours'] ?? null,
            'completedat' => $status->iscompletedstatus
                ? ($task->completedat ?? now())
                : null,
        ]);

        return back()->with('success', 'Task updated.');
    }

    public function makeSubtask(Request $request, Task $task)
    {
        $data = $request->validate([
            'parenttaskid' => ['required', 'integer', 'exists:tasks,id'],
        ]);

        $parentTask = Task::findOrFail($data['parenttaskid']);

        if ($task->id === $parentTask->id) {
            return back()->withErrors([
                'parenttaskid' => 'A task cannot be its own parent.',
            ]);
        }

        if ((int) $task->projectid !== (int) $parentTask->projectid) {
            return back()->withErrors([
                'parenttaskid' => 'Both tasks must belong to the same project.',
            ]);
        }

        if ($task->parenttaskid !== null) {
            return back()->withErrors([
                'parenttaskid' => 'This task is already a subtask.',
            ]);
        }

        if ($parentTask->parenttaskid !== null) {
            return back()->withErrors([
                'parenttaskid' => 'A task can only be added to a top-level task.',
            ]);
        }

        if ($task->subtasks()->exists()) {
            return back()->withErrors([
                'parenttaskid' => 'A task with existing subtasks cannot be converted into a subtask.',
            ]);
        }

        $task->update([
            'parenttaskid' => $parentTask->id,
        ]);
        
        $returnUrl = $this->safeReturnUrl($request->input('return_url'));

        if ($returnUrl) {
            return redirect($returnUrl)
                ->with('success', 'Task moved under “'.$parentTask->tasktitle.'” as a subtask.');
        }

        return redirect()
            ->route('tasks.show', $parentTask)
            ->with('success', 'Task moved under “'.$parentTask->tasktitle.'” as a subtask.');
    }

    public function duplicate(Request $request, Task $task)
    {
        $data = $request->validate([
            'tasktitle' => ['nullable', 'string', 'max:200'],
            'copy_subtasks' => ['nullable', 'boolean'],
        ]);

        if ($task->parenttaskid !== null) {
            return back()->withErrors([
                'tasktitle' => 'Duplicate the parent task rather than an individual subtask.',
            ]);
        }

        $defaultOpenStatusId = $task->project
            ->taskStatuses()
            ->where('iscompletedstatus', false)
            ->orderBy('sortorder')
            ->value('id');

        if (! $defaultOpenStatusId) {
            return back()->withErrors([
                'tasktitle' => 'This project does not have an open task status.',
            ]);
        }

        DB::transaction(function () use (
            $task,
            $data,
            $defaultOpenStatusId,
            &$newTask
        ) {
            $newTask = Task::create([
                'projectid' => $task->projectid,
                'parenttaskid' => null,
                'statusid' => $defaultOpenStatusId,
                'tasktitle' => $data['tasktitle']
                    ?: 'Copy of '.$task->tasktitle,
                'description' => $task->description,
                'taskexpectation' => $task->taskexpectation,
                'statuscomment' => null,
                'priority' => $task->priority,
                'assignedto' => $task->assignedto,
                'estimatedefforthours' => $task->estimatedefforthours,
                'actualefforthours' => null,
                'startdate' => null,
                'duedate' => null,
                'completedat' => null,
                'sortorder' => 0,
                'isrecurringtemplate' => 0,
                'generatedfromtemplateid' => null,
            ]);

            $newTask->labels()->sync($task->labels()->pluck('labels.id'));

            if ($data['copy_subtasks'] ?? false) {
                foreach ($task->subtasks as $subtask) {
                    $newSubtask = Task::create([
                        'projectid' => $newTask->projectid,
                        'parenttaskid' => $newTask->id,
                        'statusid' => $defaultOpenStatusId,
                        'tasktitle' => $subtask->tasktitle,
                        'description' => $subtask->description,
                        'taskexpectation' => $subtask->taskexpectation,
                        'statuscomment' => null,
                        'priority' => $subtask->priority,
                        'assignedto' => $subtask->assignedto,
                        'estimatedefforthours' => $subtask->estimatedefforthours,
                        'actualefforthours' => null,
                        'startdate' => null,
                        'duedate' => null,
                        'completedat' => null,
                        'sortorder' => $subtask->sortorder,
                        'isrecurringtemplate' => 0,
                        'generatedfromtemplateid' => null,
                    ]);

                    $newSubtask->labels()->sync(
                        $subtask->labels()->pluck('labels.id')
                    );
                }
            }
        });

        return redirect()
            ->route('tasks.show', $newTask)
            ->with('success', 'Task duplicated.');
    }

    private function safeReturnUrl(?string $returnUrl): ?string
{
    if (blank($returnUrl)) {
        return null;
    }

    $appUrl = rtrim(config('app.url'), '/');

    return Str::startsWith($returnUrl, $appUrl.'/')
        || $returnUrl === $appUrl
        ? $returnUrl
        : null;
}

private function redirectAfterTaskAction(
    Request $request,
    Task $task,
    string $message,
    ?string $fallbackRoute = null
) {
    $returnUrl = $this->safeReturnUrl($request->input('return_url'));

    if ($returnUrl) {
        return redirect($returnUrl)->with('success', $message);
    }

    if ($fallbackRoute === 'alltasks') {
        return redirect()
            ->route('tasksall.all')
            ->with('success', $message);
    }

    if ($fallbackRoute === 'outlook') {
        return redirect()
            ->route('tasks.outlook')
            ->with('success', $message);
    }

    return redirect()
        ->route('tasks.show', $task)
        ->with('success', $message);
}
}