# PHP 8.3 on Debian 13 (trixie). PHP 7.4 is end of life and gets no security
# fixes. Laravel 6.20 + Backpack 4.0 and composer.lock predate PHP 8.1; they run
# on 8.3 with deprecation notices kept out of Laravel's error-to-exception
# conversion (app/Bootstrap/HandleExceptions.php, docker/php.ini).
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

# composer.json/composer.lock still cap PHP below 8 (root "php": "^7.2",
# ocramius/package-versions 1.11 "^7.4.7"). `php+` ignores only those upper
# bounds; the lower bounds are still enforced.
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress \
        --ignore-platform-req=php+ \
    && chown -R www-data:www-data vendor bootstrap/cache storage

# Pristine storage skeleton — the runtime storage/ is a mounted volume, seeded
# from this copy on first start (see docker/entrypoint.sh).
RUN cp -a storage /var/storage-skel

COPY docker/entrypoint.sh /usr/local/bin/depot-entrypoint
COPY docker/cron.d-depot /etc/cron.d/depot
RUN chmod +x /usr/local/bin/depot-entrypoint && chmod 644 /etc/cron.d/depot

ENTRYPOINT ["depot-entrypoint"]
CMD ["apache2-foreground"]
