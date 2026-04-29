<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\ActivityLogger;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Client::class);

        return Inertia::render('Clients/Index', [
            'clients'       => Client::withCount(['tasks', 'documents'])->latest()->get(),
            'documentTypes' => DocumentType::orderBy('name')->pluck('name'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Client::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'type' => ['nullable', Rule::in(['A', 'B', 'C', 'PRATAMA'])],
            'pic_name' => 'nullable|string|max:255',
            'pic_phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $client = Client::create($validated);

        ActivityLogger::created('client', $client->id, $client->name, "Menambahkan faskes '{$client->name}'", $validated);

        return back()->with('success', 'Faskes / Client berhasil ditambahkan.');
    }

    public function update(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'type' => ['nullable', Rule::in(['A', 'B', 'C', 'PRATAMA'])],
            'pic_name' => 'nullable|string|max:255',
            'pic_phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $oldValues = $client->getOriginal();
        $client->update($validated);

        ActivityLogger::updated('client', $client->id, $client->name, $oldValues, $client->fresh()->toArray(), "Mengupdate faskes '{$client->name}'");

        return back()->with('success', 'Faskes / Client berhasil diperbarui.');
    }

    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);

        // Proteksi data master
        if ($client->tasks()->count() > 0) {
            return back()->with('error', 'Client tidak bisa dihapus karena masih memiliki history Task.');
        }

        ActivityLogger::deleted('client', $client->id, $client->name, "Menghapus faskes '{$client->name}'");

        $client->forceDelete();

        return back()->with('success', 'Faskes / Client berhasil dihapus.');
    }
}
