#!/bin/sh
set -e

# A APP_KEY nunca vai dentro da imagem. Se a plataforma de deploy não definiu
# uma, geramos em memória para o contêiner subir em vez de quebrar no boot.
if [ -z "${APP_KEY}" ]; then
    echo "APP_KEY ausente - gerando uma chave temporaria para esta execucao."
    APP_KEY="base64:$(head -c 32 /dev/urandom | base64)"
    export APP_KEY
fi

# Com SQLite, o arquivo precisa existir antes da migração.
if [ "${DB_CONNECTION}" = "sqlite" ] && [ -n "${DB_DATABASE}" ]; then
    mkdir -p "$(dirname "${DB_DATABASE}")"
    touch "${DB_DATABASE}"
    chown www-data:www-data "${DB_DATABASE}"
fi

# Espera o banco aceitar conexão: em docker compose o MySQL leva alguns
# segundos a mais que a aplicação para ficar pronto.
if [ -n "${DB_HOST}" ]; then
    echo "Aguardando o banco em ${DB_HOST}:${DB_PORT:-3306}..."
    for i in $(seq 1 30); do
        if php -r "exit(@fsockopen(getenv('DB_HOST'), (int)(getenv('DB_PORT') ?: 3306)) ? 0 : 1);"; then
            echo "Banco disponivel."
            break
        fi
        sleep 2
    done
fi

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link 2>/dev/null || true

exec "$@"
