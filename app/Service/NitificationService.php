<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Task;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class NotificationService
{
    public const DUE_SOON_DAYS = 3;

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

    public function sendDueSoonNotifications(int $daysAhead = self::DUE_SOON_DAYS): int
    {
        $today = CarbonImmutable::today();
        $endDate = $today->addDays($daysAhead);

        $tasks = Task::query()
            ->with(['client:id,name'])
            ->whereNotNull('assigned_to')
            ->whereNotNull('release_date')
            ->where('status', '!=', 'completed')
            ->whereDate('release_date', '>=', $today)
            ->whereDate('release_date', '<=', $endDate)
            ->get();

        return $tasks->reduce(function (int $count, Task $task) use ($today) {
            if ($this->deadlineNotificationExistsForToday($task)) {
                return $count;
            }

            $releaseDate = CarbonImmutable::parse($task->release_date);
            $daysLeft = $today->diffInDays($releaseDate, false);

            Notification::create([
                'user_id' => $task->assigned_to,
                'type' => 'deadline_soon',
                'title' => 'Deadline task mendekat',
                'body' => sprintf(
                    'Task "%s" untuk %s jatuh tempo pada %s (%d hari lagi).',
                    $task->title,
                    $task->client?->name ?? 'client terkait',
                    $releaseDate->translatedFormat('d F Y'),
                    $daysLeft
                ),
                'link' => route('tasks.show', $task),
                'is_read' => false,
            ]);

            return $count + 1;
        }, 0);
    }

    protected function deadlineNotificationExistsForToday(Task $task): bool
    {
        return Notification::query()
            ->where('user_id', $task->assigned_to)
            ->where('type', 'deadline_soon')
            ->where('link', route('tasks.show', $task))
            ->whereDate('created_at', CarbonImmutable::today())
            ->exists();
    }
}
