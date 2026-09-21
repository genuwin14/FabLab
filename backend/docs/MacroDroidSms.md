# FabLab – SMS from an Android Phone (MacroDroid)

This is the setup for sending the registration OTP as a **real text message**
during a local demo, without paying for an SMS gateway. An Android phone with a
SIM does the sending; Laravel just tells it what to send.

It works on `localhost`, which is the whole point. Laravel makes an **outbound**
call to MacroDroid, so the laptop never has to be reachable from the internet —
no public URL, no port forwarding, no ngrok.

```
Browser → Laravel (localhost:8000) → MacroDroid trigger URL → the phone → SIM → recipient
```

> The phone is the gateway. If the phone is off, out of load, or asleep, no
> text leaves — see [§8](#8-if-it-fails-on-the-day) for the fallback that keeps
> the demo moving.

---

## 1. What is already built into this system

None of this needs new code. It is all committed and tested.

| Piece | Where | State |
| :--- | :--- | :--- |
| Driver-switchable SMS service | [`app/Services/SmsService.php`](../app/Services/SmsService.php) | Done — `macrodroid`, `philsms`, `unisms` and `log` drivers |
| Settings | [`config/sms.php`](../config/sms.php) | Done — read through `config()`, so `config:cache` is safe |
| Test command | `php artisan sms:test` | Done — prints the driver, the webhook, and why a send failed |
| Automated tests | [`tests/Feature/SmsSendingTest.php`](../tests/Feature/SmsSendingTest.php) | 20 passing |
| Where it is called | Registration, OTP resend, password reset | Done |

The `.env` on this machine **already has a webhook URL saved** from the last
time this was tested:

```env
MACRODROID_WEBHOOK_URL=http://trigger.macrodroid.com/8490eecf-…-c179d9671a26/sms
MACRODROID_NUMBER_PARAM=sms_number
MACRODROID_MESSAGE_PARAM=sms_text
MACRODROID_NUMBER_FORMAT=e164
```

Two things follow from that URL, and they are the two things people get wrong:

- the macro's webhook identifier must be **`sms`** (the last part of the URL), and
- the phone must be the **same handset** that produced that device id. A new
  phone, or MacroDroid reinstalled, means a new device id and a new URL to
  paste in.

`SMS_DRIVER` is set to `macrodroid`. Set it back to `log` when you are done
testing and nothing will be sent again ([§8](#8-if-it-fails-on-the-day)).

---

## 2. On the phone: install and permissions

1. Install **MacroDroid** from the Play Store. The free version is enough — it
   allows five macros and we need one.
2. Open it and let it through the first-run permission screens.
3. Grant **SMS** permission explicitly: Android will not let MacroDroid text
   until you do. If you skipped it, go to
   **Settings → Apps → MacroDroid → Permissions → SMS → Allow**.
4. Turn **off battery optimisation** for MacroDroid:
   **Settings → Apps → MacroDroid → Battery → Unrestricted**.
   This is the single most common cause of "it worked yesterday" — Android
   freezes the app in the background and the trigger never arrives.
5. Make sure the SIM has **load** and can send a normal text. Send one from the
   Messages app first. If the SIM cannot text, no amount of setup will help.

---

## 3. On the phone: create the two variables

MacroDroid copies the query-string parameters into global variables **of the
same name**, and — this is the part that silently eats your message — the
variable has to exist **before** the trigger fires. If it does not exist, the
value is dropped and the macro texts nothing.

**MacroDroid → ☰ menu → Variables → + (add)**, and create two:

| Name | Type |
| :--- | :--- |
| `sms_number` | String |
| `sms_text` | String |

Spelling matters. These names must match `MACRODROID_NUMBER_PARAM` and
`MACRODROID_MESSAGE_PARAM` in `.env` exactly.

---

## 4. On the phone: create the macro

**MacroDroid → Add Macro (+)**, then:

**Trigger** → *Connectivity* → **Webhook (URL)**

- Set the identifier to **`sms`**.
- MacroDroid shows you the finished URL — it looks like
  `https://trigger.macrodroid.com/<device-id>/sms`.
- Tap to copy it. You will paste it into `.env` in [§5](#5-point-laravel-at-the-phone).

**Action** → *Messaging* → **Send SMS**

- **Number**: insert the variable, not typed text. Use the `{v}` / magic-text
  button and pick `sms_number` (it appears as `{v=sms_number}`).
- **Message**: same again with `sms_text` (`{v=sms_text}`).
- Choose **Send SMS directly** if asked — not "open the messaging app", which
  would need someone to tap Send on the phone.

**Name** the macro something like `FabLab OTP`, save it, and make sure its
toggle is **enabled** in the macro list.

Optional but nice for a demo: add a second action, *Notifications → Display
Notification*, showing `{v=sms_text}`. You then get visible proof on the phone
screen that the trigger landed, even before the text is delivered.

---

## 5. Point Laravel at the phone

Open `backend/.env` and set:

```env
SMS_DRIVER=macrodroid
MACRODROID_WEBHOOK_URL=<the URL you copied in §4>
```

Then clear the cached config, or Laravel will keep using the old values:

```bash
cd backend
php artisan config:clear
```

The other MacroDroid lines can stay as they are. `MACRODROID_NUMBER_FORMAT`
controls how the number is written before it reaches Android:

| Value | Sends | Use when |
| :--- | :--- | :--- |
| `e164` | `+639171234567` | Default. What Android prefers. |
| `local` | `09171234567` | Try this if the carrier rejects `+63`. |
| `raw` | Exactly what is stored | Debugging only. |

---

## 6. Test it

**Step 1 — one message, from the command line.** Use a number you can actually
read, i.e. a second phone or the panel's own handset:

```bash
cd backend
php artisan sms:test 09171234567 "FabLab test"
```

The command prints which driver is live and which webhook it is calling, so you
can confirm at a glance that it is not still on `log`:

```
  Driver  : macrodroid
  To      : 09171234567
  Message : FabLab test
  Webhook : //trigger.macrodroid.com/8490…

  Accepted by the driver.
```

**"Accepted by the driver" means MacroDroid took the trigger — not that the
text was delivered.** Always look at the receiving phone. A 200 from MacroDroid
and an empty inbox means the macro ran but the SMS action failed (permission,
load, or the variables from [§3](#3-on-the-phone-create-the-two-variables)).

**Step 2 — the real flow.** Start the app and register a customer:

```bash
php artisan serve
```

1. Go to `http://127.0.0.1:8000/register`.
2. Fill the form with a **real mobile number you are holding**.
3. Click Create Account — a modal asks **SMS or Email**. Choose **SMS**.
4. The 6-digit code arrives as a text: *"Your FABLAB verification code is: 123456"*.
5. Type it on the verify page. **Resend** on that page goes through the same
   path, so it is worth testing once too.

**Step 3 — watch the log while you do it.** In a second terminal:

```bash
php artisan pail
```

`SMS (macrodroid) triggered for …` is success. Anything else is named in
[§7](#7-when-it-does-not-work).

---

## 7. When it does not work

Every line below has actually happened. The reason is always written to
`storage/logs/laravel.log`.

| What the log says | What it means | Fix |
| :--- | :--- | :--- |
| `MACRODROID_WEBHOOK_URL is not set` | `.env` is blank, or `config:clear` was never run | [§5](#5-point-laravel-at-the-phone) |
| `exception: Connection refused` | Nothing is listening — LAN URL with the phone on another network, or the phone is off | [§9](#9-cloud-url-vs-lan-url) |
| `exception: … timed out` | The phone is asleep or has no signal | Wake it; check battery optimisation |
| `failed … Status: 404` | The identifier in the URL does not match the macro's, or the macro is disabled | [§4](#4-on-the-phone-create-the-macro) |
| Nothing in the log at all | Driver is still `log` or `philsms` | `php artisan sms:test` prints the live driver |

And the silent ones, where the log says success but no text arrives:

| Symptom | Cause |
| :--- | :--- |
| Trigger accepted, nothing sent | MacroDroid has no SMS permission |
| Text sent but blank, or to no one | `sms_number` / `sms_text` did not exist as variables before the trigger fired ([§3](#3-on-the-phone-create-the-two-variables)) |
| Works, then stops after a few minutes idle | Battery optimisation froze MacroDroid ([§2](#2-on-the-phone-install-and-permissions)) |
| Carrier rejects the number | Switch `MACRODROID_NUMBER_FORMAT` to `local` |
| Dual-SIM phone texts from the wrong SIM | Set the SIM explicitly in the Send SMS action |

A registration never breaks because of SMS. `send()` returns `false` and logs;
the account is still created. The cost of a failure is only that nobody receives
the code — which is why [§8](#8-if-it-fails-on-the-day) exists.

---

## 8. If it fails on the day

Two fallbacks, in order of how little they disturb the demo:

**1. Choose Email instead of SMS** in the verification modal. Same 6-digit code,
same verify page, different transport — nothing else about the flow changes. If
`MAIL_MAILER=log`, the code is written to `storage/logs/laravel.log` rather than
emailed, which is still enough to finish the registration in front of the panel.

**2. Switch the driver back to `log`:**

```env
SMS_DRIVER=log
```

```bash
php artisan config:clear
```

Every "sent" message then goes to `storage/logs/laravel.log` as
`SMS (log driver) to 09…: Your FABLAB verification code is: 123456`, and nothing
is ever sent. This is the safe default — leave the system on `log` whenever you
are not actively demonstrating SMS, so a stray test does not text a stranger.

---

## 9. Cloud URL vs LAN URL

MacroDroid offers two ways to reach the same macro.

**Cloud (recommended, and what `.env` currently holds):**

```
https://trigger.macrodroid.com/<device-id>/sms
```

The phone can be on mobile data, the laptop on any Wi-Fi. Both only need
internet. **Use this for the defense** — it survives the venue putting you on a
different network, which is exactly what venues do.

**LAN (same Wi-Fi only):**

```
http://<phone-ip>:8080/sms
```

Slightly faster and works with no internet at all, but fragile: the phone's IP
changes whenever it rejoins the network, and many public and campus Wi-Fi
networks use client isolation, which blocks the laptop from reaching the phone
at all.

> **Note for this machine:** the LAN address saved in the `.env` comment is
> `192.168.254.146`, which is stale — this laptop is currently on
> `192.168.1.9`, a different network. If you want the LAN route, re-read the
> phone's current IP from the Webhook trigger screen and check the first three
> numbers match the laptop's. Also disconnect any VPN: it will route the call
> away from the local network.

---

## 10. Defense-day checklist

Run this the morning of, not the night before — a phone that slept for eight
hours is the usual failure.

- [ ] Phone charged, SIM has load, aeroplane mode off.
- [ ] MacroDroid open at least once today; the `FabLab OTP` macro shows as enabled.
- [ ] Battery optimisation still **Unrestricted** for MacroDroid.
- [ ] `SMS_DRIVER=macrodroid` in `backend/.env`, and `php artisan config:clear` has been run.
- [ ] `php artisan sms:test 09XXXXXXXXX "ready"` → text received on the handset.
- [ ] One full registration with a real number → OTP received and accepted.
- [ ] **Resend** on the verify page → second code received.
- [ ] Decide who holds the phone during the demo, and keep its screen awake.
- [ ] Know where the Email fallback button is ([§8](#8-if-it-fails-on-the-day)).

---

## 11. One thing to be ready to answer

If the panel asks *"is this how it would run in production?"* — no, and say so
plainly. MacroDroid is a **local demonstration gateway**: one phone, one SIM,
sending one text at a time. It is the honest choice for a defense because it
proves the OTP flow end-to-end with a real message on a real handset, at no cost
and with no signed-up gateway account.

For production the same `SmsService` switches to a commercial gateway by
changing one line — `SMS_DRIVER=philsms` — with no other code change. That is
why the driver is configurable in the first place, and it is worth pointing at
[`config/sms.php`](../config/sms.php) if the question comes up. It is also the
reason the deployment guide rules MacroDroid out for the hosted build: a phone on
home Wi-Fi is not reachable from a shared-hosting server
([Deployment.md §12](Deployment.md)).
