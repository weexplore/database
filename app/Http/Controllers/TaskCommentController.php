<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TaskCommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $data = $request->validate([
            'commenttext' => ['required', 'string', 'max:2000'],
        ]);

        $task->comments()->create([
            'commenttext' => $data['commenttext'],
        ]);

        return redirect()
            ->route('tasks.show', [
                'task' => $task,
                'from' => $request->input('from'),
                'return_url' => $request->input('return_url'),
            ])
            ->with('success', 'Comment added.');
    }

    public function destroy(Request $request, TaskComment $comment)
    {
        if ($comment->userid !== auth()->id()) {
            return redirect()
                ->back()
                ->with('error', 'You can only delete your own comments.');
        }

        $task = $comment->task;
        $comment->delete();

        return $this->redirectAfterCommentAction(
            $request,
            $task,
            'Comment deleted.'
        );
    }

    private function redirectAfterCommentAction(
        Request $request,
        ?Task $task,
        string $message
    ) {
        $returnUrl = $this->safeReturnUrl($request->input('return_url'));

        if ($returnUrl) {
            return redirect($returnUrl)->with('success', $message);
        }

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', $message);
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
}