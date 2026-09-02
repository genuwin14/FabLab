<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .greeting {
            font-weight: bold;
            margin-bottom: 20px;
        }

        .content {
            margin-bottom: 20px;
        }

        .items {
            border-collapse: collapse;
            margin: 15px 0;
        }

        .items td {
            border-bottom: 1px solid #dee2e6;
            padding: 6px 20px 6px 0;
        }

        .items .total td {
            border-bottom: none;
            font-weight: bold;
        }

        .footer {
            font-size: 0.875rem;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <p class="greeting">Hello {{ $order->user->fullname }},</p>

    <div class="content">
        <p>Thank you for your order! We have received order <strong>#{{ $order->order_number }}</strong>.</p>

        <table class="items">
            @foreach ($order->orderItems as $item)
                <tr>
                    <td>{{ $item->product->name ?? 'Item' }} &times; {{ $item->quantity }}</td>
                    <td>&#8369;{{ number_format($item->price * $item->quantity, 2) }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td>Total</td>
                <td>&#8369;{{ number_format((float) $order->total_amount, 2) }}</td>
            </tr>
        </table>

        @if ($order->isPurchaseRequest())
            <p>This order was placed on a Purchase Request. Please file your Purchase Request with
                <strong>{{ config('fablab.procurement_email') }}</strong> and enter the PR number in your orders page
                @if ($order->pr_deadline)
                    by <strong>{{ $order->pr_deadline->format('j M Y') }}</strong>
                @endif
                &mdash; the order is held until then.</p>
        @else
            <p>Your order is now awaiting review. We will email you as it moves along.</p>
        @endif

        <p>Thank you for choosing CSPC FabLab.</p>
    </div>

    <div class="footer">
        <p>All Rights Reserved &copy; {{ date('Y') }} CSPC Fablab.</p>
    </div>
</body>

</html>
