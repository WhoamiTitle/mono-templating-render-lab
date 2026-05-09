FROM php:8.2-cli-alpine

RUN apk add --no-cache postgresql-dev \
 && docker-php-ext-install pdo pdo_pgsql

WORKDIR /app
COPY . .

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
 && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
 && rm composer-setup.php \
 && composer install --no-dev --no-interaction --optimize-autoloader

EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
