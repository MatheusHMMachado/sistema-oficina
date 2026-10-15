#!/bin/sh
set -e

# Adiciona a porta dinâmica do Railway caso seja diferente de 80 e 8080
if [ -n "$PORT" ] && [ "$PORT" != "80" ] && [ "$PORT" != "8080" ]; then
    echo "Adicionando porta customizada $PORT ao Nginx..."
    sed -i "s/listen 8080 default_server;/listen 8080 default_server;\n    listen $PORT default_server;/g" /etc/nginx/http.d/default.conf
fi

# Valida as configurações do Nginx
nginx -t

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

