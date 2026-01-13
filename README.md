## Post Comments

### 1. Запуск Docker-контейнеров

```bash
docker compose up -d
```

### 2. Настройка переменных окружения

```bash
cp src/.env.example src/.env
```

### 5. Установка зависимостей Laravel

```bash
# Установка PHP зависимостей
docker exec app composer install

# Применение миграций
docker exec app php artisan migrate
```

## Доступ к приложению
- **Веб-интерфейс**: http://localhost

## Тестирование
docker exec app php artisan test

## Документация
http://localhost/documentation
