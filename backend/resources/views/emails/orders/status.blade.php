@extends('emails.layout')

@section('content')
    @php
        // The same variant colors the app's dialogs use: green for done,
        // red for cancelled, navy for everything in between.
        $pill = match ($newStatus) {
            'completed' => '#198754',
            'cancelled' => '#dc3545',
            default => '#0e2e45',
        };
    @endphp

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
                    style="display:inline-block;background-color:{{ $pill }};color:#ffffff;font-size:12px;font-weight:bold;letter-spacing:1px;padding:6px 14px;border-radius:999px;">
                    {{ strtoupper($label) }}
                </span>
            </td>
        </tr>
    </table>

    @switch($newStatus)
        @case('processing')
            <p style="margin:0 0 12px;">Your order is now in production.</p>
            <p style="margin:0 0 12px;">We will email you again as soon as it moves to the next step.</p>
            @break

        @case('ready_for_pickup')
            <p style="margin:0 0 12px;">Your order is ready for pickup!</p>
            <p style="margin:0 0 12px;">Please visit the CSPC FabLab and present your transaction slip to collect it.</p>
            @break

        @case('for_delivery')
            <p style="margin:0 0 12px;">Your order has been released for delivery.</p>
            <p style="margin:0 0 12px;">We will email you again once it has been handed over.</p>
            @break

        @case('completed')
            <p style="margin:0 0 12px;">Your order has been completed.</p>
            <p style="margin:0 0 12px;">Thank you for choosing CSPC FabLab &mdash; we hope to see you again!</p>
            @break

        @case('cancelled')
            <p style="margin:0 0 12px;">Your order has been cancelled.</p>
            @if ($order->reason)
                <table role="presentation" cellpadding="0" cellspacing="0" width="100%"
                    style="background-color:#fdf3f4;border-left:4px solid #dc3545;border-radius:4px;margin:0 0 12px;">
                    <tr>
                        <td style="padding:10px 14px;color:#58151c;">Reason: {{ $order->reason }}</td>
                    </tr>
                </table>
            @endif
            <p style="margin:0 0 12px;">If you have any questions, please get in touch with the CSPC FabLab.</p>
            @break

        @default
            <p style="margin:0 0 12px;">Your order is now {{ $label }}.</p>
    @endswitch

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
