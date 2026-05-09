# RenderLab — Frontend

Vue 3 + Vite + TypeScript — песочница для сравнения движков шаблонов Handlebars, Pug и EJS (рендеринг на стороне клиента).

---

## Быстрый старт

```bash
npm install
npm run dev        # http://localhost:5173 — моки включены, бэкенд не нужен
```

В режиме моков все вызовы login/register обрабатываются в памяти. Можно вводить **любые** email и пароль — проверки учётных данных нет.

---

## Запуск с реальным бэкендом (Docker)

Требуется Docker с Compose v2. Из **корня репозитория**:

```bash
# 1. Запустить PostgreSQL + PHP-бэкенд
docker compose up -d

# 2. Выполнить миграции базы данных
docker compose exec backend php bin/migrate.php
```

Бэкенд доступен по адресу `http://localhost:8000`.

### Подключить фронтенд к бэкенду

Создать файл `frontend/.env.local` (добавлен в .gitignore):

```
VITE_API_URL=http://localhost:8000
```

Затем запустить dev-сервер:

```bash
npm run dev
```

Dev-сервер по-прежнему стартует с `__USE_MOCK__=true`. Чтобы использовать реальный API, нужен production-билд. Простейший способ проверить интеграцию с бэкендом:

```bash
# Собрать и запустить превью с реальным бэкендом
VITE_API_URL=http://localhost:8000 npm run build
npm run preview    # http://localhost:4173
```

### Создание пользователя

Готовых dev-пользователей нет. Зарегистрироваться можно через UI на странице `/register` или через curl:

```bash
curl -s -X POST http://localhost:8000/users \
  -H "Content-Type: application/json" \
  -d '{"email":"dev@example.com","password":"secret123"}' | jq
```

---

## Команды

| Команда | Описание |
|---------|----------|
| `npm run dev` | Dev-сервер на `localhost:5173` — моки включены |
| `npm run build` | Production-сборка (type-check + бандл, моки выключены) |
| `npm run preview` | Запустить production-сборку локально |
| `npm run type-check` | Только `vue-tsc` |
| `npm run lint` | oxlint + ESLint с `--fix` |
| `npm run format` | oxfmt |

---

## Настройка IDE

[VS Code](https://code.visualstudio.com/) + [Vue (Official)](https://marketplace.visualstudio.com/items?itemName=Vue.volar) (отключить Vetur, если установлен).
