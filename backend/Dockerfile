FROM php:8.2-cli

WORKDIR /var/www

RUN apt-get update && \
    apt-get install -y libzip-dev zip unzip libpng-dev libonig-dev libxml2-dev mariadb-client && \
    docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]