@extends('layout.app')

@section('content')
    <div class="d-flex vh-100" style="background-color: #f8f9fa; overflow: hidden;">
        <aside class="d-none d-md-block border-end border-white border-opacity-10 shadow-sm position-fixed top-0 start-0 h-100"
            style="width: 280px; z-index: 1040;">
            @include('admin.partials.sidebar')
        </aside>
        <div class="d-none d-md-block sidebar-spacer flex-shrink-0" style="width: 280px;"></div>

        <div class="offcanvas offcanvas-start border-0" tabindex="-1" id="adminSidebarOffcanvas"
            aria-labelledby="adminSidebarOffcanvasLabel" style="width: 280px; background-color: #0e2e45;">
            <div class="offcanvas-body p-0 overflow-hidden">
                @include('admin.partials.sidebar')
            </div>
        </div>

        <div class="flex-grow-1 d-flex flex-column" style="background-color: #f1f4f8; overflow: hidden;">
            <header class="flex-shrink-0 bg-white shadow-sm" style="z-index: 1042;">
                @include('admin.partials.navbar')
            </header>

            <main class="flex-grow-1 p-4" style="overflow-y: auto;">
                <div class="container-fluid">
                    @include('admin.reports.components.tabs')

                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3">
                            <form method="GET" action="{{ route('admin.reports.equipment') }}"
                                class="d-flex flex-wrap align-items-center gap-2">
                                <select name="status" class="form-select rounded-2 w-auto"
                                    onchange="this.form.submit()">
                                    <option value="">All Statuses</option>
                                    <option value="Serviceable" {{ $status === 'Serviceable' ? 'selected' : '' }}>Serviceable</option>
                                    <option value="Non-Serviceable" {{ $status === 'Non-Serviceable' ? 'selected' : '' }}>Non-Serviceable</option>
                                    <option value="Functional" {{ $status === 'Functional' ? 'selected' : '' }}>Functional</option>
                                    <option value="Returned to supplier for repair" {{ $status === 'Returned to supplier for repair' ? 'selected' : '' }}>Returned for Repair</option>
                                </select>
                                <div class="ms-auto d-flex gap-2">
                                    <a href="{{ route('admin.reports.equipment.pdf', ['status' => $status]) }}"
                                        class="btn btn-danger d-flex align-items-center gap-2 rounded-2 px-3">
                                        <i class="bi bi-file-pdf"></i>
                                        <span class="small fw-bold">Export PDF</span>
                                    </a>
                                    <a href="{{ route('admin.reports.equipment.docx', ['status' => $status]) }}"
                                        class="btn btn-primary d-flex align-items-center gap-2 rounded-2 px-3">
                                        <i class="bi bi-file-word"></i>
                                        <span class="small fw-bold">Export Word</span>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr class="bg-primary bg-opacity-10">
                                            <th class="ps-4 py-3 text-primary small text-uppercase fw-bold border-0">Equipment</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Brand</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Property No.</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Date Acquired</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0 text-end">Cost</th>
                                            <th class="pe-4 py-3 text-primary small text-uppercase fw-bold border-0">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @forelse($rows as $row)
                                            <tr>
                                                <td class="ps-4 py-2 fw-semibold text-dark">{{ $row['name'] }}</td>
                                                <td class="text-muted small">{{ $row['brand'] ?: '—' }}</td>
                                                <td class="font-monospace small">{{ $row['property_no'] ?: '—' }}</td>
                                                <td class="text-muted small">{{ $row['date_acquired'] ? $row['date_acquired']->format('M j, Y') : '—' }}</td>
                                                <td class="text-end fw-bold text-dark">₱{{ number_format($row['cost'], 2) }}</td>
                                                <td class="pe-4">
                                                    @php
                                                        $statusColors = [
                                                            'Serviceable' => ['bg' => '#d1f3df', 'fg' => '#0c6c3a'],
                                                            'Functional' => ['bg' => '#d1f3df', 'fg' => '#0c6c3a'],
                                                            'Non-Serviceable' => ['bg' => '#fbe1e3', 'fg' => '#a02633'],
                                                            'Returned to supplier for repair' => ['bg' => '#fff3cd', 'fg' => '#7a5b00'],
                                                        ];
                                                        $colors = $statusColors[$row['status']] ?? ['bg' => '#e9ecef', 'fg' => '#495057'];
                                                    @endphp
                                                    <span class="badge rounded-pill px-3 py-2"
                                                        style="background-color: {{ $colors['bg'] }}; color: {{ $colors['fg'] }};">
                                                        {{ $row['status'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5 text-muted">
                                                    <i class="bi bi-tools fs-1 opacity-25 d-block mb-2"></i>
                                                    No equipment found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection
