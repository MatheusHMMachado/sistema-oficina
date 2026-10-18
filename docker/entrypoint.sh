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

    # Se RAILWAY_PUBLIC_DOMAIN estiver definido e não for produção, ajusta o APP_URL
    if [ -n "$RAILWAY_PUBLIC_DOMAIN" ] && [ "$APP_ENV" != "production" ]; then
        echo "Railway Public Domain detectado: https://$RAILWAY_PUBLIC_DOMAIN"
        export APP_URL="https://$RAILWAY_PUBLIC_DOMAIN"
    fi

    echo "Realizando cache das configurações..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true

    echo "Rodando as migrations do banco de dados..."
    php artisan migrate --force || echo "Aviso: Migrations falharam ou o banco ainda não está pronto."

    # Identifica se é ambiente de Staging, Teste ou PR Efêmero do Railway
    IS_PR_OR_STAGING=false
    if [ "$RUN_SEEDS" = "true" ] || [ "$RUN_SEEDS" = "1" ]; then
        IS_PR_OR_STAGING=true
    elif [ "$APP_ENV" = "staging" ] || [ "$APP_ENV" = "testing" ]; then
        IS_PR_OR_STAGING=true
    elif [ -n "$RAILWAY_ENVIRONMENT_NAME" ] && [ "$RAILWAY_ENVIRONMENT_NAME" != "production" ]; then
        IS_PR_OR_STAGING=true
    fi

    if [ "$IS_PR_OR_STAGING" = "true" ]; then
        echo "Ambiente Staging/PR Efêmero detectado (RAILWAY_ENV=${RAILWAY_ENVIRONMENT_NAME:-N/A}, APP_ENV=${APP_ENV:-N/A})."
        echo "Executando DatabaseSeeder completo (admin, mecânico, clientes, veículos e catálogo)..."
        php artisan db:seed --force || echo "Aviso: db:seed falhou ou já executado parcialmente."
    else
        echo "Ambiente de Produção detectado. Garantindo apenas usuário administrador padrão..."
        php artisan db:seed --class=UserSeeder --force || true
    fi
fi

echo "Iniciando processo principal: $@"
exec "$@"

