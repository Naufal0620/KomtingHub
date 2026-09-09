<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __invoke(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(resolvePerPage($request, 10))
            ->withQueryString();

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return back();
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }

    public function destroy(Request $request, string $notification)
    {
        $request->user()->notifications()->whereKey($notification)->delete();

        return back();
    }

    public function clearAll(Request $request)
    {
        $request->user()->notifications()->delete();

        return back();
    }
}
