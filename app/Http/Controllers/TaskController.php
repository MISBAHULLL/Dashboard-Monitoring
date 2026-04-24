<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Client;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['client', 'product', 'engineer', 'assignee']);

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
        
        return Inertia::render('Tasks/Index', [
            'tasks' => $query->latest()->paginate(10)->withQueryString(),
            'filters' => $request->all(['search', 'product_id', 'client_id', 'engineer_id', 'category', 'status', 'has_link', 'date_from', 'date_to']),
            
            // Kirim data master ke Vue untuk dropdown filter
            'clients' => Client::where('is_active', true)->get(['id', 'name']),
            'product_teams' => Team::where('type', 'PRODUCT')->where('is_active', true)->get(['id', 'name']),
            'engineer_teams' => Team::where('type', 'ENGINEER')->where('is_active', true)->get(['id', 'name']),
        ]);
    }

    public function create()
    {
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

        Task::create($validated);

        return redirect()->route('tasks.index')->with('success', 'Task berhasil dibuat.');
    }

    public function edit(Task $task)
    {
        return Inertia::render('Tasks/Edit', [
            'task' => $task,
            'clients' => Client::where('is_active', true)->get(['id', 'name']),
            'product_teams' => Team::where('type', 'PRODUCT')->where('is_active', true)->get(['id', 'name']),
            'engineer_teams' => Team::where('type', 'ENGINEER')->where('is_active', true)->get(['id', 'name']),
            'users' => User::where('is_active', true)->get(['id', 'name']),
            'existing_modules' => Task::select('modul')->whereNotNull('modul')->where('modul', '!=', '')->distinct()->pluck('modul'),
        ]);
    }

    public function update(Request $request, Task $task)
    {
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

        $task->update($validated);

        return redirect()->route('tasks.index')->with('success', 'Task berhasil diperbarui.');
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return back()->with('success', 'Task berhasil dihapus.');
    }

    public function kanban()
    {
        // Ambil semua task yang belum selesai
        $activeTasks = Task::with(['client', 'assignee', 'product'])
            ->where('status', '!=', 'completed')
            ->get();
            
        // Ambil task yang sudah selesai dalam 7 hari terakhir
        $completedTasks = Task::with(['client', 'assignee', 'product'])
            ->where('status', 'completed')
            ->where('completed_at', '>=', now()->subDays(7))
            ->get();

        $tasks = $activeTasks->merge($completedTasks);

        return Inertia::render('Tasks/Kanban', [
            'tasks' => $tasks
        ]);
    }

    public function updateStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => ['required', \Illuminate\Validation\Rule::in(['open', 'in_progress', 'revision', 'completed'])]
        ]);

        if ($validated['status'] === 'completed' && $task->status !== 'completed') {
            $validated['completed_at'] = now();
        } elseif ($validated['status'] !== 'completed') {
            $validated['completed_at'] = null;
        }

        $task->update($validated);

        return back();
    }
}
