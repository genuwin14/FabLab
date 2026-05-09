<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inventory of Materials</title>
    <style>
        /* Top margin reserves space for the letterhead drawn via page_script
           (letterhead blue line at y=72pt; the rest is breathing room). */
        @page { margin: 3.1cm 1.5cm 1.1cm 1.5cm; }
        body { font-family: Arial, Helvetica, sans-serif; color: #212529; font-size: 11pt; }

        /* Doc header — Arial 12 (printed per section: title + dept + as-of) */
        .doc-header { text-align: center; margin-bottom: 16pt; }
        .doc-header h1 { font-size: 12pt; font-weight: bold; margin: 0 0 2pt 0; letter-spacing: 0.5pt; text-transform: uppercase; }
        .doc-header .dept-name { font-size: 12pt; font-weight: normal; margin: 0 0 2pt 0; text-transform: uppercase; letter-spacing: 0.3pt; }
        .doc-header .as-of { font-size: 12pt; font-weight: normal; font-style: normal; color: #212529; margin: 0; }

        .section { margin-top: 0; }
        .section + .section { page-break-before: always; }

        /* Data table — Arial 10 */
        table { width: 100%; border-collapse: collapse; font-size: 10pt; }
        th, td { border: 1px solid #999; padding: 2pt 6pt; vertical-align: middle; text-align: center; }
        thead th { font-weight: bold; padding: 4pt 6pt; }
        .item-name { font-weight: bold; }
        .available { font-weight: bold; }
        .available.zero { color: #a02633; }
        .available.positive { color: #0c6c3a; }

        .type-badge {
            display: inline-block;
            padding: 1pt 6pt;
            border-radius: 8pt;
            font-size: 8.5pt;
            font-weight: bold;
        }
        .type-Product { background-color: #d8e7fb; color: #0d49a8; }
        .type-Raw\\ Material { background-color: #fde2c9; color: #b35106; }
        .type-Texture { background-color: #e1d3f7; color: #4f2d96; }

        .empty-section { text-align: center; padding: 8pt; font-style: italic; color: #888; font-size: 9pt; }
        .footer-note { margin-top: 12pt; font-size: 9pt; font-style: italic; color: #666; }
        script { display: none; }
    </style>
</head>
<body>
    @foreach($sections as $deptName => $rows)
        <div class="section">
            <div class="doc-header">
                <h1>INVENTORY OF MATERIALS</h1>
                <div class="dept-name">
                    @if($deptName !== 'Uncategorized')PEDS @endif{{ strtoupper($deptName) }}
                </div>
                <div class="as-of">As of {{ $asOfDate->format('F j, Y') }}</div>
            </div>

            @if(count($rows) > 0)
                <table>
                    <thead>
                        <tr>
                            <th style="width: 22%;">Item</th>
                            <th style="width: 8%;">Unit</th>
                            <th style="width: 16%;">No. of Units on Display</th>
                            <th style="width: 12%;">No. of Sponsored Units</th>
                            <th style="width: 11%;">No. of Damaged Units</th>
                            <th style="width: 12%;">No. of Units Consumed</th>
                            <th style="width: 19%;">Available Units for Production</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr>
                                <td class="item-name">{{ $row['name'] }}</td>
                                <td>{{ $row['unit'] }}</td>
                                <td>{{ $row['on_display'] > 0 ? number_format($row['on_display']) : '—' }}</td>
                                <td>{{ $row['sponsored'] > 0 ? number_format($row['sponsored']) : '—' }}</td>
                                <td>{{ $row['damaged'] > 0 ? number_format($row['damaged']) : '—' }}</td>
                                <td>{{ $row['consumed'] > 0 ? number_format($row['consumed']) : '—' }}</td>
                                <td class="available {{ $row['available'] <= 0 ? 'zero' : 'positive' }}">
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

    @include('admin.reports.pdf.components.letterhead')
    @include('admin.reports.pdf.components.page-footer')
</body>
</html>
