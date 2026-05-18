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

        $query = Client::withCount(['tasks', 'documents'])->latest();

        if (request('trashed') === 'only') {
            $query->onlyTrashed();
        }

        return Inertia::render('Clients/Index', [
            'clients'       => $query->get(),
            'documentTypes' => DocumentType::orderBy('name')->pluck('name'),
            'activeTrashed' => request('trashed') === 'only',
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

        $client->delete();

        return back()->with('success', 'Faskes / Client berhasil dihapus.');
    }

    public function restore(int $client)
    {
        $client = Client::withTrashed()->findOrFail($client);

        $this->authorize('restore', $client);

        if (! $client->trashed()) {
            return back()->with('info', 'Faskes / Client ini masih aktif, tidak perlu dipulihkan.');
        }

        $deletedAt = $client->deleted_at;

        $client->restore();

        ActivityLogger::updated('client', $client->id, $client->name, ['deleted_at' => $deletedAt], ['deleted_at' => null], "Memulihkan faskes '{$client->name}'");

        return back()->with('success', 'Faskes / Client berhasil dipulihkan.');
    }

    public function bulkRestore(Request $request)
    {
        $this->authorize('restoreAny', Client::class);

        $validated = $request->validate([
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'exists:clients,id'],
            'restore_all' => ['nullable', 'boolean'],
        ]);

        $restoreAll = $request->boolean('restore_all');
        $ids = $validated['ids'] ?? [];

        if (! $restoreAll && $ids === []) {
            return back()->with('error', 'Pilih minimal satu faskes untuk dipulihkan.');
        }

        $clients = Client::onlyTrashed()
            ->when(! $restoreAll, fn ($query) => $query->whereIn('id', $ids))
            ->get();

        foreach ($clients as $client) {
            $deletedAt = $client->deleted_at;
            $client->restore();

            ActivityLogger::updated('client', $client->id, $client->name, ['deleted_at' => $deletedAt], ['deleted_at' => null], "Memulihkan faskes '{$client->name}' secara massal");
        }

        return back()->with('success', "{$clients->count()} faskes berhasil dipulihkan.");
    }
}
