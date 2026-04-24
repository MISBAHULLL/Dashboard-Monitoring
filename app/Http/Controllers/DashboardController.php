<?php

namespace App\Http\Controllers;

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

        // 1. Data statistik umum
        $stats = [
            'total_tasks' => Task::count(),
            'open_tasks' => Task::where('status', 'open')->count(),
            'in_progress_tasks' => Task::where('status', 'in_progress')->count(),
            'completed_tasks' => Task::where('status', 'completed')->count(),
            'total_clients' => Client::count(),
            'total_teams' => Team::count(),
        ];

        // 2. Jika user adalah admin, tampilkan AdminDashboard
        if ($user->isAdmin()) {
            
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

            return Inertia::render('Dashboard/AdminDashboard', [
                'stats' => $stats,
                'chart_donut' => $chartDonut,
                'chart_area' => $chartArea,
                'recent_tasks' => Task::with(['client', 'product', 'assignee'])
                                    ->latest()
                                    ->take(5)
                                    ->get(),
            ]);
        }

        // 3. Jika user adalah member biasa, tampilkan MemberDashboard
        return Inertia::render('Dashboard/MemberDashboard', [
            'stats' => $stats, // Nanti bisa difilter khusus task milik member tersebut
            'my_tasks' => Task::with(['client', 'product'])
                                ->where('assigned_to', $user->id)
                                ->whereNotIn('status', ['completed'])
                                ->latest()
                                ->take(5)
                                ->get(),
        ]);
    }
}
