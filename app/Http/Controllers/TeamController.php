<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    public function index()
    {
        return Inertia::render('Teams/Index', [
            // withCount('users') akan otomatis membuat atribut 'users_count'
            'teams' => Team::withCount('users')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['PRODUCT', 'ENGINEER'])],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Team::create($validated);

        return back()->with('success', 'Tim berhasil ditambahkan.');
    }

    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['PRODUCT', 'ENGINEER'])],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $team->update($validated);

        return back()->with('success', 'Tim berhasil diperbarui.');
    }

    public function destroy(Team $team)
    {
        // Proteksi agar tidak menghapus tim yang masih ada anggotanya
        if ($team->users()->count() > 0) {
            return back()->with('error', 'Tim tidak bisa dihapus karena masih memiliki anggota pengguna.');
        }

        $team->delete();

        return back()->with('success', 'Tim berhasil dihapus.');
    }
}
