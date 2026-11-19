#!/usr/bin/env bash
#
# fablab.sh: set up and run FabLab on Docker.
#
#     ./fablab.sh install     first-time setup (safe to run again)
#     ./fablab.sh help        every command
#
# It lives in backend/docker/ and works from there, wherever it is called
# from. The full guide is backend/docs/DockerDeployment.md.

set -euo pipefail

# Git Bash on Windows rewrites arguments that look like Unix paths into
# Windows paths. Everywhere else this does nothing.
export MSYS_NO_PATHCONV=1

cd "$(dirname "${BASH_SOURCE[0]}")"
DOCKER_DIR=$PWD
APP_DIR=$(cd .. && pwd)
ENV_FILE=$DOCKER_DIR/.env
BACKUP_ROOT=$DOCKER_DIR/backups

# The 3D customiser's models. Too big for git, so they are copied in by hand.
MODELS=(t-shirt.glb polo.glb tote_bag.glb cup.glb umbreella_open.glb)

if [ -t 1 ]; then
    BOLD=$'\e[1m' GREEN=$'\e[32m' YELLOW=$'\e[33m' RED=$'\e[31m' RESET=$'\e[0m'
else
    BOLD='' GREEN='' YELLOW='' RED='' RESET=''
fi

say()  { printf '%s==>%s %s%s%s\n' "$GREEN" "$RESET" "$BOLD" "$*" "$RESET"; }
note() { printf '    %s\n' "$*"; }
warn() { printf '%swarning:%s %s\n' "$YELLOW" "$RESET" "$*" >&2; }
die()  { printf '%serror:%s %s\n' "$RED" "$RESET" "$*" >&2; exit 1; }

interactive() { [ -t 0 ]; }

# ask QUESTION DEFAULT: prints the answer. Off a terminal, prints DEFAULT.
ask() {
    local reply=''
    if interactive; then read -r -p "$1 [$2]: " reply; fi
    printf '%s' "${reply:-$2}"
}

# confirm QUESTION: yes or no, no being the default. Off a terminal: no.
confirm() {
    local reply=''
    interactive || return 1
    read -r -p "$1 [y/N] " reply
    [[ $reply =~ ^[Yy] ]]
}

# confirm_overwrite WHAT ASSUME_YES: for the commands that replace data.
confirm_overwrite() {
    warn "This replaces $1."
    [ "$2" = yes ] && return 0
    interactive || die "Not on a terminal. Add --yes to confirm."
    local reply=''
    read -r -p "Type yes to go ahead: " reply
    [ "$reply" = yes ] || die "Stopped. Nothing was changed."
}

compose() { docker compose "$@"; }

# -T when there is no terminal (cron, pipes), or docker refuses.
tty_flag() { if [ -t 0 ] && [ -t 1 ]; then :; else printf -- '-T'; fi; }

# --- docker/.env -------------------------------------------------------------

# env_get KEY: the value, without surrounding quotes. Empty if unset.
env_get() {
    local line
    line=$(grep -E "^$1=" "$ENV_FILE" 2>/dev/null | tail -n 1 || true)
    line=${line#*=}
    line=${line%$'\r'}
    case $line in
        \"*\") line=${line#\"}; line=${line%\"} ;;
        \'*\') line=${line#\'}; line=${line%\'} ;;
    esac
    printf '%s' "$line"
}

# env_set KEY VALUE: changes the line in place, keeping the file's permissions.
env_set() {
    local tmp
    tmp=$(mktemp)
    if grep -qE "^$1=" "$ENV_FILE"; then
        KEY=$1 VALUE=$2 awk 'index($0, ENVIRON["KEY"] "=") == 1 { print ENVIRON["KEY"] "=" ENVIRON["VALUE"]; next } { print }' \
            "$ENV_FILE" > "$tmp"
    else
        cat "$ENV_FILE" > "$tmp"
        printf '%s=%s\n' "$1" "$2" >> "$tmp"
    fi
    cat "$tmp" > "$ENV_FILE"
    rm -f "$tmp"
}

random_hex() { od -An -N"$1" -tx1 /dev/urandom | tr -d ' \n'; }
new_app_key() { printf 'base64:%s' "$(head -c 32 /dev/urandom | base64 | tr -d '\n')"; }

db_name() { local name; name=$(env_get DB_DATABASE); printf '%s' "${name:-fablab_db}"; }

# --- checks ------------------------------------------------------------------

need_docker() {
    command -v docker >/dev/null 2>&1 \
        || die "Docker is not installed. See https://docs.docker.com/engine/install/"
    docker compose version >/dev/null 2>&1 \
        || die "Docker Compose v2 ('docker compose') is missing. Install the docker-compose-plugin package."
    docker info >/dev/null 2>&1 \
        || die "Cannot reach Docker. Is it running? If so, run this with sudo or add your user to the docker group."
}

need_env() {
    [ -f "$ENV_FILE" ] || die "docker/.env does not exist yet. Run ./fablab.sh install first."
}

running() { [ "$(docker inspect -f '{{.State.Running}}' "$1" 2>/dev/null)" = true ]; }

need_running() {
    running "$1" || die "$1 is not running. Start FabLab with ./fablab.sh start"
}

check_models() {
    local missing=() model
    for model in "${MODELS[@]}"; do
        [ -f "$APP_DIR/public/gbl/$model" ] || missing+=("$model")
    done
    [ ${#missing[@]} -eq 0 ] && return 0

    warn "3D customiser models missing from $APP_DIR/public/gbl/: ${missing[*]}"
    note "They are not in git (one is over 80 MB). Unzip fablab-gbl.zip into"
    note "backend/public/gbl/ and run this again, or the customiser stays empty."
    if [ "${1:-}" = yes ] || confirm "Build without them anyway?"; then
        return 0
    fi
    exit 1
}

# wait_healthy CONTAINER SECONDS
wait_healthy() {
    local name=$1 limit=$2 waited=0 state
    while :; do
        state=$(docker inspect -f '{{.State.Status}}/{{if .State.Health}}{{.State.Health.Status}}{{end}}' "$name" 2>/dev/null || echo missing)
        case $state in
            running/healthy | running/) return 0 ;;
            restarting/* | exited/* | dead/* | missing | */unhealthy) return 1 ;;
        esac
        [ "$waited" -ge "$limit" ] && return 1
        sleep 5
        waited=$((waited + 5))
    done
}

start_failed() {
    warn "FabLab did not come up. The last log lines:"
    compose logs --no-color --tail=30 "$@" >&2 || true
    die "The Troubleshooting section of docs/DockerDeployment.md covers the usual causes."
}

# Starts whatever is not running and waits for the website to answer.
bring_up() {
    say "Starting the containers"
    compose up -d --remove-orphans "$@" || start_failed
    wait_healthy fablab-app 600 || start_failed fablab-app
    wait_healthy fablab-scheduler 120 || start_failed fablab-scheduler
}

# --- database helpers --------------------------------------------------------

root_mysql() {
    compose exec -T -e MYSQL_PWD="$(env_get DB_ROOT_PASSWORD)" fablab-db \
        mysql -uroot --default-character-set=utf8mb4 "$@"
}

start_db_only() {
    compose up -d fablab-db
    wait_healthy fablab-db 300 || start_failed fablab-db
}

# load_sql FILE: empties the database, then loads FILE (.sql or .sql.gz).
load_sql() {
    local file=$1 db
    db=$(db_name)
    note "Emptying $db"
    root_mysql -e "DROP DATABASE IF EXISTS \`$db\`; CREATE DATABASE \`$db\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    note "Loading $(basename "$file")"
    # The sed drops the first line newer MariaDB dumps start with, which
    # MySQL's client cannot read.
    case $file in
        *.gz) gzip -dc "$file" ;;
        *) cat "$file" ;;
    esac | sed '\#^/\*M!999999[\]- enable the sandbox mode \*/$#d' | root_mysql "$db"
}

# --- commands ----------------------------------------------------------------

cmd_install() {
    local data=ask assume_yes=no arg url
    for arg in "$@"; do
        case $arg in
            --demo) data=demo ;;
            --empty) data=empty ;;
            --no-data) data=none ;;
            --yes) assume_yes=yes ;;
            *) die "install: unknown option $arg (try --demo, --empty, --no-data)" ;;
        esac
    done

    need_docker
    check_models "$assume_yes"

    if [ -f "$ENV_FILE" ]; then
        say "Keeping the settings already in docker/.env"
    else
        if docker volume inspect fablab-db-data >/dev/null 2>&1; then
            warn "A FabLab database already exists on this server, but docker/.env does not."
            note "New passwords would not match it. Put the old .env back (every backup"
            note "folder has a copy named env), or see the guide to start over."
            die "Stopped. Nothing was changed."
        fi
        say "Writing the settings file docker/.env"
        cp .env.example "$ENV_FILE"
        chmod 600 "$ENV_FILE"
        url=$(ask "Address people will use for the site" "${APP_URL:-http://$(server_ip):$(env_get APP_PORT)}")
        url=${url%/}
        env_set APP_URL "$url"
        env_set GOOGLE_REDIRECT_URI "$url/auth/google/callback"
        case $url in https://*) env_set SESSION_SECURE_COOKIE true ;; esac
    fi

    # Fill in whatever secret is still blank, including in a hand-made .env.
    [ -n "$(env_get APP_KEY)" ] || env_set APP_KEY "$(new_app_key)"
    [ -n "$(env_get DB_PASSWORD)" ] || env_set DB_PASSWORD "$(random_hex 16)"
    [ -n "$(env_get DB_ROOT_PASSWORD)" ] || env_set DB_ROOT_PASSWORD "$(random_hex 16)"

    say "Building the image (the first build takes several minutes)"
    compose build
    note "On a new database, fablab-app creates every table before it answers."
    bring_up

    setup_data "$data"
    summary
}

server_ip() {
    local ip=''
    ip=$(hostname -I 2>/dev/null | awk '{print $1}') || true
    printf '%s' "${ip:-localhost}"
}

setup_data() {
    local choice=$1 count
    count=$(root_mysql -N -B -e 'SELECT COUNT(*) FROM users' "$(db_name)" | tr -d '\r')
    if [ "$count" != 0 ]; then
        note "The database already has $count account(s), so its data is left alone."
        return 0
    fi

    if [ "$choice" = ask ]; then
        if interactive; then
            printf '\n%s\n' "${BOLD}The database is empty. How should FabLab start?${RESET}"
            printf '  1) Empty, with your own admin account (for real use)\n'
            printf '  2) Demo data: sample products and orders, and test accounts whose password is "password"\n'
            printf '  3) Nothing yet: I will import a database next (./fablab.sh import-db)\n'
            while :; do
                read -r -p "Choose 1, 2 or 3: " choice
                case $choice in
                    1) choice=empty; break ;;
                    2) choice=demo; break ;;
                    3) choice=none; break ;;
                esac
            done
        else
            choice=none
        fi
    fi

    case $choice in
        empty)
            interactive || die "--empty asks for the admin's details, so it needs a terminal."
            say "Create the first admin account"
            compose exec fablab-app php artisan users:create --role=admin
            ;;
        demo)
            say "Loading the demo data"
            compose exec -T fablab-app php artisan db:seed --force --no-ansi
            warn "The demo accounts admin@gmail.com, staff@gmail.com and customer@gmail.com"
            note "all have the password \"password\", and made-up phone numbers. Change them"
            note "(Profile page) or disable them before anyone else can reach the site."
            ;;
        none)
            note "No data loaded. Next: ./fablab.sh import-db <file>, or ./fablab.sh create-user"
            ;;
    esac
}

summary() {
    local url port host
    url=$(env_get APP_URL)
    port=$(env_get APP_PORT)
    port=${port:-8080}
    host=$(env_get APP_BIND)
    case $host in '' | 0.0.0.0) host=127.0.0.1 ;; esac
    if command -v curl >/dev/null 2>&1 && ! curl -fsS --max-time 10 "http://$host:$port/up" >/dev/null; then
        warn "The site runs, but http://$host:$port does not answer from this server."
        note "Check APP_BIND and APP_PORT in docker/.env, and the server's firewall."
    fi
    printf '\n'
    say "FabLab is running"
    note "Site:      $url"
    note "Directly:  http://$(server_ip):$port  (port $port on this server)"
    note "Settings:  $ENV_FILE"
    printf '\n'
    note "Still to do (docs/DockerDeployment.md walks through each):"
    note " - e-mail, SMS and Google sign-in settings in docker/.env, then ./fablab.sh restart"
    note " - HTTPS in front of the site, then SESSION_SECURE_COOKIE=true"
    note " - a nightly ./fablab.sh backup"
}

cmd_status() {
    need_docker
    compose ps
    if [ -f "$ENV_FILE" ]; then
        printf '\n'
        note "Site: $(env_get APP_URL)"
    fi
}

cmd_start() {
    need_docker; need_env
    bring_up
    note "Site: $(env_get APP_URL)"
}

cmd_stop() {
    need_docker
    compose stop
}

cmd_restart() {
    need_docker; need_env
    # Recreating, not just restarting, is what makes the containers read
    # docker/.env again.
    say "Recreating fablab-app and fablab-scheduler with the current settings"
    compose up -d --force-recreate --remove-orphans fablab-app fablab-scheduler || start_failed
    wait_healthy fablab-app 600 || start_failed fablab-app
    wait_healthy fablab-scheduler 120 || start_failed fablab-scheduler
    note "Site: $(env_get APP_URL)"
}

cmd_logs() {
    need_docker
    compose logs -f --tail=100 "${1:-fablab-app}"
}

cmd_update() {
    local assume_yes=no arg
    for arg in "$@"; do
        case $arg in
            --yes) assume_yes=yes ;;
            *) die "update: unknown option $arg" ;;
        esac
    done
    need_docker; need_env
    check_models "$assume_yes"
    if running fablab-db; then
        cmd_backup
    else
        warn "fablab-db is not running, so there is no backup before this update."
    fi
    say "Rebuilding the image from $APP_DIR"
    # --pull picks up security fixes in the PHP base image.
    compose build --pull
    compose pull fablab-db || warn "Could not check for a newer MySQL image; keeping the current one."
    bring_up
    say "Updated. New migrations, if any, ran as fablab-app started."
}

cmd_backup() {
    need_docker; need_env; need_running fablab-db
    local dir
    dir=$BACKUP_ROOT/$(date +%Y%m%d-%H%M%S)
    mkdir -p "$dir"
    chmod 700 "$BACKUP_ROOT" "$dir"

    say "Backing up the database"
    compose exec -T -e MYSQL_PWD="$(env_get DB_ROOT_PASSWORD)" fablab-db \
        mysqldump -uroot --single-transaction --quick --no-tablespaces --routines --triggers \
        --default-character-set=utf8mb4 "$(db_name)" | gzip > "$dir/database.sql.gz"
    gzip -dc "$dir/database.sql.gz" | tail -n 1 | grep -q 'Dump completed' \
        || die "The database dump in $dir looks incomplete. Check: ./fablab.sh logs fablab-db"

    say "Backing up the uploaded files"
    compose run --rm --no-deps -T --entrypoint tar fablab-app -C storage/app -czf - . > "$dir/uploads.tar.gz"

    # The settings, APP_KEY and database passwords included. A restore onto a
    # new server needs them.
    cp "$ENV_FILE" "$dir/env"
    chmod 600 "$dir/env"

    say "Backup saved in $dir"
    du -sh "$dir"/* | sed 's/^/    /'
}

cmd_restore() {
    local dir='' assume_yes=no arg
    for arg in "$@"; do
        case $arg in
            --yes) assume_yes=yes ;;
            *) dir=$arg ;;
        esac
    done
    [ -n "$dir" ] || die "Usage: ./fablab.sh restore backups/<date-time> [--yes]"
    dir=${dir%/}
    [ -f "$dir/database.sql.gz" ] || die "$dir/database.sql.gz not found."
    need_docker; need_env

    confirm_overwrite "the current database and uploaded files with the backup in $dir" "$assume_yes"
    if [ -f "$dir/env" ] && ! cmp -s "$dir/env" "$ENV_FILE"; then
        warn "The backup's settings (env) differ from docker/.env. The current docker/.env is kept;"
        note "if this is a new server, copy $dir/env over docker/.env first, so APP_KEY matches."
    fi

    compose stop fablab-scheduler fablab-app >/dev/null 2>&1 || true
    start_db_only
    say "Restoring the database"
    load_sql "$dir/database.sql.gz"

    if [ -f "$dir/uploads.tar.gz" ]; then
        say "Restoring the uploaded files"
        compose run --rm --no-deps -T --entrypoint sh fablab-app -c \
            'find storage/app -mindepth 1 -delete && tar -C storage/app -xzf -' < "$dir/uploads.tar.gz"
    fi

    bring_up
    say "Restored from $dir"
}

cmd_import_db() {
    local file='' assume_yes=no arg
    for arg in "$@"; do
        case $arg in
            --yes) assume_yes=yes ;;
            *) file=$arg ;;
        esac
    done
    [ -n "$file" ] || die "Usage: ./fablab.sh import-db <file.sql or file.sql.gz> [--yes]"
    [ -f "$file" ] || die "$file not found."
    need_docker; need_env

    # A dump that names its own database would load into that one, not ours.
    if { case $file in *.gz) gzip -dc "$file" ;; *) cat "$file" ;; esac; } \
        | grep -qE '^(CREATE DATABASE|USE `)'; then
        die "$file has CREATE DATABASE or USE lines. Export the one database without them (see the guide)."
    fi

    confirm_overwrite "everything in the FabLab database with $file" "$assume_yes"
    if running fablab-db; then
        cmd_backup
    fi

    compose stop fablab-scheduler fablab-app >/dev/null 2>&1 || true
    start_db_only
    say "Importing $file"
    load_sql "$file"
    # fablab-app runs any migrations the export is missing as it starts.
    bring_up
    say "Imported. Uploaded files are separate: ./fablab.sh import-uploads <folder>"
}

cmd_import_uploads() {
    local src=${1:-} target=${2:-public}
    [ -n "$src" ] && [ -d "$src" ] || die "Usage: ./fablab.sh import-uploads <folder> [public|private]"
    case $target in
        public | private) ;;
        *) die "The second argument is public (images, designs) or private (order PDFs)." ;;
    esac
    need_docker; need_env

    say "Copying $src into the site's $target files"
    # Existing files with the same name are replaced; nothing else is removed.
    tar -C "$src" -cf - . \
        | compose run --rm --no-deps -T --entrypoint tar fablab-app -C "storage/app/$target" -xf -
    say "Done."
}

cmd_create_user() {
    need_docker; need_running fablab-app
    compose exec $(tty_flag) fablab-app php artisan users:create "$@"
}

cmd_artisan() {
    need_docker; need_running fablab-app
    [ $# -gt 0 ] || die "Usage: ./fablab.sh artisan <command> [options], e.g. ./fablab.sh artisan about"
    compose exec $(tty_flag) fablab-app php artisan "$@"
}

cmd_shell() {
    need_docker; need_running fablab-app
    compose exec fablab-app bash
}

usage() {
    cat <<EOF
FabLab on Docker

Usage: ./fablab.sh <command> [arguments]

Setting up
  install [--demo | --empty | --no-data]
                          First-time setup: settings, image, containers, data.
                          Safe to run again; it keeps existing settings and data.

Day to day
  status                  The containers and the site's address
  start | stop
  restart                 Also applies changes made to docker/.env
  logs [container]        Follow a log (default fablab-app; or fablab-scheduler, fablab-db)
  update                  Back up, rebuild from the code in this folder, restart

Accounts and maintenance
  create-user             Make an admin or staff account
  artisan <command...>    Run a php artisan command inside fablab-app
  shell                   Open a shell inside fablab-app

Data
  backup                  Database, uploads and settings to docker/backups/<date-time>/
  restore <folder>        Put a backup back (replaces the current data)
  import-db <file>        Load a .sql or .sql.gz export, e.g. from phpMyAdmin
  import-uploads <folder> [public|private]
                          Copy uploaded files in (public: images and designs,
                          private: order PDFs)

The full guide: backend/docs/DockerDeployment.md
EOF
}

main() {
    local command=${1:-help}
    [ $# -gt 0 ] && shift
    case $command in
        install) cmd_install "$@" ;;
        status) cmd_status ;;
        start) cmd_start ;;
        stop) cmd_stop ;;
        restart) cmd_restart ;;
        logs) cmd_logs "$@" ;;
        update) cmd_update "$@" ;;
        backup) cmd_backup ;;
        restore) cmd_restore "$@" ;;
        import-db) cmd_import_db "$@" ;;
        import-uploads) cmd_import_uploads "$@" ;;
        create-user) cmd_create_user "$@" ;;
        artisan) cmd_artisan "$@" ;;
        shell) cmd_shell ;;
        help | -h | --help) usage ;;
        *) usage >&2; exit 1 ;;
    esac
}

main "$@"
