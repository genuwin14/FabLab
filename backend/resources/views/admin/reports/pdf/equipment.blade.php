<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inventory of Machinery and Equipment</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: DejaVu Sans, sans-serif; color: #212529; font-size: 9pt; }
        .header { text-align: center; margin-bottom: 14pt; }
        .header h1 { font-size: 13pt; margin: 0 0 4pt 0; letter-spacing: 0.5pt; }
        .header .as-of { font-size: 9pt; font-style: italic; color: #555; }
        .header .filter { font-size: 9pt; font-style: italic; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 5pt 6pt; vertical-align: middle; }
        thead th { background-color: #0e2e45; color: #fff; font-size: 8.5pt; text-align: center; text-transform: uppercase; }
        .num { text-align: right; }
        .center { text-align: center; }
        .item-name { font-weight: bold; }
        .status-badge { display: inline-block; padding: 1pt 6pt; border-radius: 8pt; font-size: 7.5pt; font-weight: bold; }
        .status-Serviceable, .status-Functional { background-color: #d1f3df; color: #0c6c3a; }
        .status-Non-Serviceable { background-color: #fbe1e3; color: #a02633; }
        .status-Returned\\ to\\ supplier\\ for\\ repair { background-color: #fff3cd; color: #7a5b00; }
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
                <th style="width: 25%;">Machinery and Equipment</th>
                <th style="width: 18%;">Brand</th>
                <th style="width: 22%;">Property No.</th>
                <th style="width: 12%;">Date Acquired</th>
                <th style="width: 11%;">Cost</th>
                <th style="width: 12%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td class="item-name">{{ $row['name'] }}</td>
                    <td>{{ $row['brand'] ?: '—' }}</td>
                    <td style="font-family: monospace; font-size: 8pt;">{{ $row['property_no'] ?: '—' }}</td>
                    <td class="center">{{ $row['date_acquired'] ? $row['date_acquired']->format('M j, Y') : '—' }}</td>
                    <td class="num">₱{{ number_format($row['cost'], 2) }}</td>
                    <td class="center">
                        <span class="status-badge status-{{ str_replace(' ', '\\ ', $row['status']) }}">{{ $row['status'] }}</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="center">No equipment found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
