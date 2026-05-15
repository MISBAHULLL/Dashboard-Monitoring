<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\Team;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\SimpleExcel\SimpleExcelWriter;

class TaskController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Task::class);

        $user = $request->user();
        $query = Task::with(['client', 'product', 'engineer', 'assignee', 'documents:id,title,type'])->withCount('comments');

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
            if ($request->status === 'overdue') {
                // Overdue = release_date sudah lewat dan status belum completed
                $query->whereNotNull('release_date')
                    ->whereDate('release_date', '<', now()->toDateString())
                    ->where('status', '!=', 'completed');
            } else {
                $query->where('status', $request->status);
            }
        }
        if ($request->filled('has_link')) {
            if ($request->has_link === 'yes') {
                $query->whereNotNull('task_url')
                    ->where('task_url', '!=', '')
                    ->where('task_url', '!=', '-');
            } else {
                $query->where(function ($q) {
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
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('modul', 'like', "%{$search}%");
            });
        }

        // Filter by specific task IDs (used by dashboard "Lihat Semua" link)
        if ($request->filled('ids')) {
            $ids = is_array($request->ids) ? $request->ids : explode(',', $request->ids);
            $query->whereIn('id', array_map('intval', $ids));
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
            'task_templates' => \App\Models\TaskTemplate::where('created_by', request()->user()->id)->get(),
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
            'force_duplicate' => 'nullable|boolean',
        ]);

        $validated['created_by'] = $request->user()->id;

        // Cek duplikat (kecuali force_duplicate = true)
        if (empty($validated['force_duplicate'])) {
            $duplicate = Task::where('title', $validated['title'])
                ->where('client_id', $validated['client_id'])
                ->where('product_id', $validated['product_id'])
                ->where('category', $validated['category'])
                ->when(!empty($validated['modul']), fn ($q) => $q->where('modul', $validated['modul']))
                ->first();

            if ($duplicate) {
                return back()->withErrors([
                    'duplicate' => "Task serupa sudah ada: \"{$duplicate->title}\" (ID: {$duplicate->id}). Centang opsi 'Abaikan pengecekan duplikat' jika memang ingin membuat task ini.",
                ])->withInput();
            }
        }

        unset($validated['force_duplicate']);

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
            'release_reason' => 'nullable|string|max:500'
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
        $oldReleaseDate = $task->getOriginal('release_date');
        $previousAssigneeId = $task->assigned_to;
        $task->update($validated);
        $task->loadMissing('client');

        // Catat perubahan tanggal release jika berubah
        $newReleaseDate = $validated['release_date'] ?? null;
        if ($oldReleaseDate && $newReleaseDate && Carbon::parse($oldReleaseDate)->toDateString() !== Carbon::parse($newReleaseDate)->toDateString()) {
            \App\Models\ReleaseDateLog::create([
                'task_id'    => $task->id,
                'changed_by' => $request->user()->id,
                'old_date'   => $oldReleaseDate,
                'new_date'   => $newReleaseDate,
                'reason'     => $request->input('release_reason') ?? 'Tidak ada alasan',
            ]);
        }

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
        $completedDisplayLimit = 20;

        // Ambil semua task yang belum selesai
        $activeTasksQuery = Task::with(['client', 'assignee', 'product'])->withCount('comments')
            ->where('status', '!=', 'completed')
            ->orderBy('created_at', 'asc');

        // Ambil semua completed tasks, terbaru dulu, limit 20 untuk performa Kanban
        $completedTasksQuery = Task::with(['client', 'assignee', 'product'])->withCount('comments')
            ->where('status', 'completed')
            ->orderByDesc('completed_at')
            ->limit($completedDisplayLimit);

        // Total semua completed tasks (tanpa limit, untuk meta count)
        $totalCompletedQuery = Task::where('status', 'completed');

        if ($user->isMember()) {
            $activeTasksQuery->where('assigned_to', $user->id);
            $completedTasksQuery->where('assigned_to', $user->id);
            $totalCompletedQuery->where('assigned_to', $user->id);
        }

        $activeTasks = $activeTasksQuery->get();
        $completedTasks = $completedTasksQuery->get();
        $totalCompletedCount = $totalCompletedQuery->count();

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
                'completed_display_limit' => $completedDisplayLimit,
                'active_count' => $activeTasks->count(),
                'completed_count' => $totalCompletedCount,
                'completed_shown' => $completedTasks->count(),
                'total_count' => $activeTasks->count() + $totalCompletedCount,
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
            'status' => ['required', Rule::in(['open', 'in_progress', 'revision', 'completed'])],
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
            'status' => ['required', Rule::in(['open', 'in_progress', 'revision', 'completed'])],
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

    public function export(Request $request)
    {
        $this->authorize('viewAny', Task::class);

        $user = $request->user();
        $query = Task::with(['client', 'product', 'engineer', 'assignee']);

        if ($user->isMember()) {
            $query->where('assigned_to', $user->id);
        }

        // Jika ada ids spesifik (dari checkbox), filter hanya task tersebut
        if ($request->filled('ids')) {
            $ids = is_array($request->ids) ? $request->ids : explode(',', $request->ids);
            $query->whereIn('id', array_map('intval', $ids));
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
            if ($request->status === 'overdue') {
                $query->whereNotNull('release_date')
                    ->whereDate('release_date', '<', now()->toDateString())
                    ->where('status', '!=', 'completed');
            } else {
                $query->where('status', $request->status);
            }
        }
        if ($request->filled('has_link')) {
            if ($request->has_link === 'yes') {
                $query->whereNotNull('task_url')
                    ->where('task_url', '!=', '')
                    ->where('task_url', '!=', '-');
            } else {
                $query->where(function ($q) {
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
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('modul', 'like', "%{$search}%");
            });
        }

        $tasks = $query->latest()->get();

        ActivityLogger::log('exported', 'task', null, 'Daftar Task', 'Mengunduh laporan excel daftar task');

        $fileName = 'laporan_task_'.date('Y-m-d_His').'.csv';
        $filePath = storage_path('app/temp/'.$fileName);

        // Pastikan folder temp ada
        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $writer = SimpleExcelWriter::create($filePath);

        foreach ($tasks as $task) {
            $writer->addRow([
                'ID' => $task->id,
                'Judul Task' => $task->title,
                'Deskripsi' => $task->description,
                'Product' => $task->product?->name ?? '-',
                'Client / Faskes' => $task->client?->name ?? '-',
                'Modul / Fitur' => $task->modul ?? '-',
                'URL' => $task->task_url === '-' ? '' : $task->task_url,
                'Jenis' => $task->category,
                'Prioritas' => $task->priority,
                'Status' => $task->status,
                'SLA Status' => strtoupper(str_replace('_', ' ', $task->sla_status)),
                'Engineer' => $task->engineer?->name ?? '-',
                'Assignee' => $task->assignee?->name ?? '-',
                'Tanggal Release' => $task->release_date ? Carbon::parse($task->release_date)->format('d M Y') : '-',
                'Tanggal Selesai' => $task->completed_at ? Carbon::parse($task->completed_at)->format('d M Y') : '-',
                'Dibuat Oleh' => $task->creator?->name ?? '-',
            ]);
        }

        $writer->close();

        return response()->download($filePath, $fileName, [
            'Content-Type' => 'text/csv',
        ])->deleteFileAfterSend(true);
    }
    public function import(Request $request)
    {
        $this->authorize('create', Task::class);
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
            'force_duplicate' => 'nullable|boolean',
        ]);
        $forceDuplicate = $request->boolean('force_duplicate', false);
        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension() ?: 'csv';
        $tempFileName = uniqid('import_') . '.' . $extension;
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $fullPath = $tempDir . DIRECTORY_SEPARATOR . $tempFileName;
        $file->move($tempDir, $tempFileName);
        // Buat lookup maps: nama → id (case-insensitive)
        $productMap = Team::where('type', 'PRODUCT')->pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id]);
        $engineerMap = Team::where('type', 'ENGINEER')->pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id]);
        $clientMap = Client::pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id]);
        $validCategories = ['Fitur Berbayar', 'Regulasi', 'Saran Fitur', 'Prioritas'];
        $validPriorities = ['urgent', 'high', 'medium', 'low'];
        $imported = 0;
        $skipped = 0;
        $duplicates = 0;
        $errors = [];
        $reader = \Spatie\SimpleExcel\SimpleExcelReader::create($fullPath);
        foreach ($reader->getRows() as $index => $row) {
            $rowNum = $index + 2; // baris 1 = header
            // Cari FK dari nama
            $productId = $productMap[strtolower(trim($row['Product'] ?? ''))] ?? null;
            $clientId = $clientMap[strtolower(trim($row['Client'] ?? $row['Faskes'] ?? $row['Client / Faskes'] ?? ''))] ?? null;
            $engineerId = $engineerMap[strtolower(trim($row['Engineer'] ?? ''))] ?? null;
            // Validasi wajib
            $title = trim($row['Judul Task'] ?? $row['Title'] ?? $row['Judul'] ?? '');
            if (!$title || !$productId || !$clientId) {
                $skipped++;
                $errors[] = "Baris {$rowNum}: Data wajib tidak lengkap (Judul/Product/Client).";
                continue;
            }
            // Map category dan priority
            $category = $row['Jenis'] ?? $row['Category'] ?? $row['Kategori'] ?? 'Saran Fitur';
            if (!in_array($category, $validCategories)) {
                $category = 'Saran Fitur';
            }
            $priority = strtolower($row['Prioritas'] ?? $row['Priority'] ?? 'medium');
            if (!in_array($priority, $validPriorities)) {
                $priority = 'medium';
            }
            $modul = $row['Modul'] ?? $row['Modul / Fitur'] ?? null;

            // Cek duplikat (kecuali force_duplicate)
            if (!$forceDuplicate) {
                $existingTask = Task::where('title', $title)
                    ->where('client_id', $clientId)
                    ->where('product_id', $productId)
                    ->where('category', $category)
                    ->when(!empty($modul), fn ($q) => $q->where('modul', $modul))
                    ->exists();

                if ($existingTask) {
                    $duplicates++;
                    $errors[] = "Baris {$rowNum}: Duplikat - task \"{$title}\" sudah ada.";
                    continue;
                }
            }

            $releaseDate = null;
            $rawDate = $row['Tanggal Release'] ?? $row['Release Date'] ?? null;
            if ($rawDate) {
                try {
                    $releaseDate = Carbon::parse($rawDate)->format('Y-m-d');
                } catch (\Exception $e) {
                    $releaseDate = null;
                }
            }
            Task::create([
                'title'        => $title,
                'description'  => $row['Deskripsi'] ?? $row['Description'] ?? null,
                'modul'        => $modul,
                'product_id'   => $productId,
                'client_id'    => $clientId,
                'engineer_id'  => $engineerId,
                'category'     => $category,
                'priority'     => $priority,
                'status'       => 'open',
                'task_url'     => $row['URL'] ?? $row['Task URL'] ?? '-',
                'release_date' => $releaseDate,
                'created_by'   => $request->user()->id,
            ]);
            $imported++;
        }
        // Cleanup temp file
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        ActivityLogger::log('imported', 'task', null, 'Import Task', "Mengimport {$imported} task dari file", null, [
            'imported' => $imported,
            'skipped' => $skipped,
            'duplicates' => $duplicates,
        ]);
        $message = "Berhasil mengimport {$imported} task.";
        if ($duplicates > 0) {
            $message .= " {$duplicates} baris dilewati karena duplikat.";
        }
        if ($skipped > 0) {
            $message .= " {$skipped} baris dilewati karena data tidak lengkap.";
        }
        return back()->with('success', $message)->with('import_errors', $errors);
    }
}
