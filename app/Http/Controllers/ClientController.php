<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index()
    {
        return Inertia::render('Clients/Index', [
            // withCount('tasks') berguna untuk menampilkan total project si client
            'clients' => Client::withCount('tasks')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'type' => ['nullable', Rule::in(['A', 'B', 'C', 'PRATAMA'])],
            'pic_name' => 'nullable|string|max:255',
            'pic_phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        Client::create($validated);

        return back()->with('success', 'Faskes / Client berhasil ditambahkan.');
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'type' => ['nullable', Rule::in(['A', 'B', 'C', 'PRATAMA'])],
            'pic_name' => 'nullable|string|max:255',
            'pic_phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $client->update($validated);

        return back()->with('success', 'Faskes / Client berhasil diperbarui.');
    }

    public function destroy(Client $client)
    {
        // Proteksi data master
        if ($client->tasks()->count() > 0) {
            return back()->with('error', 'Client tidak bisa dihapus karena masih memiliki history Task.');
        }

        $client->delete();

        return back()->with('success', 'Faskes / Client berhasil dihapus.');
    }
}
