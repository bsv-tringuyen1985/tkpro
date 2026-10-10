#!/bin/sh
set -eu
cd /var/www/html
for i in $(seq 1 120); do
  if php /usr/local/bin/db-probe.php schema 2>/dev/null; then break; fi
  if [ "$i" -eq 120 ]; then echo "[cms] API schema/seed unavailable" >&2; exit 1; fi
  sleep 2
done
if [ ! -f vendor/autoload.php ]; then composer install --no-dev --optimize-autoloader --no-interaction; fi
php artisan config:clear
php /usr/local/bin/merge-media.php
php artisan storage:link --force
php artisan route:clear
php artisan view:clear
exec php artisan serve --host=0.0.0.0 --port=8001
