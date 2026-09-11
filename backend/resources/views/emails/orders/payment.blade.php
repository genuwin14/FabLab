@extends('emails.layout')

@section('content')
    <p style="margin:0 0 16px;font-weight:bold;color:#0e2e45;font-size:16px;">Hello {{ $order->user->fullname }},</p>

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%"
        style="background-color:#f7f9fb;border:1px solid #e3e7ec;border-radius:8px;margin:0 0 20px;">
        <tr>
            <td style="padding:16px 20px;">
                <div style="color:#6c757d;font-size:12px;letter-spacing:1px;">ORDER</div>
                <div style="color:#0e2e45;font-size:18px;font-weight:bold;">#{{ $order->order_number }}</div>
            </td>
            <td align="right" style="padding:16px 20px;">
                <span
                    style="display:inline-block;background-color:#198754;color:#ffffff;font-size:12px;font-weight:bold;letter-spacing:1px;padding:6px 14px;border-radius:999px;">
                    PAYMENT RECEIVED
                </span>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 12px;">Thank you — your payment has been recorded and your order is now queued for production.</p>
    <p style="margin:0 0 12px;">Keep the official receipt from PAXS. Its number is what you will show to collect your order:</p>

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%"
        style="background-color:#fff8e1;border:1px dashed #997404;border-radius:8px;margin:0 0 12px;">
        <tr>
            <td style="padding:12px 16px;">
                <div style="color:#997404;font-size:11px;font-weight:bold;letter-spacing:1px;">RECEIPT NUMBER</div>
                <div style="color:#0e2e45;font-size:20px;font-weight:bold;font-family:Consolas,Menlo,monospace;">{{ $order->payment_reference }}</div>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 12px;">We will email you again as soon as your order moves to the next step.</p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:24px 0 8px;">
        <tr>
            <td style="background-color:#ffc508;border-radius:8px;">
                <a href="{{ route('customer.orders.index') . '#order-' . $order->order_id }}"
                    style="display:inline-block;padding:12px 24px;color:#0e2e45;font-size:14px;font-weight:bold;text-decoration:none;">
                    View My Orders
                </a>
            </td>
        </tr>
    </table>
@endsection
