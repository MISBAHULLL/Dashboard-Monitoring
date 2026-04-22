<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * TeamController — Mengelola CRUD data Tim.
 *
 * Setiap method di controller ini menangani 1 aksi:
 * - index()   → Tampilkan daftar tim
 * - store()   → Simpan tim baru
 * - update()  → Update tim yang sudah ada
 * - destroy() → Hapus tim (soft delete)
 */
class TeamController extends Controller
{
    /**
     * Tampilkan halaman daftar tim.
     *
     * Inertia::render() mengirim data dari PHP ke Vue component.
     * Ini menggantikan blade view di Laravel tradisional.
     */
    public function index(Request $request)
    {
        // Query tim dengan filter dan pagination
        $teams = Team::query()
            ->when($request->search, function ($query, $search) {
                // 'when' = conditional query: hanya filter jika ada parameter 'search'
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->orderBy('name')
            ->paginate(10)                    // Pagination: 10 item per halaman
            ->withQueryString();               // Pertahankan query string di URL pagination

        // Render Vue page 'Teams/Index' dan kirim data
        return Inertia::render('Teams/Index', [
            'teams'   => $teams,
            'filters' => $request->only(['search', 'type']),
        ]);
    }

    /**
     * Simpan tim baru ke database.
     *
     * Request validation di Laravel = auto-return error ke frontend
     * jika ada field yang tidak valid.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|in:product,engineer',
            'description' => 'nullable|string|max:1000',
        ]);

        Team::create($validated);

        return redirect()->route('teams.index')
            ->with('success', 'Tim berhasil ditambahkan!');
    }

    /**
     * Update data tim yang sudah ada.
     *
     * Route Model Binding: Laravel otomatis cari Team berdasarkan ID di URL.
     * Jika tidak ditemukan → auto 404.
     */
    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|in:product,engineer',
            'description' => 'nullable|string|max:1000',
            'is_active'   => 'boolean',
        ]);

        $team->update($validated);

        return redirect()->route('teams.index')
            ->with('success', 'Tim berhasil diupdate!');
    }

    /**
     * Hapus tim (soft delete).
     *
     * Soft delete = data tidak benar-benar dihapus dari database,
     * hanya ditandai 'deleted_at' (bisa di-restore nanti).
     */
    public function destroy(Team $team)
    {
        $team->delete();

        return redirect()->route('teams.index')
            ->with('success', 'Tim berhasil dihapus!');
    }
}
