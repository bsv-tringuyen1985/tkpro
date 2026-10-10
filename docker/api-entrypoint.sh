#!/bin/sh
set -eu
cd /var/www/html
for i in $(seq 1 60); do
  if php /usr/local/bin/db-probe.php connect 2>/dev/null; then break; fi
  if [ "$i" -eq 60 ]; then echo "[api] database unavailable" >&2; exit 1; fi
  sleep 2
done
if [ ! -f vendor/autoload.php ]; then composer install --no-dev --optimize-autoloader --no-interaction; fi
php artisan config:clear
php artisan storage:link --force
php artisan migrate --force
USER_COUNT=$(php /usr/local/bin/db-probe.php users)
if [ "$USER_COUNT" = "0" ]; then php artisan db:seed --force; fi
php artisan route:clear
exec php artisan serve --host=0.0.0.0 --port=8000
