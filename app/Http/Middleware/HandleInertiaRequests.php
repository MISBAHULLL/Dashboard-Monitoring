<?php

namespace App\Http\Middleware;

use App\Models\Notification;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

        /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'theme' => $user->theme,
                    // Kita bisa tambahkan load() kalau butuh nama team
                    // 'team' => $request->user()->team?->name, 
                ] : null,
            ],
            'notifications' => $user ? function () use ($user) {
                return [
                    'unread_count' => Notification::query()
                        ->where('user_id', $user->id)
                        ->where('is_read', false)
                        ->count(),
                    'items' => Notification::query()
                        ->where('user_id', $user->id)
                        ->latest()
                        ->limit(8)
                        ->get(['id', 'type', 'title', 'body', 'link', 'is_read', 'created_at'])
                        ->map(fn (Notification $notification) => [
                            'id' => $notification->id,
                            'type' => $notification->type,
                            'title' => $notification->title,
                            'body' => $notification->body,
                            'link' => $notification->link,
                            'is_read' => $notification->is_read,
                            'created_at' => $notification->created_at?->toIso8601String(),
                        ])
                        ->values(),
                ];
            } : [
                'unread_count' => 0,
                'items' => [],
            ],
            // Kita juga bisa kirim pesan flash session untuk Toast notifications nanti
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
                'info' => $request->session()->get('info'),
            ],
        ];
    }
}
