<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * ClientController — Mengelola CRUD data Client/Faskes.
 */
class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
            })
            ->withCount('tasks')               // Tambahkan hitungan task per client
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'nullable|string|max:50|unique:clients,code',
            'address'        => 'nullable|string|max:500',
            'contact_person' => 'nullable|string|max:255',
            'contact_email'  => 'nullable|email|max:255',
            'contact_phone'  => 'nullable|string|max:20',
        ]);

        Client::create($validated);

        return redirect()->route('clients.index')
            ->with('success', 'Client berhasil ditambahkan!');
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'nullable|string|max:50|unique:clients,code,' . $client->id,
            'address'        => 'nullable|string|max:500',
            'contact_person' => 'nullable|string|max:255',
            'contact_email'  => 'nullable|email|max:255',
            'contact_phone'  => 'nullable|string|max:20',
            'is_active'      => 'boolean',
        ]);

        $client->update($validated);

        return redirect()->route('clients.index')
            ->with('success', 'Client berhasil diupdate!');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Client berhasil dihapus!');
    }
}
