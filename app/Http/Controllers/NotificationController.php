<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markAsRead(Request $request, Notification $notification): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($notification->user_id === $user->id, 403);

        if (! $notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        if ($request->boolean('visit') && $notification->link) {
            return redirect()->to($notification->link);
        }

        return back();
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('dismissed_at')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back();
    }

    public function dismissRead(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        Notification::query()
            ->where('user_id', $user->id)
            ->where('is_read', true)
            ->whereNull('dismissed_at')
            ->update(['dismissed_at' => now()]);

        return back();
    }

    public function dismissAll(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('dismissed_at')
            ->update([
                'is_read' => true,
                'dismissed_at' => now(),
            ]);

        return back();
    }
}
