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
        // Ambil task beserta relasi tabel referensinya
        $query = Task::with(['client', 'product', 'engineer', 'assignee']);

        // Nanti kita tambahkan filter pencarian yang kompleks di sini
        
        return Inertia::render('Tasks/Index', [
            'tasks' => $query->latest()->paginate(10),
            
            // Kirim data master ke Vue untuk dropdown filter
            'clients' => Client::where('is_active', true)->get(['id', 'name']),
            'teams' => Team::where('is_active', true)->get(['id', 'name', 'type']),
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
}
