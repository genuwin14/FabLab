<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inventory of Materials</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: DejaVu Sans, sans-serif; color: #212529; font-size: 9pt; }
        .doc-header { text-align: center; margin-bottom: 18pt; }
        .doc-header h1 { font-size: 14pt; margin: 0 0 4pt 0; letter-spacing: 0.5pt; }
        .doc-header .as-of { font-size: 9pt; font-style: italic; color: #555; }

        .section { margin-top: 20pt; page-break-inside: avoid; }
        .section-heading {
            font-size: 11pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 8pt;
            padding: 4pt 0;
            background-color: #fff7d6;
            border-top: 2px solid #ffc508;
            border-bottom: 2px solid #ffc508;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
        }
        .section-heading.uncategorized {
            background-color: #f0f0f0;
            border-top-color: #999;
            border-bottom-color: #999;
        }

        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 5pt 6pt; vertical-align: middle; }
        thead th { background-color: #0e2e45; color: #fff; font-size: 8.5pt; text-align: center; text-transform: uppercase; }
        .num { text-align: right; }
        .center { text-align: center; }
        .item-name { font-weight: bold; }
        .available { font-weight: bold; }
        .available.zero { color: #a02633; }
        .available.positive { color: #0c6c3a; }

        .type-badge {
            display: inline-block;
            padding: 1pt 6pt;
            border-radius: 8pt;
            font-size: 7.5pt;
            font-weight: bold;
        }
        .type-Product { background-color: #d8e7fb; color: #0d49a8; }
        .type-Raw\\ Material { background-color: #fde2c9; color: #b35106; }
        .type-Texture { background-color: #e1d3f7; color: #4f2d96; }

        .empty-section { text-align: center; padding: 8pt; font-style: italic; color: #888; font-size: 8.5pt; }
        .footer-note { margin-top: 16pt; font-size: 8pt; font-style: italic; color: #666; }
    </style>
</head>
<body>
    <div class="doc-header">
        <h1>INVENTORY OF MATERIALS</h1>
        <div class="as-of">As of {{ $asOfDate->format('F j, Y') }}</div>
    </div>

    @foreach($sections as $deptName => $rows)
        <div class="section">
            <div class="section-heading {{ $deptName === 'Uncategorized' ? 'uncategorized' : '' }}">
                @if($deptName !== 'Uncategorized')PEDS @endif{{ $deptName }}
            </div>

            @if(count($rows) > 0)
                <table>
                    <thead>
                        <tr>
                            <th style="width: 9%;">Type</th>
                            <th style="width: 26%;">Item</th>
                            <th style="width: 9%;">Unit</th>
                            <th style="width: 11%;">On Display</th>
                            <th style="width: 11%;">Sponsored</th>
                            <th style="width: 10%;">Damaged</th>
                            <th style="width: 11%;">Consumed</th>
                            <th style="width: 13%;">Available</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr>
                                <td class="center">
                                    <span class="type-badge type-{{ str_replace(' ', '\\ ', $row['type']) }}">{{ $row['type'] }}</span>
                                </td>
                                <td class="item-name">{{ $row['name'] }}</td>
                                <td class="center">{{ $row['unit'] }}</td>
                                <td class="num">{{ $row['on_display'] > 0 ? number_format($row['on_display']) : '—' }}</td>
                                <td class="num">{{ $row['sponsored'] > 0 ? number_format($row['sponsored']) : '—' }}</td>
                                <td class="num">{{ $row['damaged'] > 0 ? number_format($row['damaged']) : '—' }}</td>
                                <td class="num">{{ $row['consumed'] > 0 ? number_format($row['consumed']) : '—' }}</td>
                                <td class="num available {{ $row['available'] <= 0 ? 'zero' : 'positive' }}">
                                    {{ $row['available'] > 0 ? number_format($row['available']) : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-section">No items assigned to this section.</div>
            @endif
        </div>
    @endforeach

    <p class="footer-note">Note: A dash (—) indicates the item is out of stock or no data is available.</p>
</body>
</html>
