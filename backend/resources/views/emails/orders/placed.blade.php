@extends('emails.layout')

@section('content')
    <p style="margin:0 0 16px;font-weight:bold;color:#0e2e45;font-size:16px;">Hello {{ $order->user->fullname }},</p>

    <p style="margin:0 0 16px;">Thank you for your order! We have received order
        <strong style="color:#0e2e45;">#{{ $order->order_number }}</strong>.</p>

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%"
        style="border:1px solid #e3e7ec;border-radius:8px;margin:0 0 20px;">
        <tr>
            <td colspan="2"
                style="background-color:#0e2e45;color:#ffffff;font-size:12px;font-weight:bold;letter-spacing:1px;padding:10px 16px;border-radius:8px 8px 0 0;">
                ORDER SUMMARY
            </td>
        </tr>
        @foreach ($order->orderItems as $item)
            <tr>
                <td style="padding:10px 16px;border-bottom:1px solid #eef1f4;">
                    {{ $item->product->name ?? 'Item' }} &times; {{ $item->quantity }}
                </td>
                <td align="right" style="padding:10px 16px;border-bottom:1px solid #eef1f4;white-space:nowrap;">
                    &#8369;{{ number_format($item->price * $item->quantity, 2) }}
                </td>
            </tr>
        @endforeach
        <tr>
            <td style="padding:12px 16px;font-weight:bold;color:#0e2e45;">Total</td>
            <td align="right" style="padding:12px 16px;font-weight:bold;color:#0e2e45;white-space:nowrap;">
                &#8369;{{ number_format((float) $order->total_amount, 2) }}
            </td>
        </tr>
    </table>

    @if ($order->isPurchaseRequest())
        <table role="presentation" cellpadding="0" cellspacing="0" width="100%"
            style="background-color:#fff8e1;border-left:4px solid #ffc508;border-radius:4px;margin:0 0 16px;">
            <tr>
                <td style="padding:12px 16px;color:#5c4a03;">
                    This order was placed on a Purchase Request. Please file your Purchase Request with
                    <strong>{{ config('fablab.procurement_email') }}</strong> and enter the PR number in your orders page
                    @if ($order->pr_deadline)
                        by <strong>{{ $order->pr_deadline->format('j M Y') }}</strong>
                    @endif
                    &mdash; the order is held until then.
                </td>
            </tr>
        </table>
    @else
        <p style="margin:0 0 16px;">Your order is now awaiting review. We will email you as it moves along.</p>
    @endif

    <p style="margin:0 0 16px;">Thank you for choosing CSPC FabLab.</p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:8px 0;">
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
