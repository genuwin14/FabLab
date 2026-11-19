# syntax=docker/dockerfile:1
#
# The FabLab image. fablab-app and fablab-scheduler both run it.
#
# Build it from backend/, the folder with artisan in it:
#     docker build -f docker/Dockerfile -t fablab-app .
# Normally ./fablab.sh install and ./fablab.sh update build it for you.

ARG PHP_VERSION=8.2
ARG NODE_VERSION=24

# -----------------------------------------------------------------------------
# Stage 1: the front-end bundle, public/build/. It only carries the Poppins
# font rule, since Bootstrap and the rest come from CDNs. The live site has
# it, so the image does too.
# -----------------------------------------------------------------------------
FROM node:${NODE_VERSION}-bookworm-slim AS assets

WORKDIR /build
COPY package.json package-lock.json ./
# The lockfile pairs laravel-vite-plugin 2.1 with vite 6.4, although the
# plugin asks for vite 7, so a plain `npm ci` refuses it. --legacy-peer-deps
# installs exactly the locked versions, the ones the developer's builds use.
RUN npm ci --no-audit --no-fund --legacy-peer-deps
COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# -----------------------------------------------------------------------------
# Stage 2: Apache + PHP with the application in /var/www/html.
# -----------------------------------------------------------------------------
FROM php:${PHP_VERSION}-apache-bookworm

# The extensions composer.lock asks for that the base image lacks: pdo_mysql
# (database), gd (PhpWord images, barcodes), zip (PhpWord .docx files), plus
# bcmath, which the README lists, and opcache for speed.
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        libfreetype6-dev libjpeg62-turbo-dev libpng-dev libwebp-dev libzip-dev unzip; \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp; \
    docker-php-ext-install -j"$(nproc)" bcmath gd opcache pdo_mysql zip; \
    rm -rf /var/lib/apt/lists/*

# Production php.ini, then FabLab's limits on top.
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php/fablab.ini "$PHP_INI_DIR/conf.d/zz-fablab.ini"

# Apache serves public/ only, honours Laravel's public/.htaccess, and keeps
# its version number to itself.
COPY docker/apache/fablab.conf /etc/apache2/sites-available/000-default.conf
RUN set -eux; \
    a2enmod rewrite headers; \
    echo 'ServerName localhost' > /etc/apache2/conf-available/servername.conf; \
    a2enconf servername; \
    sed -ri 's/^ServerTokens .*/ServerTokens Prod/; s/^ServerSignature .*/ServerSignature Off/' \
        /etc/apache2/conf-available/security.conf

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

# Dependencies before the code, so a code-only change reuses this layer.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist \
        --no-scripts --no-autoloader

COPY . .
COPY --from=assets /build/public/build ./public/build

# dump-autoload also runs `artisan package:discover` (composer.json scripts).
# The uploads are served through the usual public/storage link, which can't
# come from the build folder: there it points at a path on the developer's PC.
RUN set -eux; \
    composer dump-autoload --no-dev --optimize --no-interaction; \
    ln -sfn ../storage/app/public public/storage; \
    mkdir -p storage/app/public storage/app/private storage/logs \
             storage/framework/cache/data storage/framework/sessions storage/framework/views \
             bootstrap/cache; \
    chown -R www-data:www-data storage bootstrap/cache; \
    sed 's/\r$//' docker/entrypoint.sh > /usr/local/bin/fablab-entrypoint; \
    chmod 0755 /usr/local/bin/fablab-entrypoint

# Everything runs as www-data, artisan commands included, so a file one of
# them creates (today's log, say) is never left owned by root and unwritable
# for the website. Docker lets a non-root process bind port 80 inside the
# container; compose.yaml also sets the sysctl that allows it.
USER www-data

ENTRYPOINT ["fablab-entrypoint"]
CMD ["apache2-foreground"]
