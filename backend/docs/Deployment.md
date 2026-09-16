# FabLab – Deployment Guide (Hostinger Shared Hosting)

This guide takes the FabLab application from your development machine to a live
site on a **Hostinger shared hosting "Single" plan** (1 website, 10 GB SSD,
1 mailbox, PHP + MySQL).

It is written for whoever does the deployment — you do not need to have written
the code, but you do need the project folder, the hPanel login, and a domain
name pointed at the plan.

> **Estimated time**: about 2 hours the first time, most of it waiting for
> uploads. Later re-deployments take ~15 minutes ([§13](#13-deploying-an-update)).

The local development setup is a different document — see the
[README](../README.md). Everything here assumes the app already runs on your
machine.

---

## 1. What the plan gives you, and what it does not

| Plan feature | What it means for FabLab |
| :--- | :--- |
| 1 website | Production only. No staging site on this plan — test locally before you deploy. |
| 10 GB SSD storage | Comfortable. The app is roughly 300 MB installed (99 MB of `vendor/`, 132 MB of 3D models); the rest is room for uploaded designs and order files. |
| 1 mailbox (1 yr free) | Use it as the sender for receipts, OTPs and password resets ([§10](#10-email)). |
| PHP + MySQL | Everything FabLab needs. Laravel 12 requires PHP 8.2+. |
| Cron jobs | Required — the scheduler and the mail queue both run from cron ([§9](#9-cron-jobs)). |

**Things shared hosting does *not* give you**, and how this guide works around them:

| Missing | Workaround |
| :--- | :--- |
| Node.js on the server | Not needed. The layout only loads a compiled bundle if one exists; if you want it, build it on your machine and upload the result ([§3](#3-build-the-release-on-your-machine)). |
| A long-running queue worker (Supervisor) | Drain the queue from cron instead ([§9.2](#92-queue-worker)). |
| Root access / custom `php.ini` | All the settings you need are exposed in hPanel ([§5](#5-configure-php-in-hpanel)). |
| Guaranteed SSH | Every step below has a no-SSH alternative. Check **hPanel → Advanced → SSH Access** to see which path you are on. |

---

## 2. Before you start

Collect these first — stopping halfway to hunt for a credential is how deployments go wrong.

- [ ] hPanel login for the Hostinger account.
- [ ] A domain name, already pointed at the Hostinger nameservers (check **hPanel → Domains**; DNS can take a few hours to propagate).
- [ ] The full project folder on your machine, with the app working locally.
- [ ] Google OAuth credentials (Client ID + Secret) from the Google Cloud console — you will add a new redirect URI in [§11](#11-google-sign-in).
- [ ] The PhilSMS API token, if you are switching SMS on ([§12](#12-sms)).
- [ ] Decide now: **fresh empty database, or a copy of your local data?** ([§8.3](#83-schema-and-data))

---

## 3. Build the release on your machine

The server has no Composer-with-Node toolchain you can rely on, so you produce a
finished, ready-to-run copy locally and upload that.

Run this inside `backend/`:

```bash
# Production dependencies only, with an optimised autoloader.
composer install --no-dev --optimize-autoloader
```

> **Why `--no-dev`?** It drops PHPUnit, Faker, Pail and friends — roughly a third
> of `vendor/` and several thousand files you would otherwise be uploading and
> that count against the account's inode limit.

The front-end bundle is **optional**. `layout/app.blade.php` wraps its `@vite`
call in a `file_exists(public_path("build/manifest.json"))` check, so the app
runs fine with no build at all — Bootstrap 5 and Bootstrap Icons come from the
jsDelivr CDN, and the only thing in `resources/css/app.css` is the Poppins font
override. Build it if you want that font applied:

```bash
npm install
npm run build    # writes public/build/
```

> Because Bootstrap is loaded from a CDN, the site needs the *visitor* to have
> internet access to jsDelivr. That is normally a given, but it is the reason a
> page can look unstyled on a locked-down campus network.

### 3.1 What goes up, and what does not

Several folders that the app **needs at runtime** are excluded from git
(`.gitignore`), so a `git clone` on the server would produce a broken site.
Check this list before you zip anything:

| Path | Upload? | Notes |
| :--- | :--- | :--- |
| `vendor/` | **Yes** | Not in git. Without it there is no Laravel. |
| `public/build/` | Only if you built it | Not in git. Optional — see above. Skipping it costs you the Poppins font and nothing else. |
| `public/gbl/` | **Yes** | Not in git. ~132 MB of `.glb` product models for the 3D customiser. |
| `public/img/`, `public/js/` | Yes | ~7 MB of shop imagery and the customiser scripts. |
| `storage/app/public/` | Yes, structure only | Create the empty `designs/`, `products/`, `profile-photos/`, `textures/` folders. Upload existing files only if you are carrying local data over. |
| `node_modules/` | **No** | Build-time only. Tens of thousands of files — it would eat the inode allowance for nothing. |
| `.env` | **No** | Never upload the local one. You write a fresh production `.env` in [§7](#7-write-the-production-env). |
| `storage/framework/cache`, `sessions`, `views` contents | **No** | Upload the empty folders; the app refills them. |
| `storage/logs/*.log`, `.phpunit.result.cache`, `jmeter.log` | **No** | Local noise. |
| `tests/`, `tools/`, `docs/` | Optional | Harmless, but they are dead weight on the server. |

Zip the `backend/` folder with those exclusions applied. Expect roughly
**250 MB**; the `.glb` models are most of it and they barely compress.

> **Tip:** upload one `.zip` and extract it on the server. Uploading 20,000 loose
> files through a browser file manager will take hours and will silently drop files.

---

## 4. Choose where the files live

This is the one decision that trips people up. Laravel's webroot is
`backend/public/` — **only that folder may be web-accessible.** If you drop the
whole project into `public_html`, your `.env` (database password, Google secret,
app key) becomes downloadable over the internet.

### Option A — Change the document root (preferred)

1. Upload and extract the project to `~/domains/<your-domain>/fablab/`, so that
   `~/domains/<your-domain>/fablab/artisan` exists.
2. In hPanel, open **Websites → your site → Dashboard → Advanced → Website root
   folder** (Hostinger has also labelled this "Change website's root directory").
3. Set it to `domains/<your-domain>/fablab/public` and save.
4. Load your domain. You should get the FabLab landing page, not a directory listing.

### Option B — Split the public folder (fallback)

Use this if your plan does not expose the root-folder setting.

1. Upload the project to `~/fablab/` (a sibling of `public_html`, **not** inside it).
2. Move the *contents* of `~/fablab/public/` into `~/public_html/` — including the
   hidden `.htaccess`.
3. Edit `~/public_html/index.php` and repoint its two `require` lines one level further up:

   ```php
   require __DIR__.'/../fablab/vendor/autoload.php';
   $app = require_once __DIR__.'/../fablab/bootstrap/app.php';
   ```

   Also update the maintenance-mode check near the top of the same file:

   ```php
   if (file_exists($maintenance = __DIR__.'/../fablab/storage/framework/maintenance.php')) {
   ```

> Whichever option you pick, confirm the guard works: browsing to
> `https://<your-domain>/.env` must return **403 or 404**, never a file download.
> If it downloads, stop and fix the layout before going any further.

Hostinger serves with LiteSpeed, which reads the `.htaccess` that ships in
`public/` — the rewrite rules that make Laravel's routes work are already there.
You do not need to write any server config.

---

## 5. Configure PHP in hPanel

**hPanel → Advanced → PHP Configuration.**

### 5.1 Version

Select **PHP 8.2 or 8.3**. Laravel 12 refuses to boot on anything older.

### 5.2 Extensions

Tick these on the *Extensions* tab. They are what the installed packages
actually require:

| Extension | Needed by |
| :--- | :--- |
| `pdo_mysql`, `mysqli` | Database |
| `mbstring`, `ctype`, `tokenizer`, `json`, `filter`, `hash`, `pcre`, `session` | Laravel core |
| `openssl` | Encryption, HTTPS calls, SMTP |
| `curl` | Google sign-in, PhilSMS |
| `fileinfo` | File uploads |
| `dom`, `libxml`, `xml`, `iconv` | DOCX and PDF generation |
| `gd` | Barcodes, PDF images, DOCX images |
| `zip` | DOCX reports (PhpWord) |
| `bcmath` | Money and stock arithmetic |

### 5.3 PHP options

On the *PHP options* tab, set at least:

| Setting | Value | Why |
| :--- | :--- | :--- |
| `memory_limit` | `256M` (512M if available) | Building the sales and inventory DOCX/PDF reports is memory-hungry. |
| `max_execution_time` | `120` | Report generation and the materials import run long. |
| `upload_max_filesize` | `16M` | The materials DOCX import accepts files up to 10 MB. |
| `post_max_size` | `20M` | Must exceed `upload_max_filesize`. |
| `max_input_time` | `120` | Large uploads on a slow connection. |
| `opcache` | On | Free speed; Laravel benefits a lot. |

---

## 6. Create the database

**hPanel → Databases → MySQL Databases.**

1. Create a database, e.g. `u123456789_fablab`.
2. Create a user, e.g. `u123456789_fablab`, with a long generated password. **Save it** — you need it in the next step.
3. Grant that user all privileges on that database.

Hostinger prefixes both names with your account ID. Write down the exact values;
`DB_HOST` on Hostinger shared hosting is normally `localhost`.

---

## 7. Write the production `.env`

Create `.env` in the project root on the server (next to `artisan`) — use the
hPanel **File Manager → New File**, or upload it over SFTP. Start from
`.env.example` and set the values below.

```ini
APP_NAME="FabLab"
APP_ENV=production
APP_KEY=                      # filled in by key:generate — see §8.2
APP_DEBUG=false
APP_URL=https://your-domain.com

APP_LOCALE=en
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u123456789_fablab
DB_USERNAME=u123456789_fablab
DB_PASSWORD=<the password from §6>

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_DOMAIN=your-domain.com
SESSION_SECURE_COOKIE=true
SANCTUM_STATEFUL_DOMAINS=your-domain.com,www.your-domain.com

CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
BROADCAST_CONNECTION=log

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_SCHEME=smtps
MAIL_USERNAME=noreply@your-domain.com
MAIL_PASSWORD=<mailbox password>
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="FabLab"

GOOGLE_CLIENT_ID=<from Google Cloud>
GOOGLE_CLIENT_SECRET=<from Google Cloud>
GOOGLE_REDIRECT_URI=https://your-domain.com/auth/google/callback

SMS_DRIVER=log
SMS_COUNTRY_CODE=63
PHILSMS_URL=https://dashboard.philsms.com/api/v3
PHILSMS_API_TOKEN=
PHILSMS_SENDER=FabLabs

FABLAB_PR_DEADLINE_DAYS=7
FABLAB_PROCUREMENT_EMAIL=procurement@cspc.edu.ph
```

The five that are most often left at their development values, and what breaks
if you do:

| Key | Consequence of getting it wrong |
| :--- | :--- |
| `APP_DEBUG=false` | Left `true`, every error page shows stack traces, file paths and config values to the public. Non-negotiable. |
| `APP_URL` | Wrong value breaks emailed links, PDF letterheads and password-reset URLs. |
| `SESSION_DOMAIN` / `SANCTUM_STATEFUL_DOMAINS` | Left as `localhost`, logins appear to succeed and then bounce straight back to the login page. |
| `SESSION_SECURE_COOKIE=true` | Only set this **after** SSL is live ([§14](#14-ssl)), otherwise nobody can log in over plain HTTP. |
| `GOOGLE_REDIRECT_URI` | Must match the Google Cloud entry character for character, https included. |

> Set the file's permissions to **600** if the File Manager lets you. `.env` should
> never be world-readable.

---

## 8. Install the application

Everything in this section is a one-time job. Pick the column that matches your access.

### 8.1 Running commands without SSH

If SSH is not available on your plan, you can still run any artisan command:
create a **cron job** in hPanel with the command you need, set it to run every
minute, wait for it to fire once, then **delete the cron job**. Check
`storage/logs/` for the outcome.

The database import in §8.3 avoids most of this, so you will only need the trick
two or three times.

### 8.2 Application key

The app cannot decrypt sessions or cookies without one.

```bash
php artisan key:generate --force
```

No SSH? Generate it on your own machine with the same command, then copy the
`APP_KEY=base64:...` line into the server's `.env`.

> Never reuse a key across environments, and never change it after go-live —
> it invalidates every session and every encrypted column.

### 8.3 Schema and data

**Fresh, empty system** (recommended for a real handover):

```bash
php artisan migrate --force
```

That creates all 47 tables. You then need at least one admin account to log in
with — the simplest safe route is to seed only the users and then immediately
change the passwords:

```bash
php artisan db:seed --class=UserSeeder --force
```

> ⚠️ **Do not run the full `php artisan db:seed` on a production site.** The
> default seeder loads the complete demo dataset — sample products, fake orders,
> invented suppliers and purchase requests — and the accounts it creates use the
> password `password`. If you do use it (for a demo or defence run), change
> every seeded password before the site is reachable by anyone else.

**Carrying your local data over** (for a demo build where the seeded catalogue
*is* the content):

1. On your machine: `mysqldump -u root fablab > fablab.sql`
2. In hPanel → **Databases → phpMyAdmin**, open the new database and import that file.
3. Skip `migrate` entirely — the dump already contains the schema.
4. Copy `storage/app/public/` and `storage/app/private/` up as well, or the
   product images and order PDFs the rows point at will 404.

### 8.4 Storage symlink

Uploaded designs, product photos and profile pictures are served through
`public/storage`, which is a symlink:

```bash
php artisan storage:link
```

Run it via the cron trick from §8.1 if you have no SSH. To confirm it worked,
open any product image on the live site — a broken image here almost always
means a missing symlink.

### 8.5 Move inline images onto disk (optional)

Some product, raw-material and texture rows still store their image as a base64
data URI inside the database. They render fine, but they bloat the database and
every page that lists them. Once the symlink above is in place:

```bash
php artisan images:offload --dry-run   # see what would move
php artisan images:offload             # actually move it
```

This is safe to run at any time, and safe to skip.

### 8.6 Permissions

```bash
chmod -R 775 storage bootstrap/cache
```

The web server must be able to write to both. "The stream or file could not be
opened" in a 500 error is this.

### 8.7 Cache for speed

Run these **last**, after `.env` is final:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

> **Remember this:** once config is cached, editing `.env` changes nothing until
> you run `php artisan config:clear` (or re-run `config:cache`). Every confusing
> "I changed it and it did not take effect" moment on a deployed Laravel site is
> this one.

---

## 9. Cron jobs

**hPanel → Advanced → Cron Jobs.** Two entries are needed. Without them, parts
of the system quietly stop working.

First find your PHP CLI path — over SSH, `which php`. It is typically
`/usr/bin/php`, and `/opt/alt/php82/usr/bin/php` if you need to pin the version.
Replace `/home/u123456789/domains/your-domain.com/fablab` below with your real path.

### 9.1 Scheduler

```
* * * * * /usr/bin/php /home/u123456789/domains/your-domain.com/fablab/artisan schedule:run >> /dev/null 2>&1
```

This single line drives all three scheduled tasks:

| Task | When | What it does |
| :--- | :--- | :--- |
| `notifications:check-overdue-pos` | Daily 07:00 | Flags purchase orders past their expected date. |
| `orders:close-expired-prs` | Daily 00:15 | Closes orders whose purchase-request deadline has passed. |
| `stock:retune-thresholds` | 1st of month, 01:30 | Recalculates low-stock thresholds from the previous month. |

### 9.2 Queue worker

Order receipt emails are queued (`App\Mail\OrderReceipt` implements
`ShouldQueue`), so **with no worker they are written to the `jobs` table and
never sent.** This cron drains the queue and exits:

```
* * * * * /usr/bin/php /home/u123456789/domains/your-domain.com/fablab/artisan queue:work --stop-when-empty --max-time=55 --tries=3 >> /dev/null 2>&1
```

`--stop-when-empty --max-time=55` keeps it from overlapping the next run or
tripping the host's process limits.

> **If hPanel will not let you schedule every minute** — some shared plans have a
> 5- or 15-minute minimum — you have two choices. Either accept that receipts are
> delayed by up to that interval, or set `QUEUE_CONNECTION=sync` in `.env` so
> mail sends inline during checkout. `sync` makes the customer wait a second or
> two longer at checkout and turns a mail-server outage into a checkout error, so
> prefer the cron if you can get it.
>
> The scheduler cron does **not** have this problem in the same way — its tasks
> only need to fire once a day, so any interval of an hour or less is fine.

### 9.3 Timezone

Cron on the server runs in the server's timezone, which may not be
Asia/Manila. If "daily at 07:00" matters, check `config/app.php`'s timezone and
the server clock together before you trust the schedule.

---

## 10. Email

The plan includes one mailbox, and the app sends real mail: registration OTPs,
password resets, order receipts, low-stock alerts and status changes.

1. **hPanel → Emails → Email Accounts** — create `noreply@your-domain.com`.
2. Put its SMTP details in `.env` as shown in §7 (`smtp.hostinger.com`, port
   465, `MAIL_SCHEME=smtps`; port 587 with `MAIL_SCHEME=tls` also works).
3. Verify with the built-in commands:

   ```bash
   php artisan smtp:test your-address@gmail.com
   php artisan test:registration-email your-address@gmail.com
   php artisan test:send-receipt your-address@gmail.com
   ```

> **Do not carry the local Gmail SMTP settings into production.** A personal
> Gmail account needs an app password, is rate-limited, and puts a student's
> personal address on every system email. The domain mailbox is also far less
> likely to land in spam, because mail sent from Hostinger's servers matches the
> domain's SPF record.

If email lands in spam, check **hPanel → Emails → DNS settings** and make sure
the SPF and DKIM records for the domain are in place.

---

## 11. Google sign-in

In the [Google Cloud console](https://console.cloud.google.com/) → your project →
**APIs & Services → Credentials → your OAuth 2.0 Client**:

- **Authorised JavaScript origin**: `https://your-domain.com`
- **Authorised redirect URI**: `https://your-domain.com/auth/google/callback`

Keep the localhost entries if you still develop locally — a client can hold
several. The redirect URI must match `GOOGLE_REDIRECT_URI` in `.env` exactly;
`redirect_uri_mismatch` on the Google screen means it does not (a missing `www`
or a stray trailing slash is the usual cause).

If the app is still on a Google "Testing" publishing status, only the test users
you listed can sign in. Publish it before a demo.

---

## 12. SMS

`SMS_DRIVER` chooses how text messages go out:

| Driver | Use when |
| :--- | :--- |
| `log` | Default and safe. Messages are written to `storage/logs/` and nothing is sent. Start here. |
| `philsms` | Real SMS through the paid gateway. Set `PHILSMS_API_TOKEN` and `PHILSMS_SENDER`. |
| `macrodroid` | An Android phone sends from its own SIM. **Not viable here** — it needs a webhook the phone can reach, and a phone on a home Wi-Fi network is not reachable from Hostinger's servers. Use `philsms` in production. |

Test before you rely on it:

```bash
php artisan sms:test 09171234567
```

---

## 13. Deploying an update

Later changes follow a shorter loop:

1. Locally: `composer install --no-dev --optimize-autoloader` (and `npm run build`, if you use the bundle).
2. Put the site into maintenance mode: `php artisan down` (skip if you have no
   command access; the window is short).
3. Upload the changed files. In practice that is `app/`, `resources/`,
   `routes/`, `config/`, `database/migrations/`, `public/build/` — and `vendor/`
   only if `composer.lock` changed.
4. Run any new migrations: `php artisan migrate --force`.
5. Rebuild the caches: `php artisan config:cache route:cache view:cache` (as
   three separate commands).
6. `php artisan up`.

> **Never overwrite the server's `.env` with your local one.** It is the single
> most common way to take a live site down — it swaps the production database
> credentials and `APP_KEY` for development ones, and logs everyone out.
>
> **If you do use `public/build/`, never upload it half-finished.** Upload the
> new `build` folder alongside the old one and then swap, so no request is ever
> served a manifest that points at files which are not there yet.

---

## 14. SSL

**hPanel → Websites → your site → Security → SSL.** Install the free Let's
Encrypt certificate and turn on **Force HTTPS**.

Only after `https://your-domain.com` loads with a padlock:

- set `APP_URL` to the `https://` address,
- set `SESSION_SECURE_COOKIE=true`,
- run `php artisan config:cache`.

Doing it in the other order locks everyone out, because a secure cookie is never
sent over plain HTTP.

---

## 15. Go-live checklist

Walk the whole system once before handing over the URL. Each line below has
failed on a real deployment.

- [ ] `https://your-domain.com/.env` returns 403/404, not a download.
- [ ] `https://your-domain.com/up` returns the health-check page.
- [ ] The landing page is styled — if it is bare text, the Bootstrap CDN is not loading.
- [ ] Product images and the 3D customiser models load (`public/gbl/` uploaded, storage symlink live).
- [ ] Register a new customer: the OTP email arrives.
- [ ] Log in with Google.
- [ ] Log in as admin, staff and customer.
- [ ] Place a test order end to end; the receipt email arrives within a minute or two (queue cron).
- [ ] Generate a sales report as PDF and as DOCX.
- [ ] Upload a product image from the admin panel and see it appear on the shop.
- [ ] Run the materials DOCX import with a real file.
- [ ] Trigger a low-stock alert and confirm the notification.
- [ ] Forgot-password produces a working reset link with the live domain in it.
- [ ] Delete the demo data and test accounts if this is a real handover.
- [ ] Take a first backup ([§17](#17-backups)).

---

## 16. Troubleshooting

| Symptom | Cause | Fix |
| :--- | :--- | :--- |
| 500 error, blank page | Something failed at boot | Read `storage/logs/laravel-*.log`. Temporarily set `APP_DEBUG=true`, reproduce, then **set it back to false**. |
| "No application encryption key" | `APP_KEY` empty | §8.2. |
| "The stream or file could not be opened" | `storage/` not writable | §8.6. |
| Login redirects back to login | Session cookie rejected | `SESSION_DOMAIN`, `SANCTUM_STATEFUL_DOMAINS`, and `SESSION_SECURE_COOKIE` vs actual HTTPS. |
| Site unstyled | The jsDelivr CDN is unreachable, or `public/build/` is stale | Open the browser console and look for blocked CDN requests. If you deployed a bundle, re-run `npm run build` and re-upload `public/build/`. |
| Images 404 under `/storage/...` | Symlink missing | §8.4. |
| 3D customiser shows nothing | `public/gbl/` not uploaded | It is gitignored — §3.1. |
| Config change has no effect | Cached config | `php artisan config:clear`, then `config:cache`. |
| Receipts never arrive | No queue worker | §9.2. Check the `jobs` table — rows piling up confirm it. |
| `redirect_uri_mismatch` | Google URI mismatch | §11. |
| Mixed-content warnings behind HTTPS | URLs generated as `http://` | Confirm `APP_URL` is `https://`; if it persists, add `URL::forceScheme('https')` in `AppServiceProvider::boot()` when `APP_ENV=production`. |
| 404 on every route but `/` | Rewrite rules not applied | `public/.htaccess` was not uploaded — it is a hidden file, so enable "show hidden files" in the File Manager. |
| Upload of a large DOCX fails | PHP limits | §5.3. |

---

## 17. Backups

Shared hosting is not a backup. Set up both of these on day one:

1. **hPanel → Files → Backups** — confirm automatic backups are enabled for the
   plan and note how far back they go.
2. **Your own copy** — before every deployment, export the database from
   phpMyAdmin and download `storage/app/`. The database holds the orders; that
   folder holds the uploaded designs and generated order PDFs. Losing either is
   unrecoverable.

---

## 18. When this plan stops being enough

The Single plan is sized for a defence, a pilot, or a lab with light traffic. The
signals that it is time to move to a VPS or a higher shared tier:

- Load testing showed the app is comfortable at ~50 concurrent shop users on a
  single Apache instance ([Testing.md](Testing.md)) — but that was dedicated
  local hardware, not a shared server with neighbours competing for CPU.
- Receipt emails feeling slow because cron cannot run every minute (§9.2).
- Uploaded designs pushing `storage/` toward the 10 GB ceiling.
- Needing a staging site — the plan allows only one.

A VPS additionally buys you a real Supervisor-managed queue worker, Redis for
cache and sessions, and SSH by default, which turns every "run it from a
throwaway cron job" workaround in this guide back into a normal command.
