#!/bin/bash

echo "🚀 Deploying AuditNet to VPS..."

# Копируем правильные .env файлы
echo "📝 Setting up environment files..."
cp backend/.env.vps backend/.env
if [ -f frontend/.env.vps ]; then
    cp frontend/.env.vps frontend/.env.production
fi

# Генерируем APP_KEY если его нет
echo "🔑 Generating application key..."
cd backend
if grep -q "APP_KEY=$" .env; then
    php artisan key:generate --force
fi
cd ..

# Останавливаем старые контейнеры
echo "🛑 Stopping old containers..."
docker compose down

# Пересобираем и запускаем
echo "🔨 Building and starting containers..."
docker compose up -d --build

# Ждём пока база данных запустится
echo "⏳ Waiting for database..."
sleep 10

# Запускаем миграции
echo "📦 Running migrations..."
docker compose exec -T backend php artisan migrate --force

# Устанавливаем PHP-зависимости (volume mount перезаписывает vendor из образа)
echo "📦 Installing Composer dependencies..."
docker compose exec -T backend composer install --no-dev --optimize-autoloader --no-interaction

# Очищаем кеш
echo "🧹 Clearing cache..."
docker compose exec -T backend php artisan config:clear
docker compose exec -T backend php artisan cache:clear
docker compose exec -T backend php artisan route:clear
docker compose exec -T backend php artisan view:clear

# Устанавливаем права на storage
echo "🔒 Setting permissions..."
docker compose exec -T backend chmod -R 775 storage bootstrap/cache
docker compose exec -T backend chown -R www-data:www-data storage bootstrap/cache

echo "✅ Deployment complete!"
echo ""
echo "🌐 Your services are available at:"
echo "   Site:       https://аудитнет.рф"
echo "   IP check:   http://138.16.174.40"
echo ""
echo "📊 Check logs with: docker compose logs -f"
