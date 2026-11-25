<?php

namespace App\Notifications\Concerns;

use App\Notifications\Channels\SmsChannel;

/**
 * How an order's customer hears about it: the in-app bell, unless they turned
 * it off in Settings, plus email and texts if they ticked them at checkout.
 *
 * For notifications that carry the order as `$this->order`.
 *
 * Every text these build stays within one SMS: 160 plain characters, no
 * links (UniSMS's test sender may not send them), and "PHP" for the peso.
 * A single ₱ switches a text to Unicode, which fits 70 characters instead
 * of 160, so most of these would go out in two or three parts.
 */
trait FollowsUpdateChoice
{
    /**
     * @param  bool  $mail  false where another email already says the same
     *                      thing, as the transaction slip does for approval
     * @return array<int, string>
     */
    protected function customerChannels(object $notifiable, bool $mail = true): array
    {
        $channels = [];

        // The Settings switch is about the bell, and says so. It used to
        // silence the emails too, which would now also swallow the texts the
        // customer asked for on this very order.
        if ($notifiable->notifications_enabled ?? true) {
            $channels[] = 'database';
        }

        // Texts before email: Laravel stops at the first channel that
        // throws, and the mail server can throw where SmsService never does.
        if ($this->order->notify_sms && filled($notifiable->routeNotificationFor('sms', $this))) {
            $channels[] = SmsChannel::class;
        }

        if ($mail && $this->order->notify_email) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /** "PHP 1,250.00" */
    protected function smsAmount(float|string|null $amount): string
    {
        return 'PHP ' . number_format((float) $amount, 2);
    }

    /**
     * The receipt number PAXS issued, when it is short enough to text. The
     * admin types it into a field that takes 255 characters, and a clipped
     * number would be worse than none.
     */
    protected function smsReceipt(): ?string
    {
        $receipt = trim((string) $this->order->payment_reference);

        return $receipt !== '' && mb_strlen($receipt) <= 20 ? $receipt : null;
    }
}
