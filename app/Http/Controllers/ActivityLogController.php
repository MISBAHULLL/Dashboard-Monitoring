<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user:id,name')
            ->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('target_title', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(25)->withQueryString();

        return Inertia::render('ActivityLogs/Index', [
            'logs' => $logs,
            'filters' => $request->all(['action', 'module', 'search']),
            'actions' => ['created', 'updated', 'deleted', 'imported', 'exported', 'status_changed', 'logged_in'],
            'modules' => ['task', 'team', 'client', 'document', 'user', 'system'],
        ]);
    }
}
