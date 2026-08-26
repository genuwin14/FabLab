<!DOCTYPE html>
<html>

<head>
    <style>
        /* The page itself is the receipt, so the slip fills it edge to edge and
           carries its own padding instead of floating on a backdrop. */
        @page {
            margin: 0;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 8pt;
            line-height: 1.35;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        .receipt-container {
            padding: 10pt;
        }

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: bold;
        }

        .text-uppercase {
            text-transform: uppercase;
        }

        .text-muted {
            color: #6c757d;
        }

        .small {
            font-size: 7.5pt;
        }

        .border-dashed {
            border-bottom: 1.5pt dashed #000;
            margin: 8pt 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8pt;
        }

        .table th,
        .table td {
            text-align: left;
            padding: 2pt 0;
            /* A product name with no spaces would otherwise run into the Qty
               column instead of wrapping inside the narrow roll. */
            word-break: break-word;
        }

        .text-end {
            text-align: right;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .mb-4 {
            margin-bottom: 10pt;
        }

        p {
            margin: 2pt 0;
        }

        h2,
        h3 {
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="receipt-container">
        <div class="text-center mb-4">
            {{-- Logo temporarily disabled due to GD extension issues
            @php
            $logoPath = public_path('img/FABLAB-LOGO.png');
            if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;
            } else {
            $logoSrc = '';
            }
            @endphp
            @if($logoSrc)
            <img src="{{ $logoSrc }}" alt="Logo"
                style="width: 60px; height: 60px; margin-bottom: 5px; filter: grayscale(100%);">
            @endif
            --}}
            <h2 class="fw-bold text-uppercase mb-0" style="font-size: 13pt; letter-spacing: 1pt;">CSPC FABLAB</h2>
            <p class="text-muted small mb-0">Camarines Sur Polytechnic Colleges</p>
            <div class="border-dashed"></div>
            <h3 class="fw-bold text-uppercase mb-0" style="font-size: 10pt;">Transaction Slip</h3>
            <p class="text-muted small">{{ $order->created_at->format('M d, Y h:i A') }}</p>
            <p class="small">Order #: {{ $order->order_number }}</p>
        </div>

        <div class="small">
            <p><span class="text-muted">Customer:</span> <span class="fw-bold">{{ $order->user->fullname }}</span></p>
        </div>

        <div class="border-dashed"></div>

        <table class="table small">
            <thead>
                <tr>
                    <th style="width: 55%;">Product</th>
                    <th class="text-center" style="width: 15%;">Qty</th>
                    <th class="text-end" style="width: 30%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td class="text-center">x{{ $item->quantity }}</td>
                        <td class="text-end">P{{ number_format($item->price * $item->quantity, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="border-dashed"></div>

        {{-- A table, not flexbox: DomPDF ignores flex, which would leave the
             amount hugging the label instead of the right edge. --}}
        <table class="table" style="margin-bottom: 0;">
            <tr>
                <td class="fw-bold text-uppercase small">Total Amount</td>
                <td class="fw-bold text-end" style="font-size: 9.5pt;">P{{ number_format($order->total_amount, 2) }}</td>
            </tr>
        </table>

        <div class="border-dashed"></div>

        <div class="text-center mb-4">
            <p class="small fw-bold mb-0">PAYMENT INSTRUCTION</p>
            <p class="small text-muted mb-0">Please present this receipt at the<br><strong>CSPC Cashier</strong> for
                payment.</p>
        </div>

        <div class="text-center">
            @php
                $generator = new \Picqer\Barcode\BarcodeGeneratorHTML();
                // widthFactor: 1 (thinner bars), height: 30
                $barcode = $generator->getBarcode($order->order_number, $generator::TYPE_CODE_128, 1, 30);
            @endphp
            <div style="margin: 0 auto; display: inline-block; background: white;">
                {!! $barcode !!}
            </div>
            <p class="text-muted" style="font-size: 6pt; margin: 3pt 0 0 0;">SYSTEM GENERATED RECEIPT</p>
        </div>
    </div>
</body>

</html>
