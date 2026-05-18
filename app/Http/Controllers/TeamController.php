<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Team::class);

        $query = Team::withCount('users')->latest();

        if (request('trashed') === 'only') {
            $query->onlyTrashed();
        }

        return Inertia::render('Teams/Index', [
            // withCount('users') akan otomatis membuat atribut 'users_count'
            'teams' => $query->get(),
            'activeTrashed' => request('trashed') === 'only',
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Team::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['PRODUCT', 'ENGINEER'])],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $team = Team::create($validated);

        ActivityLogger::created('team', $team->id, $team->name, "Menambahkan tim '{$team->name}'", $validated);

        return back()->with('success', 'Tim berhasil ditambahkan.');
    }

    public function update(Request $request, Team $team)
    {
        $this->authorize('update', $team);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['PRODUCT', 'ENGINEER'])],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $oldValues = $team->getOriginal();
        $team->update($validated);

        ActivityLogger::updated('team', $team->id, $team->name, $oldValues, $team->fresh()->toArray(), "Mengupdate tim '{$team->name}'");

        return back()->with('success', 'Tim berhasil diperbarui.');
    }

    public function destroy(Team $team)
    {
        $this->authorize('delete', $team);

        // Proteksi agar tidak menghapus tim yang masih ada anggotanya
        if ($team->users()->count() > 0) {
            return back()->with('error', 'Tim tidak bisa dihapus karena masih memiliki anggota pengguna.');
        }

        ActivityLogger::deleted('team', $team->id, $team->name, "Menghapus tim '{$team->name}'");

        $team->delete();

        return back()->with('success', 'Tim berhasil dihapus.');
    }

    public function restore(int $team)
    {
        $team = Team::withTrashed()->findOrFail($team);

        $this->authorize('restore', $team);

        if (! $team->trashed()) {
            return back()->with('info', 'Tim ini masih aktif, tidak perlu dipulihkan.');
        }

        $deletedAt = $team->deleted_at;

        $team->restore();

        ActivityLogger::updated('team', $team->id, $team->name, ['deleted_at' => $deletedAt], ['deleted_at' => null], "Memulihkan tim '{$team->name}'");

        return back()->with('success', 'Tim berhasil dipulihkan.');
    }

    public function bulkRestore(Request $request)
    {
        $this->authorize('restoreAny', Team::class);

        $validated = $request->validate([
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'exists:teams,id'],
            'restore_all' => ['nullable', 'boolean'],
        ]);

        $restoreAll = $request->boolean('restore_all');
        $ids = $validated['ids'] ?? [];

        if (! $restoreAll && $ids === []) {
            return back()->with('error', 'Pilih minimal satu tim untuk dipulihkan.');
        }

        $teams = Team::onlyTrashed()
            ->when(! $restoreAll, fn ($query) => $query->whereIn('id', $ids))
            ->get();

        foreach ($teams as $team) {
            $deletedAt = $team->deleted_at;
            $team->restore();

            ActivityLogger::updated('team', $team->id, $team->name, ['deleted_at' => $deletedAt], ['deleted_at' => null], "Memulihkan tim '{$team->name}' secara massal");
        }

        return back()->with('success', "{$teams->count()} tim berhasil dipulihkan.");
    }
}
