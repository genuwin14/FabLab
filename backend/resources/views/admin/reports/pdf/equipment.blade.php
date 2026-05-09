<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inventory of Machinery and Equipment</title>
    <style>
        /* Top margin reserves space for the letterhead drawn via page_script
           (letterhead blue line at y=72pt; the rest is breathing room). */
        @page { margin: 3.1cm 1.5cm 1.1cm 1.5cm; }
        body { font-family: Arial, Helvetica, sans-serif; color: #212529; font-size: 11pt; }

        /* Doc header — Arial 12 */
        .header { text-align: center; margin-bottom: 16pt; }
        .header h1 { font-size: 12pt; font-weight: bold; margin: 0 0 2pt 0; letter-spacing: 0.5pt; text-transform: uppercase; }
        .header .filter { font-size: 12pt; font-weight: normal; font-style: normal; color: #212529; margin: 0 0 2pt 0; }
        .header .as-of { font-size: 12pt; font-weight: normal; font-style: normal; color: #212529; margin: 0; }

        /* Data table — Arial 10. table-layout:fixed forces DomPDF to honor the
           per-column widths instead of expanding columns to fit content. */
        table { width: 100%; border-collapse: collapse; table-layout: fixed; font-family: Arial, Helvetica, sans-serif; font-size: 10pt; }
        th, td { border: 1px solid #999; padding: 2pt 6pt; vertical-align: middle; text-align: center; font-family: Arial, Helvetica, sans-serif; font-size: 10pt; word-wrap: break-word; }
        thead th { font-weight: bold; padding: 4pt 6pt; }
        .item-name { font-weight: bold; }
        /* Cost cell uses DejaVu Sans because the peso sign (₱, U+20B1) is not in the
           Type-1 Helvetica/Arial fallback DomPDF uses, so it would render as "?". */
        .cost-cell { font-family: DejaVu Sans, sans-serif; }
        script { display: none; }
    </style>
</head>
<body>
    <div class="header">
        <h1>INVENTORY OF MACHINERY AND EQUIPMENT</h1>
        @if($status !== '')
            <div class="filter">Filtered by status: {{ $status }}</div>
        @endif
        <div class="as-of">As of {{ $asOfDate->format('F j, Y') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 18%;">Machinery and Equipment</th>
                <th style="width: 13%;">Brand</th>
                <th style="width: 17%;">Property No.</th>
                <th style="width: 23%;">Date Acquired</th>
                <th style="width: 13%;">Cost</th>
                <th style="width: 16%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['brand'] ?: '—' }}</td>
                    <td>{{ $row['property_no'] ?: '—' }}</td>
                    <td>{{ $row['date_acquired'] ? $row['date_acquired']->format('M j, Y') : '—' }}</td>
                    <td class="cost-cell">₱{{ number_format($row['cost'], 2) }}</td>
                    <td>{{ $row['status'] }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No equipment found.</td></tr>
            @endforelse
        </tbody>
    </table>

    @include('admin.reports.pdf.components.letterhead')
    @include('admin.reports.pdf.components.page-footer')
</body>
</html>
