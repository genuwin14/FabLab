# FabLab - Inventory Management

A Laravel 12 application covering the shop front, order pipeline, stock movement, procurement and reporting for three roles: customer, staff and admin.

This README is the setup guide. Follow the steps in order — by the end you'll have the app running locally with seeded sample data.

> **Estimated time**: ~15 minutes on a machine that already has PHP and Composer installed.

The per-role guides live in [docs/UserGuide/](docs/UserGuide/); start with the [hub](docs/UserGuide/README.md) or the [System Process Guide](docs/UserGuide/SystemProcess.md) if you'd rather see the whole system in the order you use it.

---

## 1. Prerequisites

Install these tools first. The versions listed are the **minimum** — newer is fine.

| Tool | Version | Notes |
| :--- | :--- | :--- |
| **PHP** | 8.2+ | Required extensions: `pdo_mysql` (or `pdo_sqlite`), `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd` |
| **Composer** | 2.x | PHP dependency manager — [getcomposer.org](https://getcomposer.org/) |
| **Node.js** | 18+ | Optional — the app runs without it. Only needed to rebuild front-end assets ([§7](#7-optional-front-end-assets)) or to use `composer dev` ([§8](#8-run-the-app)) |
| **MySQL or MariaDB** | 8.0+ / 10.5+ | Optional — SQLite works out of the box for testing |

### Recommended (Windows)
- **[XAMPP](https://www.apachefriends.org/)** or **[Laragon](https://laragon.org/)** — bundles PHP + MySQL + Apache with one installer.
- **[VS Code](https://code.visualstudio.com/)** — for editing.

### Verify your install
Open a terminal and run:
```bash
php -v          # should show 8.2.x or higher
composer -V     # should show Composer 2.x
node -v         # optional — see the Node.js row above
```
If either of the first two fails, install the missing tool before continuing.

---

## 2. Project Layout

Unpack the project folder anywhere on your machine. Inside it:

```
fablab-inventory/
└── backend/        ← Laravel app (all setup commands run here)
    └── docs/       ← User guides
```

> **All commands from this point on run inside the `backend/` folder** unless stated otherwise.

```bash
cd backend
```

---

## 3. Install PHP Dependencies

```bash
composer install
```

This downloads Laravel 12, Sanctum, Socialite, DomPDF, PHPWord, and everything else listed in [composer.json](composer.json) into `vendor/`.

> If you see "*command not found: composer*", you skipped [Prerequisites](#1-prerequisites).

---

## 4. Configure Environment

### 4.1 Copy the example env file
```bash
copy .env.example .env          # Windows (cmd / PowerShell)
cp .env.example .env            # macOS / Linux
```

### 4.2 Generate the application key
```bash
php artisan key:generate
```
This writes a unique `APP_KEY` into your `.env`. The app **will not boot** without it.

### 4.3 Choose your database
Open `.env` and edit the `DB_*` lines.

**Option A — SQLite (easiest, zero config):**
```env
DB_CONNECTION=sqlite
```
Then create the empty database file:
```bash
type nul > database\database.sqlite     # Windows
touch database/database.sqlite          # macOS / Linux
```

**Option B — MySQL / MariaDB (recommended for production):**

1. Create an empty database (using phpMyAdmin, MySQL Workbench, or the CLI):
   ```sql
   CREATE DATABASE inventory_monitoring CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. Update `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=inventory_monitoring
   DB_USERNAME=root
   DB_PASSWORD=
   ```

### 4.4 (Optional) Configure Google Sign-In
If you want the **Login with Google** button to work, fill these in `.env`:
```env
GOOGLE_CLIENT_ID=your-client-id-here
GOOGLE_CLIENT_SECRET=your-client-secret-here
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```
Get credentials from [Google Cloud Console → APIs & Services → Credentials](https://console.cloud.google.com/apis/credentials). If you leave these blank, normal email/password login still works.

### 4.5 (Optional) Configure SMS gateway
OTP and phone verification send through **[PhilSMS](https://philsms.com/)**, read by `app/Services/SmsService.php`. These keys are **not** in `.env.example`, so add them yourself if you want real delivery:
```env
PHILSMS_API_TOKEN=your-api-token-here
PHILSMS_SENDER=FabLabs
PHILSMS_URL=https://dashboard.philsms.com/api/v3
```
Leave them out for local testing — the OTP is still generated and written to `storage/logs/laravel.log`, which is enough to complete a registration.

### 4.6 (Optional) Configure email
By default `.env.example` ships with `MAIL_MAILER=log` — outgoing emails (e.g., password-reset codes) are written to `storage/logs/laravel.log` instead of being sent. To enable real email, set `MAIL_MAILER=smtp` and fill in the `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD` lines with credentials from your SMTP provider (Gmail SMTP, Mailtrap, SendGrid, etc.).

---

## 5. Run Migrations & Seed Data

```bash
php artisan migrate --seed
```

This will:
1. Create every table defined by the migrations in `database/migrations/`.
2. Seed sample categories, suppliers, products, raw materials, textures, equipment, orders, and purchase orders.
3. Create three default user accounts (see below).

### Default seeded accounts

| Role | Email | Password |
| :--- | :--- | :--- |
| Admin | `admin@gmail.com` | `password` |
| Staff | `staff@gmail.com` | `password` |
| Customer | `customer@gmail.com` | `password` |

> **Change these immediately in production.** They exist only to let you log in on a fresh install.

---

## 6. Storage Symlink

The app stores uploaded files (product images, etc.) in `storage/app/public`. Link it to `public/storage` so they're served correctly:

```bash
php artisan storage:link
```

---

## 7. (Optional) Front-end Assets

**You can skip this step.** The interface is server-rendered Blade, and every front-end library it uses — Bootstrap 5, Bootstrap Icons, Google Fonts, ApexCharts, three.js, JsBarcode — is loaded from a CDN in the Blade layouts. There is no build step between you and a working app.

The only Vite-managed files are `resources/css/app.css` and `resources/js/app.js`, which are near-empty stubs. Both layouts guard the directive that loads them:

```blade
@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endif
```

So with no `public/build/` and no Vite dev server, the tag is skipped and nothing breaks. Run the build only if you start putting your own CSS or JS into those two files:

```bash
npm install
npm run build      # or: npm run dev, for a hot-reloading dev server
```

> Because the CDN libraries are fetched by the browser, the app needs an internet connection to look right — offline, the pages render unstyled.

---

## 8. Run the App

### Normal mode — one command
From `backend/`:
```bash
php artisan serve
```
That's the whole thing. Open `http://localhost:8000` and log in. Notifications are written straight to the database rather than queued, so no worker is needed for them.

Two optional extras, each in its own terminal:
```bash
php artisan schedule:work      # only for the daily overdue purchase-order check
php artisan queue:listen       # only if you later move work onto the queue
```

> Scheduled tasks are registered in `bootstrap/app.php` — currently just the
> overdue purchase-order check, which runs daily at 07:00. Without a scheduler
> process (or the cron entry in [§12](#12-production-deployment-notes) in
> production) that check never runs, and overdue POs raise no notification.

### Everything at once — needs Node
If you have Node installed and want the extras plus a live log tail in one window:
```bash
composer dev
```
This shells out to `npx concurrently`, so it **will not work without Node**, even though the app itself doesn't need it. It starts five processes (color-coded in your terminal):
- `php artisan serve` — web server on `http://localhost:8000`
- `php artisan queue:listen` — background queue worker (notifications, etc.)
- `php artisan schedule:work` — task scheduler (the daily overdue purchase-order check)
- `php artisan pail` — live log tail
- `npm run dev` — Vite hot-reload

Hit `Ctrl+C` to stop all five at once.

### Open in your browser
Navigate to **[http://localhost:8000](http://localhost:8000)** and log in with one of the [seeded accounts](#default-seeded-accounts).

---

## 9. Verify the Install

Run through this 60-second smoke test to confirm everything works:

1. Go to `http://localhost:8000/login`.
2. Log in as `admin@gmail.com` / `password`. You should land on `/admin/dashboard` with KPIs.
3. Log out, then log in as `customer@gmail.com` / `password`. You should land on `/customer/shop` and see seeded products.
4. Add a product to your cart — the cart icon count should update.
5. Open `http://localhost:8000/admin/reports/materials` (after logging back in as admin) — the report page should render.

If all five pass, the system is set up correctly.

---

## 10. Common Issues

| Symptom | Fix |
| :--- | :--- |
| **"No application encryption key has been specified"** | You skipped `php artisan key:generate`. Run it. |
| **`SQLSTATE[HY000] [2002] No connection`** | MySQL isn't running, or `DB_*` values in `.env` don't match your local DB. Start MySQL (XAMPP/Laragon) or switch to SQLite. |
| **`could not find driver` (pdo_mysql)** | Enable `extension=pdo_mysql` in your `php.ini`, then restart your terminal. |
| **`419 Page Expired`** on login forms | `APP_KEY` was changed after sessions were created. Clear `storage/framework/sessions/*` and your browser cookies. |
| **Pages render unstyled** | The CSS comes from a CDN — check the machine's internet connection. |
| **Images return 404** | You skipped `php artisan storage:link`. |
| **OTP never arrives during registration** | The PhilSMS keys aren't set ([§4.5](#45-optional-configure-sms-gateway)). For local dev, check `storage/logs/laravel.log` — the OTP is logged there. |
| **`Class "GD" not found` (PDF/image export)** | Enable `extension=gd` in `php.ini`. |
| **Slow first page-load** | Run `php artisan config:cache && php artisan route:cache` in production. Skip in dev (changes won't pick up). |

### Reset & start over
If something gets into a weird state and you want to wipe everything:
```bash
php artisan migrate:fresh --seed
```
This drops every table, re-runs all migrations, and re-seeds the sample data. **Destroys all local data — do not run in production.**

---

## 11. Next Steps

Read the guides in [docs/UserGuide/](docs/UserGuide/):

- [System Process Guide](docs/UserGuide/SystemProcess.md) — every process in the order you have to do them, from an empty database to a completed order. Start here.
- [Customer Guide](docs/UserGuide/CustomerUserGuide.md)
- [Staff Guide](docs/UserGuide/StaffUserGuide.md)
- [Admin Guide](docs/UserGuide/AdminUserGuide.md)

---

## 12. Production Deployment Notes

This guide targets **local development**. For a production deployment, additionally:

- Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`.
- Set `APP_URL` to your real domain (e.g., `https://inventory.example.com`).
- Serve via Apache or Nginx with a proper virtual host pointing to `backend/public/`.
- Run `php artisan config:cache`, `route:cache`, and `view:cache` for performance.
- Only if you added your own CSS/JS to `resources/`, run `npm run build` (not `dev`) to compile minified assets. A stock deployment doesn't need Node at all.
- Configure a real mail driver, real SMS gateway, real database credentials.
- Set up a queue worker as a system service (systemd / supervisor) instead of `queue:listen`.
- Run `php artisan images:offload` once, after `storage:link`, to move any images still held in the database as base64 onto the public disk. `--dry-run` lists what would move. Rows it hasn't converted keep rendering either way.
- Add the scheduler to cron so scheduled checks actually fire (the overdue purchase-order alert depends on it):
  ```
  * * * * * cd /path/to/backend && php artisan schedule:run >> /dev/null 2>&1
  ```
  On Windows, register the same command as a Task Scheduler job running every minute.
- Rotate the seeded admin/staff passwords — or remove the `UserSeeder` from `DatabaseSeeder` before deploying.
- Set proper file permissions on `storage/` and `bootstrap/cache/` (writable by the web server user).
