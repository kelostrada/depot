#!/bin/sh
# Seed the storage volume on first start, then run the given command
# (apache2-foreground for web, cron -f for the scheduler container).
set -e

if [ ! -d /var/www/html/storage/framework ]; then
    cp -a /var/storage-skel/. /var/www/html/storage/
    chown -R www-data:www-data /var/www/html/storage
fi

# Directories newer Laravel versions expect, missing from volumes seeded by
# older images.
for dir in app/private framework/cache/data framework/cache/locks; do
    if [ ! -d "/var/www/html/storage/$dir" ]; then
        mkdir -p "/var/www/html/storage/$dir"
        chown -R www-data:www-data "/var/www/html/storage/$dir"
    fi
done

exec "$@"
