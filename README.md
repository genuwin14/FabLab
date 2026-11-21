# FabLab: server setup

**Web-Based Customization with an Inventory Monitoring System for the CSPC FabLab**

This page gets the site running at `https://fablab.cspc.edu.ph` in about 30
minutes, most of it the first build. It is the short version. The full guide,
with every option and a troubleshooting table, is
[backend/docs/DockerDeployment.md](backend/docs/DockerDeployment.md). The §
numbers below point into it.

FabLab runs as three containers on one Linux server:

| Container | Job |
| :--- | :--- |
| `fablab-app` | The website (Apache + PHP 8.2), published on port **8080** |
| `fablab-scheduler` | The timed jobs (same image) |
| `fablab-db` | MySQL 8.0, database `fablab_db`. Not published: only the two above can reach it. |

One script, `backend/docker/fablab.sh`, does the setup and the day-to-day work.

## 1. Before you start

- A Linux server with **Docker Engine 20.10+** and the **Compose v2** plugin
  (check with `docker --version` and `docker compose version`), and `unzip`.
- 2 CPU cores and 2 GB of RAM (4 GB is comfortable). About 3 GB of disk, plus
  room for uploads and backups.
- Internet access from the server. The first build downloads from Docker Hub,
  `packagist.org`, `github.com` and `registry.npmjs.org`. If outbound traffic
  goes through a campus proxy, see §13.
- DNS for `fablab.cspc.edu.ph` pointing at your reverse proxy.

## 2. Unzip

```bash
sudo unzip /path/to/FabLab-<date>.zip -d /srv      # creates /srv/fablab
sudo chown -R "$USER": /srv/fablab
cd /srv/fablab/backend/docker
```

From git instead of a zip: `git clone <repository URL> /srv/fablab`.

## 3. Install

```bash
./fablab.sh install          # sudo ./fablab.sh install, if Docker needs sudo on this server
```

It asks two questions:

1. **Address people will use for the site.** Press Enter to test it first at
   `http://<server IP>:8080`. If the reverse proxy (step 4) is already in
   place, type `https://fablab.cspc.edu.ph` instead.
2. **How should FabLab start?**
   - `1` **Empty, with your own admin account.** Choose this for real use. You
     type the admin's name, e-mail, mobile number and password.
   - `2` **Demo data.** Sample products and orders, plus test accounts whose
     password is `password`. For trying it out only, never on a public site.
   - `3` **Nothing yet.** Choose this if you will load an existing FabLab
     database next (§11).

The script then writes `backend/docker/.env`, generating the app key and the
database passwords. It builds the image (5 to 10 minutes the first time),
starts the containers, creates the tables, and prints the site address.

**Check:** `./fablab.sh status` shows all three containers `Up`, with
`fablab-app` and `fablab-db` `(healthy)`. Then open the address and sign in as
the admin.

## 4. HTTPS and the subdomain

The containers serve plain HTTP on port 8080. HTTPS is handled by your reverse
proxy, which must:

- forward `fablab.cspc.edu.ph` to `http://<server>:8080`
- send the `X-Forwarded-Proto`, `X-Forwarded-For` and `X-Forwarded-Host` headers
- accept uploads up to **20 MB** (nginx: `client_max_body_size 20m;`)
- wait up to **3 minutes** for a reply (nginx: `proxy_read_timeout 180s;`)

§7.1 has a complete nginx config. If the proxy runs on this same server, also
set `APP_BIND=127.0.0.1` in `.env`, because ports that Docker publishes bypass
`ufw`.

If you pressed Enter at the address question, switch the site to HTTPS now by
setting these in `backend/docker/.env`:

```ini
APP_URL=https://fablab.cspc.edu.ph
GOOGLE_REDIRECT_URI=https://fablab.cspc.edu.ph/auth/google/callback
SESSION_SECURE_COOKIE=true
```

Then run `./fablab.sh restart`.

## 5. E-mail, SMS and Google sign-in

Until these are set up, the site writes e-mails and text messages to its log
instead of sending them (`./fablab.sh logs` shows them). When the FabLab team
gives you the account details, put them in `backend/docker/.env` and run
`./fablab.sh restart`:

| For | Settings | Test |
| :--- | :--- | :--- |
| E-mail (Gmail with an App Password) | `MAIL_MAILER=smtp`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` | `./fablab.sh artisan smtp:test <your e-mail>` |
| SMS (UniSMS) | `SMS_DRIVER=unisms`, `UNISMS_API_KEY`, `UNISMS_SENDER`, `UNISMS_WEBHOOK_SECRET` | `./fablab.sh artisan sms:test <your mobile number>` |
| Google sign-in | `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` | The Google button on the login page |

§6 has the details, including what to register on the Google Cloud and UniSMS
side.

## 6. Day to day

Run these from `backend/docker/`:

| Command | What it does |
| :--- | :--- |
| `./fablab.sh status` | Shows the containers, their health and the site address |
| `./fablab.sh logs` | Follows the app's log (Ctrl+C stops) |
| `./fablab.sh create-user` | Makes an admin or staff account (the app has no screen for this) |
| `./fablab.sh backup` | Saves the database, uploads and settings to `backups/<date-time>/` |
| `./fablab.sh restore backups/<date-time>` | Puts a backup back, replacing the current data |
| `./fablab.sh restart` | Applies changes made to `.env` |
| `./fablab.sh help` | Lists every other command |

- The containers come back by themselves after a reboot, as long as Docker
  starts at boot (`sudo systemctl enable docker`).
- A nightly backup takes two cron lines (§10). Copy `backups/` off the server
  regularly.
- `.env` and `backups/` contain the app key and the database passwords, so keep
  them private. Never change `APP_KEY`, `DB_PASSWORD` or `DB_ROOT_PASSWORD`
  after the install.

## 7. Installing a new version

When you receive a newer zip:

```bash
cd /srv
sudo mv fablab fablab-old
sudo unzip /path/to/FabLab-<new date>.zip -d /srv && sudo chown -R "$USER": /srv/fablab
sudo cp -p  fablab-old/backend/docker/.env    fablab/backend/docker/
sudo cp -rp fablab-old/backend/docker/backups fablab/backend/docker/   # if you have backups
cd fablab/backend/docker && ./fablab.sh update
```

`update` takes a backup, rebuilds the image and restarts, so the site is down
for about a minute. The data is kept in Docker volumes, so it carries over.
Delete `fablab-old` once the new version works. From git, this is just
`git pull` followed by `./fablab.sh update`.

## 8. If something goes wrong

Start with `./fablab.sh status` and `./fablab.sh logs`. The first lines of the
app's log usually name the problem. §13 lists the rest.

| Symptom | Fix |
| :--- | :--- |
| `Cannot reach Docker` | Start Docker (`sudo systemctl start docker`), or run `sudo ./fablab.sh …` |
| `port is already allocated` | Set another `APP_PORT` in `.env`, then run `./fablab.sh restart` |
| Signing in sends you back to the login page | `SESSION_SECURE_COOKIE=true` while the site is still plain HTTP. Set it to `false` until HTTPS works. |
| Pages look unstyled | The visitor's browser cannot reach `cdn.jsdelivr.net`, `cdnjs.cloudflare.com` or `fonts.googleapis.com` |

## Good to know

- Times shown by the app, and the scheduled jobs, run on UTC, which is 8 hours
  behind Manila (§12.1).
- PHP 8.2 gets security fixes only until 31 December 2026. To move to 8.3, set
  `PHP_VERSION=8.3` in `.env` and run `./fablab.sh update` (§12.3).
- User guides for admins, staff and customers are in
  [backend/docs/UserGuide/](backend/docs/UserGuide/README.md).
