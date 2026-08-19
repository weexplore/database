<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskDependency;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskDependencyController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $data = $request->validate([
            'dependsontaskid' => [
                'required',
                'integer',
                'exists:tasks,id',
                Rule::notIn([$task->id]),
            ],
            'dependencytype' => ['required', Rule::in(['FS', 'SS', 'FF', 'SF'])],
            'lagdays' => ['nullable', 'integer', 'min:-3650', 'max:3650'],
        ]);

        // Retrieve the predecessor selected on the form.
        $predecessor = Task::findOrFail($data['dependsontaskid']);

        // A dependency may only link tasks in the same project.
        if ((int) $predecessor->projectid !== (int) $task->projectid) {
            return back()
                ->withInput()
                ->withErrors([
                    'dependsontaskid' => 'Dependencies must link tasks in the same project.',
                ]);
        }

        // Update if it already exists; otherwise create it.
        TaskDependency::updateOrCreate(
            [
                'taskid' => $task->id,
                'dependsontaskid' => $predecessor->id,
            ],
            [
                'dependencytype' => $data['dependencytype'],
                'lagdays' => $data['lagdays'] ?? 0,
            ]
        );

        return back()->with('success', 'Dependency added.');
    }

    public function destroy(Task $task, TaskDependency $dependency)
    {
        // Do not allow a dependency belonging to another task to be deleted
        // through this task's URL.
        abort_unless(
            (int) $dependency->taskid === (int) $task->id,
            404
        );

        $dependency->delete();

        return back()->with('success', 'Dependency removed.');
    }
}