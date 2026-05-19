<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\DocumentVersion;
use App\Models\Task;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    private const ALLOWED_FILE_MIMES = 'pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png';

    public function index(): Response
    {
        $this->authorize('viewAny', Document::class);

        $query = Document::with(['client:id,name', 'creator:id,name'])->latest();

        if (request('trashed') === 'only') {
            $query->onlyTrashed();
        }

        if (request('client_id')) {
            $query->where('client_id', request('client_id'));
        }

        $documents = $query->paginate(20)->withQueryString();

        $clients = Client::orderBy('name')->get(['id', 'name']);
        $documentTypes = DocumentType::orderBy('name')->pluck('name');

        return Inertia::render('Documents/Index', [
            'documents' => $documents,
            'clients' => $clients,
            'documentTypes' => $documentTypes,
            'activeClientId' => request('client_id') ? (int) request('client_id') : null,
            'activeTrashed' => request('trashed') === 'only',
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Document::class);

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'doc_url' => 'nullable|url|max:500',
            'file' => 'nullable|file|max:10240|mimes:'.self::ALLOWED_FILE_MIMES,
            'notes' => 'nullable|string|max:500',
        ]);

        DocumentType::firstOrCreate(['name' => strtoupper(trim($validated['type']))]);
        $validated['type'] = strtoupper(trim($validated['type']));

        $data = [
            'client_id' => $validated['client_id'],
            'title' => $validated['title'],
            'type' => $validated['type'],
            'doc_url' => $validated['doc_url'] ?? null,
            'current_version' => 1,
            'created_by' => Auth::id(),
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('documents', 'public');
            $data['file_path'] = $path;
            $data['file_name'] = $file->getClientOriginalName();
            $data['mime_type'] = $file->getMimeType();
            $data['file_size'] = $file->getSize();
        }

        $document = Document::create($data);

        // Buat version record HANYA jika ada file
        if ($request->hasFile('file')) {
            DocumentVersion::create([
                'document_id' => $document->id,
                'version_number' => 1,
                'file_path' => $document->file_path,
                'doc_url' => Storage::url($document->file_path),
                'file_size' => $document->file_size,
                'notes' => $validated['notes'] ?? 'Versi awal',
                'uploaded_by' => Auth::id(),
            ]);
        }

        ActivityLogger::created('document', $document->id, $document->title, "Membuat dokumen '{$document->title}'", $data);

        return back()->with('success', 'Dokumen berhasil ditambahkan.');
    }

    public function update(Request $request, Document $document)
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'doc_url' => 'nullable|url|max:500',
            'file' => 'nullable|file|max:10240|mimes:'.self::ALLOWED_FILE_MIMES,
            'notes' => 'nullable|string|max:500',
        ]);

        DocumentType::firstOrCreate(['name' => strtoupper(trim($validated['type']))]);
        $validated['type'] = strtoupper(trim($validated['type']));

        $oldValues = $document->getOriginal();

        $data = [
            'client_id' => $validated['client_id'],
            'title' => $validated['title'],
            'type' => $validated['type'],
            'doc_url' => $validated['doc_url'] ?? null,
        ];

        // HANYA jika ada file baru yang diupload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('documents', 'public');

            // Increment version
            $newVersion = $document->current_version + 1;

            // Update document dengan file baru
            $data['file_path'] = $path;
            $data['file_name'] = $file->getClientOriginalName();
            $data['mime_type'] = $file->getMimeType();
            $data['file_size'] = $file->getSize();
            $data['current_version'] = $newVersion;

            $document->update($data);

            // Buat SATU record DocumentVersion untuk versi baru
            DocumentVersion::create([
                'document_id' => $document->id,
                'version_number' => $newVersion,
                'file_path' => $path,
                'doc_url' => Storage::url($path),
                'file_size' => $file->getSize(),
                'notes' => $validated['notes'] ?? "Update ke versi {$newVersion}",
                'uploaded_by' => Auth::id(),
            ]);
        } else {
            // Jika tidak ada file baru, hanya update metadata
            $document->update($data);
        }

        ActivityLogger::updated('document', $document->id, $document->title, $oldValues, $document->fresh()->toArray(), "Mengupdate dokumen '{$document->title}'");

        return back()->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function show(Document $document): Response
    {
        $this->authorize('view', $document);

        $document->load([
            'client:id,name,city',
            'creator:id,name',
            'tasks:id,title,category,status',
        ]);

        $clientDocuments = Document::with([
            'tasks' => fn ($q) => $q->select('tasks.id', 'tasks.title', 'tasks.category', 'tasks.status'),
        ])
            ->where('client_id', $document->client_id)
            ->latest()
            ->get();

        // Task milik client yang:
        // (1) belum punya dokumen sama sekali, ATAU
        // (2) sudah terhubung ke dokumen yang sedang dibuka
        // Dan statusnya bukan completed
        $linkedTaskIds = $document->tasks->pluck('id');

        $clientTasks = Task::where('client_id', $document->client_id)
            ->where('status', '!=', 'completed')
            ->where(function ($q) use ($linkedTaskIds) {
                $q->whereDoesntHave('documents')
                    ->orWhereIn('id', $linkedTaskIds);
            })
            ->select('id', 'title', 'category', 'status')
            ->latest()
            ->get();

        $documentTypes = DocumentType::orderBy('name')->pluck('name');

        return Inertia::render('Documents/Show', [
            'document' => $document,
            'clientDocuments' => $clientDocuments,
            'clientTasks' => $clientTasks,
            'documentTypes' => $documentTypes,
        ]);
    }

    public function syncTasks(Request $request, Document $document)
    {
        $this->authorize('update', $document);

        $request->validate([
            'tasks' => 'array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.status' => 'nullable|in:revision,completed',
        ]);

        // Bangun array [task_id => ['status' => ...]] untuk sync dengan pivot
        $syncData = collect($request->tasks ?? [])
            ->mapWithKeys(fn ($t) => [$t['id'] => ['status' => $t['status'] ?? null]])
            ->toArray();

        // Reset semua relasi lama, set ulang yang dicentang (sesuai project lama)
        $document->tasks()->sync($syncData);

        return redirect()->route('documents.show', $document)
            ->with('success', 'Relasi task berhasil diperbarui.');
    }

    public function destroy(Document $document)
    {
        $this->authorize('delete', $document);

        ActivityLogger::deleted('document', $document->id, $document->title, "Menghapus dokumen '{$document->title}'");

        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    public function restore(int $document)
    {
        $document = Document::withTrashed()->findOrFail($document);

        $this->authorize('restore', $document);

        if (! $document->trashed()) {
            return back()->with('info', 'Dokumen ini masih aktif, tidak perlu dipulihkan.');
        }

        $deletedAt = $document->deleted_at;

        $document->restore();

        ActivityLogger::updated('document', $document->id, $document->title, ['deleted_at' => $deletedAt], ['deleted_at' => null], "Memulihkan dokumen '{$document->title}'");

        return back()->with('success', 'Dokumen berhasil dipulihkan.');
    }

    public function bulkRestore(Request $request)
    {
        $this->authorize('restoreAny', Document::class);

        $validated = $request->validate([
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'exists:documents,id'],
            'restore_all' => ['nullable', 'boolean'],
        ]);

        $restoreAll = $request->boolean('restore_all');
        $ids = $validated['ids'] ?? [];

        if (! $restoreAll && $ids === []) {
            return back()->with('error', 'Pilih minimal satu dokumen untuk dipulihkan.');
        }

        $documents = Document::onlyTrashed()
            ->when(! $restoreAll, fn ($query) => $query->whereIn('id', $ids))
            ->get();

        foreach ($documents as $document) {
            $deletedAt = $document->deleted_at;
            $document->restore();

            ActivityLogger::updated('document', $document->id, $document->title, ['deleted_at' => $deletedAt], ['deleted_at' => null], "Memulihkan dokumen '{$document->title}' secara massal");
        }

        return back()->with('success', "{$documents->count()} dokumen berhasil dipulihkan.");
    }
}
