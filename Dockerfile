# PHP 7.4 to match production (seohost runs this app on lsphp74; Laravel 6 +
# composer.lock predate PHP 8).
FROM php:7.4-apache

ENV TZ=Europe/Warsaw
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update \
    && apt-get install -y --no-install-recommends curl git unzip libzip-dev cron \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql bcmath zip opcache && docker-php-ext-enable opcache

# Serve Laravel's public/ as the docroot.
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' \
        /etc/apache2/sites-available/000-default.conf /etc/apache2/apache2.conf \
    && a2enmod rewrite

RUN curl -sS https://getcomposer.org/installer | php -- --2 --install-dir=/usr/local/bin --filename=composer

COPY --chown=www-data:www-data . /var/www/html/
WORKDIR /var/www/html

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress \
    && chown -R www-data:www-data vendor bootstrap/cache storage

# Pristine storage skeleton — the runtime storage/ is a mounted volume, seeded
# from this copy on first start (see docker/entrypoint.sh).
RUN cp -a storage /var/storage-skel

COPY docker/entrypoint.sh /usr/local/bin/depot-entrypoint
COPY docker/cron.d-depot /etc/cron.d/depot
RUN chmod +x /usr/local/bin/depot-entrypoint && chmod 644 /etc/cron.d/depot

ENTRYPOINT ["depot-entrypoint"]
CMD ["apache2-foreground"]
