# PHP 8.3 on Debian 13 (trixie): Laravel 13 + Backpack 7 (free MIT core).
FROM php:8.3-apache-trixie

ENV TZ=Europe/Warsaw
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update \
    && apt-get install -y --no-install-recommends curl git unzip libzip-dev cron \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql bcmath zip opcache && docker-php-ext-enable opcache

# Production error settings (log, don't display; no deprecations; no X-Powered-By).
COPY docker/php.ini $PHP_INI_DIR/conf.d/zz-depot.ini

# Serve Laravel's public/ as the docroot.
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' \
        /etc/apache2/sites-available/000-default.conf /etc/apache2/apache2.conf \
    && a2enmod rewrite

RUN curl -sS https://getcomposer.org/installer | php -- --2 --install-dir=/usr/local/bin --filename=composer

COPY --chown=www-data:www-data . /var/www/html/
WORKDIR /var/www/html

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Backpack's CSS/JS (Basset) are downloaded from their CDNs here, at build time,
# into public/basset (read-only for the web server); the running app serves
# them locally and never fetches anything (config/backpack/basset.php). Fail the
# build if any asset could not be cached.
RUN php artisan basset:cache > /tmp/basset.log 2>&1 || { cat /tmp/basset.log; exit 1; }; \
    cat /tmp/basset.log; \
    if grep -q "Failed to cache" /tmp/basset.log; then exit 1; fi; \
    chmod -R a+rX public/basset && rm -rf storage/basset /tmp/basset.log

RUN chown -R www-data:www-data vendor bootstrap/cache storage

# Pristine storage skeleton — the runtime storage/ is a mounted volume, seeded
# from this copy on first start (see docker/entrypoint.sh).
RUN cp -a storage /var/storage-skel

COPY docker/entrypoint.sh /usr/local/bin/depot-entrypoint
COPY docker/cron.d-depot /etc/cron.d/depot
RUN chmod +x /usr/local/bin/depot-entrypoint && chmod 644 /etc/cron.d/depot

ENTRYPOINT ["depot-entrypoint"]
CMD ["apache2-foreground"]
