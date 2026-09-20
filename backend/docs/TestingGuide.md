# How to Test FabLab — Step by Step

Two tests, on any Windows computer:

- **Speed test (JMeter)** — 50 people use the site at the same time. Does it stay fast?
- **Security test (OWASP ZAP)** — a scanner attacks the site on purpose. Can it break in?

Follow the steps in order. Copy and paste each command.

**This guide = how to run the tests. [Testing.md](Testing.md) = what the tests found.**

| | |
| :--- | :--- |
| Installing the tools | About 30 minutes, once |
| Speed test | 2 minutes |
| Security test | About 1 hour (there is a 10-minute version — see [Step 8](#step-8-short-version-for-a-live-demo)) |

---

## Before you start

The FabLab website must already work on this computer. Check:

```powershell
cd C:\FabLab\backend
php artisan --version
```

If that prints a version number, you're fine. If it doesn't, set the app up first using the [README](../README.md) — there is no point testing a site that doesn't run yet.

> Replace `C:\FabLab` with wherever the folder actually is on that computer. Everything below uses that same folder.

---

## Step 1 — Install the tools

Download and install these three. (XAMPP is already there, or the app wouldn't run.)

| # | Tool | Where to get it | What to do |
| :-- | :--- | :--- | :--- |
| 1 | **Java (Temurin JDK 17)** | <https://adoptium.net/temurin/releases/?version=17> | Choose **Windows · x64 · JDK · .msi** and run it. The other two tools need this. |
| 2 | **Apache JMeter 5.6.3** | <https://jmeter.apache.org/download_jmeter.cgi> | Download the **Binary** zip. Don't install — just **unzip it to `C:\fablab-tools`** |
| 3 | **OWASP ZAP** | <https://www.zaproxy.org/download/> | Windows 64-bit installer. Click Next until it finishes. |

That's it. Nothing needs configuring.

> **No internet on the day?** Copy the `C:\fablab-tools` folder onto a USB stick beforehand, plus the Java `.msi` file. Only Java needs actually installing.

---

## Step 2 — Open PowerShell and set it up

Open PowerShell, then run these two lines:

```powershell
cd C:\FabLab\backend\tools\loadtest
. .\testing-env.ps1
```

> The `. ` at the start (dot, then a space) is important. Without it the script still runs and still says it found everything, but it keeps none of it, and Step 5 fails. It will warn you if you miss it.

You should see something like:

```
FabLab testing environment
--------------------------
  Application  C:\FabLab\backend
  JDK          C:\Program Files\Eclipse Adoptium\jdk-17...
  JMeter       C:\fablab-tools\apache-jmeter-5.6.3\bin\jmeter.bat
  ZAP          C:\Program Files\ZAP\Zed Attack Proxy\zap-2.17.0.jar
  XAMPP        C:\xampp

All four found.
```

**All four must be found.** If one says `NOT FOUND` in red, the message tells you what to do. Usually it just means the tool was put somewhere unusual, so tell the script where:

```powershell
. .\testing-env.ps1 -JMeterHome 'D:\apache-jmeter-5.6.3'
```

**Keep this window open.** You'll use it again. If you close it, run these two lines again in the new one.

---

## Step 3 — Start the test server

The tests can't use `php artisan serve` — it only handles one person at a time, so a test of 50 people would just measure them queuing.

Build a test server setup (this does **not** change the computer's own XAMPP):

```powershell
.\make-apache-conf.ps1
```

It will say `Syntax OK` and print a command. **Open a second PowerShell window** and run this in it:

```powershell
& "C:\xampp\apache\bin\httpd.exe" -f "$env:USERPROFILE\fablab-test\httpd-fablab.conf" -D FOREGROUND
```

Leave that window open — the server runs inside it. Press **Ctrl+C** in it when you're finished testing.

Also start **MySQL** from the XAMPP Control Panel.

Now check it works: open <http://127.0.0.1:8080> in a browser. You should see the FabLab home page.

---

## Step 4 — Get the app ready

Back in the **first** window, three things:

**a) Turn off email.** The security scanner fills in the sign-up and forgot-password forms hundreds of times. If email is on, it sends hundreds of real emails. Open `C:\FabLab\backend\.env` in Notepad and change one line:

```env
MAIL_MAILER=log
```

**b) Lock in the settings.** Without this, about 1 in every 20 requests fails during the speed test. (Why: [Testing.md](Testing.md#22-run-1--5-of-requests-failed-with-http-500).)

```powershell
cd C:\FabLab\backend
php artisan config:cache
```

**c) Reset the test data.** The tests sign in as `customer@gmail.com` / `password`.

```powershell
php artisan migrate:fresh --seed
```

> ⚠️ This **deletes everything in the database**. On the client's computer, make sure there's nothing in there they want to keep.

---

## Step 5 — Run the speed test

> **Same window as Step 2?** If you opened a new one, or closed the old one, run Step 2's two lines again first. Otherwise this fails with *"The expression after '&' ... was not valid"*, which just means `$JMeter` is empty. Quick check — this should print a path, not a blank line:
> ```powershell
> $JMeter
> ```

```powershell
cd C:\FabLab\backend
Remove-Item -Recurse -Force docs\testing\jmeter\html-report -ErrorAction SilentlyContinue

& $JMeter -n -t tools\loadtest\fablab-load-test.jmx `
  -l docs\testing\jmeter\results.jtl `
  -e -o docs\testing\jmeter\html-report `
  -Jusers=50 -Jrampup=10 -Jloops=10
```

Takes about 2 minutes. It pretends to be 50 customers who log in, then browse the shop, cart, orders, customizer and notifications over and over.

See the results:

```powershell
start docs\testing\jmeter\html-report\index.html
```

**What to look at:**

| Look for | Should be | What it means |
| :--- | :--- | :--- |
| **Errors** | `0.00%` | Nothing broke |
| **Average** | Under 1 second | Pages load fast even with 50 people |
| **Throughput** | ~25 per second | How much the site handles |

Logging in is always the slowest step. That's normal — it's the password check doing its job.

> The numbers **will be different on a different computer** — that depends on the computer's speed. What should stay the same is `0.00%` errors. Always say which computer your numbers came from.

### Want screenshots of it running?

Open the app version instead, and press the green **Start** arrow:

```powershell
& $JMeter -t "C:\FabLab\backend\tools\loadtest\fablab-load-test.jmx"
```

Watch the counter at the top right climb to **50/50**. Screenshot the **Summary Report** and the **Aggregate Report** tabs.

---

## Step 6 — Run the security test

**a) Start ZAP.** Open a **third** PowerShell window:

```powershell
cd C:\FabLab\backend\tools\loadtest
. .\testing-env.ps1
& java -jar "$ZapJar" -port 8090 -config api.key=fablabzap
```

ZAP's window opens. Choose **"No, I do not want to persist this session."** Leave it open — it's nice to screenshot while it works.

**b) Start the scan.** Go back to the **first** window:

```powershell
cd C:\FabLab\backend\tools\loadtest
.\zap-scan.ps1 -Target http://127.0.0.1:8080
```

Now wait — about an hour. It prints its progress every 10 seconds so you know it's alive:

```
  spider (anonymous)            100%
  active scan (anonymous)        34%
```

It scans twice: first as a stranger who isn't logged in, then as a logged-in customer. That second pass is the important one — it's checking the pages only customers can reach.

**c) Read the report.**

```powershell
start C:\FabLab\backend\docs\testing\zap\zap-report.html
```

**What to look at:**

| Look for | Should be | What it means |
| :--- | :--- | :--- |
| **High** | `0` | No serious security holes |
| **Medium** | A few | Missing safety settings, not broken code — [the fixes are listed here](Testing.md#34-recommended-fixes) |
| **Low / Informational** | Several | Minor notes |

You will also see about a hundred "page not found" errors on strange addresses like `/%5C%5C*%7C/`. Ignore them — the scanner guesses addresses out of the page's JavaScript, and those guesses aren't real pages.

---

## Step 7 — Put the computer back

Important on someone else's computer. In the first window:

```powershell
cd C:\FabLab\backend
php artisan migrate:fresh --seed    # clears the junk accounts the scan created
php artisan config:clear            # lets .env work normally again
```

Then:

1. Change `MAIL_MAILER` back to `smtp` in `.env`.
2. Press **Ctrl+C** in the server window (Step 3).
3. Close ZAP — don't save the session.
4. **Copy the `C:\FabLab\backend\docs\testing` folder onto a USB stick.** That folder is your proof. It's the only thing you need to take with you.

> Don't skip `config:clear`. If you do, later changes to `.env` will seem to do nothing, and it's a confusing problem to track down.

---

## Step 8 — Short version for a live demo

Nobody will sit through a one-hour scan. For a live demo, show the finished report you already have, then run short versions so people can watch the tools work:

```powershell
# Speed test: 20 people, about 40 seconds
& $JMeter -n -t tools\loadtest\fablab-load-test.jmx -l docs\testing\jmeter\demo.jtl `
  -e -o docs\testing\jmeter\html-report-demo -Jusers=20 -Jloops=3

# Security scan: 4 minutes per pass instead of 25
.\zap-scan.ps1 -Target http://127.0.0.1:8080 -MaxScanMinutes 4 `
  -ReportDir C:\FabLab\backend\docs\testing\zap-demo
```

**Show it in this order:**

1. JMeter's counter climbing to **20/20** — 20 people at once.
2. The **0.00% error** column.
3. ZAP's **Active Scan** tab, attacks scrolling past.
4. ZAP's **History** tab, filtered to **419** — every fake request being rejected. This is the protection working, live.
5. The full report from before, showing **0 High**.

Say clearly that the short scan only proves the tool runs. The real result is the full scan in [Testing.md](Testing.md).

**Have ready before you begin:** the server running, MySQL running, ZAP already open (it's slow to start), and the full report open in a browser tab.

---

## If something goes wrong

| What you see | What it means | What to do |
| :--- | :--- | :--- |
| `NOT FOUND` in red at Step 2 | A tool isn't where the script looked | Re-run with the path, e.g. `. .\testing-env.ps1 -JMeterHome 'D:\apache-jmeter-5.6.3'` |
| `The expression after '&' ... was not valid` | This window hasn't been set up, so `$JMeter` is empty | Do [Step 2](#step-2--open-powershell-and-set-it-up) again **in this window**, then run the command again |
| `testing-env.ps1` says *NOTHING WAS SAVED* | It was run without the leading dot | Run `. .\testing-env.ps1` — dot, space, then the name |
| `running scripts is disabled` | Windows is blocking scripts | Run `Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass`, then try again. This only affects this window |
| `java is not recognized` | Java isn't installed, or this window wasn't set up | Install Java (Step 1), then run Step 2 in this window |
| `Port 8080 is already in use` | Something else is using it | The message names it. Close it, or use `.\make-apache-conf.ps1 -Port 8090` — then use `8090` everywhere instead |
| `No Apache config at...` | XAMPP is on another drive | `.\make-apache-conf.ps1 -XamppRoot D:\xampp` |
| Every page shows an error | The app isn't working on this computer | Go back to **Before you start** |
| About 5% of the speed test fails | You skipped Step 4b | `php artisan config:cache` |
| Every login fails in JMeter | The test accounts are missing | `php artisan migrate:fresh --seed` |
| `cannot write to existing folder` | The results folder is already there | Delete `docs\testing\jmeter\html-report` and run again |
| Everything is very slow, ~1 per second | You're testing `artisan serve`, not the test server | Use port **8080** — Step 3 |
| ZAP script fails immediately | ZAP isn't running yet | Do Step 6a first, and wait for ZAP's window to open |
| Real emails start arriving | You skipped Step 4a | Set `MAIL_MAILER=log`, then run `php artisan config:cache` again |
| Windows Firewall pops up | Normal | Click Allow (private networks). Everything stays on this computer anyway |

---

## If they ask you questions

**"Why not just use `php artisan serve`?"**
It handles one request at a time. A 50-person test against it would only measure people waiting in line, not the website.

**"How does the test log in?"**
The same way a person does. It opens the login page, reads the hidden security token off the form, and sends it back with the email and password — so it's a real login, not a shortcut.

**"How do you know the security scan was really logged in?"**
It's scanning customer-only pages like the cart and the order list. Those redirect you to the login page if you're not signed in, so if it can see them, it's signed in.

**"What does '0 High' mean?"**
The scanner found no serious security holes. The Medium items are missing safety settings on the web server, not mistakes in the code.

**"Did the scan prove anything actually works?"**
Yes, two things. Every fake request it sent was rejected with a `419` error — that's the protection against forged requests doing its job. And after hundreds of attempts at the sign-up and password-reset forms, not one bad record got into the database.

**"Why is the `XSRF-TOKEN` cookie flagged?"**
Laravel is supposed to do that — the browser needs to read that one. The actual login cookie is protected, and the report shows only `XSRF-TOKEN` listed.

**"Can you run it again right now?"**
Yes — that's what this guide is for. The short version takes about 10 minutes (Step 8).

---

## Where everything lives

| What | Where |
| :--- | :--- |
| Speed test results | `backend\docs\testing\jmeter\html-report\index.html` |
| Security report | `backend\docs\testing\zap\zap-report.html` |
| Screenshots | `backend\docs\testing\screenshots\` |
| The test scripts | `backend\tools\loadtest\` |

A new test **overwrites** the old results. To keep both, copy the `docs\testing` folder somewhere else first.

---

**Related:** [Testing.md](Testing.md) — the full results and what they mean · [Deployment.md](Deployment.md) — putting the site on a real server · [README](../README.md) — installing the app.
