<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Client;
use App\Models\TaskComment;
use App\Models\Team;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Task::class);

        $user = $request->user();
        $query = Task::with(['client', 'product', 'engineer', 'assignee'])->withCount('comments');

        if ($user->isMember()) {
            $query->where('assigned_to', $user->id);
        }

        // Menerapkan Filter Berjenjang
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('engineer_id')) {
            $query->where('engineer_id', $request->engineer_id);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('has_link')) {
            if ($request->has_link === 'yes') {
                $query->whereNotNull('task_url')
                      ->where('task_url', '!=', '')
                      ->where('task_url', '!=', '-');
            } else {
                $query->where(function($q) {
                    $q->whereNull('task_url')
                      ->orWhere('task_url', '')
                      ->orWhere('task_url', '-');
                });
            }
        }
        if ($request->filled('date_from')) {
            $query->whereDate('release_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('release_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('modul', 'like', "%{$search}%");
            });
        }
        
        $tasks = $query->latest()->paginate(10)->withQueryString();
        $tasks->through(function (Task $task) use ($user) {
            return [
                ...$task->toArray(),
                'comments_count' => $task->comments_count,
                'can_edit' => $user->can('update', $task),
                'can_delete' => $user->can('delete', $task),
                'can_update_status' => $user->can('updateStatus', $task),
            ];
        });

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'filters' => $request->all(['search', 'product_id', 'client_id', 'engineer_id', 'category', 'status', 'has_link', 'date_from', 'date_to']),
            'permissions' => [
                'can_create' => $user->can('create', Task::class),
            ],
            
            // Kirim data master ke Vue untuk dropdown filter
            'clients' => Client::where('is_active', true)->get(['id', 'name']),
            'product_teams' => Team::where('type', 'PRODUCT')->where('is_active', true)->get(['id', 'name']),
            'engineer_teams' => Team::where('type', 'ENGINEER')->where('is_active', true)->get(['id', 'name']),
            'users' => User::where('is_active', true)->get(['id', 'name']),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Task::class);

        return Inertia::render('Tasks/Create', [
            // Kirim data master ke Vue untuk form pilihan Dropdown
            'clients' => Client::where('is_active', true)->get(['id', 'name']),
            'product_teams' => Team::where('type', 'PRODUCT')->where('is_active', true)->get(['id', 'name']),
            'engineer_teams' => Team::where('type', 'ENGINEER')->where('is_active', true)->get(['id', 'name']),
            'users' => User::where('is_active', true)->get(['id', 'name']),
            'existing_modules' => Task::select('modul')->whereNotNull('modul')->where('modul', '!=', '')->distinct()->pluck('modul'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Task::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'product_id' => 'required|exists:teams,id',
            'engineer_id' => 'nullable|exists:teams,id',
            'assigned_to' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'modul' => 'nullable|string|max:100',
            'task_url' => 'nullable|string|max:255',
            'category' => ['required', Rule::in(['Fitur Berbayar', 'Regulasi', 'Saran Fitur', 'Prioritas'])],
            'priority' => ['required', Rule::in(['urgent', 'high', 'medium', 'low'])],
            'status' => ['required', Rule::in(['open', 'in_progress', 'revision', 'completed'])],
            'release_date' => 'nullable|date',
        ]);

        $validated['created_by'] = $request->user()->id;

        // Cegah error SQL "Column task_url cannot be null" karena database lama mewajibkan isi
        if (empty($validated['task_url'])) {
            $validated['task_url'] = '-';
        }

        // Auto catat waktu selesai jika statusnya completed
        if ($validated['status'] === 'completed') {
            $validated['completed_at'] = now();
        }

        $task = Task::create($validated);
        $task->loadMissing('client');

        $this->notificationService->notifyTaskAssignment($task);

        ActivityLogger::created('task', $task->id, $task->title, 'Membuat task baru', $validated);

        return redirect()->route('tasks.index')->with('success', 'Task berhasil dibuat.');
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);

        return Inertia::render('Tasks/Edit', [
            'task' => $task,
            'clients' => Client::where('is_active', true)->get(['id', 'name']),
            'product_teams' => Team::where('type', 'PRODUCT')->where('is_active', true)->get(['id', 'name']),
            'engineer_teams' => Team::where('type', 'ENGINEER')->where('is_active', true)->get(['id', 'name']),
            'users' => User::where('is_active', true)->get(['id', 'name']),
            'existing_modules' => Task::select('modul')->whereNotNull('modul')->where('modul', '!=', '')->distinct()->pluck('modul'),
        ]);
    }

    public function show(Task $task, Request $request)
    {
        $this->authorize('view', $task);

        $task->load([
            'client:id,name',
            'product:id,name',
            'engineer:id,name',
            'assignee:id,name',
            'creator:id,name',
            'comments' => fn ($query) => $query
                ->with('user:id,name')
                ->latest(),
        ]);

        $user = $request->user();

        return Inertia::render('Tasks/Show', [
            'task' => [
                ...$task->toArray(),
                'comments' => $task->comments
                    ->map(fn (TaskComment $comment) => [
                        'id' => $comment->id,
                        'body' => $comment->body,
                        'is_pinned' => $comment->is_pinned,
                        'created_at' => $comment->created_at?->toIso8601String(),
                        'user' => $comment->user ? [
                            'id' => $comment->user->id,
                            'name' => $comment->user->name,
                        ] : null,
                        'can_delete' => $user->can('delete', $comment),
                        'can_pin' => $user->can('pin', $comment),
                    ])
                    ->sortByDesc(fn (array $comment) => $comment['is_pinned'])
                    ->values(),
            ],
            'permissions' => [
                'can_edit' => $user->can('update', $task),
                'can_comment' => $user->can('create', TaskComment::class),
            ],
        ]);
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'product_id' => 'required|exists:teams,id',
            'engineer_id' => 'nullable|exists:teams,id',
            'assigned_to' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'modul' => 'nullable|string|max:100',
            'task_url' => 'nullable|string|max:255',
            'category' => ['required', Rule::in(['Fitur Berbayar', 'Regulasi', 'Saran Fitur', 'Prioritas'])],
            'priority' => ['required', Rule::in(['urgent', 'high', 'medium', 'low'])],
            'status' => ['required', Rule::in(['open', 'in_progress', 'revision', 'completed'])],
            'release_date' => 'nullable|date',
        ]);

        // Cegah error SQL "Column task_url cannot be null"
        if (empty($validated['task_url'])) {
            $validated['task_url'] = '-';
        }

        // Logic manajemen waktu penyelesaian
        if ($validated['status'] === 'completed' && $task->status !== 'completed') {
            $validated['completed_at'] = now();
        } elseif ($validated['status'] !== 'completed') {
            $validated['completed_at'] = null; // Reset jika status dikembalikan ke open/in_progress
        }

        $oldValues = $task->getOriginal();
        $previousAssigneeId = $task->assigned_to;
        $task->update($validated);
        $task->loadMissing('client');

        $this->notificationService->notifyTaskAssignment($task, $previousAssigneeId);

        if ($validated['status'] !== ($oldValues['status'] ?? null)) {
            ActivityLogger::statusChanged('task', $task->id, $task->title, $oldValues['status'] ?? 'open', $validated['status']);
        }

        ActivityLogger::updated('task', $task->id, $task->title, $oldValues, $task->fresh()->toArray(), 'Mengupdate task');

        return redirect()->route('tasks.index')->with('success', 'Task berhasil diperbarui.');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        ActivityLogger::deleted('task', $task->id, $task->title, "Menghapus task '{$task->title}'");

        $task->delete();

        return back()->with('success', 'Task berhasil dihapus.');
    }

    public function kanban()
    {
        $this->authorize('viewAny', Task::class);

        $user = request()->user();
        $completedWindowDays = 7;

        // Ambil semua task yang belum selesai
        $activeTasksQuery = Task::with(['client', 'assignee', 'product'])->withCount('comments')
            ->where('status', '!=', 'completed');

        // Ambil task yang sudah selesai dalam 7 hari terakhir
        $completedTasksQuery = Task::with(['client', 'assignee', 'product'])->withCount('comments')
            ->where('status', 'completed')
            ->where('completed_at', '>=', now()->subDays($completedWindowDays));

        if ($user->isMember()) {
            $activeTasksQuery->where('assigned_to', $user->id);
            $completedTasksQuery->where('assigned_to', $user->id);
        }

        $activeTasks = $activeTasksQuery->get();
            
        $completedTasks = $completedTasksQuery->get();

        $tasks = $activeTasks->merge($completedTasks)->values()->map(function (Task $task) use ($user) {
            return [
                ...$task->toArray(),
                'comments_count' => $task->comments_count,
                'can_edit' => $user->can('update', $task),
                'can_update_status' => $user->can('updateStatus', $task),
            ];
        });

        return Inertia::render('Tasks/Kanban', [
            'tasks' => $tasks,
            'meta' => [
                'completed_window_days' => $completedWindowDays,
                'active_count' => $activeTasks->count(),
                'recent_completed_count' => $completedTasks->count(),
                'total_count' => $tasks->count(),
            ],
            'permissions' => [
                'can_create' => $user->can('create', Task::class),
            ],
        ]);
    }

    public function updateStatus(Request $request, Task $task)
    {
        $this->authorize('updateStatus', $task);

        $validated = $request->validate([
            'status' => ['required', \Illuminate\Validation\Rule::in(['open', 'in_progress', 'revision', 'completed'])]
        ]);

        if ($validated['status'] === 'completed' && $task->status !== 'completed') {
            $validated['completed_at'] = now();
        } elseif ($validated['status'] !== 'completed') {
            $validated['completed_at'] = null;
        }

        $oldStatus = $task->status;
        $task->update($validated);

        ActivityLogger::statusChanged('task', $task->id, $task->title, $oldStatus, $validated['status']);

        return back();
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:tasks,id',
        ]);

        $tasks = Task::whereIn('id', $request->ids)->get();

        foreach ($tasks as $task) {
            if ($request->user()->can('delete', $task)) {
                ActivityLogger::deleted('task', $task->id, $task->title, "Menghapus task '{$task->title}' secara massal");
                $task->delete();
            }
        }

        return back()->with('success', 'Task yang dipilih berhasil dihapus.');
    }

    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:tasks,id',
            'status' => ['required', \Illuminate\Validation\Rule::in(['open', 'in_progress', 'revision', 'completed'])]
        ]);

        $tasks = Task::whereIn('id', $request->ids)->get();

        foreach ($tasks as $task) {
            if ($request->user()->can('updateStatus', $task) && $task->status !== $request->status) {
                $oldStatus = $task->status;
                $updateData = ['status' => $request->status];

                if ($request->status === 'completed') {
                    $updateData['completed_at'] = now();
                } else {
                    $updateData['completed_at'] = null;
                }

                $task->update($updateData);
                ActivityLogger::statusChanged('task', $task->id, $task->title, $oldStatus, $request->status);
            }
        }

        return back()->with('success', 'Status task yang dipilih berhasil diperbarui.');
    }

    public function bulkAssign(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:tasks,id',
            'assigned_to' => 'required|exists:users,id',
        ]);

        $tasks = Task::whereIn('id', $request->ids)->get();

        foreach ($tasks as $task) {
            if ($request->user()->can('update', $task)) {
                $previousAssigneeId = $task->assigned_to;
                $oldValues = $task->toArray();
                
                $task->update(['assigned_to' => $request->assigned_to]);
                
                $this->notificationService->notifyTaskAssignment($task, $previousAssigneeId);
                ActivityLogger::updated('task', $task->id, $task->title, $oldValues, $task->fresh()->toArray(), 'Mengassign task secara massal');
            }
        }

        return back()->with('success', 'Task yang dipilih berhasil di-assign.');
    }
}
