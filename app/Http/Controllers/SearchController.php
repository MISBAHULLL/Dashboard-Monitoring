<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Document;
use App\Models\Task;
use App\Models\Team;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = trim($request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $user = $request->user();
        $searchTerm = "%{$query}%";
        $results = [];

        // 1. Search Tasks
        $taskQuery = Task::with(['client:id,name', 'product:id,name'])
            ->select('id', 'title', 'modul', 'status', 'client_id', 'assigned_to')
            ->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('modul', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });

        if ($user->isMember()) {
            $taskQuery->where('assigned_to', $user->id);
        }

        $tasks = $taskQuery->latest()->limit(8)->get();

        if ($tasks->isNotEmpty()) {
            $results[] = [
                'label' => 'Tasks',
                'icon' => 'ListTodo',
                'items' => $tasks->map(fn ($task) => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'subtitle' => $task->client?->name ?? '-',
                    'meta' => $task->modul,
                    'type' => 'task',
                    'status' => $task->status,
                    'url' => route('tasks.show', $task->id),
                ])->toArray(),
            ];
        }

        // 2. Search Clients (Admin only)
        if ($user->isAdmin()) {
            $clients = Client::select('id', 'name', 'city', 'type')
                ->where('name', 'like', $searchTerm)
                ->orWhere('city', 'like', $searchTerm)
                ->latest()
                ->limit(5)
                ->get();

            if ($clients->isNotEmpty()) {
                $results[] = [
                    'label' => 'Faskes / Clients',
                    'icon' => 'Building2',
                    'items' => $clients->map(fn ($client) => [
                        'id' => $client->id,
                        'title' => $client->name,
                        'subtitle' => $client->city ?? '-',
                        'meta' => $client->type,
                        'type' => 'client',
                        'url' => '/clients',
                    ])->toArray(),
                ];
            }

            // 3. Search Teams (Admin only)
            $teams = Team::select('id', 'name', 'type')
                ->where('name', 'like', $searchTerm)
                ->latest()
                ->limit(5)
                ->get();

            if ($teams->isNotEmpty()) {
                $results[] = [
                    'label' => 'Tim',
                    'icon' => 'UsersRound',
                    'items' => $teams->map(fn ($team) => [
                        'id' => $team->id,
                        'title' => $team->name,
                        'subtitle' => $team->type,
                        'meta' => null,
                        'type' => 'team',
                        'url' => '/teams',
                    ])->toArray(),
                ];
            }

            // 4. Search Documents (Admin only)
            $documents = Document::with('client:id,name')
                ->select('id', 'title', 'type', 'client_id')
                ->where('title', 'like', $searchTerm)
                ->latest()
                ->limit(5)
                ->get();

            if ($documents->isNotEmpty()) {
                $results[] = [
                    'label' => 'Dokumen',
                    'icon' => 'FileText',
                    'items' => $documents->map(fn ($doc) => [
                        'id' => $doc->id,
                        'title' => $doc->title,
                        'subtitle' => $doc->client?->name ?? '-',
                        'meta' => $doc->type,
                        'type' => 'document',
                        'url' => '/clients',
                    ])->toArray(),
                ];
            }
        }

        return response()->json($results);
    }
}
