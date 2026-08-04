<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <style>
        /* Top margin reserves space for the letterhead drawn via page_script. */
        @page { margin: 3.1cm 1.5cm 1.1cm 1.5cm; }
        body { font-family: Arial, Helvetica, sans-serif; color: #212529; font-size: 11pt; }

        .header { text-align: center; margin-bottom: 16pt; }
        .header h1 { font-size: 12pt; font-weight: bold; margin: 0 0 2pt 0; letter-spacing: 0.5pt; text-transform: uppercase; }
        .header .filter { font-size: 12pt; margin: 0 0 2pt 0; }
        .header .as-of { font-size: 12pt; margin: 0; }

        h2 { font-size: 11pt; margin: 14pt 0 4pt 0; text-transform: uppercase; letter-spacing: 0.3pt; }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 10pt; }
        th, td { border: 1px solid #999; padding: 2pt 6pt; vertical-align: middle; text-align: center; word-wrap: break-word; }
        thead th { font-weight: bold; padding: 4pt 6pt; }
        td.text-start { text-align: left; }
        /* The peso sign is not in DomPDF's Helvetica fallback, so amounts use DejaVu. */
        .amount { font-family: DejaVu Sans, sans-serif; }
        .summary td { text-align: left; }
        .summary td.value { text-align: right; font-weight: bold; }
        script { display: none; }
    </style>
</head>

<body>
    <div class="header">
        <h1>Sales Report</h1>
        <div class="filter">{{ $rangeLabel }}</div>
        <div class="as-of">
            {{ $report['rangeStart']->format('F j, Y') }} to {{ $report['rangeEnd']->format('F j, Y') }}
        </div>
        <div class="as-of">Generated {{ $asOfDate->format('F j, Y') }}</div>
    </div>

    <h2>Summary</h2>
    <table class="summary">
        <tbody>
            <tr>
                <td style="width: 60%;">Total revenue (completed orders)</td>
                <td class="value amount">₱{{ number_format($report['totalRevenue'], 2) }}</td>
            </tr>
            <tr>
                <td>Completed orders</td>
                <td class="value">{{ number_format($report['orderCount']) }}</td>
            </tr>
            <tr>
                <td>Average order value</td>
                <td class="value amount">₱{{ number_format($report['avgOrderValue'], 2) }}</td>
            </tr>
            <tr>
                <td>Items sold</td>
                <td class="value">{{ number_format($report['itemsSold']) }}</td>
            </tr>
            <tr>
                <td>All-time revenue</td>
                <td class="value amount">₱{{ number_format($report['allTimeRevenue'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Best sellers</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 44%;">Product</th>
                <th style="width: 22%;">SKU</th>
                <th style="width: 14%;">Qty sold</th>
                <th style="width: 20%;">Revenue</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['topProducts'] as $product)
                <tr>
                    <td class="text-start">{{ $product->name }}</td>
                    <td>{{ $product->sku ?: '—' }}</td>
                    <td>{{ number_format($product->qty) }}</td>
                    <td class="amount">₱{{ number_format($product->revenue, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No sales in this period.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>{{ $report['groupByMonth'] ? 'Revenue by month' : 'Revenue by day' }}</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 40%;">{{ $report['groupByMonth'] ? 'Month' : 'Date' }}</th>
                <th style="width: 25%;">Orders</th>
                <th style="width: 35%;">Revenue</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['chartLabels'] as $i => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td>{{ number_format($report['orderSeries'][$i] ?? 0) }}</td>
                    <td class="amount">₱{{ number_format($report['revenueSeries'][$i] ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3">No periods in this range.</td></tr>
            @endforelse
        </tbody>
    </table>

    @include('admin.reports.pdf.components.letterhead')
    @include('admin.reports.pdf.components.page-footer')
</body>

</html>
