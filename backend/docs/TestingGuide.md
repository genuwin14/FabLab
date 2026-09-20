# FabLab – Performance and Security Testing: User Guide

How to run the two tests yourself, from a cold machine to a finished report.

This is the **procedure**. The **results** of the run recorded on 6 September 2026 — the tables, the graphs and what they mean — are in [Testing.md](Testing.md). Read this one when you need to reproduce the run, produce fresh screenshots, or re-test after a change.

| | |
| :--- | :--- |
| Time needed | About 5 minutes of setup, 2 minutes for JMeter, 50–60 minutes for the full ZAP scan |
| You will produce | A JMeter HTML dashboard, a ZAP report in HTML/JSON/Markdown, and screenshots |
| Everything lands in | `backend/docs/testing/` |

---

## 1. What you need installed

All four are already on the development machine at these exact paths. If you are on a different machine, install them and substitute your own paths everywhere below.

| Tool | Version | Path on this machine |
| :--- | :--- | :--- |
| **JDK** (runs both tools) | Eclipse Temurin 17 | `C:\Program Files\Eclipse Adoptium\jdk-17.0.20.101-hotspot` |
| **Apache JMeter** | 5.6.3 | `C:\tools\apache-jmeter-5.6.3\bin\jmeter.bat` |
| **OWASP ZAP** | 2.17.0 | `C:\Program Files\ZAP\Zed Attack Proxy\zap-2.17.0.jar` |
| **XAMPP** (Apache 2.4 + PHP 8.2 + MySQL 8) | — | `C:\xampp` |

The JDK is **not on PATH**. Every PowerShell window you use for testing must start with:

```powershell
$env:JAVA_HOME = 'C:\Program Files\Eclipse Adoptium\jdk-17.0.20.101-hotspot'
$env:PATH      = "$env:JAVA_HOME\bin;$env:PATH"
java -version    # should print 17.x — if it doesn't, nothing below will work
```

The two things you run live in the repo:

| File | What it is |
| :--- | :--- |
| [tools/loadtest/fablab-load-test.jmx](../tools/loadtest/fablab-load-test.jmx) | The JMeter test plan |
| [tools/loadtest/zap-scan.ps1](../tools/loadtest/zap-scan.ps1) | The script that drives ZAP through its REST API |

---

## 2. Prepare the system under test

Do all five steps. Steps 2.2 and 2.3 are the ones people forget, and skipping either one makes the results wrong rather than merely different.

### 2.1 Serve the app through Apache, not `artisan serve`

`php artisan serve` is single-threaded on Windows. Point 50 virtual users at it and every request queues behind the one in front, so you measure the queue, not the application. Both tests must go through Apache.

A standalone Apache config already exists at `C:\tools\httpd-fablab.conf`. It listens on **port 8080**, uses its own PID and log files, and serves `C:\FabLab\backend\public`:

```apache
PidFile   "C:/tools/httpd-fablab.pid"
ErrorLog  "C:/tools/httpd-fablab-error.log"
CustomLog "C:/tools/httpd-fablab-access.log" common
<VirtualHost *:8080>
    ServerName    fablab.local
    DocumentRoot  "C:/FabLab/backend/public"
    <Directory "C:/FabLab/backend/public">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Start it in its own PowerShell window and leave that window open:

```powershell
C:\xampp\apache\bin\httpd.exe -f C:\tools\httpd-fablab.conf -D FOREGROUND
```

It runs in the foreground so you stop it with **Ctrl+C** when you're done. It needs no administrator rights, and it does **not** touch the XAMPP Apache service on port 80 — leave that one alone; restarting it needs admin and will fail.

Start **MySQL** from the XAMPP Control Panel as usual, then check the site answers:

```powershell
(Invoke-WebRequest http://127.0.0.1:8080/ -UseBasicParsing).StatusCode   # 200
```

### 2.2 Cache the configuration — this is not optional

```powershell
cd C:\FabLab\backend
php artisan config:cache
```

Without this, roughly **1 request in 20 fails with HTTP 500** under concurrency. Apache on Windows runs PHP as a threaded module, and Laravel's `.env` loader uses `putenv()`/`getenv()`, which are shared across the threads of one process. When 50 requests bootstrap at the same instant they race on that shared environment, and some of them end up reading Laravel's *defaults* instead of your `.env` — you'll see `Unknown database 'laravel'` and `Database file at path [fablab_db] does not exist` in `storage/logs/laravel.log`. With the config cached, `env()` is never called at request time and the race disappears. Section 2.2 of [Testing.md](Testing.md#22-run-1--5-of-requests-failed-with-http-500) has the evidence from both runs.

> If you change `.env` after this point, the change has no effect until you re-run `config:cache`.

### 2.3 Send mail to the log, not to Gmail

ZAP's active scan fuzzes the **register** and **forgot password** forms, which means it submits them hundreds of times. In `.env`:

```env
MAIL_MAILER=log
```

then `php artisan config:cache` again. Mail now goes to `storage/logs/laravel.log`. Skip this and the scan sends real email through the configured Gmail account.

### 2.4 Have the seeded accounts in place

Both tests sign in as the seeded customer, `customer@gmail.com` / `password` (see `database/seeders/UserSeeder.php`). If the database is empty or stale:

```powershell
php artisan migrate:fresh --seed
```

### 2.5 Optional but tidy

Set `APP_URL=http://127.0.0.1:8080` while testing so anything the app generates absolutely (mail links, PDF slips) points at the instance actually under test. Put it back to `:8000` afterwards, along with `MAIL_MAILER`.

---

## 3. Part A — Load testing with JMeter

### 3.1 What the plan does

`fablab-load-test.jmx` simulates one customer session per thread. Opened in the JMeter GUI its tree reads:

```
FabLab Load Test
├── User Defined Variables      host, port, email, password
├── HTTP Request Defaults       ${host}:${port}
├── HTTP Cookie Manager         per-thread cookies, NOT cleared between loops
├── HTTP Header Manager
└── Thread Group "Customers"    ${users} threads, ${rampup}s ramp-up, ${loops} loops
    ├── Once Only Controller "Login once per user"
    │   ├── GET /login          → Regex Extractor pulls name="_token" value="(...)"  into ${csrf}
    │   └── POST /login         email + password + _token
    │       └── Response Assertion  "Landed on the shop"  (/customer/shop)
    ├── GET / (landing)
    ├── GET /customer/shop
    ├── GET /customer/cart
    ├── GET /customer/orders
    ├── GET /customer/customize
    ├── GET /notifications/poll
    ├── Uniform Random Timer    500 ms + up to 1000 ms think time
    ├── View Results Tree
    ├── Summary Report
    └── Aggregate Report
```

Two details matter for realism. The **cookie manager is per thread and is not cleared between iterations**, so each virtual user logs in once and then holds its own Laravel session for all ten loops, exactly like a real browser. The **CSRF token is extracted from the live login page**, so the plan survives Laravel's token rotation instead of hard-coding a token that would expire.

Every parameter has a default and can be overridden from the command line:

| Property | Default | Override with |
| :--- | :--- | :--- |
| `host` | `127.0.0.1` | `-Jhost=...` |
| `port` | `8080` | `-Jport=...` |
| `users` | `50` | `-Jusers=...` |
| `rampup` | `10` (seconds) | `-Jrampup=...` |
| `loops` | `10` | `-Jloops=...` |
| `email` / `password` | seeded customer | `-Jemail=... -Jpassword=...` |

50 users × 10 loops × 6 pages, plus the two login requests per user, is **3,100 samples**.

### 3.2 The headless run (this is the one that produces the report)

Run JMeter from the command line, not the GUI, when you want numbers you can quote. The GUI's own rendering steals CPU from the load generator.

```powershell
$env:JAVA_HOME = 'C:\Program Files\Eclipse Adoptium\jdk-17.0.20.101-hotspot'
cd C:\FabLab\backend

# -e -o refuses to write into an existing non-empty folder, so clear it first
Remove-Item -Recurse -Force docs\testing\jmeter\html-report -ErrorAction SilentlyContinue
Remove-Item -Force docs\testing\jmeter\results.jtl -ErrorAction SilentlyContinue

C:\tools\apache-jmeter-5.6.3\bin\jmeter.bat `
  -n -t tools\loadtest\fablab-load-test.jmx `
  -l docs\testing\jmeter\results.jtl `
  -e -o docs\testing\jmeter\html-report `
  -Jhost=127.0.0.1 -Jport=8080 -Jusers=50 -Jrampup=10 -Jloops=10
```

| Flag | Meaning |
| :--- | :--- |
| `-n` | Non-GUI (headless) |
| `-t` | The test plan to run |
| `-l` | Where to write the raw sample log (`.jtl`) |
| `-e -o` | After the run, generate the HTML dashboard into this folder |

It takes about two minutes. JMeter prints a summary line every 30 seconds and finishes with a total; then open the dashboard:

```powershell
start docs\testing\jmeter\html-report\index.html
```

### 3.3 The GUI run (this is the one that produces the screenshots)

For the defense you also want to *show* the tool working. Open the plan in the GUI:

```powershell
C:\tools\apache-jmeter-5.6.3\bin\jmeter.bat -t C:\FabLab\backend\tools\loadtest\fablab-load-test.jmx
```

1. Click the green **Start** arrow (or Ctrl+R).
2. While it runs, the counter at the top right shows active threads — **50/50** at full ramp. Screenshot the **Summary Report** here; that's Figure 1 in [Testing.md](Testing.md#24-run-3--jmeter-gui-run-screenshots).
3. When it stops, screenshot **Summary Report** (per-page averages, error %, throughput) and **Aggregate Report** (median, 90th, 95th, 99th percentiles).
4. Open **View Results Tree**, click any sample, and use the **Response Body** tab to show it returned real HTML with HTTP 200.

Expect the GUI run to be a little slower than the headless one (947 ms average versus 757 ms on the recorded runs) — that's the GUI's overhead, not the application's.

### 3.4 Reading the result

Look at three things, in this order:

1. **Error %** — must be `0.00%`. Anything else means the config isn't cached (section 2.2) or MySQL fell over.
2. **Average and 90% Line per page** — the recorded run was 757 ms average across 3,100 samples, with no single request above 2.3 s.
3. **Throughput** — 25.6 requests/second sustained over the 121-second run.

`POST /login` is always the slowest step by a wide margin. That is bcrypt hashing plus session regeneration, and it is supposed to be slow.

---

## 4. Part B — Security testing with OWASP ZAP

### 4.1 Start ZAP with its API open

Open a **second** PowerShell window (Apache is still running in the first):

```powershell
$env:JAVA_HOME = 'C:\Program Files\Eclipse Adoptium\jdk-17.0.20.101-hotspot'
& "$env:JAVA_HOME\bin\java.exe" -jar "C:\Program Files\ZAP\Zed Attack Proxy\zap-2.17.0.jar" `
  -port 8090 -config api.key=fablabzap
```

The ZAP desktop window opens (choose **No, I do not want to persist this session** when asked). `-port 8090` is ZAP's own proxy/API port — nothing to do with the app on 8080 — and `api.key=fablabzap` is the key the script authenticates with. Leave ZAP's window open and visible: you will screenshot it while the scan runs.

To run without the desktop window, add `-daemon`.

### 4.2 Run the scan script

In a **third** window:

```powershell
cd C:\FabLab\backend\tools\loadtest
.\zap-scan.ps1 -Target http://127.0.0.1:8080 -ReportDir C:\FabLab\backend\docs\testing\zap
```

| Parameter | Default | Notes |
| :--- | :--- | :--- |
| `-Zap` | `http://127.0.0.1:8090` | Where ZAP's API is listening |
| `-ApiKey` | `fablabzap` | Must match what you started ZAP with |
| `-Target` | `http://127.0.0.1:8080` | The app under test |
| `-Email` / `-Password` | seeded customer | The account ZAP logs in as |
| `-ReportDir` | `C:\FabLab\backend\docs\testing\zap` | Where the three report files land |
| `-MaxScanMinutes` | `25` | Cap **per active-scan phase** |

Two phases at 25 minutes each means the whole thing takes **50–60 minutes**. The script prints a percentage every 10 seconds so you can see it is alive:

```
  spider (anonymous)            100%
  active scan (anonymous)        34%
```

### 4.3 What the script is doing, step by step

You will be asked this. The script performs, through ZAP's REST API, exactly what a person would do by hand in the ZAP desktop:

1. **Creates a context** called `FabLab` covering `http://127.0.0.1:8080/*`, and **excludes** `/logout` and `/login/google` from both the spider and the scanner. Without those exclusions the scanner logs itself out mid-scan, or wanders off into Google's sign-in pages.
2. **Registers `_token` as an anti-CSRF token** (`acsrf/addOptionToken`). This is what makes the rest work against Laravel: before each login attempt, ZAP re-fetches the login page and substitutes a fresh token.
3. **Configures form-based authentication** against `POST /login` with `email={%username%}&password={%password%}&_token=ZAP`, using cookie-based session management, and tells ZAP how to recognise each state — logged **in** by the presence of `/logout`, logged **out** by the presence of `name="password"`.
4. **Creates the user** `customer` with the seeded credentials and enables it.
5. **Sets limits**: spider 5 minutes, active scan `-MaxScanMinutes`, 6 threads per host.
6. **Phase 1 — anonymous.** Spiders from `/`, then active-scans everything found. This is the attack surface a stranger sees: landing page, login, register, forgot-password, verify-code.
7. **Phase 2 — as the customer.** Spiders and active-scans from `/customer/shop` *as the authenticated user*, covering shop, cart, checkout, orders, customiser, saved designs, notifications and settings.
8. **Writes the reports** as `zap-report.html`, `.json` and `.md` into `-ReportDir`, then prints the alert counts by risk and the total number of messages.

While phase 1 runs, screenshot ZAP's **Active Scan** tab — you'll see the fuzzed POSTs to `/login`, `/register` and `/forgot-password/send` going past. During phase 2, the same tab shows fuzzed POSTs to `/customer/profile` *with a session cookie attached*, which is the proof that the authenticated scan really was authenticated.

### 4.4 Reading the report

Open `docs\testing\zap\zap-report.html`. Work top down:

1. **Summary of Alerts** — the headline. High must be **0**. The recorded run found 0 High, 3 Medium, 7 Low, 6 Informational *alert types*.
2. Watch the difference between **alert types and instances**. ZAP's summary table counts types; expand one and you'll see it listed once per URL. "CSP header not set" is one type but 100-odd instances, because it's missing on every page. [Testing.md §3.2](Testing.md#32-results) gives both counts so the two tables don't look contradictory.
3. **Read what each Medium actually is.** All three in the recorded run are missing hardening headers and missing CDN `integrity=` attributes — configuration, not a flaw in the application logic. The fixes are listed in [Testing.md §3.4](Testing.md#34-recommended-fixes).
4. **Check the negatives, because they're the real finding.** Nothing appeared from the SQL injection, XSS, path traversal, remote file inclusion, command injection or external redirect rule families, against either the public forms or the authenticated pages.
5. **Ignore the spider noise.** You'll see a hundred or so 404s on nonsense URLs like `/%5C%5C*%7C/`. ZAP's spider extracts anything path-shaped out of inline JavaScript, including regular-expression fragments. They are not routes.

Two protections you can demonstrate from the report rather than assert:

- **CSRF held.** Every forged POST ZAP sent without a valid `_token` came back **HTTP 419**. Filter the History tab by 419 to show it.
- **Validation held.** Fuzzing register/forgot-password/verify-code created no rows — the users, orders, products, notifications and designs tables held the same counts before and after.

### 4.5 A note on the `XSRF-TOKEN` cookie finding

ZAP reports "Cookie without HttpOnly flag" against `XSRF-TOKEN`. This is **by design in Laravel**: that cookie has to be readable by JavaScript so the front end can echo it back as a header. The session cookie itself, `laravel-session`, *is* HttpOnly — expand the alert and you'll see only `XSRF-TOKEN` listed. Say so before the panel asks.

---

## 5. Afterwards — put the machine back

In order:

```powershell
cd C:\FabLab\backend

# 1. The active scan created junk rows (registrations, password-reset attempts)
php artisan migrate:fresh --seed

# 2. Restore .env: MAIL_MAILER=smtp, APP_URL=http://127.0.0.1:8000

# 3. Stop caching config, or local .env edits will silently do nothing
php artisan config:clear
```

Then **Ctrl+C** the Apache window and close ZAP (decline to save the session).

Step 3 is the one that bites. If you leave the config cached, you will spend an afternoon wondering why an `.env` change has no effect.

---

## 6. Troubleshooting

| Symptom | Cause | Fix |
| :--- | :--- | :--- |
| `java` not recognised | JDK isn't on PATH | Set `$env:JAVA_HOME` and prepend `$env:JAVA_HOME\bin` — section 1 |
| JMeter: ~5% of samples fail with 500 | Config not cached; threads racing on `.env` | `php artisan config:cache` — section 2.2 |
| JMeter: `Login did not redirect to /customer/shop` on every thread | Seeded customer missing, or wrong `-Jemail`/`-Jpassword` | `php artisan migrate:fresh --seed` |
| JMeter: `_token` comes back as `TOKEN_NOT_FOUND` | `GET /login` didn't return the form — app down, or wrong port | Check `http://127.0.0.1:8080/login` in a browser |
| JMeter: "cannot write to existing folder" | `-e -o` won't overwrite | Delete `docs\testing\jmeter\html-report` first |
| Response times are terrible and throughput is ~1 req/s | You're testing `artisan serve` on :8000 | Point at :8080 — section 2.1 |
| `zap-scan.ps1` fails on the first API call | ZAP not started, wrong port, or wrong API key | Confirm `http://127.0.0.1:8090` opens; `-ApiKey` must match `api.key=` |
| ZAP scan finds almost nothing under `/customer` | It got logged out | Check the `/logout` exclusion survived, and that the logged-in indicator still matches the markup |
| Real emails arrive during the ZAP scan | `MAIL_MAILER` still `smtp` | Set it to `log` **and** re-run `config:cache` — section 2.3 |
| Apache won't start: "make_sock: could not bind" | Port 8080 already taken | Stop whatever holds it, or change `Listen`/`VirtualHost` to a free port and pass `-Jport` / `-Target` to match |

---

## 7. Where the evidence ends up

| Path | What it is |
| :--- | :--- |
| `docs/testing/jmeter/results.jtl` | Raw sample log, final headless run |
| `docs/testing/jmeter/html-report/index.html` | JMeter dashboard for that run |
| `docs/testing/jmeter/results-run1-no-config-cache.jtl` | The failing first run, kept as evidence for section 2.2 |
| `docs/testing/jmeter/html-report-run1-no-config-cache/` | Dashboard for that first run |
| `docs/testing/zap/zap-report.html` / `.json` / `.md` | The ZAP report in three formats |
| `docs/testing/screenshots/` | The eight figures used in [Testing.md](Testing.md) |

Related documents: [Testing.md](Testing.md) (the results and what they mean) · [Deployment.md](Deployment.md) (getting the app onto a real server) · [README](../README.md) (installing and running it locally).
