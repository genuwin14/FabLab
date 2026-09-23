{{-- The Stock Monitoring count beside its sidebar link: items at or below
     their low-stock line (App\Services\StockWatch). The sidebar is drawn
     twice per page — the desktop rail and the phone drawer — and the bell's
     poll keeps every copy current through [data-stock-badge]. --}}
@php $stockWatchCount = app(\App\Services\StockWatch::class)->count(); @endphp
<span class="sidebar-count-badge" data-stock-badge @if($stockWatchCount === 0) hidden @endif>
    <span data-stock-count>{{ $stockWatchCount > 99 ? '99+' : $stockWatchCount }}</span>
    <span class="visually-hidden">items at or below their low-stock level</span>
</span>

@once
    <style>
        .sidebar-inner .nav-link:has(> .sidebar-count-badge) { position: relative; }

        .sidebar-count-badge {
            margin-left: auto;
            flex-shrink: 0;
            min-width: 1.4rem;
            padding: 0.2rem 0.45rem;
            border-radius: 999px;
            background-color: #dc3545;
            color: #fff;
            font-size: 0.66rem;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            box-shadow: 0 0 0 2px #05111a;
        }

        .sidebar-count-badge[hidden] { display: none !important; }

        /* Collapsed rail: the label is gone, so the count sits on the icon's
           corner — also while the rail is still preloading collapsed. */
        .sidebar-inner.sidebar-collapsed .sidebar-count-badge,
        html.sidebar-preload-collapsed .sidebar-inner .sidebar-count-badge {
            position: absolute;
            top: 1px;
            right: -2px;
            margin: 0;
            min-width: 1.05rem;
            padding: 0.14rem 0.3rem;
            font-size: 0.55rem;
        }
    </style>
@endonce
