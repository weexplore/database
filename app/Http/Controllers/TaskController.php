<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\TaskComment;
use App\Models\TaskRecurrence;
use App\Models\Label;
use App\Models\KnowledgeItem;
use App\Models\KnowledgeNote;
use App\Models\KnowledgeReviewLog;
use App\Models\KnowledgeSource;
use App\Models\Trip;
use App\Models\TripItem;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;

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
            'knowledgeItems.primaryCategory',
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

        $knowledgeItems = KnowledgeItem::query()
            ->with('primaryCategory')
            ->where('isactive', 1)
            ->orderBy('itemname')
            ->get();

        return view('tasks.show', compact(
            'task',
            'projects',
            'statuses',
            'labels',
            'knowledgeItems',
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
            'startdate' => ['nullable', 'date'],
            'duedate' => [
                'nullable',
                'date',
                'after_or_equal:startdate',
            ],
            'labelids'     => ['nullable', 'array'],
            'labelids.*'   => ['integer', 'exists:labels,id'],
            'estimatedefforthours' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'actualefforthours' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'taskexpectation' => ['nullable', 'string'],
            'statuscomment' => ['nullable', 'string'],
            'knowledgeitemids' => ['nullable', 'array'],
            'knowledgeitemids.*' => ['integer', 'exists:knowledgeitems,id'],
            'duedate.after_or_equal' =>
                'The due date must be on or after the start date.',
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

        $data['tasktitle'] = trim((string) $data['title']);
        unset($data['title']);

        $data['assignedto'] = $data['assigneeid'] ?? null;
        unset($data['assigneeid']);

        $labelIds = $data['labelids'] ?? [];
        unset($data['labelids']);

        $knowledgeItemIds = $data['knowledgeitemids'] ?? [];
        unset($data['knowledgeitemids']);

        $task = Task::create($data);
        $task->labels()->sync($labelIds);

        $task->knowledgeItems()->sync($knowledgeItemIds);

       if (!empty($task->parenttaskid)) {
            return redirect()
                ->route('tasks.show', [
                    'task' => $task->parenttaskid,
                    'from' => $request->input('from'),
                    'return_url' => $request->input('return_url'),
                ])
                ->with('success', 'Sub-task created.');
        }

        $returnUrl = $this->safeReturnUrl(
            $request,
            $request->input('return_url')
        );

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
            'startdate' => ['nullable', 'date'],
            'duedate' => [
                'nullable',
                'date',
                'after_or_equal:startdate',
            ],
            'labelids'     => ['nullable', 'array'],
            'labelids.*'   => ['integer', 'exists:labels,id'],
            'estimatedefforthours' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'actualefforthours' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'taskexpectation' => ['nullable', 'string'],
            'statuscomment' => ['nullable', 'string'],
            'knowledgeitemids' => ['nullable', 'array'],
            'knowledgeitemids.*' => ['integer', 'exists:knowledgeitems,id'],
            'duedate.after_or_equal' =>
                'The due date must be on or after the start date.',
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

       foreach ([
            'estimatedefforthours',
            'actualefforthours',
        ] as $field) {
            if (($data[$field] ?? null) === '') {
                $data[$field] = null;
            }
        }

        $data['tasktitle'] = trim((string) $data['title']);
        unset($data['title']);

        $data['assignedto'] = $data['assigneeid'] ?? null;
        unset($data['assigneeid']);

        $labelIds = $data['labelids'] ?? [];
        unset($data['labelids']);

        $knowledgeItemIds = $data['knowledgeitemids'] ?? [];
        unset($data['knowledgeitemids']);

        $task->update($data);

        $task->labels()->sync($labelIds);

        $task->knowledgeItems()->sync($knowledgeItemIds);

        $returnUrl = $this->safeReturnUrl(
            $request,
            $request->input('return_url')
        );

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

        $returnUrl = $this->safeReturnUrl(
            $request,
            $request->input('return_url')
        );

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

        $returnUrl = $this->safeReturnUrl(
            $request,
            $request->input('return_url')
        );

        if ($returnUrl) {
            return redirect($returnUrl)
                ->with('success', 'Task moved to project: '.$newProject->projectname);
        }

        return redirect()
            ->route('tasks.index', $newProject)
            ->with('success', 'Task moved to project: '.$newProject->projectname);
    }
    public function updateRecurrence(
    Request $request,
    Task $task
): RedirectResponse {
    $data = $request->validate([
        'frequency' => ['nullable', 'in:daily,weekly,monthly,yearly'],
        'intervalcount' => ['nullable', 'integer', 'min:1'],
        'leaddaysbeforedue' => ['nullable', 'integer', 'min:0'],
        'startsonoccurrence' => ['nullable', 'date'],
        'endsonoccurrence' => [
            'nullable',
            'date',
            'after_or_equal:startsonoccurrence',
        ],
        'maxoccurrences' => ['nullable', 'integer', 'min:1'],
        'isactive' => ['nullable', 'boolean'],

        'monthlypattern' => [
            'nullable',
            'in:day_of_month,nth_weekday,last_day',
        ],
        'monthday' => ['nullable', 'integer', 'between:1,31'],
        'monthweeknumber' => ['nullable', 'integer', 'in:1,2,3,4,-1'],
        'monthweekday' => ['nullable', 'integer', 'between:0,6'],
    ]);

    /*
     * A blank frequency is the UI's “None” selection.
     * Remove the recurrence definition entirely rather than retaining an
     * invalid/inactive row with a NULL frequency.
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
     * A start date is required for every active recurrence. It is the anchor
     * used when first creating a recurrence and after a schedule change.
     */
    if (blank($data['startsonoccurrence'] ?? null)) {
        return back()
            ->withInput()
            ->withErrors([
                'startsonoccurrence' =>
                    'A recurrence start date is required when a frequency is selected.',
            ]);
    }

    /*
     * Build the exact recurrence values to save. Use effective defaults so
     * empty interval/lead fields compare consistently with stored values.
     */
    $recurrenceValues = [
        'frequency' => $data['frequency'],
        'intervalcount' => (int) ($data['intervalcount'] ?? 1),
        'leaddaysbeforedue' => (int) (
            $data['leaddaysbeforedue'] ?? 0
        ),
        'startsonoccurrence' => $data['startsonoccurrence'],
        'endsonoccurrence' => $data['endsonoccurrence'] ?? null,
        'maxoccurrences' => $data['maxoccurrences'] ?? null,
        'isactive' => $request->boolean('isactive'),

        'monthlypattern' => $data['monthlypattern'] ?? null,
        'monthday' => $data['monthday'] ?? null,
        'monthweeknumber' => $data['monthweeknumber'] ?? null,
        'monthweekday' => $data['monthweekday'] ?? null,
    ];

    /*
     * Retrieve without saving so we can compare the old persisted values
     * against the submitted schedule before any update occurs.
     */
    $recurrence = TaskRecurrence::firstOrNew([
        'tasktemplateid' => $task->id,
    ]);

    $isNewRecurrence = ! $recurrence->exists;

    /*
     * Only fields which change the actual timing mechanics belong here.
     *
     * Deliberately excluded:
     * - endsonoccurrence: limits recurrence but does not alter its cadence
     * - maxoccurrences: limits recurrence but does not alter its cadence
     * - isactive: pauses/resumes recurrence rather than changing its pattern
     */
    $scheduleFields = [
        'frequency',
        'intervalcount',
        'leaddaysbeforedue',
        'startsonoccurrence',
        'monthlypattern',
        'monthday',
        'monthweeknumber',
        'monthweekday',
    ];

    $recurrence->fill($recurrenceValues);

    /*
     * New recurrence: lastgeneratedat will naturally be NULL.
     * Existing changed recurrence: discard the cursor created under the old
     * schedule, so the scheduler recalculates from startsonoccurrence.
     */
    $scheduleChanged = ! $isNewRecurrence
        && $recurrence->isDirty($scheduleFields);

    DB::transaction(function () use (
        $task,
        $recurrence,
        $scheduleChanged
    ) {
        $task->isrecurringtemplate = 1;
        $task->save();

    if ($scheduleChanged) {
        $recurrence->lastgeneratedon = null;
    }

        $recurrence->save();
    });

    $message = $scheduleChanged
        ? 'Recurring task settings updated. Future generation will be recalculated from the recurrence start date.'
        : 'Recurring task settings updated.';

    return $this->redirectAfterTaskAction(
        $request,
        $task,
        $message,
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
            ->route('tasksall.all', $request->only([
                'projectid',
                'labelid',
                'search',
                'hideclosed',
                'templatesonly',
                'sort',
                'dir',
                'page',
            ]))
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
        ->orderby('startdate')
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
    |--------------------------------------------------------------------------
    | Today's Knowledge Activity
    |--------------------------------------------------------------------------
    |
    | This is an audit-style activity feed. It intentionally includes both
    | new records and edits made today; it is not a review or expiry queue.
    |
    */

    $knowledgeItemsUpdatedToday = KnowledgeItem::query()
        ->with('primaryCategory')
        ->whereBetween('updatedat', [$today, $tomorrow])
        ->get();

    $knowledgeNotesUpdatedToday = KnowledgeNote::query()
        ->with('knowledgeItem.primaryCategory')
        ->whereBetween('updatedat', [$today, $tomorrow])
        ->get();

    $knowledgeSourcesUpdatedToday = KnowledgeSource::query()
        ->with('knowledgeItem.primaryCategory')
        ->whereBetween('updatedat', [$today, $tomorrow])
        ->get();

    $knowledgeReviewLogsUpdatedToday = KnowledgeReviewLog::query()
        ->with('knowledgeItem.primaryCategory')
        ->whereBetween('updatedat', [$today, $tomorrow])
        ->get();

    $todayKnowledgeActivity = collect();

    foreach ($knowledgeItemsUpdatedToday as $knowledgeItem) {
        $wasCreatedToday = $knowledgeItem->createdat !== null
            && $knowledgeItem->createdat->betweenIncluded($today, $tomorrow);

        $todayKnowledgeActivity->push([
            'type' => 'item',
            'label' => 'Knowledge item',
            'knowledgeItem' => $knowledgeItem,
            'record' => $knowledgeItem,
            'title' => $knowledgeItem->itemname,
            'detail' => $knowledgeItem->primaryCategory?->categoryname,
            'tab' => 'details',
            'createdToday' => $wasCreatedToday,
            'updatedAt' => $knowledgeItem->updatedat,
        ]);
    }

    foreach ($knowledgeNotesUpdatedToday as $knowledgeNote) {
        $wasCreatedToday = $knowledgeNote->createdat !== null
            && $knowledgeNote->createdat->betweenIncluded($today, $tomorrow);

        $todayKnowledgeActivity->push([
            'type' => 'note',
            'label' => 'Note',
            'knowledgeItem' => $knowledgeNote->knowledgeItem,
            'record' => $knowledgeNote,
            'title' => $knowledgeNote->knowledgeItem?->itemname ?? 'Knowledge Item',
            'detail' => $knowledgeNote->title
                ?: ucfirst((string) $knowledgeNote->notetype),
            'tab' => 'notes',
            'editingNoteId' => $knowledgeNote->id,
            'createdToday' => $wasCreatedToday,
            'updatedAt' => $knowledgeNote->updatedat,
        ]);
    }

    foreach ($knowledgeSourcesUpdatedToday as $knowledgeSource) {
        $todayKnowledgeActivity->push([
            'type' => 'source',
            'label' => 'Source',
            'knowledgeItem' => $knowledgeSource->knowledgeItem,
            'record' => $knowledgeSource,
            'title' => $knowledgeSource->knowledgeItem?->itemname ?? 'Knowledge Item',
            'detail' => $knowledgeSource->sourcetitle
                ?: $knowledgeSource->sourceurl
                ?: ucfirst((string) $knowledgeSource->sourcetype),
            'tab' => 'sources',
            'editingSourceId' => $knowledgeSource->id,
            'updatedAt' => $knowledgeSource->updatedat,
        ]);
    }

    foreach ($knowledgeReviewLogsUpdatedToday as $knowledgeReviewLog) {
        $wasCreatedToday = $knowledgeReviewLog->createdat !== null
            && $knowledgeReviewLog->createdat->betweenIncluded($today, $tomorrow);

        $todayKnowledgeActivity->push([
            'type' => 'review',
            'label' => 'Review log',
            'knowledgeItem' => $knowledgeReviewLog->knowledgeItem,
            'record' => $knowledgeReviewLog,
            'title' => $knowledgeReviewLog->knowledgeItem?->itemname ?? 'Knowledge Item',
            'detail' => $knowledgeReviewLog->summary
                ?: ucfirst((string) $knowledgeReviewLog->reviewtype),
            'tab' => 'review-logs',
            'editingReviewLogId' => $knowledgeReviewLog->id,
            'createdToday' => $wasCreatedToday,
            'updatedAt' => $knowledgeReviewLog->updatedat,
        ]);
    }

    $todayKnowledgeActivity = $todayKnowledgeActivity
        ->filter(fn (array $activity) => $activity['knowledgeItem'] !== null)
        ->sortByDesc('updatedAt')
        ->values();

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
    
        $todaysActivityEstimatedHours = $todaysActivity->sum(
            fn (array $activity) => (float) ($activity['task']->estimatedefforthours ?? 0)
        );

        $todaysActivityActualHours = $todaysActivity->sum(
            fn (array $activity) => (float) ($activity['task']->actualefforthours ?? 0)
        );

    $statuses = TaskStatus::query()
        ->orderBy('projectid')
        ->orderBy('sortorder')
        ->get()
        ->groupBy('projectid');

    $knowledgeReviewBaseQuery = KnowledgeItem::query()
        ->with('primaryCategory')
        ->where('isactive', 1)
        ->whereNotNull('nextreviewdate');

    $overdueKnowledgeItemReviews = (clone $knowledgeReviewBaseQuery)
        ->whereDate('nextreviewdate', '<', $today->toDateString())
        ->orderBy('nextreviewdate')
        ->orderBy('itemname')
        ->get();

    $knowledgeItemReviewsDueToday = (clone $knowledgeReviewBaseQuery)
        ->whereDate('nextreviewdate', $today->toDateString())
        ->orderBy('itemname')
        ->get();

    $watchlistItems = KnowledgeItem::query()
        ->with([
            'primaryCategory:id,categoryname',
            'itemType:id,typename',
        ])
        ->where('isactive', 1)
        ->where('iswatchlist', 1)
        ->orderBy('itemname')
        ->get([
            'id',
            'primarycategoryid',
            'itemname',
            'itemtype',
            'itemstatus',
            'summary',
            'nextreviewdate',
            'isfeatured',
            'iswatchlist',
        ]);

    $upcomingKnowledgeItemReviews = (clone $knowledgeReviewBaseQuery)
        ->whereDate('nextreviewdate', '>', $today->toDateString())
        ->whereDate('nextreviewdate', '<=', $weekEnd->toDateString())
        ->orderBy('nextreviewdate')
        ->orderBy('itemname')
        ->get();

    $knowledgeNoteReviewBaseQuery = KnowledgeNote::query()
        ->with([
            'knowledgeItem.primaryCategory',
        ])
        ->whereNotNull('reviewdate')
        ->whereHas('knowledgeItem', function (Builder $query) {
            $query->where('isactive', 1);
        });

    $overdueKnowledgeNoteReviews = (clone $knowledgeNoteReviewBaseQuery)
        ->whereDate('reviewdate', '<', $today->toDateString())
        ->orderBy('reviewdate')
        ->orderBy('id')
        ->get();

    $knowledgeNoteReviewsDueToday = (clone $knowledgeNoteReviewBaseQuery)
        ->whereDate('reviewdate', $today->toDateString())
        ->orderBy('id')
        ->get();

    $upcomingKnowledgeNoteReviews = (clone $knowledgeNoteReviewBaseQuery)
        ->whereDate('reviewdate', '>', $today->toDateString())
        ->whereDate('reviewdate', '<=', $weekEnd->toDateString())
        ->orderBy('reviewdate')
        ->orderBy('id')
        ->get();

    $knowledgeReviewLogBaseQuery = KnowledgeReviewLog::query()
        ->with([
            'knowledgeItem.primaryCategory',
        ])
        ->whereNotNull('nextreviewdate')
        ->whereHas('knowledgeItem', function (Builder $query) {
            $query->where('isactive', 1);
        });

    $overdueKnowledgeReviewFollowUps = (clone $knowledgeReviewLogBaseQuery)
        ->whereDate('nextreviewdate', '<', $today->toDateString())
        ->orderBy('nextreviewdate')
        ->orderBy('id')
        ->get();

    $knowledgeReviewFollowUpsDueToday = (clone $knowledgeReviewLogBaseQuery)
        ->whereDate('nextreviewdate', $today->toDateString())
        ->orderBy('id')
        ->get();

    $upcomingKnowledgeReviewFollowUps = (clone $knowledgeReviewLogBaseQuery)
        ->whereDate('nextreviewdate', '>', $today->toDateString())
        ->whereDate('nextreviewdate', '<=', $weekEnd->toDateString())
        ->orderBy('nextreviewdate')
        ->orderBy('id')
        ->get();

                /*
         * Trips beginning soon, plus currently active trips.
         */
        $upcomingTrips = Trip::query()
            ->whereIn('tripstatus', ['planned', 'active'])
            ->where(function (Builder $query) use ($today, $weekEnd) {
                $query
                    ->where(function (Builder $plannedQuery) use ($today, $weekEnd) {
                        $plannedQuery
                            ->where('tripstatus', 'planned')
                            ->whereNotNull('startdate')
                            ->whereDate('startdate', '>=', $today->toDateString())
                            ->whereDate('startdate', '<=', $weekEnd->toDateString());
                    })
                    ->orWhere(function (Builder $activeQuery) use ($today) {
                        $activeQuery
                            ->where('tripstatus', 'active')
                            ->where(function (Builder $dateQuery) use ($today) {
                                $dateQuery
                                    ->where(function (Builder $boundedTripQuery) use ($today) {
                                        $boundedTripQuery
                                            ->whereNotNull('startdate')
                                            ->whereDate('startdate', '<=', $today->toDateString())
                                            ->whereNotNull('enddate')
                                            ->whereDate('enddate', '>=', $today->toDateString());
                                    })
                                    ->orWhere(function (Builder $openEndedTripQuery) use ($today) {
                                        $openEndedTripQuery
                                            ->whereNotNull('startdate')
                                            ->whereDate('startdate', '<=', $today->toDateString())
                                            ->whereNull('enddate');
                                    });
                            });
                    });
            })
            ->orderByRaw('startdate IS NULL')
            ->orderBy('startdate')
            ->orderBy('tripname')
            ->get();

        /*
         * Planned work linked to an active or planned trip.
         *
         * Exclude records that are already complete or deliberately not being
         * done. A Trip Item with no itemdate is not actionable in Outlook.
         */
        $upcomingTripItems = TripItem::query()
            ->with([
                'trip',
                'place',
                'destination',
                'destinationItem.destination',
            ])
            ->whereNotNull('itemdate')
            ->whereDate('itemdate', '>=', $today->toDateString())
            ->whereDate('itemdate', '<=', $weekEnd->toDateString())
            ->whereNotIn('status', ['completed', 'cancelled', 'skipped'])
            ->whereHas('trip', function (Builder $query) {
                $query->whereIn('tripstatus', ['planned', 'active']);
            })
            ->orderBy('itemdate')
            ->orderBy('sortorder')
            ->orderBy('title')
            ->get();

        /*
         * Recurrence templates expected to be generated in the next seven days.
         *
         * The scheduled command generates at:
         *
         *     next occurrence due date - lead days before due
         *
         * We calculate that same threshold here, but do not create anything.
         */
        $recurringTasksToGenerate = $this->recurringTasksScheduledForWindow(
            $today,
            $weekEnd
        );

        /*
         * These are overdue generation events: the command should already
         * have generated them. They are useful operational diagnostics if the
         * scheduler/container/cron has stopped running.
         */
        $overdueRecurringTaskGenerations = $this->recurringTasksScheduledForWindow(
            null,
            $today->copy()->subDay()
        );

                $attachmentExpiryWindowEnd = $today->copy()
            ->addDays(14)
            ->endOfDay();

        $attachmentExpiryWindowEnd = $today->copy()->addDays(14);

        $expiringKnowledgeAttachments = DB::table('knowledgeitem_attachments as kia')
            ->join(
                'knowledgeitems as ki',
                'ki.id',
                '=',
                'kia.knowledgeitemid'
            )
            ->join(
                'knowledgeattachments as ka',
                'ka.id',
                '=',
                'kia.knowledgeattachmentid'
            )
            ->leftJoin(
                'knowledgecategories as kc',
                'kc.id',
                '=',
                'ki.primarycategoryid'
            )
            ->where('ki.isactive', 1)
            ->whereNotNull('kia.expirydate')
            ->whereBetween('kia.expirydate', [
                $today->toDateString(),
                $attachmentExpiryWindowEnd->toDateString(),
            ])
            ->orderBy('kia.expirydate')
            ->orderBy('ki.itemname')
            ->select([
                'kia.id as attachment_link_id',
                'kia.expirydate',
                'kia.description as link_description',
                'ki.id as knowledge_item_id',
                'ki.itemname as knowledge_item_name',
                'ki.itemtype as knowledge_item_type',
                'kc.categoryname as category_name',
                'ka.id as attachment_id',
                'ka.attachmenttype',
                'ka.originalfilename',
            ])
            ->get();

        $projects = Project::query()
            ->where('status', 'active')
            ->orderBy('projectname')
            ->get();

    return view('tasks.outlook', compact(
        'today',
        'weekEnd',
        'overdueTasks',
        'dueTodayTasks',
        'inProgressTasks',
        'upcomingTasks',

        'overdueKnowledgeItemReviews',
        'knowledgeItemReviewsDueToday',
        'upcomingKnowledgeItemReviews',

        'overdueKnowledgeNoteReviews',
        'knowledgeNoteReviewsDueToday',
        'upcomingKnowledgeNoteReviews',

        'watchlistItems',

        'overdueKnowledgeReviewFollowUps',
        'knowledgeReviewFollowUpsDueToday',
        'upcomingKnowledgeReviewFollowUps',

        'upcomingTrips',
        'upcomingTripItems',
        'recurringTasksToGenerate',
        'overdueRecurringTaskGenerations',
        'expiringKnowledgeAttachments',

        'todaysActivity',
        'todaysActivityEstimatedHours',
        'todaysActivityActualHours',
        'todayKnowledgeActivity',
        'statuses',
        'projects'
    ));
}

public function storeFromOutlook(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'tasktitle' => ['required', 'string', 'max:255'],

        'projectid' => [
            'required',
            'integer',
            Rule::exists(Project::class, 'id'),
        ],

        'statusid' => [
            'nullable',
            'integer',
            Rule::exists(TaskStatus::class, 'id'),
        ],

        

        'priority' => [
            'nullable',
            Rule::in(['low', 'medium', 'high', 'urgent']),
        ],

        'startdate' => ['nullable', 'date'],

        'duedate' => [
            'nullable',
            'date',
            'after_or_equal:startdate',
        ],

        'estimatedefforthours' => [
            'nullable',
            'numeric',
            'min:0',
            'max:9999.99',
        ],
    ], [], [
        'tasktitle' => 'task title',
        'projectid' => 'project',
        'statusid' => 'status',
        'startdate' => 'start date',
        'duedate' => 'due date',
        'estimatedefforthours' => 'estimated effort',
    ]);

    $project = Project::query()
        ->where('status', 'active')
        ->findOrFail($validated['projectid']);

    $statusId = $validated['statusid'] ?? null;

    /*
     * A TaskStatus record is project-specific. Do not allow a status from
     * another project's workflow to be assigned merely because it has a
     * valid ID.
     */
    if ($statusId !== null) {
        $statusBelongsToProject = TaskStatus::query()
            ->whereKey($statusId)
            ->where('projectid', $project->id)
            ->where('isactive', 1)
            ->exists();

        if (! $statusBelongsToProject) {
            throw ValidationException::withMessages([
                'statusid' =>
                    'The selected status is not active for the selected project.',
            ]);
        }
    } else {
        /*
         * Default to the first active open status for this project.
         * If none exists, the task is still valid with statusid NULL, and
         * your existing Outlook query explicitly includes NULL statuses as
         * open tasks.
         */
        $statusId = TaskStatus::query()
            ->where('projectid', $project->id)
            ->where('isactive', 1)
            ->where('iscompletedstatus', 0)
            ->orderBy('sortorder')
            ->value('id');
    }

    $task = Task::create([
        'projectid' => $project->id,
        'tasktitle' => trim((string) $validated['tasktitle']),
        'statusid' => $statusId,
        'priority' => $validated['priority'] ?? 'medium',
        'startdate' => $validated['startdate'] ?? null,
        'duedate' => $validated['duedate'] ?? null,
        'estimatedefforthours' =>
            $validated['estimatedefforthours'] ?? null,
        'isrecurringtemplate' => false,
    ]);

    return redirect()
        ->route('tasks.outlook')
        ->with(
            'success',
            "Task '{$task->tasktitle}' added successfully."
        );
}

    public function updateFromOutlook(
        Request $request,
        Task $task
    ): RedirectResponse {
        $data = $request->validate([
            'startdate' => ['nullable', 'date'],

            'duedate' => [
                'nullable',
                'date',
                'after_or_equal:startdate',
            ],

            'priority' => [
                'required',
                'in:low,medium,high,urgent',
            ],

            'statusid' => [
                'required',
                'exists:task_statuses,id',
            ],

            'statuscomment' => ['nullable', 'string'],

            'estimatedefforthours' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999.99',
            ],

            'actualefforthours' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999.99',
            ],
        ], [
            'duedate.after_or_equal' =>
                'The due date must be on or after the start date.',
        ]);

        $status = TaskStatus::query()
            ->whereKey($data['statusid'])
            ->where('projectid', $task->projectid)
            ->where('isactive', 1)
            ->firstOrFail();

        $task->update([
            'startdate' => $data['startdate'] ?: null,
            'duedate' => $data['duedate'] ?: null,
            'priority' => $data['priority'],
            'statusid' => $status->id,
            'statuscomment' => $data['statuscomment'] ?? null,
            'estimatedefforthours' =>
                $data['estimatedefforthours'] ?? null,
            'actualefforthours' =>
                $data['actualefforthours'] ?? null,
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
        
        $returnUrl = $this->safeReturnUrl(
            $request,
            $request->input('return_url')
        );

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

    private function safeReturnUrl(
        Request $request,
        ?string $returnUrl
    ): ?string {
        if (blank($returnUrl)) {
            return null;
        }

        $parsedUrl = parse_url($returnUrl);

        if ($parsedUrl === false) {
            return null;
        }

        /*
        * A relative path is always safe within this application.
        * Example: /tasksall/all?templatesonly=1
        */
        if (empty($parsedUrl['host'])) {
            return Str::startsWith($returnUrl, '/')
                ? $returnUrl
                : null;
        }

        /*
        * For absolute URLs, compare against the actual current request origin,
        * rather than config('app.url'). This supports your internal server host
        * and port even if APP_URL differs from the browser URL.
        */
        $returnHost = $parsedUrl['host'] ?? null;
        $returnScheme = $parsedUrl['scheme'] ?? null;
        $returnPort = isset($parsedUrl['port'])
            ? (int) $parsedUrl['port']
            : null;

        $requestHost = $request->getHost();
        $requestScheme = $request->getScheme();
        $requestPort = (int) $request->getPort();

        $normalisedReturnPort = $returnPort
            ?? ($returnScheme === 'https' ? 443 : 80);

        $normalisedRequestPort = $requestPort
            ?: ($requestScheme === 'https' ? 443 : 80);

        if (
            !hash_equals((string) $requestHost, (string) $returnHost)
            || !hash_equals((string) $requestScheme, (string) $returnScheme)
            || $normalisedRequestPort !== $normalisedReturnPort
        ) {
            return null;
        }

        return $returnUrl;
    }

private function redirectAfterTaskAction(
    Request $request,
    Task $task,
    string $message,
    ?string $fallbackRoute = null
) {
    $returnUrl = $this->safeReturnUrl(
        $request,
        $request->input('return_url')
    );

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

    private function recurringTasksScheduledForWindow(
        ?\Carbon\Carbon $windowStart,
        \Carbon\Carbon $windowEnd
    ) {
        $today = now()->startOfDay();

        return TaskRecurrence::query()
            ->with([
                'taskTemplate.project',
                'taskTemplate.labels',
            ])
            ->where('isactive', 1)
            ->whereDate('startsonoccurrence', '<=', $windowEnd->toDateString())
            ->where(function (Builder $query) use ($windowEnd) {
                $query->whereNull('endsonoccurrence')
                    ->orWhereDate(
                        'endsonoccurrence',
                        '>=',
                        $windowEnd->toDateString()
                    );
            })
            ->get()
            ->map(function (TaskRecurrence $recurrence) {
                $template = $recurrence->taskTemplate;

                if (! $template) {
                    return null;
                }

                $nextDue = $this->nextRecurringTaskDueDate($recurrence);

                if (! $nextDue) {
                    return null;
                }

                $leadDays = max(
                    0,
                    (int) ($recurrence->leaddaysbeforedue ?? 0)
                );

                $generationDate = $nextDue
                    ->copy()
                    ->subDays($leadDays)
                    ->startOfDay();

                $recurrenceRootId = $template->recurrencerootid
                    ?? $template->id;

                $generatedCount = Task::query()
                    ->where('recurrencerootid', $recurrenceRootId)
                    ->whereNull('parenttaskid')
                    ->count();

                if (
                    $recurrence->maxoccurrences
                    && $generatedCount >= (int) $recurrence->maxoccurrences
                ) {
                    return null;
                }

                return [
                    'recurrence' => $recurrence,
                    'template' => $template,
                    'nextDue' => $nextDue,
                    'generationDate' => $generationDate,
                    'leadDays' => $leadDays,
                    'generatedCount' => $generatedCount,
                ];
            })
            ->filter()
            ->filter(function (array $scheduled) use ($windowStart, $windowEnd) {
                $generationDate = $scheduled['generationDate'];

                if ($windowStart && $generationDate->isBefore($windowStart)) {
                    return false;
                }

                return ! $generationDate->isAfter($windowEnd);
            })
            ->sortBy([
                ['generationDate', 'asc'],
                ['nextDue', 'asc'],
            ])
            ->values();
    }

    private function nextRecurringTaskDueDate(
        TaskRecurrence $recurrence
    ): ?\Carbon\Carbon {
        $interval = max(1, (int) ($recurrence->intervalcount ?? 1));

        if (! $recurrence->lastgeneratedon) {
            return $recurrence->startsonoccurrence
                ? \Carbon\Carbon::parse($recurrence->startsonoccurrence)
                    ->startOfDay()
                : null;
        }

        $baseDate = \Carbon\Carbon::parse(
            $recurrence->lastgeneratedon
        )->startOfDay();

        return match ($recurrence->frequency) {
            'daily' => $baseDate->copy()->addDays($interval),
            'weekly' => $baseDate->copy()->addWeeks($interval),
            'monthly' => $baseDate->copy()->addMonthsNoOverflow($interval),
            'yearly' => $baseDate->copy()->addYearsNoOverflow($interval),
            default => null,
        };
    }
}