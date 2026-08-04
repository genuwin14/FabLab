# Setup Guide

This guide walks you through setting up the **Inventory Monitoring System v2** from a fresh clone. Follow the steps in order — by the end you'll have the app running locally with seeded sample data.

> **Estimated time**: ~15 minutes on a machine that already has PHP, Composer, and Node installed.

---

## 1. Prerequisites

Install these tools first. The versions listed are the **minimum** — newer is fine.

| Tool | Version | Notes |
| :--- | :--- | :--- |
| **PHP** | 8.2+ | Required extensions: `pdo_mysql` (or `pdo_sqlite`), `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd` |
| **Composer** | 2.x | PHP dependency manager — [getcomposer.org](https://getcomposer.org/) |
| **Node.js** | 18+ | Comes with NPM — [nodejs.org](https://nodejs.org/) |
| **MySQL or MariaDB** | 8.0+ / 10.5+ | Optional — SQLite works out of the box for testing |
| **Git** | latest | To clone the repository |

### Recommended (Windows)
- **[XAMPP](https://www.apachefriends.org/)** or **[Laragon](https://laragon.org/)** — bundles PHP + MySQL + Apache with one installer.
- **[VS Code](https://code.visualstudio.com/)** — for editing.

### Verify your install
Open a terminal and run:
```bash
php -v          # should show 8.2.x or higher
composer -V     # should show Composer 2.x
node -v         # should show v18+ or v20+
npm -v
```
If any of these fail, install the missing tool before continuing.

---

## 2. Clone the Repository

```bash
git clone <repository-url> Inventory-Monitoring-System-v2
cd Inventory-Monitoring-System-v2
```

The project layout is:
```
Inventory-Monitoring-System-v2/
├── backend/        ← Laravel app (all setup commands run here)
└── docs/           ← Documentation (this file lives here)
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

This downloads Laravel 12, Sanctum, Socialite, DomPDF, PHPWord, and everything else listed in [composer.json](../backend/composer.json) into `vendor/`.

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
The OTP/phone verification uses a **MacroDroid Android device on your LAN** (see [TechStack.md §6](TechStack.md#sms-local-gateway)). For local testing you can leave SMS unconfigured — the OTP will still be generated and logged. To enable real SMS delivery, point the app at your MacroDroid endpoint (configured inside the relevant controller — search for the SMS URL).

### 4.6 (Optional) Configure email
By default `.env.example` ships with `MAIL_MAILER=log` — outgoing emails (e.g., password-reset codes) are written to `storage/logs/laravel.log` instead of being sent. To enable real email, set `MAIL_MAILER=smtp` and fill in the `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD` lines with credentials from your SMTP provider (Gmail SMTP, Mailtrap, SendGrid, etc.).

---

## 5. Run Migrations & Seed Data

```bash
php artisan migrate --seed
```

This will:
1. Create every table listed in [DatabaseSchema.md](DatabaseSchema.md).
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

## 7. Install Frontend Dependencies & Build Assets

```bash
npm install
npm run build
```

- `npm install` downloads Vite, axios, and the 3D mesh tooling (`@gltf-transform/*`, `draco3dgltf`, `meshoptimizer`) used by the customization studio.
- `npm run build` compiles CSS/JS into `public/build/` for production.

> During development, use `npm run dev` instead of `npm run build` — it starts a hot-reloading Vite dev server.

---

## 8. Run the App

### Easy mode — one command does everything
From `backend/`:
```bash
composer dev
```
This starts five processes concurrently (color-coded in your terminal):
- `php artisan serve` — web server on `http://localhost:8000`
- `php artisan queue:listen` — background queue worker (notifications, etc.)
- `php artisan schedule:work` — task scheduler (the daily overdue purchase-order check)
- `php artisan pail` — live log tail
- `npm run dev` — Vite hot-reload

Hit `Ctrl+C` to stop all five at once.

### Manual mode — one process per terminal
If `composer dev` isn't available or you prefer separate windows:
```bash
php artisan serve              # Terminal 1
php artisan queue:listen       # Terminal 2 (optional, for notifications)
php artisan schedule:work      # Terminal 3 (optional, for scheduled checks)
npm run dev                    # Terminal 4
```

> Scheduled tasks are registered in `bootstrap/app.php` — currently the overdue
> purchase-order check, which runs daily at 07:00. Without a scheduler process
> (or the cron entry below in production) that check never runs, and overdue POs
> raise no notification.

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
| **Vite "manifest not found"** | You forgot `npm run build` (production) or `npm run dev` (local). |
| **Images return 404** | You skipped `php artisan storage:link`. |
| **OTP never arrives during registration** | The MacroDroid SMS gateway isn't configured. For local dev, check `storage/logs/laravel.log` — the OTP is logged. |
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

- Read [TechStack.md](TechStack.md) to understand the full stack.
- Read [DatabaseSchema.md](DatabaseSchema.md) for the data model.
- Read [ModuleFlow.md](ModuleFlow.md) to see how each feature flows end-to-end.
- Read the per-role user guides in [UserGuide/](UserGuide/):
  - [CustomerUserGuide.md](UserGuide/CustomerUserGuide.md)
  - [StaffUserGuide.md](UserGuide/StaffUserGuide.md)
  - [AdminUserGuide.md](UserGuide/AdminUserGuide.md)

---

## 12. Production Deployment Notes

This guide targets **local development**. For a production deployment, additionally:

- Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`.
- Set `APP_URL` to your real domain (e.g., `https://inventory.example.com`).
- Serve via Apache or Nginx with a proper virtual host pointing to `backend/public/`.
- Run `php artisan config:cache`, `route:cache`, and `view:cache` for performance.
- Run `npm run build` (not `dev`) to compile minified assets.
- Configure a real mail driver, real SMS gateway, real database credentials.
- Set up a queue worker as a system service (systemd / supervisor) instead of `queue:listen`.
- Add the scheduler to cron so scheduled checks actually fire (the overdue purchase-order alert depends on it):
  ```
  * * * * * cd /path/to/backend && php artisan schedule:run >> /dev/null 2>&1
  ```
  On Windows, register the same command as a Task Scheduler job running every minute.
- Rotate the seeded admin/staff passwords — or remove the `UserSeeder` from `DatabaseSeeder` before deploying.
- Set proper file permissions on `storage/` and `bootstrap/cache/` (writable by the web server user).
