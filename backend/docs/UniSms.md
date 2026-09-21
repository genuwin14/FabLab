# FabLab – SMS through UniSMS

[UniSMS](https://unismsapi.com) is a paid SMS gateway for the Philippines. It
reaches Globe, Smart, TNT, Sun and DITO, and unlike the MacroDroid setup there
is no phone to keep charged and awake — you pay per message and UniSMS sends it.

This is one of four drivers behind the same `SmsService`, so nothing else in the
application changes. Registration OTPs, the resend button and password-reset
codes all go out through whichever driver `SMS_DRIVER` names.

| Driver | What sends the text |
| :--- | :--- |
| `log` | Nothing. The message is written to `storage/logs/laravel.log`. |
| `macrodroid` | An Android phone on your desk, from its own SIM. |
| `philsms` | The PhilSMS gateway. |
| `unisms` | UniSMS. **This document.** |

---

## 1. What you need from the dashboard

Sign in at [unismsapi.com/dashboard](https://unismsapi.com/dashboard). Three
things live there, and you need all three before a single text will send.

### 1.1 Credits

Credits are **prepaid** and the minimum top-up is ₱500. Roughly ₱0.50 a message,
falling to about ₱0.35 in volume. They do not expire.

> A balance of zero fails in exactly the same way as a wrong key: a rejection,
> and a line in the log. If sending stops working, check the balance first.

### 1.2 A Sender ID

The Sender ID is the name the recipient sees instead of a number — `FabLabs`,
for instance. **You cannot send without one.** Apply for it on the dashboard;
it is free for a registered business, and approval is not instant, so do this
days before you need it rather than the morning of.

Whatever it is approved as goes in `UNISMS_SENDER`, spelled identically.

### 1.3 An API key

Under **API Keys**, generate one and copy it. You are shown the full key once.
It goes in `UNISMS_API_KEY`.

---

## 2. Settings

In `backend/.env`:

```env
SMS_DRIVER=unisms
SMS_COUNTRY_CODE=63

UNISMS_URL=https://unismsapi.com/api
UNISMS_API_KEY=<the key from API Keys>
UNISMS_SENDER=FabLabs
UNISMS_TIMEOUT=15
UNISMS_WEBHOOK_SECRET=<from the Webhooks page — see §4>
```

Then, if you have ever run `config:cache` on this machine:

```bash
php artisan config:clear
```

> Skipping that is the classic way to spend an afternoon on this. A cached
> config keeps the old values, so a correct key in `.env` changes nothing at
> all and the failures look identical.

Numbers are stored however customers typed them — `09171234567`,
`+639171234567`, `0917 123 4567` — and `SmsService` rewrites them to the
`+639171234567` that UniSMS requires. You do not have to clean them up first.

---

## 3. Test it

```bash
php artisan sms:test 09171234567
```

The command prints the driver, the Sender ID and the first four characters of
the key, then sends. A success means UniSMS **accepted and queued** the message
— not that the handset has it yet. Watch the phone, or open **Messages** on the
dashboard.

If it fails, the reason is in `storage/logs/laravel.log`, verbatim from UniSMS:

| What the log says | What it usually is |
| :--- | :--- |
| `UNISMS_API_KEY is not set` | The key is missing, or a cached config is hiding it (§2). |
| Status `401` | The key is wrong, revoked, or from another account. |
| Status `422` | The Sender ID is not approved, or the balance is empty. |
| Status `429` | Too fast. The log line includes how long to wait. |
| `message is N characters; the limit is 670` | The message was too long and was not sent. |

---

## 4. Delivery receipts (optional)

Whether the network actually delivered a message arrives seconds to minutes
later, on a webhook. Without it you can still see delivery on the dashboard —
you just cannot see it in the application's own logs.

1. On the dashboard's **Webhooks** page, register the URL:

   ```
   https://your-domain.com/api/webhooks/unisms
   ```

2. Copy the **Webhook Secret Key** shown there into `UNISMS_WEBHOOK_SECRET`.

That endpoint is the one route in this application that anyone on the internet
can POST to, so the secret is the only thing guarding it. With the setting
blank, the route refuses everything — which is the safe way round, but it does
mean a half-finished setup looks like a broken one.

> **This needs a public URL.** UniSMS calls *in*, so `localhost` cannot receive
> receipts. Sending works fine from localhost regardless; only the receipts
> need the app to be reachable.

A receipt writes a line like this, with the same reference id the send logged,
so you can follow one OTP end to end:

```
UniSMS message.sent for +639171234567 (reference msg_84e8b93b), status sent.
UniSMS message.failed for +639171234567 (reference msg_84e8b93b): Subscriber unreachable
```

---

## 5. Where the code is

| Piece | File |
| :--- | :--- |
| The driver | [`app/Services/SmsService.php`](../app/Services/SmsService.php) — `sendViaUniSms()` |
| Settings | [`config/sms.php`](../config/sms.php) — `drivers.unisms` |
| Webhook | [`app/Http/Controllers/Webhooks/UniSmsWebhookController.php`](../app/Http/Controllers/Webhooks/UniSmsWebhookController.php) |
| Route | [`routes/api.php`](../routes/api.php) |
| Tests | [`tests/Feature/SmsSendingTest.php`](../tests/Feature/SmsSendingTest.php), [`tests/Feature/UniSmsWebhookTest.php`](../tests/Feature/UniSmsWebhookTest.php) |

`send()` never throws. Registration and password reset call it inline, so a
gateway that is down has to return `false` and let the page finish rather than
500 on someone signing up.

---

## 6. Falling back

Nothing is one-way. If UniSMS has a bad day mid-demo, put `SMS_DRIVER=macrodroid`
or `SMS_DRIVER=log` back in `.env`, run `php artisan config:clear`, and carry on
— the OTP is written to `storage/logs/laravel.log` under the `log` driver, which
is enough to keep a demonstration moving.
