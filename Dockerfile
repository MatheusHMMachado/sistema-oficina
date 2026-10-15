# ============================================================
# STAGE 1 — Build de assets frontend (Node / Vite)
# ============================================================
FROM node:18-slim AS node-builder

WORKDIR /build

# Copiar manifestos de dependências primeiro (cache de camada)
COPY package.json package-lock.json* ./

RUN npm install --ignore-scripts

# Copiar código necessário para o build do Vite
COPY vite.config.js ./
COPY resources/ ./resources/

RUN npm run build


# ============================================================
# STAGE 2 — Dependências PHP (Composer)
# ============================================================
FROM composer:2 AS composer-builder

WORKDIR /build

# Copiar manifestos de dependências primeiro (cache de camada)
COPY composer.json composer.lock ./

# Instalar dependências de produção sem scripts (sem artisan ainda)
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --optimize-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

# Copiar código-fonte completo e executar scripts pós-install
COPY . .
RUN composer dump-autoload --optimize --no-dev


# ============================================================
# STAGE 3 — Imagem final de produção (PHP 8.2-FPM Alpine)
# ============================================================
FROM php:8.2-fpm-alpine AS production

LABEL maintainer="Sistema Oficina Mecânica"
LABEL description="Imagem de produção do Sistema de Gerenciamento de Oficina"

# Instalar dependências do sistema e extensões PHP necessárias
RUN apk add --no-cache \
        curl \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libzip-dev \
        icu-dev \
        oniguruma-dev \
        postgresql-client \
        libpq-dev \
        nginx \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pgsql \
        gd \
        bcmath \
        opcache \
        zip \
        intl \
        mbstring \
    && rm -rf /var/cache/apk/*

# Instalar extensão Redis via PECL
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Diretório de trabalho da aplicação
WORKDIR /var/www/html

# Copiar configurações customizadas
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-app.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/10-opcache.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Copiar dependências do Composer (vendor) do stage 2
COPY --from=composer-builder /build/vendor ./vendor

# Copiar assets buildados do stage 1
COPY --from=node-builder /build/public/build ./public/build

# Copiar código-fonte da aplicação
COPY . .

# Copiar e configurar entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Copiar healthcheck
COPY docker/healthcheck.sh /usr/local/bin/healthcheck.sh
RUN chmod +x /usr/local/bin/healthcheck.sh

# Criar diretórios necessários e ajustar permissões
RUN mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data \
        storage \
        bootstrap/cache \
    && chmod -R 775 \
        storage \
        bootstrap/cache

# Expor portas: PHP-FPM (9000) e Nginx (80)
EXPOSE 80 9000

# Health check
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD /usr/local/bin/healthcheck.sh

# Entrypoint customizado
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Comando padrão: iniciar PHP-FPM em foreground
CMD ["php-fpm", "-F"]
