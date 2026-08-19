<?php
namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $data = $request->validate([
            'commenttext' => 'required|string|max:2000',
        ]);

        $task->comments()->create([
            'commenttext' => $data['commenttext'],
        ]);

        return redirect()->route('tasks.show', $task)->with('success', 'Comment added.');
    }

    public function destroy(\App\Models\TaskComment $comment)
    {
        if ($comment->userid !== auth()->id()) {
            return redirect()->back()->with('error', 'You can only delete your own comments.');
        }

        $taskId = $comment->taskid;
        $comment->delete();

        return redirect()->route('tasks.show', $taskId)->with('success', 'Comment deleted.');
    }
}
