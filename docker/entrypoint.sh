#!/bin/sh
set -e

# Se o comando passado for o php-fpm (o padrão do Dockerfile), rodar o nginx também
if [ "$1" = 'php-fpm' ]; then
    echo "Iniciando o Nginx em background..."
    nginx

    echo "Realizando cache das configurações..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true

    echo "Rodando as migrations do banco de dados..."
    php artisan migrate --force || true
fi

echo "Iniciando processo principal: $@"
exec "$@"
