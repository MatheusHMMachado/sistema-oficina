#!/bin/sh
set -e

# Ajusta a porta do Nginx dinamicamente caso a plataforma forneça a variável $PORT (Railway, Render, Fly.io)
if [ -n "$PORT" ]; then
    echo "Ajustando porta do Nginx para $PORT..."
    sed -i "s/listen 80;/listen $PORT;/g" /etc/nginx/http.d/default.conf
fi

# Se o comando passado for o php-fpm (o padrão do Dockerfile), rodar o nginx também
if [ "$1" = 'php-fpm' ]; then
    echo "Iniciando o Nginx em background..."
    nginx

    echo "Criando link simbólico do storage..."
    php artisan storage:link || true

    echo "Realizando cache das configurações..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true

    echo "Rodando as migrations do banco de dados..."
    php artisan migrate --force || echo "Aviso: Migrations falharam ou o banco ainda não está pronto."

    echo "Garantindo usuário administrador padrão..."
    php artisan db:seed --class=UserSeeder --force || true
fi

echo "Iniciando processo principal: $@"
exec "$@"

