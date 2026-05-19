FROM php:8.2-cli-alpine

# composer binary from the official image — без сетевой загрузки установщика
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN apk add --no-cache postgresql-dev \
 && docker-php-ext-install pdo pdo_pgsql

WORKDIR /app
COPY . .

# composer сам читает HTTP_PROXY/HTTPS_PROXY/NO_PROXY из env
RUN composer install --no-dev --no-interaction --optimize-autoloader \
 && chmod +x docker-entrypoint.sh

EXPOSE 8000
ENTRYPOINT ["./docker-entrypoint.sh"]
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public", "public/index.php"]
