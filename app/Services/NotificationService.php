<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use Carbon\CarbonImmutable;

class NotificationService
{
    public const DUE_SOON_DAYS = 3;

    /**
     * Notify the assigned user when a task is assigned or reassigned.
     */
    public function notifyTaskAssignment(Task $task, ?int $previousAssigneeId = null): void
    {
        if (! $task->assigned_to || $task->assigned_to === $previousAssigneeId) {
            return;
        }

        Notification::create([
            'user_id' => $task->assigned_to,
            'type' => 'task_assigned',
            'title' => 'Tiket baru ditugaskan kepada Anda',
            'body' => sprintf(
                'Task "%s" untuk %s sekarang menjadi tanggung jawab Anda.',
                $task->title,
                $task->client?->name ?? 'client terkait'
            ),
            'link' => route('tasks.show', $task),
            'is_read' => false,
        ]);
    }

    /**
     * Notify relevant users when a task's status changes.
     * - If admin changes status → notify the assignee
     * - If member changes status → notify all admins
     */
    public function notifyStatusChanged(Task $task, string $oldStatus, string $newStatus, User $changedBy): void
    {
        if ($oldStatus === $newStatus) {
            return;
        }

        $statusLabels = [
            'open' => 'Belum Di Cek',
            'in_progress' => 'Dalam Proses',
            'revision' => 'Revisi',
            'completed' => 'Selesai',
        ];

        $newLabel = $statusLabels[$newStatus] ?? $newStatus;
        $oldLabel = $statusLabels[$oldStatus] ?? $oldStatus;

        // If the changer is admin → notify the assignee
        if ($changedBy->isAdmin() && $task->assigned_to && $task->assigned_to !== $changedBy->id) {
            Notification::create([
                'user_id' => $task->assigned_to,
                'type' => 'status_changed',
                'title' => "Status task diubah ke {$newLabel}",
                'body' => sprintf(
                    'Task "%s" diubah dari %s → %s oleh %s.',
                    $task->title,
                    $oldLabel,
                    $newLabel,
                    $changedBy->name
                ),
                'link' => route('tasks.show', $task),
                'is_read' => false,
            ]);
        }

        // If the changer is member → notify all admins
        if ($changedBy->isMember()) {
            $admins = User::where('role', 'admin')
                ->where('is_active', true)
                ->where('id', '!=', $changedBy->id)
                ->pluck('id');

            foreach ($admins as $adminId) {
                Notification::create([
                    'user_id' => $adminId,
                    'type' => 'status_changed',
                    'title' => "Status task diubah ke {$newLabel}",
                    'body' => sprintf(
                        'Task "%s" diubah dari %s → %s oleh %s.',
                        $task->title,
                        $oldLabel,
                        $newLabel,
                        $changedBy->name
                    ),
                    'link' => route('tasks.show', $task),
                    'is_read' => false,
                ]);
            }
        }

        if ($newStatus === 'completed') {
            $this->dismissTaskNotifications($task);
        }
    }

    /**
     * Notify the task assignee when a new comment is added (unless they are the commenter).
     */
    public function notifyNewComment(Task $task, User $commenter): void
    {
        if (! $task->assigned_to || $task->assigned_to === $commenter->id) {
            return;
        }

        Notification::create([
            'user_id' => $task->assigned_to,
            'type' => 'new_comment',
            'title' => 'Komentar baru pada task Anda',
            'body' => sprintf(
                '%s menambahkan komentar pada task "%s".',
                $commenter->name,
                $task->title
            ),
            'link' => route('tasks.show', $task),
            'is_read' => false,
        ]);
    }

    /**
     * Send notifications for tasks with approaching deadlines.
     */
    public function sendDueSoonNotifications(int $daysAhead = self::DUE_SOON_DAYS): int
    {
        $today = CarbonImmutable::today();

        $tasks = Task::query()
            ->with(['client:id,name'])
            ->whereNotNull('assigned_to')
            ->whereSlaDueSoon($daysAhead)
            ->select('tasks.*')
            ->get();

        return $tasks->reduce(function ($count, Task $task) use ($today) {
            if ($this->deadlineNotificationExistsForToday($task, 'deadline_soon', $task->assigned_to)) {
                return $count;
            }

            $deadline = CarbonImmutable::parse($task->sla_due_date);
            $daysLeft = $today->diffInDays($deadline, false);

            Notification::create([
                'user_id' => $task->assigned_to,
                'type' => 'deadline_soon',
                'title' => 'Deadline task mendekat',
                'body' => sprintf(
                    'Task "%s" untuk %s jatuh tempo pada %s (%d hari lagi).',
                    $task->title,
                    $task->client?->name ?? 'client terkait',
                    $deadline->translatedFormat('d F Y'),
                    $daysLeft
                ),
                'link' => route('tasks.show', $task),
                'is_read' => false,
            ]);

            return $count + 1;
        }, 0);
    }

    /**
     * Send notifications for overdue tasks to the assignee and active admins.
     */
    public function sendOverdueNotifications(): int
    {
        $today = CarbonImmutable::today();

        $tasks = Task::query()
            ->with(['client:id,name'])
            ->whereSlaOverdue()
            ->select('tasks.*')
            ->get();

        $adminIds = User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->pluck('id');

        return $tasks->reduce(function ($count, Task $task) use ($adminIds, $today) {
            $recipientIds = collect([$task->assigned_to])
                ->merge($adminIds)
                ->filter()
                ->unique()
                ->values();

            if ($recipientIds->isEmpty()) {
                return $count;
            }

            $deadline = CarbonImmutable::parse($task->sla_due_date);
            $daysLate = $deadline->diffInDays($today);
            $created = 0;

            foreach ($recipientIds as $userId) {
                if ($this->deadlineNotificationExistsForToday($task, 'deadline_overdue', (int) $userId)) {
                    continue;
                }

                Notification::create([
                    'user_id' => $userId,
                    'type' => 'deadline_overdue',
                    'title' => 'Task melewati deadline',
                    'body' => sprintf(
                        'Task "%s" untuk %s sudah lewat deadline %s (%d hari terlambat).',
                        $task->title,
                        $task->client?->name ?? 'client terkait',
                        $deadline->translatedFormat('d F Y'),
                        $daysLate
                    ),
                    'link' => route('tasks.show', $task),
                    'is_read' => false,
                ]);

                $created++;
            }

            return $count + $created;
        }, 0);
    }

    protected function deadlineNotificationExistsForToday(Task $task, string $type, ?int $userId): bool
    {
        if (! $userId) {
            return false;
        }

        return Notification::query()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->where('link', route('tasks.show', $task))
            ->whereDate('created_at', CarbonImmutable::today())
            ->exists();
    }

    public function dismissTaskNotifications(Task $task): void
    {
        Notification::query()
            ->where('link', route('tasks.show', $task))
            ->whereNull('dismissed_at')
            ->update([
                'is_read' => true,
                'dismissed_at' => now(),
            ]);
    }
}
