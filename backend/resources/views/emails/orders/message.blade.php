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
        <p>Your order <strong>#{{ $order->order_number }}</strong> has been approved!</p>
        <p>You can find the details of your transaction in the attached PDF slip. Please present this slip at the CSPC
            Cashier for payment.</p>
        <p>Thank you for choosing CSPC Fablab.</p>
    </div>

    <div class="footer">
        <p>All Rights Reserved &copy; {{ date('Y') }} CSPC Fablab.</p>
    </div>
</body>

</html>