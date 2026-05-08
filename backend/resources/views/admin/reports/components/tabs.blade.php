<div class="d-flex align-items-end justify-content-between flex-wrap gap-3 mb-3 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold text-dark mb-0">Reports</h4>
        <p class="text-muted small mb-0">As of {{ $asOfDate->format('F j, Y') }}</p>
    </div>
    <ul class="nav nav-tabs report-tabs border-0 mb-0" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request()->routeIs('admin.reports.materials') ? 'active' : '' }}"
               href="{{ route('admin.reports.materials') }}">
                <i class="bi bi-clipboard-data me-1"></i>
                <span>Materials</span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request()->routeIs('admin.reports.equipment') ? 'active' : '' }}"
               href="{{ route('admin.reports.equipment') }}">
                <i class="bi bi-tools me-1"></i>
                <span>Machinery & Equipment</span>
            </a>
        </li>
    </ul>
</div>

<style>
    .report-tabs { border-bottom: none !important; }
    .report-tabs .nav-link {
        color: #6c757d;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 0.55rem 1.1rem;
        border: 1px solid transparent;
        border-bottom: 3px solid transparent;
        border-radius: 0;
        background: transparent;
        transition: color 0.15s ease, border-color 0.15s ease;
    }
    .report-tabs .nav-link:hover {
        color: #0e2e45;
        border-bottom-color: rgba(255, 197, 8, 0.4);
    }
    .report-tabs .nav-link.active {
        color: #0e2e45;
        background-color: transparent;
        border-color: transparent;
        border-bottom-color: #ffc508;
    }
</style>
