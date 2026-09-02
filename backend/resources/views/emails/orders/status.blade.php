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
        @switch($newStatus)
            @case('processing')
                <p>Your order <strong>#{{ $order->order_number }}</strong> is now in production.</p>
                <p>We will email you again as soon as it moves to the next step.</p>
                @break

            @case('ready_for_pickup')
                <p>Your order <strong>#{{ $order->order_number }}</strong> is ready for pickup!</p>
                <p>Please visit the CSPC FabLab and present your transaction slip to collect it.</p>
                @break

            @case('for_delivery')
                <p>Your order <strong>#{{ $order->order_number }}</strong> has been released for delivery.</p>
                <p>We will email you again once it has been handed over.</p>
                @break

            @case('completed')
                <p>Your order <strong>#{{ $order->order_number }}</strong> has been completed.</p>
                <p>Thank you for choosing CSPC FabLab — we hope to see you again!</p>
                @break

            @case('cancelled')
                <p>Your order <strong>#{{ $order->order_number }}</strong> has been cancelled.</p>
                @if ($order->reason)
                    <p>Reason: {{ $order->reason }}</p>
                @endif
                <p>If you have any questions, please get in touch with the CSPC FabLab.</p>
                @break

            @default
                <p>Your order <strong>#{{ $order->order_number }}</strong> is now {{ $label }}.</p>
        @endswitch
    </div>

    <div class="footer">
        <p>All Rights Reserved &copy; {{ date('Y') }} CSPC Fablab.</p>
    </div>
</body>

</html>
