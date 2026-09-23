<?php

namespace App\Http\Controllers;

use App\Notifications\OutOfStockAlert;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
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

        $items = $user->notifications()->latest()->limit(10)->get()
            ->map(fn (DatabaseNotification $n) => $this->present($n, (string) $user->role));

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'items' => $items,
        ]);
    }

    /**
     * One notification as the bell's dropdown and its toasts draw it.
     */
    private function present(DatabaseNotification $n, string $role): array
    {
        $category = $n->data['category'] ?? 'general';

        return [
            'id' => $n->id,
            'title' => $n->data['title'] ?? 'Notification',
            'body' => $n->data['body'] ?? '',
            'icon' => $n->data['icon'] ?? 'bi-bell',
            // Through open(), never the stored link itself: see Notifier::link().
            'url' => route('notifications.open', $n->id),
            'category' => $category,
            // The page it is about, as the reader's own sidebar names it.
            'category_label' => match ($category) {
                'order' => match ($role) { 'admin' => 'All Orders', 'staff' => 'Orders', default => 'My Orders' },
                'stock' => $role === 'staff' ? 'Inventory Logs' : 'Stock Monitoring',
                'purchase' => 'Purchase Orders',
                'user' => $role === 'admin' ? 'Users' : 'Accounts',
                'design' => 'Custom Design',
                default => 'Notification',
            },
            // How loudly a toast says it: running out is worse than running low.
            'tone' => match (true) {
                $n->type === OutOfStockAlert::class => 'danger',
                $category === 'stock' => 'warning',
                default => 'info',
            },
            'time' => $n->created_at->diffForHumans(),
            'read' => $n->read_at !== null,
        ];
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
