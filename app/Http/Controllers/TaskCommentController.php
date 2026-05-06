<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    public function store(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('view', $task);
        $this->authorize('create', TaskComment::class);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        /** @var User $user */
        $user = $request->user();

        /** @var TaskComment $comment */
        $comment = $task->comments()->create([
            'user_id' => $user->id,
            'body' => $validated['body'],
            'is_pinned' => false,
        ]);

        $this->notificationService->notifyNewComment($task, $user);

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

        $comment->is_pinned = ! $previousPinnedState;
        $comment->save();

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
