<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #f4f4f4;
            padding: 20px;
        }

        .receipt-container {
            background-color: #ffffff;
            width: 100%;
            max-width: 380px;
            margin: 0 auto;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #dddddd;
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
            font-size: 0.875rem;
        }

        .border-dashed {
            border-bottom: 2px dashed #000;
            margin: 15px 0;
        }

        .table {
            width: 100%;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .table th,
        .table td {
            text-align: left;
            padding: 4px 0;
        }

        .text-end {
            text-align: right;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .mb-4 {
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body>
    <div class="receipt-container">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-uppercase mb-0" style="letter-spacing: 2px;">CSPC FABLAB</h2>
            <p class="text-muted small mb-0">Camarines Sur Polytechnic Colleges</p>
            <div class="border-dashed"></div>
            <h3 class="fw-bold text-uppercase mb-0">Order Receipt</h3>
            <p class="text-muted small">{{ $order->created_at->format('M d, Y h:i A') }}</p>
            <p class="small">Order #: {{ $order->order_number }}</p>
        </div>

        <div class="small">
            <p><span class="text-muted">Customer:</span> <span class="fw-bold">{{ $order->user->name }}</span></p>
        </div>

        <div class="border-dashed"></div>

        <table class="table small">
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Total</th>
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

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <span class="fw-bold text-uppercase small">Total Amount</span>
            <span class="fw-bold fs-5">P{{ number_format($order->total_amount, 2) }}</span>
        </div>

        <div class="border-dashed"></div>

        <div class="text-center mb-4">
            <p class="small fw-bold mb-1">PAYMENT INSTRUCTION</p>
            <p class="small text-muted mb-0">Please present this receipt at the<br><strong>CSPC Cashier</strong> for
                payment.</p>
        </div>

        <div class="text-center">
            <img src="https://bwipjs-api.metafloor.com/?bcid=code128&text={{ $order->order_number }}&scale=2&height=10&incltext=N&color=000000"
                alt="Barcode" style="width: 100%; max-width: 250px; height: auto;">
            <p class="text-muted small mt-1" style="font-size: 0.7rem;">SYSTEM GENERATED RECEIPT</p>
        </div>
    </div>
</body>

</html>