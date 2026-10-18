#!/bin/sh
set -e

cd /var/www/html

# O setup só roda quando o container sobe o servidor. Comandos avulsos
# (ex.: `docker compose run --rm app php artisan tinker`) passam direto.
if [ "$1" = "php" ] && [ "$2" = "artisan" ] && [ "$3" = "serve" ]; then

    if [ -z "${APP_KEY:-}" ]; then
        echo "ERRO: APP_KEY não definida (defina no .env)." >&2
        exit 1
    fi

    # Cria o banco se não existir, roda migrations (com retry, controlado por
    # DB_SETUP_MAX_ATTEMPTS / DB_SETUP_RETRY_DELAY) e sincroniza o super admin.
    if [ "${RUN_SETUP:-true}" = "true" ]; then
        php artisan iris:setup
    fi

    if [ "${APP_ENV:-production}" = "production" ]; then
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
    else
        php artisan config:clear
    fi
fi

exec "$@"
