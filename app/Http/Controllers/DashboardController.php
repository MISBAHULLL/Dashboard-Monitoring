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
        /** @var \App\Models\User $user */
        $user = $request->user();

        // 2. Jika user adalah admin, tampilkan AdminDashboard
        if ($user->isAdmin()) {
            $period = $request->string('period')->toString();
            $periodOptions = [
                ['value' => '7d', 'label' => '7 Hari'],
                ['value' => '30d', 'label' => '30 Hari'],
                ['value' => 'month', 'label' => 'Bulan Ini'],
                ['value' => 'all', 'label' => 'Semua'],
            ];

            if (! in_array($period, array_column($periodOptions, 'value'), true)) {
                $period = '30d';
            }

            $now = Carbon::now();
            $periodStart = match ($period) {
                '7d' => $now->copy()->subDays(6)->startOfDay(),
                '30d' => $now->copy()->subDays(29)->startOfDay(),
                'month' => $now->copy()->startOfMonth(),
                default => null,
            };
            $periodLabel = collect($periodOptions)->firstWhere('value', $period)['label'] ?? '30 Hari';

            $periodTaskQuery = Task::query()
                ->when($periodStart, fn ($query) => $query->where('created_at', '>=', $periodStart));

            $periodTaskWithTrashedQuery = Task::withTrashed()
                ->when($periodStart, fn ($query) => $query->where('created_at', '>=', $periodStart));

            $activeTasks = (clone $periodTaskQuery)->count();
            $trashedTasks = (clone $periodTaskWithTrashedQuery)->onlyTrashed()->count();

            $stats = [
                'total_tasks' => $activeTasks,
                'active_tasks' => $activeTasks,
                'trashed_tasks' => $trashedTasks,
                'total_tasks_with_trashed' => (clone $periodTaskWithTrashedQuery)->count(),
                'open_tasks' => (clone $periodTaskQuery)->where('status', 'open')->count(),
                'in_progress_tasks' => (clone $periodTaskQuery)->where('status', 'in_progress')->count(),
                'completed_tasks' => (clone $periodTaskQuery)->where('status', 'completed')->count(),
                'total_clients' => Client::count(),
                'total_teams' => Team::count(),
            ];

            // Hitung trend: jumlah data baru yang ditambahkan dalam 7 hari terakhir
            $sevenDaysAgo = Carbon::now()->subDays(7);

            $tasksTrend = Task::where('created_at', '>=', $sevenDaysAgo)->count();
            $teamsTrend = Team::where('created_at', '>=', $sevenDaysAgo)->count();
            $clientsTrend = Client::where('created_at', '>=', $sevenDaysAgo)->count();

            // Untuk pending: hitung selisih task open yang baru masuk vs yang selesai dalam 7 hari
            $newOpenTasks = Task::where('status', 'open')
                ->where('created_at', '>=', $sevenDaysAgo)->count();
            $recentlyCompleted = Task::where('status', 'completed')
                ->where('updated_at', '>=', $sevenDaysAgo)->count();
            $pendingTrend = $newOpenTasks - $recentlyCompleted;

            $trends = [
                'tasks' => $tasksTrend,
                'teams' => $teamsTrend,
                'pending' => $pendingTrend,
                'clients' => $clientsTrend,
            ];

            $today = $now->copy()->startOfDay();

            // Data untuk Donut Chart (Status)
            $chartDonut = [
                $stats['open_tasks'],
                $stats['in_progress_tasks'],
                (clone $periodTaskQuery)->where('status', 'revision')->count(),
                $stats['completed_tasks']
            ];

            // Data untuk chart tren task sesuai periode aktif.
            $chartArea = ['categories' => [], 'data' => []];

            if ($period === 'all') {
                $firstTaskCreatedAt = Task::min('created_at');
                $chartStart = $firstTaskCreatedAt
                    ? Carbon::parse($firstTaskCreatedAt)->startOfMonth()
                    : $now->copy()->startOfMonth();

                $trendData = Task::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period_key, COUNT(*) as count")
                    ->groupBy('period_key')
                    ->orderBy('period_key')
                    ->pluck('count', 'period_key');

                $cursor = $chartStart->copy();
                while ($cursor->lessThanOrEqualTo($now)) {
                    $periodKey = $cursor->format('Y-m');
                    $chartArea['categories'][] = $cursor->format('M Y');
                    $chartArea['data'][] = (int) $trendData->get($periodKey, 0);
                    $cursor->addMonth();
                }
            } else {
                $trendData = Task::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->where('created_at', '>=', $periodStart)
                    ->groupBy('date')
                    ->orderBy('date')
                    ->pluck('count', 'date');

                $cursor = $periodStart->copy();
                while ($cursor->lessThanOrEqualTo($now)) {
                    $dateStr = $cursor->format('Y-m-d');
                    $chartArea['categories'][] = $cursor->format('d M');
                    $chartArea['data'][] = (int) $trendData->get($dateStr, 0);
                    $cursor->addDay();
                }
            }
            $chartMonth = $chartArea;

            $overdueBaseQuery = Task::query()
                ->whereSlaOverdue()
                ->when($periodStart, fn ($query) => $query->where('tasks.created_at', '>=', $periodStart));
            $dueSoonBaseQuery = Task::query()
                ->whereSlaDueSoon()
                ->when($periodStart, fn ($query) => $query->where('tasks.created_at', '>=', $periodStart));

            $overdueTasks = (clone $overdueBaseQuery)
                ->with(['client:id,name', 'product:id,name', 'assignee:id,name'])
                ->select(['tasks.id', 'tasks.title', 'tasks.client_id', 'tasks.product_id', 'tasks.assigned_to', 'tasks.status', 'tasks.release_date', 'tasks.category', 'tasks.created_at', 'tasks.completed_at'])
                ->orderByRaw(Task::effectiveDeadlineExpression())
                ->limit(10)
                ->get();

            $dueSoonTasks = (clone $dueSoonBaseQuery)
                ->with(['client:id,name', 'product:id,name', 'assignee:id,name'])
                ->select(['tasks.id', 'tasks.title', 'tasks.client_id', 'tasks.product_id', 'tasks.assigned_to', 'tasks.status', 'tasks.release_date', 'tasks.category', 'tasks.created_at', 'tasks.completed_at'])
                ->orderByRaw(Task::effectiveDeadlineExpression())
                ->limit(10)
                ->get();

            $teamPerformance = Team::query()
                ->where('type', 'PRODUCT')
                ->select([
                    'teams.id',
                    'teams.name',
                    'teams.type',
                ])
                ->leftJoin('tasks', function (\Illuminate\Database\Query\JoinClause $join) {
                    $join->on('tasks.product_id', '=', 'teams.id')
                        ->whereNull('tasks.deleted_at');
                })
                ->when($periodStart, function ($query) use ($periodStart) {
                    $query->where(function ($query) use ($periodStart) {
                        $query->whereNull('tasks.id')
                            ->orWhere('tasks.created_at', '>=', $periodStart);
                    });
                })
                ->leftJoin('sla_configs', 'sla_configs.category', '=', 'tasks.category')
                ->groupBy('teams.id', 'teams.name', 'teams.type')
                ->selectRaw('COUNT(tasks.id) as total_tasks')
                ->selectRaw("SUM(CASE WHEN tasks.status = 'completed' THEN 1 ELSE 0 END) as completed_tasks")
                ->selectRaw("SUM(CASE WHEN tasks.status = 'open' THEN 1 ELSE 0 END) as open_tasks")
                ->selectRaw("SUM(CASE WHEN tasks.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tasks")
                ->selectRaw("SUM(CASE WHEN tasks.status = 'revision' THEN 1 ELSE 0 END) as revision_tasks")
                ->selectRaw(
                    "SUM(CASE WHEN tasks.status != 'completed' AND ".Task::effectiveDeadlineExpression()." IS NOT NULL AND ".Task::effectiveDeadlineExpression()." < ? THEN 1 ELSE 0 END) as overdue_tasks",
                    [$today->toDateString()]
                )
                ->orderByDesc('total_tasks')
                ->limit(10)
                ->get()
                ->map(function ($team) {
                    $totalTasks = (int) $team->getAttribute('total_tasks');
                    $completedTasks = (int) $team->getAttribute('completed_tasks');

                    return [
                        'id' => $team->id,
                        'name' => $team->name,
                        'type' => $team->type,
                        'total_tasks' => $totalTasks,
                        'completed_tasks' => $completedTasks,
                        'open_tasks' => (int) $team->getAttribute('open_tasks'),
                        'in_progress_tasks' => (int) $team->getAttribute('in_progress_tasks'),
                        'revision_tasks' => (int) $team->getAttribute('revision_tasks'),
                        'overdue_tasks' => (int) $team->getAttribute('overdue_tasks'),
                        'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0.0,
                    ];
                })
                ->values();

            return Inertia::render('Dashboard/AdminDashboard', [
                'stats' => $stats,
                'trends' => $trends,
                'chart_donut' => $chartDonut,
                'chart_area' => $chartArea,
                'chart_month' => $chartMonth,
                'overdue_count' => (clone $overdueBaseQuery)->count(),
                'due_soon_count' => (clone $dueSoonBaseQuery)->count(),
                'overdue_tasks' => $overdueTasks,
                'due_soon_tasks' => $dueSoonTasks,
                'team_performance' => $teamPerformance,
                'recent_tasks' => Task::with(['client', 'product', 'assignee'])
                                    ->when($periodStart, fn ($query) => $query->where('created_at', '>=', $periodStart))
                                    ->latest()
                                    ->take(5)
                                    ->get(),
                'dashboard_period' => $period,
                'dashboard_period_label' => $periodLabel,
                'dashboard_period_options' => $periodOptions,
            ]);
        }

        // 3. Jika user adalah member biasa, tampilkan MemberDashboard
        $memberTaskQuery = Task::query()->where('assigned_to', $user->id);
        $memberDeadlineColumns = ['tasks.id', 'tasks.title', 'tasks.client_id', 'tasks.status', 'tasks.release_date', 'tasks.category', 'tasks.created_at', 'tasks.completed_at'];
        $memberOverdueQuery = Task::query()
            ->where('assigned_to', $user->id)
            ->whereSlaOverdue();
        $memberDueSoonQuery = Task::query()
            ->where('assigned_to', $user->id)
            ->whereSlaDueSoon();

        return Inertia::render('Dashboard/MemberDashboard', [
            'stats' => [
                'total_tasks' => (clone $memberTaskQuery)->count(),
                'open_tasks' => (clone $memberTaskQuery)->where('status', 'open')->count(),
                'in_progress_tasks' => (clone $memberTaskQuery)->where('status', 'in_progress')->count(),
                'completed_tasks' => (clone $memberTaskQuery)->where('status', 'completed')->count(),
                'overdue_tasks' => (clone $memberOverdueQuery)->count(),
                'due_soon_tasks' => (clone $memberDueSoonQuery)->count(),
            ],
            'overdue_tasks' => (clone $memberOverdueQuery)
                ->with('client:id,name')
                ->select($memberDeadlineColumns)
                ->orderByRaw(Task::effectiveDeadlineExpression())
                ->limit(5)
                ->get(),
            'due_soon_tasks' => (clone $memberDueSoonQuery)
                ->with('client:id,name')
                ->select($memberDeadlineColumns)
                ->orderByRaw(Task::effectiveDeadlineExpression())
                ->limit(5)
                ->get(),
            'my_tasks' => Task::with(['client', 'product'])
                                ->where('assigned_to', $user->id)
                                ->whereNotIn('status', ['completed'])
                                ->latest()
                                ->take(5)
                                ->get(),
        ]);
    }
}
