# FabLab – Docker Deployment Guide (CSPC server)

This guide puts FabLab on a Linux server that runs Docker, at
`fablab.cspc.edu.ph`. It is written for the ICTU staff who host it. You do not
need to know Laravel or PHP, but you do need a shell on the server and the
right to run Docker.

Everything is driven by one script, `backend/docker/fablab.sh`. Most of this
guide explains what the script does and what to do around it: HTTPS, e-mail,
backups, updates.

> **Estimated time**: about 30 minutes, most of it the first image build.

Other guides: the developer's local setup is the [README](../README.md), and the
earlier shared-hosting setup is [Deployment.md](Deployment.md) (Hostinger).

---

## 1. What runs

These are the three containers from the hosting request:

| Container | Image | Job |
| :--- | :--- | :--- |
| `fablab-app` | built here, from `php:8.2-apache` | The website. Apache + PHP, port 80 inside the container, published on the server as port **8080**. |
| `fablab-scheduler` | the same image | Runs `php artisan schedule:work`, which fires the three timed jobs ([§12.1](#121-scheduled-jobs-run-on-utc)). |
| `fablab-db` | `mysql:8.0` | The database `fablab_db`. **Not published**: only the two containers above can reach it. |

And three Docker volumes hold everything that must survive a rebuild:

| Volume | Mounted at | Holds |
| :--- | :--- | :--- |
| `fablab-db-data` | `/var/lib/mysql` in `fablab-db` | The database |
| `fablab-storage` | `storage/app` in both PHP containers | Uploaded designs, product photos, profile pictures, order PDFs |
| `fablab-logs` | `storage/logs` in both PHP containers | The application's daily log files |

```
visitors ── HTTPS ──> ICTU reverse proxy ── HTTP ──> server:8080 ──> fablab-app ──┐
                       (fablab.cspc.edu.ph)                                        ├──> fablab-db
                                                                fablab-scheduler ──┘
```

The containers never handle HTTPS themselves. Your reverse proxy ends HTTPS and
forwards plain HTTP to port 8080 ([§7](#7-https-and-the-subdomain)).

---

## 2. Before you start

- [ ] **A Linux server** with Docker Engine 20.10 or newer and the Compose v2
      plugin. Check with `docker --version` and `docker compose version`. If the
      second fails, install the `docker-compose-plugin` package.
- [ ] **Size**: 2 CPU cores and 2 GB of RAM is enough (4 GB is comfortable).
      Disk: about 3 GB for the images, plus room for uploads and backups.
- [ ] **Internet access from the server.** The first build downloads from
      Docker Hub, `packagist.org`, `github.com` and `registry.npmjs.org`. While
      running, the site sends mail through `smtp.gmail.com:587`, texts through
      `unismsapi.com`, and uses Google sign-in. If the campus needs an HTTP
      proxy for outbound traffic, set it in Docker first ([§13](#13-troubleshooting)).
- [ ] **The source code**: the FabLab git repository.
- [ ] **The 3D model files**, handed over separately (see below).
- [ ] **DNS**: `fablab.cspc.edu.ph` pointing at your reverse proxy.
- [ ] For later, not needed to start: the Gmail sending account and its App
      Password, the Google sign-in client, the UniSMS API key.

> **Why the 3D models come separately.** The 3D customiser draws products from
> `.glb` model files, 132 MB in total, one of them 83 MB. That is too big for
> git, so the repository does not have them. You get them as
> `fablab-gbl.zip`. Without them the site works, but the customiser shows an
> empty stage.

---

## 3. Put the code on the server

Any location works; this guide uses `/srv/fablab`.

```bash
sudo mkdir -p /srv/fablab && sudo chown "$USER" /srv/fablab
git clone <the FabLab repository URL> /srv/fablab
cd /srv/fablab/backend          # the folder that contains "artisan"
```

Put the 3D models into `public/gbl/`, so that you have
`/srv/fablab/backend/public/gbl/t-shirt.glb` and so on. They come as
`fablab-gbl.zip` (about 110 MB), with the `.glb` files at the top of the zip:

```bash
unzip /path/to/fablab-gbl.zip -d public/gbl/
ls public/gbl/        # bag.glb cup.glb polo.glb t-shirt.glb tote_bag.glb umbreella_open.glb
```

> If your copy of the repository has no `backend/` folder and `artisan` is at
> the top, use that top folder wherever this guide says `backend/`.

---

## 4. Install

```bash
cd /srv/fablab/backend/docker
./fablab.sh install
```

The script needs `bash`, so run it as `./fablab.sh` or `bash fablab.sh`, not
`sh fablab.sh`. If Docker needs `sudo` on this server, use `sudo ./fablab.sh install`.

It asks one question:

```
Address people will use for the site [http://10.0.0.25:8080]:
```

- Want to test it on the campus network before HTTPS is set up? Press Enter.
  The site is then reached at the server's own address, port 8080.
- Is the reverse proxy for `fablab.cspc.edu.ph` already in place? Type
  `https://fablab.cspc.edu.ph`.

You can change it later ([§6](#6-settings)). Then the script:

1. Writes the settings file `docker/.env` (permissions 600). It generates the
   application key and both database passwords.
2. Builds the image. This takes 5 to 10 minutes the first time and is quicker after that.
3. Starts the three containers. On the first start, `fablab-app` creates
   every database table before it answers.
4. Asks how the database should start, if it is empty:

   | Choice | When to use it |
   | :--- | :--- |
   | **1) Empty, with your own admin account** | Real use. You type the admin's name, e-mail and password. Products, materials and colours are then added through the admin pages. |
   | **2) Demo data** | Trying the system out, training, a demonstration. It loads a sample catalogue, orders and suppliers, and three accounts (`admin@gmail.com`, `staff@gmail.com`, `customer@gmail.com`), **all with the password `password`**. Change or disable them before the site is public. |
   | **3) Nothing yet** | You are about to load a copy of an existing FabLab database ([§11](#11-moving-the-data-from-the-hostinger-site)). |

   The same choices can be given up front: `./fablab.sh install --empty`,
   `--demo` or `--no-data`.

It ends with a summary like this:

```
==> FabLab is running
    Site:      http://10.0.0.25:8080
    Directly:  http://10.0.0.25:8080  (port 8080 on this server)
    Settings:  /srv/fablab/backend/docker/.env
```

`install` is safe to run again. It keeps the existing `docker/.env` and never
touches a database that already has accounts in it. That also makes it the
first thing to try when a setup half-failed.

---

## 5. Check that it works

1. Open the site address in a browser. You should get the FabLab landing page,
   with styling.
2. Sign in with the admin account you created (or `admin@gmail.com` /
   `password` with demo data). You should land on the admin dashboard.
3. On the server:

   ```bash
   ./fablab.sh status
   ```

   All three should be `Up`, and `fablab-app` and `fablab-db` should also say
   `(healthy)`. `fablab-scheduler` has no health check of its own.

More admin or staff accounts are made the same way, at any time:

```bash
./fablab.sh create-user
```

The app has no screen for making staff accounts, so this command is how
they are made. Customers register themselves on the site.

---

## 6. Settings

All settings live in **`backend/docker/.env`**. After any change:

```bash
./fablab.sh restart
```

`restart` recreates the two PHP containers so they read the file again. A plain
`docker restart` does **not** pick up changes.

`.env.example` in the same folder documents every line. These are the ones you
will touch:

| Setting | What it is |
| :--- | :--- |
| `APP_URL` | The exact address of the site, no trailing slash. Links in e-mails, product images and Google sign-in are built from it. |
| `APP_PORT` / `APP_BIND` | Where the site is published on the server: port 8080 on all interfaces by default ([§7](#7-https-and-the-subdomain)). |
| `SESSION_SECURE_COOKIE` | `true` once the site is on HTTPS. While it is still plain HTTP it must stay `false`, or nobody can log in. |
| `MAIL_*` | Outgoing e-mail ([§6.1](#61-e-mail)). |
| `GOOGLE_*` | Sign in with Google ([§6.3](#63-sign-in-with-google)). |
| `SMS_DRIVER`, `UNISMS_*` | Text messages ([§6.2](#62-sms)). |
| `APP_KEY`, `DB_PASSWORD`, `DB_ROOT_PASSWORD` | Generated by `install`. **Do not change** them on a running system ([§13](#13-troubleshooting) explains why). |

Quoting: put `"double quotes"` around a value with spaces, and `'single quotes'`
around one that contains a `$`, because Docker Compose reads `$` as a variable.

### 6.1 E-mail

The site e-mails sign-up codes, password-reset codes, order slips and stock
alerts. Until mail is set up, `MAIL_MAILER=log` writes each message to the log
instead of sending it.

With a Gmail or Google Workspace account (for example a `@cspc.edu.ph` account
made for the FabLab):

1. Turn on 2-Step Verification for that account, then create an **App
   Password**: Google Account → Security → 2-Step Verification → App passwords.
   Workspace administrators may have to allow App Passwords first.
2. In `docker/.env`:

   ```ini
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=fablab@cspc.edu.ph
   MAIL_PASSWORD=abcdefghijklmnop      # the App Password, without spaces
   MAIL_FROM_ADDRESS=fablab@cspc.edu.ph
   MAIL_FROM_NAME="CSPC FabLab"
   ```

3. `./fablab.sh restart`, then send a test:

   ```bash
   ./fablab.sh artisan smtp:test you@cspc.edu.ph
   ```

E-mail is sent while the page loads (`QUEUE_CONNECTION=sync`), as on the current
live site, so no queue-worker container is needed. A mail failure is logged and
does not break the page.

### 6.2 SMS

Sign-up and password-reset codes can also go by text through
[UniSMS](https://unismsapi.com). Until it is set up, `SMS_DRIVER=log` writes each
text to the log.

```ini
SMS_DRIVER=unisms
UNISMS_API_KEY=<from the UniSMS dashboard, API Keys>
UNISMS_SENDER=<a Sender ID approved on that account, spelled exactly>
UNISMS_WEBHOOK_SECRET=<from the UniSMS dashboard, Webhooks>
```

On the UniSMS Webhooks page, register `https://fablab.cspc.edu.ph/api/webhooks/unisms`
for delivery receipts. Then `./fablab.sh restart` and test:

```bash
./fablab.sh artisan sms:test 09171234567
```

Credits are prepaid. A new UniSMS account only has its random test Sender ID,
which reaches **Globe and DITO numbers only**. A custom one needs business
documents. [UniSms.md](UniSms.md) has the details.

### 6.3 Sign in with Google

In the [Google Cloud console](https://console.cloud.google.com/), go to APIs & Services →
Credentials → your OAuth 2.0 client, and add:

- Authorised JavaScript origin: `https://fablab.cspc.edu.ph`
- Authorised redirect URI: `https://fablab.cspc.edu.ph/auth/google/callback`

Then in `docker/.env`:

```ini
GOOGLE_CLIENT_ID=<client ID>
GOOGLE_CLIENT_SECRET=<client secret>
GOOGLE_REDIRECT_URI=https://fablab.cspc.edu.ph/auth/google/callback
```

Google only accepts an `https://` redirect address for a real domain, so this
works once [§7](#7-https-and-the-subdomain) is done. While the OAuth app is in
"Testing" status, only the test users listed there can sign in.

---

## 7. HTTPS and the subdomain

`fablab-app` speaks plain HTTP on port 8080. Put your reverse proxy in front of
it for `fablab.cspc.edu.ph`, with the campus certificate or Let's Encrypt. The
proxy must:

- forward to `http://<server>:8080`,
- send the `X-Forwarded-Proto`, `X-Forwarded-For` and `X-Forwarded-Host` headers,
- allow request bodies up to **20 MB** (uploads and design files),
- wait up to **3 minutes** for a response (large reports take up to 2).

### 7.1 Nginx on the same server

```nginx
server {
    listen 80;
    server_name fablab.cspc.edu.ph;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name fablab.cspc.edu.ph;

    ssl_certificate     /etc/ssl/certs/fablab.cspc.edu.ph.crt;     # your certificate
    ssl_certificate_key /etc/ssl/private/fablab.cspc.edu.ph.key;

    client_max_body_size 20m;          # nginx's default of 1 MB breaks uploads

    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host              $host;
        proxy_set_header X-Forwarded-For   $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Host  $host;
        proxy_set_header X-Forwarded-Port  $server_port;
        proxy_read_timeout 180s;
    }
}
```

When the proxy runs on this same server, also set `APP_BIND=127.0.0.1` in
`docker/.env`. Port 8080 is then only reachable from the server itself.

> **Docker ports bypass `ufw`.** A port Docker publishes on `0.0.0.0` is open
> to the network even if `ufw` denies it, because Docker writes its own
> firewall rules. `APP_BIND=127.0.0.1` is the reliable way to keep 8080 private.

### 7.2 Other proxies

| Proxy | Notes |
| :--- | :--- |
| Proxy on another machine | Leave `APP_BIND=0.0.0.0` and point it at `http://<this server's IP>:8080`. Firewall port 8080 so only the proxy can reach it. |
| Apache | `ProxyPass / http://127.0.0.1:8080/`, `ProxyPassReverse / http://127.0.0.1:8080/`, `ProxyPreserveHost On`, and `RequestHeader set X-Forwarded-Proto "https"`. Apache does not send that last header by itself. |
| Nginx Proxy Manager, Traefik, Caddy | They send the forwarded headers by default. Raise the upload limit if the tool has one. |
| A proxy that runs in Docker on this server | Either point it at `http://<this server's IP>:8080`, or join `fablab-app` to the proxy's network and use `http://fablab-app:80`. To join, create `docker/compose.override.yaml`: <br>`services: { fablab-app: { networks: [default, proxy] } }`<br>`networks: { proxy: { external: true, name: <the proxy's network> } }` |

The app trusts forwarded headers only from private addresses (`TRUSTED_PROXIES`
in `docker/.env`): 10.x, 172.16–31.x, 192.168.x and 127.0.0.1. If your proxy
reaches the server from a public address, add that address to the list.

### 7.3 Switch the site to its HTTPS address

Once `https://fablab.cspc.edu.ph` shows the site:

```ini
APP_URL=https://fablab.cspc.edu.ph
GOOGLE_REDIRECT_URI=https://fablab.cspc.edu.ph/auth/google/callback
SESSION_SECURE_COOKIE=true
```

Then `./fablab.sh restart`. Check:

- [ ] `https://fablab.cspc.edu.ph` loads with a padlock, and the browser
      console shows no "mixed content" warnings.
- [ ] Logging in works, and you stay logged in from page to page.
- [ ] `https://fablab.cspc.edu.ph/.env` shows a "not found" page, never a file.

---

## 8. Day-to-day commands

Run them from `backend/docker/`:

| Command | What it does |
| :--- | :--- |
| `./fablab.sh status` | The containers, their health, and the site address |
| `./fablab.sh logs` | Follows `fablab-app`'s log: web requests and application errors. Ctrl+C stops following. `./fablab.sh logs fablab-scheduler` or `fablab-db` for the others. |
| `./fablab.sh stop` / `start` | Stops or starts everything. The data stays in the volumes. |
| `./fablab.sh restart` | Applies changes made to `docker/.env` |
| `./fablab.sh create-user` | Makes an admin or staff account |
| `./fablab.sh backup` | Saves the database, uploads and settings ([§10](#10-backups)) |
| `./fablab.sh update` | Installs a new version of the code ([§9](#9-updating)) |
| `./fablab.sh artisan <command>` | Runs a Laravel command, for example `artisan about` |
| `./fablab.sh shell` | A shell inside `fablab-app` |
| `./fablab.sh help` | The full list |

The containers restart on their own after a crash or a server reboot
(`restart: unless-stopped`), as long as Docker itself starts at boot
(`sudo systemctl enable docker`).

The daily log files are also kept inside the `fablab-logs` volume as
`laravel-YYYY-MM-DD.log`, for 30 days. To read one:

```bash
./fablab.sh shell
ls storage/logs/ && tail -n 100 storage/logs/laravel-2026-11-19.log
```

---

## 9. Updating

When the developers publish a new version:

```bash
cd /srv/fablab && git pull
cd backend/docker && ./fablab.sh update
```

`update` does four things:

1. Takes a backup ([§10](#10-backups)).
2. Rebuilds the image from the code now in the folder. It also picks up
   security fixes in the PHP base image.
3. Pulls the latest `mysql:8.0` patch release.
4. Recreates the containers. As `fablab-app` starts, it runs any new database
   migrations by itself, before it serves a page.

The site is down for about a minute while the containers swap. The 3D models in
`public/gbl/` are not touched by `git pull`, so they stay where they are.

If an update goes wrong, restore the backup it took:
`./fablab.sh restore backups/<date-time>` (the newest folder), after putting the
old code back with `git checkout <previous commit>` and `./fablab.sh update`.

---

## 10. Backups

```bash
./fablab.sh backup
```

This writes a folder `backend/docker/backups/<date-time>/` with:

| File | Contents |
| :--- | :--- |
| `database.sql.gz` | The whole database (`mysqldump`, consistent while the site runs) |
| `uploads.tar.gz` | Everything in `fablab-storage`: designs, product photos, order PDFs |
| `env` | A copy of `docker/.env`, **including the application key and database passwords** |

The site stays up while it runs. To take one every night at 2:30 and keep 14
days of them, add these lines to the crontab of the account you run
`fablab.sh` with (root's, `sudo crontab -e`, if you use `sudo`). Running it
under the same account keeps the `backups/` folder writable for both.

```cron
30 2 * * * /srv/fablab/backend/docker/fablab.sh backup >> /srv/fablab/backend/docker/backups/backup.log 2>&1
45 2 * * * find /srv/fablab/backend/docker/backups -mindepth 1 -maxdepth 1 -type d -mtime +14 -exec rm -rf {} +
```

A backup that stays on the same disk as the site does not protect against
losing that disk. Copy the `backups/` folder to another machine regularly, and
treat it as confidential, because `env` holds the passwords.

### Restoring

```bash
./fablab.sh restore backups/20261119-023000
```

It asks you to type `yes`, because it **replaces** the current database and
uploads. Then it starts the site again.

### Moving to a new server

1. On the old server: `./fablab.sh backup`.
2. On the new one: follow [§3](#3-put-the-code-on-the-server), then copy the
   backup folder over and put its settings in place **before** installing:

   ```bash
   cp backups/<date-time>/env docker/.env && chmod 600 docker/.env
   ./fablab.sh install --no-data
   ./fablab.sh restore backups/<date-time>
   ```

Keeping the old `env` keeps the old application key, so nobody is logged out.

---

## 11. Moving the data from the Hostinger site

FabLab currently runs on Hostinger shared hosting. To bring its records over
instead of starting fresh:

1. **Export the database.** In hPanel → Databases → phpMyAdmin, select the FabLab
   database, then Export → Quick → SQL → Export. You get a `.sql` file.
2. **Download the uploaded files.** In hPanel → File Manager:
   - `public_html/fablab/public/storage/` holds the images and designs. Right-click → Compress → Zip, then download it.
   - `public_html/fablab/storage/app/private/` holds the order PDFs. Download it the same way.
3. **Copy both to the server** (`scp`, WinSCP or similar) and unzip them.
4. **Load them** (the database first):

   ```bash
   ./fablab.sh import-db ~/fablab_export.sql
   ./fablab.sh import-uploads ~/hostinger-storage public    # the folder with designs/, products/, …
   ./fablab.sh import-uploads ~/hostinger-private private   # the folder with orders/
   ```

`import-db` asks you to type `yes` and replaces whatever is in the database.
It accepts `.sql` or `.sql.gz`. When the site starts again, it adds any tables or
columns the export is missing, so an export from an older version is fine.

Things to know about a Hostinger copy:

- **It contains the demo accounts.** The live database started from the demo
  data, so `admin@gmail.com`, `staff@gmail.com` and `customer@gmail.com` exist
  with the password `password`, unless someone changed them. They also have
  made-up phone numbers, so an SMS test with them texts a stranger. Change or
  disable them.
- **Everyone has to log in again.** The new server has its own application key.
  No stored data depends on the key, so nothing else is lost.
- **Use the Quick export.** A Custom export with "Add CREATE DATABASE / USE
  statement" ticked is refused, because it names Hostinger's database rather
  than this one.

---

## 12. Things worth knowing

### 12.1 Scheduled jobs run on UTC

The application keeps its clock in UTC, and `fablab-scheduler` fires its jobs
on that clock:

| Job | UTC | Philippine time |
| :--- | :--- | :--- |
| Flag purchase orders past their expected date | daily 07:00 | daily 3:00 PM |
| Close orders whose Purchase Request deadline passed | daily 00:15 | daily 8:15 AM |
| Re-tune low-stock thresholds from last month | 1st of the month 01:30 | 1st of the month 9:30 AM |

Times printed by the app (order details, slips, logs) are also in UTC, so they
are 8 hours behind Manila. That is a known issue in the app, not in the
Docker setup.

### 12.2 Visitors need the internet too

The pages load Bootstrap, the icons, the fonts, the charts and the 3D library
from public CDNs (`cdn.jsdelivr.net`, `cdnjs.cloudflare.com`,
`fonts.googleapis.com`). A browser that cannot reach them shows unstyled pages.
That matters for a closed lab network, not for normal campus internet.

### 12.3 Versions

The image uses PHP 8.2 and MySQL 8.0, as in the hosting request. Both are
reaching the end of their support:

- **PHP 8.2** gets security fixes until **31 December 2026**. To move to 8.3,
  set `PHP_VERSION=8.3` in `docker/.env` and run `./fablab.sh update`.
- **MySQL 8.0** reached end of life in **April 2026**. Its successor is 8.4 LTS,
  which upgrades a MySQL 8.0 database in place. **Back up first**, because a
  database that 8.4 has opened cannot go back to 8.0. Then set
  `MYSQL_VERSION=8.4` and run `./fablab.sh update`.

### 12.4 Where things are inside `fablab-app`

| Path | What |
| :--- | :--- |
| `/var/www/html` | The application (read-only in practice: it is rebuilt by `update`) |
| `/var/www/html/storage/app` | The `fablab-storage` volume |
| `/var/www/html/storage/logs` | The `fablab-logs` volume |
| `/var/www/html/public/gbl` | The 3D models, baked into the image at build time |

Everything inside runs as the `www-data` user (uid 33), artisan commands
included. That way no file is ever left owned by root where the website cannot
write it.

---

## 13. Troubleshooting

Start with `./fablab.sh status` and `./fablab.sh logs`. Most problems name
themselves in the first lines of `fablab-app`'s log.

| Symptom | Cause and fix |
| :--- | :--- |
| `Cannot reach Docker` | Docker is not running (`sudo systemctl start docker`), or your user may not use it. Use `sudo ./fablab.sh …`, or add yourself to the `docker` group and log in again. |
| `docker compose` not found | Install the Compose v2 plugin: `sudo apt install docker-compose-plugin` (Debian/Ubuntu, Docker's repository). |
| Build fails downloading packages | The server cannot reach the internet, or must go through a campus proxy. Give Docker the proxy in `~/.docker/config.json` (`"proxies": {"default": {"httpProxy": …, "httpsProxy": …}}`) and in the Docker daemon's settings, then build again. |
| `port is already allocated` | Something else uses port 8080. Set another `APP_PORT` in `docker/.env` and run `./fablab.sh restart`. |
| `fablab-app` keeps restarting with **"the database refused the login"** | `DB_PASSWORD` in `docker/.env` was changed after the database was created. MySQL only reads it the first time. Put the old value back. Or, if you still know `DB_ROOT_PASSWORD`, set the new password inside MySQL: `docker exec -it fablab-db mysql -uroot -p` and then `ALTER USER 'fablab'@'%' IDENTIFIED BY '<the new DB_PASSWORD>';` |
| `install` stops with **"A FabLab database already exists … docker/.env does not"** | The settings file was lost but the database volume was not. Copy `env` from the newest backup folder to `docker/.env`. If there is no backup and the data is not needed: `docker compose down -v`, then install again. **That deletes the database.** |
| The page is unstyled | The visitor's browser cannot reach the CDNs ([§12.2](#122-visitors-need-the-internet-too)). |
| Product images are broken, or links go to the wrong address | `APP_URL` does not match the address in the browser. Fix it and run `./fablab.sh restart`. |
| Logging in "works" but you land back on the login page | `SESSION_SECURE_COOKIE=true` while the site is still plain HTTP. Set it to `false`, or finish [§7](#7-https-and-the-subdomain). |
| Mixed-content warnings, or forms post to `http://` | The proxy does not send `X-Forwarded-Proto`, or its address is not in `TRUSTED_PROXIES`. With an `https://` `APP_URL` the app keeps links on https even then. Check `APP_URL` first. |
| Uploads fail with "413 Request Entity Too Large" | The reverse proxy's body-size limit. Nginx needs `client_max_body_size 20m;`. |
| The 3D customiser shows an empty stage | `public/gbl/` was empty when the image was built. Copy the models in ([§3](#3-put-the-code-on-the-server)), then `./fablab.sh update`. |
| E-mail never arrives | Run `./fablab.sh artisan smtp:test you@example.com`. It shows the SMTP error. Usually the App Password, or the server cannot reach `smtp.gmail.com:587`. |
| "Permission denied" under `storage/` | Only happens if you replaced a volume with a folder on the server (a bind mount). That folder must belong to uid 33: `sudo chown -R 33:33 <folder>`. |
| Settings changes do nothing | Run `./fablab.sh restart`, not `docker restart`. Settings are read when a container is created. |

---

## 14. Removing FabLab

```bash
cd /srv/fablab/backend/docker
docker compose down              # stops and removes the containers; the data stays
docker compose down -v           # ALSO DELETES the database and uploads. Back up first.
docker image rm fablab-app       # the built image
```

---

## Appendix: the files in `backend/docker/`

| File | Purpose |
| :--- | :--- |
| `fablab.sh` | The script this guide uses for everything |
| `compose.yaml` | The three containers, the volumes, the health checks |
| `Dockerfile` | Builds the image: PHP extensions, Composer packages, the front-end bundle, the code |
| `.env.example` | Every setting, with notes. `install` copies it to `.env`. |
| `.env` | Your settings (made by `install`, never committed to git) |
| `entrypoint.sh` | Runs as a container starts: waits for the database, caches settings, runs new migrations |
| `wait-for-db.php` | The "wait for the database" step, and its error when a password is wrong |
| `apache/fablab.conf` | Apache: serve `public/` only |
| `php/fablab.ini` | PHP limits: memory, upload size, timeouts |
| `backups/` | Made by `./fablab.sh backup` (never committed to git) |

`../.dockerignore` keeps `.env` files, `vendor/`, `node_modules/`, uploads and
logs out of the image.
