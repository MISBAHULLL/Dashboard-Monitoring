<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\DocumentType;
use App\Models\Client;
use App\Models\Task;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    public function index(): Response
    {
        $query = Document::with(['client:id,name', 'creator:id,name'])->latest();

        if (request('client_id')) {
            $query->where('client_id', request('client_id'));
        }

        $documents = $query->paginate(20)->withQueryString();

        $clients       = Client::orderBy('name')->get(['id', 'name']);
        $documentTypes = DocumentType::orderBy('name')->pluck('name');

        return Inertia::render('Documents/Index', [
            'documents'      => $documents,
            'clients'        => $clients,
            'documentTypes'  => $documentTypes,
            'activeClientId' => request('client_id') ? (int) request('client_id') : null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'title'     => 'required|string|max:255',
            'type'      => 'required|string|max:100',
            'doc_url'   => 'nullable|url|max:500',
            'file'      => 'nullable|file|max:10240',
            'notes'     => 'nullable|string|max:500',
        ]);

        DocumentType::firstOrCreate(['name' => strtoupper(trim($validated['type']))]);
        $validated['type'] = strtoupper(trim($validated['type']));

        $data = [
            'client_id'       => $validated['client_id'],
            'title'           => $validated['title'],
            'type'            => $validated['type'],
            'doc_url'         => $validated['doc_url'] ?? null,
            'current_version' => 1,
            'created_by'      => Auth::id(),
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
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'title'     => 'required|string|max:255',
            'type'      => 'required|string|max:100',
            'doc_url'   => 'nullable|url|max:500',
            'file'      => 'nullable|file|max:10240',
            'notes'     => 'nullable|string|max:500',
        ]);

        DocumentType::firstOrCreate(['name' => strtoupper(trim($validated['type']))]);
        $validated['type'] = strtoupper(trim($validated['type']));

        $oldValues = $document->getOriginal();

        $data = [
            'client_id' => $validated['client_id'],
            'title'     => $validated['title'],
            'type'      => $validated['type'],
            'doc_url'   => $validated['doc_url'] ?? null,
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
        $document->load([
            'client:id,name,city',
            'creator:id,name',
            'tasks:id,title,category,status',
        ]);

        $clientDocuments = Document::with([
                'tasks' => fn($q) => $q->select('tasks.id','tasks.title','tasks.category','tasks.status')
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
            ->where(function ($q) use ($document, $linkedTaskIds) {
                $q->whereDoesntHave('documents')
                  ->orWhereIn('id', $linkedTaskIds);
            })
            ->select('id', 'title', 'category', 'status')
            ->latest()
            ->get();

        $documentTypes = DocumentType::orderBy('name')->pluck('name');

        return Inertia::render('Documents/Show', [
            'document'        => $document,
            'clientDocuments' => $clientDocuments,
            'clientTasks'     => $clientTasks,
            'documentTypes'   => $documentTypes,
        ]);
    }

    public function syncTasks(Request $request, Document $document)
    {
        $request->validate([
            'tasks'          => 'array',
            'tasks.*.id'     => 'required|exists:tasks,id',
            'tasks.*.status' => 'nullable|in:revision,completed',
        ]);

        // Bangun array [task_id => ['status' => ...]] untuk sync dengan pivot
        $syncData = collect($request->tasks ?? [])
            ->mapWithKeys(fn($t) => [$t['id'] => ['status' => $t['status'] ?? null]])
            ->toArray();

        // Reset semua relasi lama, set ulang yang dicentang (sesuai project lama)
        $document->tasks()->sync($syncData);

        return redirect()->route('documents.show', $document)
            ->with('success', 'Relasi task berhasil diperbarui.');
    }

    public function destroy(Document $document)
    {
        ActivityLogger::deleted('document', $document->id, $document->title, "Menghapus dokumen '{$document->title}'");

        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->versions()->delete();
        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }
}
