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
                    APPROVED
                </span>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 12px;">Your order has been approved!</p>
    <p style="margin:0 0 12px;">You can find the details of your transaction in the attached PDF slip. Please present
        this slip at the CSPC Cashier for payment.</p>
    <p style="margin:0 0 12px;">Thank you for choosing CSPC FabLab.</p>

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
