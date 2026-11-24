{{-- The low-stock alert, at the top of every admin and staff page until it is
     dismissed (the panel's wording). It names what is low right now and any
     unread stock alert (App\Support\StockAlerts); dismissing it is stored, so
     it stays gone on every page and device until something new runs low. The
     bell's poll redraws it through window.renderStockAlerts, so an alert
     raised while the page is open appears without a reload. --}}
@php
    $stockAlertFeed = app(\App\Support\StockAlerts::class)->for(auth()->user());
    $stockAlertReviewUrl = route((auth()->user()?->role === 'admin' ? 'admin' : 'staff') . '.inventory.index');
@endphp
<div class="stock-alert-banner {{ $stockAlertFeed['out_of_stock'] ? 'is-danger' : '' }}" data-stock-alert-banner
    role="alert" @if($stockAlertFeed['count'] === 0) hidden @endif>
    <div class="container-fluid px-3 px-md-4 d-flex align-items-start gap-2 gap-md-3">
        <span class="stock-alert-icon" aria-hidden="true"><i class="bi bi-exclamation-triangle-fill"></i></span>

        <div class="flex-grow-1 min-w-0">
            <div class="stock-alert-heading" data-stock-alert-summary>
                @if($stockAlertFeed['count'] === 1)
                    Stock alert: 1 item needs restocking
                @else
                    Stock alert: {{ $stockAlertFeed['count'] }} items need restocking
                @endif
            </div>
            <ul class="stock-alert-list" data-stock-alert-list>
                @foreach($stockAlertFeed['items'] as $alert)
                    <li class="is-{{ $alert['tone'] }}">
                        <strong>{{ $alert['title'] }}</strong>
                        <span>{{ $alert['body'] }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="stock-alert-more" data-stock-alert-more @if($stockAlertFeed['more'] === 0) hidden @endif>
                and {{ $stockAlertFeed['more'] }} more
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <a href="{{ $stockAlertReviewUrl }}" class="btn btn-sm stock-alert-review">Review stock</a>
            <button type="button" class="btn-close stock-alert-close" data-stock-alert-dismiss
                aria-label="Dismiss the stock alert" title="Dismiss"></button>
        </div>
    </div>
</div>

@once
    <script>
        (function () {
            const banner = document.querySelector('[data-stock-alert-banner]');
            if (!banner) return;

            const DISMISS_URL = @json(route('notifications.stockAlerts.dismiss'));
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const summary = banner.querySelector('[data-stock-alert-summary]');
            const list = banner.querySelector('[data-stock-alert-list]');
            const more = banner.querySelector('[data-stock-alert-more]');
            const dismiss = banner.querySelector('[data-stock-alert-dismiss]');

            // Text, never markup: item names are typed in by staff.
            window.renderStockAlerts = function (feed) {
                if (!feed) return;

                if (!feed.count) {
                    banner.hidden = true;
                    return;
                }

                summary.textContent = 'Stock alert: ' + feed.count + (feed.count === 1 ? ' item needs' : ' items need') + ' restocking';
                list.replaceChildren.apply(list, (feed.items || []).map(function (item) {
                    const li = document.createElement('li');
                    li.className = 'is-' + (item.tone || 'warning');
                    const title = document.createElement('strong');
                    title.textContent = item.title || '';
                    const body = document.createElement('span');
                    body.textContent = item.body || '';
                    li.append(title, ' ', body);
                    return li;
                }));
                more.textContent = 'and ' + feed.more + ' more';
                more.hidden = !(feed.more > 0);
                banner.classList.toggle('is-danger', !!feed.out_of_stock);
                banner.hidden = false;
            };

            dismiss.addEventListener('click', function () {
                dismiss.disabled = true;
                fetch(DISMISS_URL, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                }).then(function (response) {
                    if (!response.ok) throw new Error('dismiss ' + response.status);
                    banner.hidden = true;
                    // The alerts are read now; let the bell's count say so.
                    if (typeof window.refreshNotifications === 'function') window.refreshNotifications();
                }).catch(function () {
                    // Still shown: a failed dismiss must not look like a done one.
                }).finally(function () {
                    dismiss.disabled = false;
                });
            });
        })();
    </script>

    <style>
        .stock-alert-banner {
            --stock-alert-tone: #ffc508;
            --stock-alert-ink: #6b4f00;
            background: #fff8e0;
            border-bottom: 1px solid rgba(255, 197, 8, 0.55);
            border-left: 4px solid var(--stock-alert-tone);
            color: #0e2e45;
            padding: 0.6rem 0;
        }
        .stock-alert-banner.is-danger {
            --stock-alert-tone: #dc3545;
            --stock-alert-ink: #9c1c28;
            background: #fdecee;
            border-bottom-color: rgba(220, 53, 69, 0.45);
        }
        .stock-alert-banner[hidden] { display: none !important; }

        .stock-alert-icon {
            color: var(--stock-alert-tone);
            font-size: 1.15rem;
            line-height: 1.3;
            flex-shrink: 0;
        }
        .stock-alert-heading {
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--stock-alert-ink);
        }
        .stock-alert-list {
            list-style: none;
            margin: 0.15rem 0 0;
            padding: 0;
            font-size: 0.8rem;
        }
        .stock-alert-list li {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .stock-alert-list li strong { font-weight: 600; }
        .stock-alert-list li.is-danger strong { color: #b02a37; }
        .stock-alert-list li span { color: rgba(14, 46, 69, 0.75); }
        .stock-alert-more {
            font-size: 0.75rem;
            color: rgba(14, 46, 69, 0.6);
            margin-top: 0.1rem;
        }
        .stock-alert-more[hidden] { display: none; }

        .stock-alert-review {
            background: #0e2e45;
            color: #fff;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.3rem 0.85rem;
            white-space: nowrap;
        }
        .stock-alert-review:hover { background: #05111a; color: #ffc508; }
        .stock-alert-close { font-size: 0.7rem; }

        @media (max-width: 575.98px) {
            .stock-alert-list li { white-space: normal; }
            .stock-alert-review { padding: 0.25rem 0.6rem; }
        }
    </style>
@endonce
