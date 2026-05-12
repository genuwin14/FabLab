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
                    <a href="{{ $n->data['url'] ?? route('notifications.index') }}"
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
        const READ_URL_TEMPLATE = @json(route('notifications.read', ['id' => '__ID__']));
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
                return '<a href="' + (it.url || INDEX_URL) + '" class="notification-item ' + (it.read ? '' : 'unread') + '" data-notif-item data-notif-id="' + esc(it.id) + '">'
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
            return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } }).then(function (r) { return r.json(); });
        }

        function post(url) {
            if (window.axios) return window.axios.post(url);
            return fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
        }

        function poll() {
            getJson(POLL_URL).then(function (data) {
                setBadge(data.unread_count || 0);
                renderItems(data.items || []);
            }).catch(function () { /* ignore transient errors */ });
        }

        if (list) {
            list.addEventListener('click', function (e) {
                const a = e.target.closest('[data-notif-item]');
                if (!a) return;
                e.preventDefault();
                const id = a.getAttribute('data-notif-id');
                const href = a.getAttribute('href') || INDEX_URL;
                const go = function () { window.location.href = href; };
                post(READ_URL_TEMPLATE.replace('__ID__', encodeURIComponent(id))).then(go).catch(go);
            });
        }

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
        poll();
        setInterval(poll, 30000);
    })();
</script>
