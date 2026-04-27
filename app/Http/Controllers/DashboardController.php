<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Task;
use App\Models\Client;
use App\Models\Team;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // 2. Jika user adalah admin, tampilkan AdminDashboard
        if ($user->isAdmin()) {
            $stats = [
                'total_tasks' => Task::count(),
                'open_tasks' => Task::where('status', 'open')->count(),
                'in_progress_tasks' => Task::where('status', 'in_progress')->count(),
                'completed_tasks' => Task::where('status', 'completed')->count(),
                'total_clients' => Client::count(),
                'total_teams' => Team::count(),
            ];

            $today = Carbon::today();
            $dueSoonEndDate = Carbon::today()->addDays(7);
            
            // Data untuk Donut Chart (Status)
            $chartDonut = [
                $stats['open_tasks'],
                $stats['in_progress_tasks'],
                Task::where('status', 'revision')->count(),
                $stats['completed_tasks']
            ];

            // Data untuk Area Chart (Tren pembuatan Task 7 hari terakhir)
            $trendData = Task::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(6))
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date');
                
            $chartArea = ['categories' => [], 'data' => []];
            for ($i = 6; $i >= 0; $i--) {
                $dateStr = now()->subDays($i)->format('Y-m-d');
                $chartArea['categories'][] = now()->subDays($i)->format('d M');
                $chartArea['data'][] = $trendData->get($dateStr, 0);
            }

            $overdueBaseQuery = Task::query()
                ->whereNotNull('release_date')
                ->whereDate('release_date', '<', $today)
                ->where('status', '!=', 'completed');

            $dueSoonBaseQuery = Task::query()
                ->whereNotNull('release_date')
                ->whereDate('release_date', '>=', $today)
                ->whereDate('release_date', '<=', $dueSoonEndDate)
                ->where('status', '!=', 'completed');

            $overdueTasks = (clone $overdueBaseQuery)
                ->with(['client:id,name', 'product:id,name', 'assignee:id,name'])
                ->select(['id', 'title', 'client_id', 'product_id', 'assigned_to', 'status', 'release_date'])
                ->orderBy('release_date')
                ->limit(10)
                ->get();

            $dueSoonTasks = (clone $dueSoonBaseQuery)
                ->with(['client:id,name', 'product:id,name', 'assignee:id,name'])
                ->select(['id', 'title', 'client_id', 'product_id', 'assigned_to', 'status', 'release_date'])
                ->orderBy('release_date')
                ->limit(10)
                ->get();

            $teamPerformance = Team::query()
                ->where('type', 'PRODUCT')
                ->select([
                    'teams.id',
                    'teams.name',
                    'teams.type',
                ])
                ->leftJoin('tasks', function ($join) {
                    $join->on('tasks.product_id', '=', 'teams.id')
                        ->whereNull('tasks.deleted_at');
                })
                ->groupBy('teams.id', 'teams.name', 'teams.type')
                ->selectRaw('COUNT(tasks.id) as total_tasks')
                ->selectRaw("SUM(CASE WHEN tasks.status = 'completed' THEN 1 ELSE 0 END) as completed_tasks")
                ->selectRaw("SUM(CASE WHEN tasks.status = 'open' THEN 1 ELSE 0 END) as open_tasks")
                ->selectRaw("SUM(CASE WHEN tasks.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tasks")
                ->selectRaw("SUM(CASE WHEN tasks.status = 'revision' THEN 1 ELSE 0 END) as revision_tasks")
                ->selectRaw(
                    "SUM(CASE WHEN tasks.status != 'completed' AND tasks.release_date IS NOT NULL AND tasks.release_date < ? THEN 1 ELSE 0 END) as overdue_tasks",
                    [$today->toDateString()]
                )
                ->orderByDesc('total_tasks')
                ->limit(10)
                ->get()
                ->map(function ($team) {
                    $totalTasks = (int) $team->total_tasks;
                    $completedTasks = (int) $team->completed_tasks;

                    return [
                        'id' => $team->id,
                        'name' => $team->name,
                        'type' => $team->type,
                        'total_tasks' => $totalTasks,
                        'completed_tasks' => $completedTasks,
                        'open_tasks' => (int) $team->open_tasks,
                        'in_progress_tasks' => (int) $team->in_progress_tasks,
                        'revision_tasks' => (int) $team->revision_tasks,
                        'overdue_tasks' => (int) $team->overdue_tasks,
                        'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0.0,
                    ];
                })
                ->values();

            return Inertia::render('Dashboard/AdminDashboard', [
                'stats' => $stats,
                'chart_donut' => $chartDonut,
                'chart_area' => $chartArea,
                'overdue_count' => (clone $overdueBaseQuery)->count(),
                'due_soon_count' => (clone $dueSoonBaseQuery)->count(),
                'overdue_tasks' => $overdueTasks,
                'due_soon_tasks' => $dueSoonTasks,
                'team_performance' => $teamPerformance,
                'recent_tasks' => Task::with(['client', 'product', 'assignee'])
                                    ->latest()
                                    ->take(5)
                                    ->get(),
            ]);
        }

        // 3. Jika user adalah member biasa, tampilkan MemberDashboard
        $memberTaskQuery = Task::query()->where('assigned_to', $user->id);

        return Inertia::render('Dashboard/MemberDashboard', [
            'stats' => [
                'total_tasks' => (clone $memberTaskQuery)->count(),
                'open_tasks' => (clone $memberTaskQuery)->where('status', 'open')->count(),
                'in_progress_tasks' => (clone $memberTaskQuery)->where('status', 'in_progress')->count(),
                'completed_tasks' => (clone $memberTaskQuery)->where('status', 'completed')->count(),
            ],
            'my_tasks' => Task::with(['client', 'product'])
                                ->where('assigned_to', $user->id)
                                ->whereNotIn('status', ['completed'])
                                ->latest()
                                ->take(5)
                                ->get(),
        ]);
    }
}
