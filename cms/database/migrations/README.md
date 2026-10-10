Schema is owned exclusively by `api/database/migrations`.

Run `php artisan migrate --force` from the API application before starting CMS.
CMS uses that same database and does not run schema migrations or seed production data.
Do not run `migrate:fresh`, `db:wipe` or `migrate:rollback` from CMS against the shared database.
