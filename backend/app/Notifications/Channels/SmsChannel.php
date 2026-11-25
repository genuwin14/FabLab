<?php

namespace App\Notifications\Channels;

use App\Services\SmsService;
use Illuminate\Notifications\Notification;

/**
 * Sends a notification as a text, through the same SmsService (and the same
 * SMS_DRIVER switch) as the sign-up and password-reset codes.
 *
 * A notification on this channel provides toSms($notifiable) and returns the
 * message. The notifiable names the number with routeNotificationForSms().
 * SmsService never throws: a gateway that is down is logged and returns
 * false, and the rest of the notification carries on.
 */
class SmsChannel
{
    public function __construct(private SmsService $sms) {}

    public function send(object $notifiable, Notification $notification): bool
    {
        $number = $notifiable->routeNotificationFor('sms', $notification);

        if (blank($number) || ! method_exists($notification, 'toSms')) {
            return false;
        }

        return $this->sms->send($number, $notification->toSms($notifiable));
    }
}
