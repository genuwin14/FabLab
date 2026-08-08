{{--
    Raw Materials page tabs. Shared by the admin and staff screens; the caller
    passes $routePrefix ('admin' | 'staff') and $activeTab ('materials' | 'log').

    Both panes are rendered server-side, so ?tab=log is a working deep link —
    which is what the controller redirects to after recording usage.
--}}
<ul class="nav nav-tabs material-tabs mb-3" id="rawMaterialTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button type="button" class="nav-link {{ $activeTab === 'materials' ? 'active' : '' }} d-flex align-items-center gap-2"
            id="tab-materials" data-bs-toggle="tab" data-bs-target="#pane-materials"
            data-tab-key="materials" role="tab" aria-controls="pane-materials"
            aria-selected="{{ $activeTab === 'materials' ? 'true' : 'false' }}">
            <i class="bi bi-box-seam"></i>
            <span>Materials</span>
            <span class="material-tab-count">{{ number_format($rawMaterials->total()) }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button type="button" class="nav-link {{ $activeTab === 'log' ? 'active' : '' }} d-flex align-items-center gap-2"
            id="tab-log" data-bs-toggle="tab" data-bs-target="#pane-log"
            data-tab-key="log" role="tab" aria-controls="pane-log"
            aria-selected="{{ $activeTab === 'log' ? 'true' : 'false' }}">
            <i class="bi bi-clock-history"></i>
            <span>Usage Log</span>
            <span class="material-tab-count">{{ number_format($movements->total()) }}</span>
        </button>
    </li>
</ul>
