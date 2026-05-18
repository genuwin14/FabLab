@php
    $categoryMeta = [
        'order'    => ['label' => 'Order',         'class' => 'bg-primary-subtle text-primary-emphasis'],
        'stock'    => ['label' => 'Inventory',     'class' => 'bg-warning-subtle text-warning-emphasis'],
        'purchase' => ['label' => 'Procurement',   'class' => 'bg-info-subtle text-info-emphasis'],
        'user'     => ['label' => 'Account',       'class' => 'bg-success-subtle text-success-emphasis'],
        'design'   => ['label' => 'Custom Design', 'class' => 'bg-secondary-subtle text-secondary-emphasis'],
        'general'  => ['label' => 'General',       'class' => 'bg-light text-muted'],
    ];

    $bucketFor = function ($date) {
        $date = \Illuminate\Support\Carbon::parse($date);
        if ($date->isToday())                       return 'Today';
        if ($date->isYesterday())                   return 'Yesterday';
        if ($date->greaterThanOrEqualTo(now()->startOfWeek()))  return 'Earlier this week';
        if ($date->greaterThanOrEqualTo(now()->startOfMonth())) return 'Earlier this month';
        return 'Older';
    };

    $grouped = $notifications->getCollection()->groupBy(fn ($n) => $bucketFor($n->created_at));
@endphp

<div class="container-fluid notif-page">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h5 class="fw-bold mb-0 text-primary">Notifications</h5>
            <small class="text-muted">
                {{ $unreadCount > 0 ? $unreadCount . ' unread' : 'You\'re all caught up' }}
            </small>
        </div>
        @if($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.readAll') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-check2-all me-1"></i>Mark all read
                </button>
            </form>
        @endif
    </div>

    @forelse($grouped as $bucket => $items)
        <div class="text-uppercase text-muted fw-bold small mb-2 {{ $loop->first ? '' : 'mt-4' }}"
            style="letter-spacing: 0.06em;">{{ $bucket }}</div>
        <div class="card border-0 shadow-sm mb-2">
            <div class="list-group list-group-flush">
                @foreach($items as $n)
                    @php
                        $data = $n->data;
                        $cat = $data['category'] ?? 'general';
                        $meta = $categoryMeta[$cat] ?? $categoryMeta['general'];
                    @endphp
                    <div class="list-group-item d-flex gap-3 align-items-start {{ $n->read_at ? '' : 'bg-light' }}">
                        <div class="rounded d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width: 40px; height: 40px; background-color: #0e2e45; color: #ffc508;">
                            <i class="bi {{ $data['icon'] ?? 'bi-bell' }}"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <a href="{{ $data['url'] ?? '#' }}" class="fw-semibold text-decoration-none text-dark">
                                    {{ $data['title'] ?? 'Notification' }}
                                </a>
                                <span class="badge rounded-pill {{ $meta['class'] }}" style="font-size: 0.65rem;">{{ $meta['label'] }}</span>
                                @unless($n->read_at)
                                    <span class="badge rounded-pill bg-danger" style="font-size: 0.6rem;">New</span>
                                @endunless
                            </div>
                            <div class="text-muted small">{{ $data['body'] ?? '' }}</div>
                            <div class="text-muted" style="font-size: 0.72rem;">{{ $n->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="d-flex flex-column gap-1 flex-shrink-0">
                            @unless($n->read_at)
                                <form method="POST" action="{{ route('notifications.read', $n->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-link text-decoration-none p-0" title="Mark as read">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                </form>
                            @endunless
                            <form method="POST" action="{{ route('notifications.destroy', $n->id) }}" class="notif-delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-link text-danger text-decoration-none p-0 notif-delete-btn"
                                    title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm">
            <div class="list-group list-group-flush">
                <div class="list-group-item text-center text-muted py-5">
                    <i class="bi bi-bell-slash fs-3 d-block mb-2"></i>
                    No notifications yet.
                </div>
            </div>
        </div>
    @endforelse

    @if($notifications->hasPages())
        <div class="mt-3">
            {{ $notifications->links() }}
        </div>
    @endif
</div>

<!-- Delete Notification Confirmation Modal -->
<div class="modal fade notification-delete-modal" id="deleteNotificationModal" tabindex="-1"
    aria-labelledby="deleteNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered notification-delete-modal-dialog">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Header -->
            <div class="notification-delete-modal-header">
                <div class="notification-delete-modal-icon">
                    <i class="bi bi-trash3-fill"></i>
                </div>
                <h5 class="modal-title fw-bold mb-1 text-white" id="deleteNotificationModalLabel">Remove Notification</h5>
                <p class="text-white-50 small mb-0">This action cannot be undone</p>
            </div>

            <!-- Confirmation Body -->
            <div class="modal-body p-4 notification-delete-modal-body">
                <p class="text-dark mb-0 text-center">
                    Are you sure you want to remove this notification?
                </p>
            </div>

            <!-- Footer with actions -->
            <div class="notification-delete-modal-footer">
                <button type="button" class="btn fw-semibold rounded-pill px-4 notification-delete-cancel-btn"
                    data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn fw-semibold rounded-pill px-4 notification-delete-confirm-btn"
                    id="confirmDeleteNotificationBtn">
                    <i class="bi bi-trash3 me-2"></i>Remove
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .notification-delete-modal-dialog { max-width: 420px; }
    .notification-delete-modal .modal-content { border-radius: 18px; }

    .notification-delete-modal-header {
        background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
        padding: 28px 24px 20px;
        text-align: center;
        position: relative;
    }
    .notification-delete-modal-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(220, 53, 69, 0.4), transparent);
    }
    .notification-delete-modal-icon {
        width: 56px;
        height: 56px;
        margin: 0 auto 14px;
        border-radius: 16px;
        background: rgba(220, 53, 69, 0.15);
        border: 1px solid rgba(220, 53, 69, 0.3);
        color: #ff8088;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .notification-delete-modal-body { background-color: #fff; }

    .notification-delete-modal-footer {
        background-color: #fff;
        padding: 16px 24px 24px;
        display: flex;
        justify-content: center;
        gap: 10px;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
    }

    .notification-delete-cancel-btn {
        background-color: #f1f4f8;
        border: 1px solid #e9ecef;
        color: #6c757d;
        transition: all 0.2s ease;
    }
    .notification-delete-cancel-btn:hover {
        background-color: #e9ecef;
        color: #0e2e45;
    }

    .notification-delete-confirm-btn {
        background-color: #dc3545;
        border: 1px solid #dc3545;
        color: #fff;
        transition: all 0.2s ease;
    }
    .notification-delete-confirm-btn:hover {
        background-color: #b02a37;
        border-color: #b02a37;
        color: #fff;
    }

    /* ── Mobile ( < sm ) — ResponsiveMobileNote.md §2 + §6 ── */
    @media (max-width: 575.98px) {
        /* List rows: tighter padding so more room for the message text. */
        .notif-page .list-group-item { padding: 0.75rem 0.85rem; gap: 0.65rem !important; }
        .notif-page .list-group-item .fw-semibold { font-size: 0.9rem; }

        /* Delete-confirm modal: smaller type/spacing, stacked buttons. */
        .notification-delete-modal-dialog { margin: 0.5rem; }
        .notification-delete-modal .modal-title { font-size: 1rem; }
        .notification-delete-modal-header { padding: 22px 18px 16px; }
        .notification-delete-modal-body { padding: 1rem !important; font-size: 0.9rem; }
        .notification-delete-modal-footer {
            padding: 12px 16px 18px;
            flex-direction: column;
        }
        .notification-delete-modal-footer .btn { width: 100%; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('deleteNotificationModal');
        if (!modalEl || typeof bootstrap === 'undefined') return;

        const modal = new bootstrap.Modal(modalEl);
        const confirmBtn = document.getElementById('confirmDeleteNotificationBtn');
        let pendingForm = null;

        document.querySelectorAll('.notif-delete-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                pendingForm = btn.closest('.notif-delete-form');
                modal.show();
            });
        });

        if (confirmBtn) {
            confirmBtn.addEventListener('click', function () {
                if (pendingForm) pendingForm.submit();
            });
        }

        modalEl.addEventListener('hidden.bs.modal', function () { pendingForm = null; });
    });
</script>
