@php
    $notifUser = auth()->user();
    $notifItems = $notifUser ? $notifUser->notifications()->latest()->limit(10)->get() : collect();
    $notifUnread = $notifUser ? $notifUser->unreadNotifications()->count() : 0;
@endphp

<div class="dropdown" id="notificationDropdown">
    <button type="button" class="navbar-action-btn position-relative" data-bs-toggle="dropdown" aria-expanded="false"
        title="Notifications">
        <i class="bi bi-bell"></i>
        <span class="action-badge bg-danger" data-notif-badge {{ $notifUnread > 0 ? '' : 'hidden' }}>{{ $notifUnread > 99 ? '99+' : $notifUnread }}</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end notification-dropdown shadow-lg border-0 mt-2">
        <li class="dropdown-header-bar d-flex justify-content-between align-items-center">
            <span>Notifications</span>
            <a href="#" class="text-white-50 small text-decoration-none" data-notif-mark-all>Mark all read</a>
        </li>
        <li>
            <div class="notification-list" data-notif-list>
                @foreach($notifItems as $n)
                    {{-- open() marks it read and redirects on this host. --}}
                    <a href="{{ route('notifications.open', $n->id) }}"
                        class="notification-item {{ $n->read_at ? '' : 'unread' }}" data-notif-item data-notif-id="{{ $n->id }}">
                        <span class="notification-item-icon"><i class="bi {{ $n->data['icon'] ?? 'bi-bell' }}"></i></span>
                        <span class="notification-item-body">
                            <span class="notification-item-title">{{ $n->data['title'] ?? 'Notification' }}</span>
                            <span class="notification-item-text">{{ $n->data['body'] ?? '' }}</span>
                            <span class="notification-item-time">{{ $n->created_at->diffForHumans() }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="notification-empty-state" data-notif-empty {{ $notifItems->isEmpty() ? '' : 'hidden' }}>
                <div class="d-flex flex-column align-items-center text-center py-4 px-3">
                    <div class="notification-empty-icon mb-2">
                        <i class="bi bi-bell-slash"></i>
                    </div>
                    <p class="text-white-50 small mb-0">You're all caught up!</p>
                    <small class="text-white-50 opacity-75" style="font-size: 0.7rem;">No new notifications.</small>
                </div>
            </div>
        </li>
        <li><a href="{{ route('notifications.index') }}" class="dropdown-footer-link">View all notifications</a></li>
    </ul>
</div>

<style>
    .notification-list { max-height: 360px; overflow-y: auto; }
    .notification-list::-webkit-scrollbar { width: 6px; }
    .notification-list::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.12); border-radius: 999px; }

    .notification-item {
        display: flex;
        gap: 10px;
        padding: 11px 16px;
        text-decoration: none;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        transition: background 0.2s ease;
    }
    .notification-item:last-child { border-bottom: 0; }
    .notification-item:hover { background: rgba(255, 255, 255, 0.05); }
    .notification-item.unread { background: rgba(255, 197, 8, 0.07); }
    .notification-item.unread:hover { background: rgba(255, 197, 8, 0.12); }
    .notification-item.unread .notification-item-title::after {
        content: '';
        display: inline-block;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #ffc508;
        margin-left: 6px;
        vertical-align: middle;
    }

    .notification-item-icon {
        flex-shrink: 0;
        width: 32px;
        height: 32px;
        border-radius: 9px;
        background: rgba(255, 255, 255, 0.06);
        color: #ffc508;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
    }
    .notification-item-body { display: flex; flex-direction: column; min-width: 0; }
    .notification-item-title {
        color: rgba(255, 255, 255, 0.92);
        font-size: 0.8rem;
        font-weight: 600;
        line-height: 1.25;
    }
    .notification-item-text {
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.74rem;
        line-height: 1.3;
        margin-top: 2px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    .notification-item-time {
        color: rgba(255, 255, 255, 0.4);
        font-size: 0.68rem;
        margin-top: 3px;
    }
</style>

<script>
    (function () {
        const root = document.getElementById('notificationDropdown');
        if (!root) return;

        const POLL_URL = @json(route('notifications.poll'));
        const READ_ALL_URL = @json(route('notifications.readAll'));
        const INDEX_URL = @json(route('notifications.index'));
        // Per reader, so two accounts sharing a browser keep separate tallies.
        const SEEN_KEY = 'fablab:notifications-seen:' + @json($notifUser?->id);
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const badge = root.querySelector('[data-notif-badge]');
        const list = root.querySelector('[data-notif-list]');
        const empty = root.querySelector('[data-notif-empty]');
        const markAll = root.querySelector('[data-notif-mark-all]');

        function esc(s) { const d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }

        function setBadge(count) {
            if (!badge) return;
            if (count > 0) { badge.textContent = count > 99 ? '99+' : count; badge.removeAttribute('hidden'); }
            else { badge.setAttribute('hidden', ''); }
        }

        function renderItems(items) {
            if (!list) return;
            if (!items || !items.length) {
                list.innerHTML = '';
                if (empty) empty.removeAttribute('hidden');
                return;
            }
            if (empty) empty.setAttribute('hidden', '');
            list.innerHTML = items.map(function (it) {
                return '<a href="' + esc(it.url || INDEX_URL) + '" class="notification-item ' + (it.read ? '' : 'unread') + '" data-notif-item data-notif-id="' + esc(it.id) + '">'
                    + '<span class="notification-item-icon"><i class="bi ' + esc(it.icon || 'bi-bell') + '"></i></span>'
                    + '<span class="notification-item-body">'
                    + '<span class="notification-item-title">' + esc(it.title) + '</span>'
                    + '<span class="notification-item-text">' + esc(it.body) + '</span>'
                    + '<span class="notification-item-time">' + esc(it.time) + '</span>'
                    + '</span></a>';
            }).join('');
        }

        function getJson(url) {
            if (window.axios) return window.axios.get(url).then(function (r) { return r.data; });
            return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } }).then(function (r) {
                // A lapsed session answers 401; treat it as no news rather
                // than as an empty list.
                if (!r.ok) throw new Error('poll ' + r.status);
                return r.json();
            });
        }

        function post(url) {
            if (window.axios) return window.axios.post(url);
            return fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
        }

        // ---- Toasts --------------------------------------------------------
        // Which notifications this reader has already been shown, kept in
        // localStorage so moving between pages or tabs never repeats a toast.
        let seenInMemory = null;

        function readSeen() {
            try {
                const raw = window.localStorage.getItem(SEEN_KEY);
                return raw ? JSON.parse(raw) : null;
            } catch (e) {
                return seenInMemory;
            }
        }

        function writeSeen(ids) {
            seenInMemory = ids;
            try { window.localStorage.setItem(SEEN_KEY, JSON.stringify(ids)); } catch (e) { /* private window: this page only */ }
        }

        // Toast every unread notification this reader hasn't been shown yet.
        // The first poll a browser ever makes only records the list as it
        // stands, so an old backlog never bursts onto the screen at once.
        function toastArrivals(items) {
            const ids = items.map(function (it) { return it.id; });
            const seen = readSeen();

            if (!Array.isArray(seen)) {
                writeSeen(ids);
                return;
            }

            const fresh = items.filter(function (it) { return !it.read && seen.indexOf(it.id) === -1; });
            writeSeen(ids.concat(seen.filter(function (id) { return ids.indexOf(id) === -1; })).slice(0, 100));

            if (!fresh.length || typeof window.showNotificationToast !== 'function') return;

            // More than three at once becomes one "and N more" toast on top.
            const extra = fresh.length - 3;
            if (extra > 0) {
                window.showNotificationToast({
                    title: extra + ' more new notification' + (extra === 1 ? '' : 's'),
                    body: 'Open the bell to see them all.',
                    icon: 'bi-bell',
                    url: INDEX_URL,
                    category_label: 'Notifications',
                });
            }

            // Oldest first, so the newest lands nearest the corner.
            fresh.slice(0, 3).reverse().forEach(function (it) { window.showNotificationToast(it); });
        }

        // The Stock Monitoring badge, in both copies of the sidebar. Customers'
        // polls carry no count, and their sidebar has no badge.
        function setStockBadge(count) {
            if (typeof count !== 'number') return;
            document.querySelectorAll('[data-stock-badge]').forEach(function (stockBadge) {
                const number = stockBadge.querySelector('[data-stock-count]');
                if (number) number.textContent = count > 99 ? '99+' : count;
                stockBadge.toggleAttribute('hidden', count <= 0);
            });
        }

        function poll() {
            getJson(POLL_URL).then(function (data) {
                setBadge(data.unread_count || 0);
                renderItems(data.items || []);
                setStockBadge(data.stock_watch);
                toastArrivals(data.items || []);
            }).catch(function () { /* ignore transient errors */ });
        }

        // Items are plain links to notifications.open, which marks the
        // notification read on the way through — nothing to intercept.

        if (markAll) {
            markAll.addEventListener('click', function (e) {
                e.preventDefault();
                post(READ_ALL_URL).then(function () {
                    setBadge(0);
                    root.querySelectorAll('.notification-item.unread').forEach(function (el) { el.classList.remove('unread'); });
                }).catch(function () {});
            });
        }

        window.refreshNotifications = poll;

        // Wait for the page: the toast renderer and Bootstrap both load at
        // the bottom of the layout, after this navbar.
        function start() {
            poll();
            setInterval(poll, 30000);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', start);
        } else {
            start();
        }

        // Coming back to the tab catches up now, not at the next tick.
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') poll();
        });
    })();
</script>
