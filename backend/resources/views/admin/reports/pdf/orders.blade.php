<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Orders</title>
    <style>
        /* Landscape: nine columns will not sit on a portrait page. The top
           margin reserves space for the letterhead drawn via page_script. */
        @page { margin: 3.1cm 1.5cm 1.1cm 1.5cm; }
        body { font-family: Arial, Helvetica, sans-serif; color: #212529; font-size: 10pt; }

        .header { text-align: center; margin-bottom: 12pt; }
        .header h1 { font-size: 12pt; font-weight: bold; margin: 0 0 2pt 0; letter-spacing: 0.5pt; text-transform: uppercase; }
        .header .filter { font-size: 11pt; margin: 0 0 2pt 0; }
        .header .as-of { font-size: 10pt; margin: 0; }

        .summary { width: 100%; border-collapse: collapse; margin-bottom: 10pt; font-size: 10pt; }
        .summary td { border: 1px solid #999; padding: 3pt 6pt; }
        .summary td.value { text-align: right; font-weight: bold; }

        table.orders { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 8.5pt; }
        .orders th, .orders td { border: 1px solid #999; padding: 3pt 4pt; vertical-align: top; text-align: left; word-wrap: break-word; }
        .orders thead th { font-weight: bold; background-color: #f1f4f8; vertical-align: middle; }
        .orders td.num { text-align: right; white-space: nowrap; }
        .orders tr { page-break-inside: avoid; }
        .muted { color: #6c757d; }
        /* The peso sign is not in DomPDF's Helvetica fallback, so amounts use DejaVu. */
        .amount { font-family: DejaVu Sans, sans-serif; }
        script { display: none; }
    </style>
</head>

<body>
    <div class="header">
        <h1>Orders</h1>
        <div class="filter">{{ $filterLabel }}</div>
        <div class="as-of">Generated {{ $generatedAt->format('F j, Y') }}</div>
    </div>

    <table class="summary">
        <tbody>
            <tr>
                <td style="width: 70%;">Orders listed</td>
                <td class="value">{{ number_format($count) }}</td>
            </tr>
            <tr>
                <td>Value of the orders not cancelled{{ $cancelledCount ? ' (' . number_format($cancelledCount) . ' cancelled left out)' : '' }}</td>
                <td class="value amount">₱{{ number_format($openValue, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="orders">
        <thead>
            <tr>
                <th style="width: 12%;">Order No.</th>
                <th style="width: 8%;">Date</th>
                <th style="width: 11%;">Customer</th>
                <th style="width: 14%;">Ordered For</th>
                <th style="width: 8%;">Paid Through</th>
                <th style="width: 20%;">Items</th>
                <th style="width: 9%;">Receipt / PR No.</th>
                <th style="width: 9%;">Status</th>
                <th style="width: 9%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['number'] }}</td>
                    <td>{{ $row['placed']->format('M j, Y') }}</td>
                    <td>{{ $row['customer'] }}</td>
                    <td>{{ $row['ordered_for'] }}</td>
                    <td>{{ $row['channel'] }}</td>
                    <td>
                        @foreach($row['items'] as $line)
                            <div>{{ $line }}</div>
                        @endforeach
                    </td>
                    <td>{!! $row['reference'] !== '' ? e($row['reference']) : '<span class="muted">—</span>' !!}</td>
                    <td>{{ $row['status'] }}</td>
                    <td class="num amount">₱{{ number_format($row['total'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="9" style="text-align: center;">No orders match these filters.</td></tr>
            @endforelse
        </tbody>
    </table>

    @include('admin.reports.pdf.components.letterhead')
    @include('admin.reports.pdf.components.page-footer')
</body>

</html>
