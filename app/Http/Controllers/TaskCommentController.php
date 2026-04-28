<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function store(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('view', $task);
        $this->authorize('create', TaskComment::class);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $comment = $task->comments()->create([
            'user_id' => auth()->id(),
            'body' => $validated['body'],
            'is_pinned' => false,
        ]);

        ActivityLogger::created(
            'task',
            $task->id,
            $task->title,
            'Menambahkan komentar pada task',
            ['comment_id' => $comment->id]
        );

        return back()->with('success', 'Komentar berhasil ditambahkan.');
    }

    public function destroy(Task $task, TaskComment $comment): RedirectResponse
    {
        abort_unless($comment->task_id === $task->id, 404);
        $this->authorize('delete', $comment);

        ActivityLogger::deleted(
            'task',
            $task->id,
            $task->title,
            'Menghapus komentar task'
        );

        $comment->delete();

        return back()->with('success', 'Komentar berhasil dihapus.');
    }

    public function togglePin(Task $task, TaskComment $comment): RedirectResponse
    {
        abort_unless($comment->task_id === $task->id, 404);
        $this->authorize('pin', $comment);

        $previousPinnedState = $comment->is_pinned;

        $comment->update([
            'is_pinned' => !$previousPinnedState,
        ]);

        ActivityLogger::updated(
            'task',
            $task->id,
            $task->title,
            ['is_pinned' => $previousPinnedState],
            ['is_pinned' => $comment->is_pinned],
            $comment->is_pinned ? 'Menyematkan komentar task' : 'Melepas sematan komentar task'
        );

        return back()->with('success', $comment->is_pinned ? 'Komentar disematkan.' : 'Sematan komentar dilepas.');
    }
}
