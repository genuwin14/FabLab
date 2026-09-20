# FabLab – Performance and Security Testing: User Guide

How to run the load test and the security scan **on any Windows machine**, starting from one that has none of the tools installed.

This is the **procedure**. The **results** of the run recorded on 6 September 2026 — the tables, the graphs and what they mean — are in [Testing.md](Testing.md). Read this one when you need to reproduce the run on a different computer, produce fresh screenshots, or re-test after a change.

Nothing below hardcodes a path. Three scripts in [tools/loadtest/](../tools/loadtest/) find what the machine actually has and adapt:

| Script | What it does |
| :--- | :--- |
| [testing-env.ps1](../tools/loadtest/testing-env.ps1) | Finds the JDK, JMeter, ZAP and XAMPP wherever they were installed, and sets up the window to use them |
| [make-apache-conf.ps1](../tools/loadtest/make-apache-conf.ps1) | Builds a throwaway Apache config that serves this app on port 8080, without touching the machine's own XAMPP setup |
| [zap-scan.ps1](../tools/loadtest/zap-scan.ps1) | Drives OWASP ZAP through its REST API so the whole scan is one command |

| | |
| :--- | :--- |
| Time needed | ~30 min to install the tools, 5 min setup, 2 min for JMeter, 50–60 min for the full ZAP scan |
| You will produce | A JMeter HTML dashboard, a ZAP report in HTML/JSON/Markdown, and screenshots |
| Everything lands in | `backend/docs/testing/` inside the checkout on that machine |

> **Before the day**, read [§9 Running this as a live demo](#9-running-this-as-a-live-demo). The full ZAP scan takes an hour; nobody is going to watch that. There are cut-down settings that finish in about ten minutes and still show the tools working.

---

## 0. What the machine needs before you start

This guide assumes the FabLab application **already runs** on the target machine — XAMPP installed, the database created and migrated, and the site opening in a browser. Getting to that point is a different job, covered by the [README](../README.md). Check it first:

```powershell
cd <wherever the checkout is>\backend
php artisan --version          # should print the Laravel version
php artisan migrate:status     # should list migrations, all Ran
```

If either fails, stop and fix that first. Load-testing an application that doesn't run yet just produces a page of errors.

You also need about **1.5 GB of free disk** and, for the installs, an internet connection.

---

## 1. Install the four tools

Install these on the machine that will do the testing. Versions below are the ones the recorded run used — a **newer version works fine**, but if you use one, say so when you present the numbers, because tool versions affect timings.

| Tool | Version used | Where to get it | How to install |
| :--- | :--- | :--- | :--- |
| **Eclipse Temurin JDK** | 17 | <https://adoptium.net/temurin/releases/?version=17> | Pick **Windows / x64 / JDK / .msi** and run it. JMeter and ZAP are both Java programs; nothing works without this. |
| **Apache JMeter** | 5.6.3 | <https://jmeter.apache.org/download_jmeter.cgi> (the **Binary** zip), or the exact version from <https://archive.apache.org/dist/jmeter/binaries/> | Not an installer — **unzip it** to `C:\fablab-tools\`, giving `C:\fablab-tools\apache-jmeter-5.6.3\` |
| **OWASP ZAP** | 2.17.0 | <https://www.zaproxy.org/download/> (Windows 64-bit installer), or <https://github.com/zaproxy/zaproxy/releases> for a specific version | Run the installer, accept the defaults |
| **XAMPP** | Apache 2.4 + PHP 8.2 | <https://www.apachefriends.org/download.html> | Already present if the app runs — see §0 |

**Where to put things.** `testing-env.ps1` looks in `C:\fablab-tools\` first, then in the other usual places (`C:\tools`, `C:\Program Files\...`, your Downloads folder). Unzipping JMeter into `C:\fablab-tools` means everything is found with no arguments. Anywhere else is fine too — you'll just pass a `-JMeterHome` flag once.

> **Doing this without internet on the day?** JMeter and ZAP both run from an extracted folder. Copy `C:\fablab-tools\` onto a USB stick beforehand, along with the JDK `.msi` (that one does need installing). Then only the JDK step needs running on the client machine.

---

## 2. Set up the PowerShell window

**Every** PowerShell window you use for testing starts with this. Open PowerShell, go to the scripts folder, and **dot-source** the environment script:

```powershell
cd <checkout>\backend\tools\loadtest
. .\testing-env.ps1
```

The leading `. ` (dot, space) matters. Without it the script runs in its own scope and everything it sets vanishes when it finishes.

It prints what it found:

```
FabLab testing environment
--------------------------
  Application  C:\FabLab\backend
  JDK          C:\Program Files\Eclipse Adoptium\jdk-17.0.20.101-hotspot  [openjdk version "17.0.20.1" 2026-08-18]
  JMeter       C:\tools\apache-jmeter-5.6.3\bin\jmeter.bat
  ZAP          C:\Program Files\ZAP\Zed Attack Proxy\zap-2.17.0.jar
  XAMPP        C:\xampp

All four found. Next: build the Apache config with
    .\make-apache-conf.ps1
```

Anything it can't find is printed in red with a line telling you what to install or which flag to pass. To point it at non-standard locations:

```powershell
. .\testing-env.ps1 -JdkHome 'D:\jdk-17' -JMeterHome 'D:\apache-jmeter-5.6.3' -ZapHome 'D:\ZAP' -XamppHome 'D:\xampp'
```

For the rest of this guide, these variables exist in your window and are used in every command:

| Variable | What it holds |
| :--- | :--- |
| `$env:JAVA_HOME`, `$env:PATH` | The JDK, so plain `java` works |
| `$JMeter` | Full path to `jmeter.bat` |
| `$ZapJar` | Full path to `zap-<version>.jar` |
| `$XamppRoot` | The XAMPP installation |
| `$AppRoot` | The `backend/` folder of the checkout |

---

## 3. Prepare the system under test

Do all five steps. Steps 3.2 and 3.3 are the ones people skip, and skipping either makes the results *wrong*, not merely different.

### 3.1 Serve the app through Apache, not `artisan serve`

`php artisan serve` is single-threaded on Windows. Point 50 virtual users at it and every request queues behind the one in front, so you measure the queue, not the application. Both tests must go through Apache.

You could edit the machine's XAMPP configuration — but on someone else's computer that means restarting their Apache service (which needs administrator rights) and disturbing whatever else they use XAMPP for. Instead, generate a **throwaway config** that runs a second, independent Apache on port 8080:

```powershell
.\make-apache-conf.ps1
```

It copies XAMPP's own `httpd.conf` and changes four things in the copy: `Listen 80` becomes `Listen 8080`, the vhosts and SSL includes are commented out (so port 443 stays free for the real XAMPP), and its own PID file, logs and a `<VirtualHost *:8080>` pointing at this app's `public/` folder are appended. Everything else — above all the PHP module, which XAMPP loads via `conf/extra/httpd-xampp.conf` — is inherited unchanged, which is why the copy works on any machine where XAMPP itself works.

The script checks the port is free, writes the config to `%USERPROFILE%\fablab-test\`, runs Apache's own syntax check, and prints the command to start it. If 8080 is taken, or XAMPP is on another drive:

```powershell
.\make-apache-conf.ps1 -XamppRoot D:\xampp -Port 8090
```

Start it in **its own PowerShell window** and leave that window open — it runs in the foreground, so **Ctrl+C** stops it:

```powershell
& "$XamppRoot\apache\bin\httpd.exe" -f "$env:USERPROFILE\fablab-test\httpd-fablab.conf" -D FOREGROUND
```

No administrator rights are needed, and the XAMPP service on port 80 is untouched. Start **MySQL** from the XAMPP Control Panel as usual, then check the site answers:

```powershell
(Invoke-WebRequest http://127.0.0.1:8080/ -UseBasicParsing).StatusCode   # 200
```

> If you used a different `-Port`, use it everywhere below too: `-Jport=` for JMeter and `-Target` for ZAP.

### 3.2 Cache the configuration — this is not optional

```powershell
cd $AppRoot
php artisan config:cache
```

Without this, roughly **1 request in 20 fails with HTTP 500** under concurrency. Apache on Windows runs PHP as a threaded module, and Laravel's `.env` loader uses `putenv()`/`getenv()`, which are shared across the threads of one process. When 50 requests bootstrap at the same instant they race on that shared environment, and some end up reading Laravel's *defaults* instead of your `.env` — you'll see `Unknown database 'laravel'` and `Database file at path [fablab_db] does not exist` in `storage/logs/laravel.log`. With the config cached, `env()` is never called at request time and the race disappears. [Testing.md §2.2](Testing.md#22-run-1--5-of-requests-failed-with-http-500) has the evidence from both runs.

> From here until §6, any change to `.env` has **no effect** until you re-run `config:cache`.

### 3.3 Send mail to the log, not to Gmail

ZAP's active scan fuzzes the **register** and **forgot password** forms, which means submitting them hundreds of times. In `.env`:

```env
MAIL_MAILER=log
```

then `php artisan config:cache` again. Mail now goes to `storage/logs/laravel.log`. Skip this and the scan sends real email through whatever account is configured.

### 3.4 Have the seeded accounts in place

Both tests sign in as the seeded customer, `customer@gmail.com` / `password` (see `database/seeders/UserSeeder.php`). If the database is empty or stale:

```powershell
php artisan migrate:fresh --seed
```

> On a client machine this **erases their data**. If the database has anything worth keeping, back it up first (`mysqldump`), or create a separate database for testing and point `.env` at it.

### 3.5 Optional but tidy

Set `APP_URL=http://127.0.0.1:8080` while testing so anything the app generates absolutely (mail links, PDF slips) points at the instance actually under test. Put it back afterwards, along with `MAIL_MAILER`.

---

## 4. Part A — Load testing with JMeter

### 4.1 What the plan does

[fablab-load-test.jmx](../tools/loadtest/fablab-load-test.jmx) simulates one customer session per thread. Opened in the JMeter GUI its tree reads:

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

Two details matter for realism. The **cookie manager is per thread and is not cleared between iterations**, so each virtual user logs in once and then holds its own Laravel session for all ten loops, exactly like a real browser. The **CSRF token is extracted from the live login page**, so the plan survives Laravel's token rotation instead of hardcoding a token that would expire.

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

### 4.2 The headless run (this is the one that produces the report)

Run JMeter from the command line, not the GUI, when you want numbers you can quote — the GUI's own rendering steals CPU from the load generator.

```powershell
cd $AppRoot

# -e -o refuses to write into an existing non-empty folder, so clear it first
Remove-Item -Recurse -Force docs\testing\jmeter\html-report -ErrorAction SilentlyContinue
Remove-Item -Force docs\testing\jmeter\results.jtl -ErrorAction SilentlyContinue

& $JMeter -n -t tools\loadtest\fablab-load-test.jmx `
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

### 4.3 The GUI run (this is the one that produces the screenshots)

For a defense you also want to *show* the tool working:

```powershell
& $JMeter -t "$AppRoot\tools\loadtest\fablab-load-test.jmx"
```

1. Click the green **Start** arrow (or Ctrl+R).
2. While it runs, the counter at the top right shows active threads — **50/50** at full ramp. Screenshot the **Summary Report** here; that's Figure 1 in [Testing.md](Testing.md#24-run-3--jmeter-gui-run-screenshots).
3. When it stops, screenshot **Summary Report** (per-page averages, error %, throughput) and **Aggregate Report** (median, 90th, 95th, 99th percentiles).
4. Open **View Results Tree**, click any sample, and use the **Response Body** tab to show it returned real HTML with HTTP 200.

Expect the GUI run to be a little slower than the headless one (947 ms average versus 757 ms on the recorded runs) — that's the GUI's overhead, not the application's.

### 4.4 Reading the result

Look at three things, in this order:

1. **Error %** — must be `0.00%`. Anything else means the config isn't cached (§3.2) or MySQL fell over.
2. **Average and 90% Line per page** — the recorded run was 757 ms average across 3,100 samples, with no single request above 2.3 s.
3. **Throughput** — 25.6 requests/second sustained over the 121-second run.

`POST /login` is always the slowest step by a wide margin. That is bcrypt hashing plus session regeneration, and it is supposed to be slow.

> **The client machine will not produce the same numbers**, and that's expected — response times track CPU, disk and RAM. What should hold on any machine is the *shape*: 0.00% errors, login slowest, throughput steady across the run. If you quote timings from a different machine, say which machine.

---

## 5. Part B — Security testing with OWASP ZAP

### 5.1 Start ZAP with its API open

Open a **second** PowerShell window (Apache is still running in the first), set the environment up again, and start ZAP:

```powershell
cd <checkout>\backend\tools\loadtest
. .\testing-env.ps1
& java -jar "$ZapJar" -port 8090 -config api.key=fablabzap
```

The ZAP desktop window opens (choose **No, I do not want to persist this session** when asked). `-port 8090` is ZAP's own proxy/API port — nothing to do with the app on 8080 — and `api.key=fablabzap` is the key the script authenticates with. Leave ZAP's window open and visible: you will screenshot it while the scan runs.

To run without the desktop window, add `-daemon`.

### 5.2 Run the scan script

In a **third** window:

```powershell
cd <checkout>\backend\tools\loadtest
. .\testing-env.ps1
.\zap-scan.ps1 -Target http://127.0.0.1:8080
```

| Parameter | Default | Notes |
| :--- | :--- | :--- |
| `-Zap` | `http://127.0.0.1:8090` | Where ZAP's API is listening |
| `-ApiKey` | `fablabzap` | Must match what you started ZAP with |
| `-Target` | `http://127.0.0.1:8080` | The app under test |
| `-Email` / `-Password` | seeded customer | The account ZAP logs in as |
| `-ReportDir` | `backend/docs/testing/zap` | Resolved relative to the script, so the checkout can live anywhere |
| `-MaxScanMinutes` | `25` | Cap **per active-scan phase** |

Two phases at 25 minutes each means the whole thing takes **50–60 minutes**. The script prints a percentage every 10 seconds so you can see it is alive:

```
  spider (anonymous)            100%
  active scan (anonymous)        34%
```

### 5.3 What the script is doing, step by step

You will be asked this. The script performs, through ZAP's REST API, exactly what a person would do by hand in the ZAP desktop:

1. **Creates a context** called `FabLab` covering the target, and **excludes** `/logout` and `/login/google` from both the spider and the scanner. Without those exclusions the scanner logs itself out mid-scan, or wanders off into Google's sign-in pages.
2. **Registers `_token` as an anti-CSRF token** (`acsrf/addOptionToken`). This is what makes the rest work against Laravel: before each login attempt, ZAP re-fetches the login page and substitutes a fresh token.
3. **Configures form-based authentication** against `POST /login` with `email={%username%}&password={%password%}&_token=ZAP`, using cookie-based session management, and tells ZAP how to recognise each state — logged **in** by the presence of `/logout`, logged **out** by the presence of `name="password"`.
4. **Creates the user** `customer` with the seeded credentials and enables it.
5. **Sets limits**: spider 5 minutes, active scan `-MaxScanMinutes`, 6 threads per host.
6. **Phase 1 — anonymous.** Spiders from `/`, then active-scans everything found. This is the attack surface a stranger sees: landing page, login, register, forgot-password, verify-code.
7. **Phase 2 — as the customer.** Spiders and active-scans from `/customer/shop` *as the authenticated user*, covering shop, cart, checkout, orders, customiser, saved designs, notifications and settings.
8. **Writes the reports** as `zap-report.html`, `.json` and `.md`, then prints the alert counts by risk and the total number of messages.

While phase 1 runs, screenshot ZAP's **Active Scan** tab — you'll see the fuzzed POSTs to `/login`, `/register` and `/forgot-password/send` going past. During phase 2, the same tab shows fuzzed POSTs to `/customer/profile` *with a session cookie attached*, which is the proof that the authenticated scan really was authenticated.

### 5.4 Reading the report

Open `docs\testing\zap\zap-report.html`. Work top down:

1. **Summary of Alerts** — the headline. High must be **0**. The recorded run found 0 High, 3 Medium, 7 Low, 6 Informational *alert types*.
2. Watch the difference between **alert types and instances**. ZAP's summary table counts types; expand one and you'll see it listed once per URL. "CSP header not set" is one type but 100-odd instances, because it's missing on every page. [Testing.md §3.2](Testing.md#32-results) gives both counts so the two tables don't look contradictory.
3. **Read what each Medium actually is.** All three in the recorded run are missing hardening headers and missing CDN `integrity=` attributes — configuration, not a flaw in the application logic. The fixes are listed in [Testing.md §3.4](Testing.md#34-recommended-fixes).
4. **Check the negatives, because they're the real finding.** Nothing appeared from the SQL injection, XSS, path traversal, remote file inclusion, command injection or external redirect rule families, against either the public forms or the authenticated pages.
5. **Ignore the spider noise.** You'll see a hundred or so 404s on nonsense URLs like `/%5C%5C*%7C/`. ZAP's spider extracts anything path-shaped out of inline JavaScript, including regular-expression fragments. They are not routes.

Two protections you can demonstrate from the report rather than assert:

- **CSRF held.** Every forged POST ZAP sent without a valid `_token` came back **HTTP 419**. Filter the History tab by 419 to show it.
- **Validation held.** Fuzzing register/forgot-password/verify-code created no rows — the users, orders, products, notifications and designs tables held the same counts before and after.

### 5.5 A note on the `XSRF-TOKEN` cookie finding

ZAP reports "Cookie without HttpOnly flag" against `XSRF-TOKEN`. This is **by design in Laravel**: that cookie has to be readable by JavaScript so the front end can echo it back as a header. The session cookie itself, `laravel-session`, *is* HttpOnly — expand the alert and you'll see only `XSRF-TOKEN` listed. Say so before the panel asks.

---

## 6. Afterwards — put the machine back

This matters more on someone else's computer than on your own. In order:

```powershell
cd $AppRoot

# 1. The active scan created junk rows (registrations, password-reset attempts)
php artisan migrate:fresh --seed

# 2. Restore .env: MAIL_MAILER=smtp, and APP_URL if you changed it

# 3. Stop caching config, or later .env edits will silently do nothing
php artisan config:clear
```

Then:

- **Ctrl+C** the Apache window, and confirm no stray `httpd` is left holding the port: `Get-Process httpd`. The two that belong to their XAMPP service are fine; yours is the one started from `%USERPROFILE%\fablab-test`.
- Close ZAP, declining to save the session.
- Delete `%USERPROFILE%\fablab-test\` if you want the generated config and logs gone.
- Copy `backend\docs\testing\` off the machine — that folder is the evidence, and it's the only thing you need to take with you.

Step 3 is the one that bites. If you leave the config cached, someone will spend an afternoon wondering why an `.env` change has no effect.

---

## 7. Troubleshooting

| Symptom | Cause | Fix |
| :--- | :--- | :--- |
| `testing-env.ps1` prints NOT FOUND for a tool that is installed | It's in a folder the script doesn't search | Re-run with the matching flag, e.g. `-JMeterHome 'D:\apache-jmeter-5.6.3'` |
| `$JMeter` and friends are empty after running the script | It wasn't dot-sourced | `. .\testing-env.ps1` — leading dot and space |
| `...ps1 cannot be loaded because running scripts is disabled` | PowerShell execution policy on that machine | `Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass`, then re-run. Per-process, so nothing is changed permanently |
| `java` not recognised | JDK missing, or the window wasn't set up | Install Temurin 17; run `. .\testing-env.ps1` in that window |
| `make-apache-conf.ps1`: "Port 8080 is already in use" | Something else holds it | It names the process — stop it, or re-run with `-Port 8090` and use that port everywhere |
| `make-apache-conf.ps1`: "No Apache config at ..." | XAMPP is somewhere else | `-XamppRoot D:\xampp` |
| Apache starts, but every page is a 500 | App not working on this machine yet | Go back to §0; check `storage/logs/laravel.log` |
| JMeter: ~5% of samples fail with 500 | Config not cached; threads racing on `.env` | `php artisan config:cache` — §3.2 |
| JMeter: `Login did not redirect to /customer/shop` on every thread | Seeded customer missing, or wrong `-Jemail`/`-Jpassword` | `php artisan migrate:fresh --seed` |
| JMeter: `_token` comes back as `TOKEN_NOT_FOUND` | `GET /login` didn't return the form — app down, or wrong port | Open `http://127.0.0.1:8080/login` in a browser |
| JMeter: "cannot write to existing folder" | `-e -o` won't overwrite | Delete `docs\testing\jmeter\html-report` first |
| Response times are terrible and throughput is ~1 req/s | You're testing `artisan serve`, not Apache | Point at port 8080 — §3.1 |
| `zap-scan.ps1` fails on the first API call | ZAP not started, wrong port, or wrong API key | Confirm `http://127.0.0.1:8090` opens; `-ApiKey` must match `api.key=` |
| ZAP scan finds almost nothing under `/customer` | It got logged out | Check the `/logout` exclusion survived, and that the logged-in indicator still matches the markup |
| Real emails arrive during the ZAP scan | `MAIL_MAILER` still `smtp` | Set it to `log` **and** re-run `config:cache` — §3.3 |
| Windows Firewall prompts when JMeter or ZAP starts | Normal | Allow on **private** networks only; everything here is loopback anyway |

---

## 8. Where the evidence ends up

All paths relative to `backend/`:

| Path | What it is |
| :--- | :--- |
| `docs/testing/jmeter/results.jtl` | Raw sample log, final headless run |
| `docs/testing/jmeter/html-report/index.html` | JMeter dashboard for that run |
| `docs/testing/jmeter/results-run1-no-config-cache.jtl` | The failing first run, kept as evidence for §3.2 |
| `docs/testing/jmeter/html-report-run1-no-config-cache/` | Dashboard for that first run |
| `docs/testing/zap/zap-report.html` / `.json` / `.md` | The ZAP report in three formats |
| `docs/testing/screenshots/` | The eight figures used in [Testing.md](Testing.md) |

**A fresh run overwrites these.** If you want to keep the committed 6 September evidence alongside a new run, copy the folder aside first, or point the scripts elsewhere: `-o docs\testing\jmeter\html-report-clientdevice` for JMeter, `-ReportDir` for ZAP.

---

## 9. Running this as a live demo

The full procedure takes an hour, most of it ZAP grinding away with nothing to look at. For a demo, run the real thing beforehand and present the committed report — then show the tools working live with cut-down settings.

**Ten-minute version:**

```powershell
# JMeter: 20 users, 3 loops -- about 40 seconds, still visibly concurrent
& $JMeter -t "$AppRoot\tools\loadtest\fablab-load-test.jmx"     # then Start, in the GUI
#   or headless:
& $JMeter -n -t tools\loadtest\fablab-load-test.jmx -l docs\testing\jmeter\demo.jtl `
  -e -o docs\testing\jmeter\html-report-demo -Jusers=20 -Jloops=3

# ZAP: 4 minutes per phase instead of 25
.\zap-scan.ps1 -Target http://127.0.0.1:8080 -MaxScanMinutes 4 `
  -ReportDir "$AppRoot\docs\testing\zap-demo"
```

Be straight about what changed: a 4-minute active scan covers a fraction of ZAP's rule set, so it is a demonstration that the scan runs, not a result. The result is the full run in [Testing.md](Testing.md).

**What's worth showing, in order:** JMeter's thread counter climbing to 20/20 → the Summary Report's 0.00% error column → ZAP's Active Scan tab with fuzzed POSTs going past → the History tab filtered to **419**, which is CSRF rejecting every forged request → the committed `zap-report.html` with **0 High**.

**Have ready before you start:** Apache running, MySQL running, ZAP already launched (it takes ~30 seconds), the JMeter GUI already open on the plan, and a browser tab on the committed report.

---

## Appendix — the recorded run

[Testing.md](Testing.md) quotes results from 6 September 2026 on the development machine. For reference, that machine had:

| | |
| :--- | :--- |
| JDK | `C:\Program Files\Eclipse Adoptium\jdk-17.0.20.101-hotspot` |
| JMeter 5.6.3 | `C:\tools\apache-jmeter-5.6.3\bin\jmeter.bat` |
| ZAP 2.17.0 | `C:\Program Files\ZAP\Zed Attack Proxy\zap-2.17.0.jar` |
| Apache config | `C:\tools\httpd-fablab.conf` (hand-written; `make-apache-conf.ps1` now generates the same thing) |
| Checkout | `C:\FabLab\backend` |
| Stack | Laravel 12, PHP 8.2.12, MySQL 8 via XAMPP, Apache 2.4.58 |

Related documents: [Testing.md](Testing.md) (the results and what they mean) · [Deployment.md](Deployment.md) (getting the app onto a real server) · [README](../README.md) (installing and running it locally).
