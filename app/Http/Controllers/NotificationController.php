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

        return back();
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        Notification::query()
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back();
    }
}
