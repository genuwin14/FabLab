# Security Test Report — OWASP ZAP

**App:** FabLab (Laravel)
**Live site:** <https://palegoldenrod-kudu-488454.hostingersite.com>
**Tool:** OWASP ZAP 2.17.0

## How I tested

Same idea as the load test — two ways, so the live site was never attacked.

On the **live site**, I only let ZAP *look*: it crawled the pages and checked the
responses (headers, cookies, redirects) but sent **no attacks**. Firing attacks at a
live shared-hosting site could break things or get the account flagged.

The real **attack scan** — SQL injection, cross-site scripting, and the rest — I ran
on a **copy of the app on my computer**, both signed out and signed in as a customer.
That's where thousands of malicious requests are safe to send, because no real data
is at risk.

> ZAP grades what it finds as High, Medium, Low, or Informational. The one that
> matters is **High** (a serious hole). We want zero.

![ZAP running an active scan against the FabLab site](screenshots/zap-app-02-active-scan.png)

*ZAP's active scan — attack requests hitting the login, register and forgot-password pages.*

## Results

| Test | What it checks | Result |
| :--- | :--- | :--- |
| Login Security | The login page is safe | ✅ Pass — no High-risk issue; login uses a security token |
| Input Validation | Attacks on the forms fail | ✅ Pass — no SQL injection or XSS found; forged requests blocked |
| Access Control | Guests can't open staff/admin pages | ✅ Pass — all redirect to login when signed out |
| Session Security | The login session is protected | ✅ Pass — the session cookie is HttpOnly and Secure |
| Web Security Scan | No serious alerts | ✅ Pass — **0 High, 0 Critical** |

**The headline: 0 High and 0 Critical alerts** — on both the live site and the full
attack scan.

![ZAP's alerts after the scan, with the High count at zero](screenshots/zap-app-04-alerts.png)

*ZAP's alerts after the scan. The red "High" flag reads 0.*

## What the attack scan found

ZAP sent **5,719 attack requests** at the local copy. None of the dangerous attacks
got through — no SQL injection, no cross-site scripting, no path traversal, no
command injection.

Two protections clearly worked:

- **Forged requests were blocked.** Every fake form submission without the security
  token was rejected (error 419), so nothing could be changed behind the scenes.
- **Nothing got into the database.** I counted the rows before and after the scan —
  the numbers were identical. Hundreds of fake sign-ups and password resets created
  zero records.

The only things ZAP flagged were **Medium and Low "missing header" warnings** —
recommended safety settings, not actual holes (for example, a Content-Security-Policy
header and clickjacking protection). These can be added later in one small piece of
code; none is urgent.

## Access control (live site)

I asked ZAP to open admin, staff and customer pages **while signed out**. Every one
bounced straight to the login page — none showed protected content:

`/admin/dashboard`, `/admin/orders`, `/admin/users`, `/staff/dashboard`,
`/staff/orders`, `/customer/orders`, `/customer/shop`, `/customer/cart` — all
redirected to `/login`.

## In short

No serious security holes. Attacks on the forms failed, forged requests were blocked,
the login session is protected, and guests can't reach staff or admin pages. The few
warnings that came up are optional hardening, not real risks. All five checks passed.

*Scripts are in `tools/loadtest/`. To re-run the scan, see [TestingGuide.md](../TestingGuide.md).*
