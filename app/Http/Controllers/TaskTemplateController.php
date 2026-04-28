<?php

namespace App\Http\Controllers;

use App\Models\TaskTemplate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskTemplateController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'product_id' => 'required|exists:teams,id',
            'engineer_id' => 'nullable|exists:teams,id',
            'description' => 'nullable|string',
            'category' => ['required', Rule::in(['Fitur Berbayar', 'Regulasi', 'Saran Fitur', 'Prioritas'])],
            'priority' => ['required', Rule::in(['urgent', 'high', 'medium', 'low'])],
        ]);

        $validated['created_by'] = $request->user()->id;

        TaskTemplate::create($validated);

        return back()->with('success', 'Template task berhasil disimpan.');
    }

    public function destroy(TaskTemplate $taskTemplate)
    {
        // Pastikan hanya pembuatnya yang bisa menghapus
        if ($taskTemplate->created_by !== request()->user()->id) {
            abort(403);
        }

        $taskTemplate->delete();

        return back()->with('success', 'Template task berhasil dihapus.');
    }
}
