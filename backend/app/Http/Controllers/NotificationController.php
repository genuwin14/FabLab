<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NotificationController extends Controller
{
    /**
     * Full notifications page (role-aware view).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = $user->notifications()->paginate(20);
        $unreadCount = $user->unreadNotifications()->count();

        $view = match ($user->role) {
            'admin' => 'admin.notifications.index',
            'staff' => 'staff.notifications.index',
            default => 'customer.notifications.index',
        };

        return view($view, compact('notifications', 'unreadCount'));
    }

    /**
     * JSON feed polled by the navbar bell.
     */
    public function poll(Request $request)
    {
        $user = $request->user();

        $items = $user->notifications()->latest()->limit(10)->get()->map(function ($n) {
            return [
                'id' => $n->id,
                'title' => $n->data['title'] ?? 'Notification',
                'body' => $n->data['body'] ?? '',
                'icon' => $n->data['icon'] ?? 'bi-bell',
                // Through open(), never the stored link itself: see Notifier::link().
                'url' => route('notifications.open', $n->id),
                'category' => $n->data['category'] ?? 'general',
                'time' => $n->created_at->diffForHumans(),
                'read' => $n->read_at !== null,
            ];
        });

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'items' => $items,
        ]);
    }

    /**
     * Follow a notification: mark it read and go where it points.
     *
     * Every link to a notification's page comes through here — the bell,
     * the notifications list, the toasts — so the redirect is always built
     * on the host the reader is signed in on, whatever was stored.
     */
    public function open(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect()->to(\App\Support\Notifier::link($notification->data['url'] ?? null));
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['unread_count' => $request->user()->unreadNotifications()->count()]);
        }

        return back();
    }

    /**
     * Mark every unread notification as read.
     */
    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['unread_count' => 0]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, string $id)
    {
        $request->user()->notifications()->findOrFail($id)->delete();

        if ($request->expectsJson()) {
            return response()->json(['unread_count' => $request->user()->unreadNotifications()->count()]);
        }

        return back()->with('success', 'Notification removed.');
    }
}
