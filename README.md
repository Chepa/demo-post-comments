## Post Comments

### 1. Настройка переменных окружения

```bash
cp src/.env.example src/.env
```
### 2. Запуск Docker-контейнеров

```bash
docker compose up -d
```

### 5. Установка зависимостей Laravel

```bash
# Установка PHP зависимостей
docker exec app composer install

# Применение миграций
docker exec app php artisan migrate

# Сгенерировать ключ приложения
docker exec app php artisan key:generate
```

## Доступ к приложению
- **Веб-интерфейс**: http://localhost

## Тестирование
docker exec app php artisan test

## Документация
http://localhost/documentation
