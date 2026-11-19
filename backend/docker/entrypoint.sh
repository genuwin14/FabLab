#!/bin/sh
# Start-up for fablab-app and fablab-scheduler. Runs as www-data.
#
# Before the website or the scheduler starts, it waits for the database,
# caches the configuration, and (website only) runs any new migrations.
# Any other command, such as `php artisan ...` through ./fablab.sh, runs
# straight away with none of this.
set -e
cd /var/www/html

prepare() {
    # The volumes are empty on a new server.
    mkdir -p storage/app/public storage/app/private storage/logs \
             storage/framework/cache/data storage/framework/sessions storage/framework/views

    if [ -z "${APP_KEY:-}" ]; then
        echo "fablab: APP_KEY is empty in docker/.env. Run ./fablab.sh install, which fills it in." >&2
        exit 1
    fi

    php docker/wait-for-db.php

    # Reads the settings once, here. This is why a change to docker/.env
    # needs ./fablab.sh restart.
    php artisan optimize --no-ansi
}

case "$1" in
    apache2-foreground)
        prepare
        if [ "${FABLAB_AUTO_MIGRATE:-true}" = "true" ]; then
            php artisan migrate --force --no-ansi
        fi
        ;;
    php)
        if [ "${2:-}" = "artisan" ] && [ "${3:-}" = "schedule:work" ]; then
            prepare
        fi
        ;;
esac

exec docker-php-entrypoint "$@"
