# AuditNet

Инструмент для аудита производительности и безопасности веб-сайтов.

## Требования

- Docker
- Docker Compose

## Запуск проекта

1. Клонируйте репозиторий:
```bash
git clone <repository-url>
cd AuditNet
```

2. Запустите все сервисы через Docker Compose:
```bash
docker-compose up -d --build
```

3. Выполните миграции базы данных:
```bash
docker-compose exec backend php artisan migrate
```

4. При необходимости сгенерируйте ключ приложения:
```bash
docker-compose exec backend php artisan key:generate
```

## Доступ к сервисам

После запуска проект будет доступен по следующим адресам:

- **Frontend**: http://localhost:5173
- **Backend API**: http://localhost:8000/api
- **phpMyAdmin**: http://localhost:8080
- **Lighthouse**: http://localhost:3000

## Остановка проекта

```bash
docker-compose down
```

Для полной очистки (включая volumes):
```bash
docker-compose down -v
```

## Полезные команды

Просмотр логов:
```bash
docker-compose logs -f [service_name]
```

Выполнение команд в контейнере backend:
```bash
docker-compose exec backend php artisan [command]
```

Перезапуск конкретного сервиса:
```bash
docker-compose restart [service_name]
```
