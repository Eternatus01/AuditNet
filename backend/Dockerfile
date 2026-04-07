FROM php:8.2-cli

WORKDIR /var/www

# Install system dependencies
RUN apt-get update && \
    apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    git \
    curl && \
    docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip && \
    apt-get clean && rm -rf /var/lib/apt/lists/* && \
    echo "max_execution_time = 300" > /usr/local/etc/php/conf.d/custom.ini && \
    echo "memory_limit = 1024M" >> /usr/local/etc/php/conf.d/custom.ini && \
    echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/custom.ini && \
    echo "upload_max_filesize = 50M" >> /usr/local/etc/php/conf.d/custom.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Install dependencies and setup permissions
RUN composer install --optimize-autoloader --no-dev --no-interaction --ignore-platform-reqs && \
    mkdir -p storage/framework/{sessions,views,cache} && \
    mkdir -p storage/logs && \
    mkdir -p bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

# Start command
CMD php artisan migrate --force && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
