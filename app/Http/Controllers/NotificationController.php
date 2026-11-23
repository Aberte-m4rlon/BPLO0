<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markAllRead(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user !== null) {
            Notification::query()
                ->where('user_id', $user->getKey())
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }
}
