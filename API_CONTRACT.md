# Templating Render Lab API Contract

Документ описывает текущий HTTP API backend. Пользовательский гайд по приложению находится в [APPLICATION_GUIDE.md](./APPLICATION_GUIDE.md).

Backend base URL при локальном запуске:

```text
http://localhost:8000
```

Swagger UI:

```text
GET /docs
```

OpenAPI JSON:

```text
GET /openapi.json
```

## Общие правила

### Формат данных

Все JSON endpoints принимают и возвращают `application/json`.

Исключения:

- `GET /docs` возвращает HTML;
- `DELETE /sessions/current` возвращает `204 No Content`;
- ошибки возвращаются в JSON.

### Ошибка

Типовой формат ошибки:

```json
{
  "error": {
    "message": "template.not_found",
    "details": {
      "templateId": "..."
    }
  }
}
```

Возможные статусы ошибок, описанные в OpenAPI:

- `400 Bad Request`;
- `401 Unauthorized`;
- `403 Forbidden`;
- `404 Not Found`;
- `409 Conflict`;
- `422 Unprocessable Entity`;
- `500 Internal Server Error`.

### Авторизация

Основной механизм авторизации - HttpOnly session cookie.

Cookie:

```text
auth_token=<jwt>
```

Логин через `POST /sessions` возвращает JSON с session и устанавливает cookie через `Set-Cookie`.

Большинство endpoints требуют активную session. Публичные endpoints:

- `POST /users`;
- `POST /sessions`;
- `GET /templates/public`;
- `GET /state/{stateId}`;
- `GET /docs`;
- `GET /openapi.json`.

### Важные доменные значения

Template engine:

```text
handlebars | pug | ejs
```

Run status:

```text
in_progress | success | failure
```

## Auth API

### Register user

```http
POST /users
```

Request:

```json
{
  "email": "user@example.com",
  "password": "secret-password"
}
```

Response `201`:

```json
{
  "userId": "uuid",
  "email": "user@example.com"
}
```

Особенности:

- email должен быть уникальным;
- пароль хэшируется на backend;
- после регистрации нужно отдельно выполнить login.

### Login

```http
POST /sessions
```

Request:

```json
{
  "email": "user@example.com",
  "password": "secret-password"
}
```

Response `200`:

```json
{
  "sessionId": "uuid",
  "userId": "uuid",
  "expiresAt": "2026-05-13T12:00:00+00:00"
}
```

Side effect:

```text
Set-Cookie: auth_token=...; HttpOnly; SameSite=Lax
```

На HTTPS-стенде cookie должен выдаваться с `Secure`, если `COOKIE_SECURE=true`.

### Current session

```http
GET /sessions/current
```

Требует session cookie.

Response `200`:

```json
{
  "userId": "uuid",
  "email": "user@example.com"
}
```

Используется frontend при старте приложения для сверки local auth-state с реальной cookie-session.

### Logout

```http
DELETE /sessions/current
```

Требует session cookie.

Response:

```text
204 No Content
```

Side effect:

```text
Set-Cookie: auth_token=; expires=...
```

## Templates API

### Template object

```json
{
  "templateId": "uuid",
  "ownerId": "uuid",
  "name": "Handlebars Hello",
  "engineType": "handlebars",
  "templateBody": "<h1>Hello, {{name}}</h1>",
  "isPublic": false,
  "isActive": true,
  "createdAt": "2026-05-13T12:00:00+00:00",
  "updatedAt": "2026-05-13T12:10:00+00:00"
}
```

### Create template

```http
POST /templates
```

Требует session cookie.

Request:

```json
{
  "name": "My template",
  "engineType": "pug",
  "templateBody": "h1 Hello, #{name}",
  "isPublic": false
}
```

Required:

- `name`;
- `engineType`;
- `templateBody`.

Optional:

- `isPublic`, default `false`.

Response `201`: `Template`.

### List my templates

```http
GET /templates
```

Требует session cookie.

Query filters:

- `engineType`;
- `name`;
- `isActive`;
- `isPublic`.

Response `200`:

```json
{
  "items": [
    {
      "templateId": "uuid",
      "ownerId": "uuid",
      "name": "My template",
      "engineType": "pug",
      "templateBody": "h1 Hello",
      "isPublic": false,
      "isActive": true,
      "createdAt": "...",
      "updatedAt": "..."
    }
  ]
}
```

Frontend для списка личных шаблонов использует:

```http
GET /templates?isActive=true
```

### List public templates

```http
GET /templates/public
```

Публичный endpoint, session не требуется.

Query filters:

- `engineType`;
- `name`.

Response `200`: `TemplateList`.

Важно: публичные шаблоны можно открыть или клонировать из frontend. Для клонирования frontend использует уже загруженный public template snapshot, потому что `GET /templates/{templateId}` является owner-scoped.

### Get template

```http
GET /templates/{templateId}
```

Требует session cookie.

Response `200`: `Template`.

Важно: endpoint owner-scoped. Если шаблон существует, но принадлежит другому пользователю, backend вернет not found.

### Update template body

```http
PUT /templates/{templateId}/body
```

Требует session cookie и ownership.

Request:

```json
{
  "templateBody": "<h1>Hello, {{name}}</h1>"
}
```

Response `200`: `Template`.

### Update template publicity

```http
PUT /templates/{templateId}/publicity
```

Требует session cookie и ownership.

Request:

```json
{
  "isPublic": true
}
```

Response `200`:

```json
{
  "templateId": "uuid",
  "isPublic": true,
  "updatedAt": "2026-05-13T12:10:00+00:00"
}
```

### Deactivate template

```http
POST /templates/{templateId}/deactivation
```

Требует session cookie и ownership.

Response `200`: `Template`.

Backend использует deactivation вместо hard delete. После деактивации шаблон не должен отображаться в активном списке.

### Template stats

```http
GET /templates/{templateId}/stats
```

Требует session cookie и ownership.

Response `200`:

```json
{
  "templateId": "uuid",
  "totalRuns": 3,
  "successRuns": 2,
  "failedRuns": 1,
  "avgDurationMs": 12.5,
  "minDurationMs": 10,
  "maxDurationMs": 20
}
```

## Shared State API

Shared state хранит состояние sandbox для share-ссылок.

### State object

```json
{
  "slotA": {
    "engineId": "handlebars",
    "code": "<h1>Hello, {{name}}</h1>"
  },
  "slotB": {
    "engineId": "pug",
    "code": "h1 Hello, #{name}"
  },
  "json": "{\n  \"name\": \"World\"\n}"
}
```

### Save state

```http
POST /state
```

Требует session cookie.

Request: `State object`.

Response `201`:

```json
{
  "id": "uuid"
}
```

Frontend строит share URL:

```text
/s/{id}
```

### Get state

```http
GET /state/{stateId}
```

Публичный endpoint, session не требуется.

Response `200`: `State object`.

Если state не найден, backend вернет `404`.

## Benchmark Runs API

Benchmark run - основной persisted flow для результатов benchmark в текущем frontend.

Он может быть связан с template через `templateId`, либо хранить snapshot напрямую через:

- `engineType`;
- `templateBody`;
- `context`.

Frontend сейчас сохраняет benchmark без обязательного template, чтобы не создавать скрытые временные templates.

### BenchmarkRun object

```json
{
  "benchmarkRunId": "uuid",
  "ownerId": "uuid",
  "templateId": null,
  "engineType": "handlebars",
  "templateBodySnapshot": "<h1>Hello, {{name}}</h1>",
  "context": {
    "name": "World"
  },
  "iterationsN": 100,
  "status": "success",
  "samplesMs": [0.1, 0.2, 0.3],
  "avgMs": 0.2,
  "minMs": 0.1,
  "maxMs": 0.3,
  "p95Ms": 0.3,
  "outputBytes": 87,
  "errorCode": null,
  "errorMessage": null,
  "startedAt": "2026-05-13T12:00:00+00:00",
  "finishedAt": "2026-05-13T12:00:01+00:00"
}
```

### Start benchmark run

```http
POST /benchmark-runs
```

Требует session cookie.

Request with snapshot:

```json
{
  "engineType": "handlebars",
  "templateBody": "<h1>Hello, {{name}}</h1>",
  "context": {
    "name": "World"
  },
  "iterationsN": 100
}
```

Request with template:

```json
{
  "templateId": "uuid",
  "context": {
    "name": "World"
  },
  "iterationsN": 100
}
```

Rules:

- `context` is required;
- `iterationsN` is required;
- either `templateId` or snapshot fields must be enough for backend to resolve engine/body;
- frontend clamps iterations to `1..10000`.

Response `201`:

```json
{
  "benchmarkRunId": "uuid",
  "templateId": null,
  "ownerId": "uuid",
  "status": "in_progress",
  "iterationsN": 100,
  "startedAt": "2026-05-13T12:00:00+00:00"
}
```

### Complete benchmark success

```http
POST /benchmark-runs/{benchmarkRunId}/success
```

Требует session cookie и ownership.

Request:

```json
{
  "samplesMs": [0.1, 0.2, 0.3],
  "avgMs": 0.2,
  "minMs": 0.1,
  "maxMs": 0.3,
  "p95Ms": 0.3,
  "outputBytes": 87
}
```

Response `200`:

```json
{
  "benchmarkRunId": "uuid",
  "status": "success",
  "finishedAt": "2026-05-13T12:00:01+00:00"
}
```

Важно: summary считается на frontend из `samplesMs` и передается в backend. Backend валидирует значения и сохраняет их.

### Complete benchmark failure

```http
POST /benchmark-runs/{benchmarkRunId}/failure
```

Требует session cookie и ownership.

Request:

```json
{
  "errorCode": "render.error",
  "errorMessage": "Invalid template"
}
```

Response `200`:

```json
{
  "benchmarkRunId": "uuid",
  "status": "failure",
  "finishedAt": "2026-05-13T12:00:01+00:00"
}
```

### List benchmark runs

```http
GET /benchmark-runs
```

Требует session cookie.

Query filters:

- `templateId`;
- `engineType`;
- `status`;
- `iterationsN`.

Response `200`:

```json
{
  "items": [
    {
      "benchmarkRunId": "uuid",
      "ownerId": "uuid",
      "templateId": null,
      "engineType": "handlebars",
      "templateBodySnapshot": "...",
      "context": {
        "name": "World"
      },
      "iterationsN": 100,
      "status": "success",
      "samplesMs": [0.1, 0.2],
      "avgMs": 0.15,
      "minMs": 0.1,
      "maxMs": 0.2,
      "p95Ms": 0.2,
      "outputBytes": 87,
      "errorCode": null,
      "errorMessage": null,
      "startedAt": "...",
      "finishedAt": "..."
    }
  ]
}
```

### Get benchmark run

```http
GET /benchmark-runs/{benchmarkRunId}
```

Требует session cookie и ownership.

Response `200`: `BenchmarkRun`.

## Render Runs API

Render runs - backend lifecycle для единичного render-run по сохраненному template. Текущий основной frontend benchmark flow использует `/benchmark-runs`, но render-run endpoints остаются частью backend API.

### RenderRun object

```json
{
  "runId": "uuid",
  "ownerId": "uuid",
  "templateId": "uuid",
  "engineType": "handlebars",
  "context": {
    "name": "World"
  },
  "status": "success",
  "durationMs": 10,
  "outputText": "<h1>Hello, World</h1>",
  "errorCode": null,
  "errorMessage": null,
  "startedAt": "2026-05-13T12:00:00+00:00",
  "finishedAt": "2026-05-13T12:00:01+00:00"
}
```

### Start render run

```http
POST /render-runs
```

Требует session cookie.

Request:

```json
{
  "templateId": "uuid",
  "context": {
    "name": "World"
  }
}
```

Response `201`: `RenderRun`.

### Complete render success

```http
POST /render-runs/{runId}/success
```

Требует session cookie и ownership.

Request:

```json
{
  "durationMs": 10,
  "outputText": "<h1>Hello, World</h1>"
}
```

Response `200`: `RenderRun`.

### Complete render failure

```http
POST /render-runs/{runId}/failure
```

Требует session cookie и ownership.

Request:

```json
{
  "durationMs": 10,
  "errorCode": "render.error",
  "errorMessage": "Invalid template"
}
```

Response `200`: `RenderRun`.

### List render runs

```http
GET /render-runs
```

Требует session cookie.

Query filters:

- `templateId`;
- `engineType`;
- `status`.

Response `200`: `RenderRunList`.

### Get render run

```http
GET /render-runs/{runId}
```

Требует session cookie и ownership.

Response `200`: `RenderRun`.

### Recent render failures

```http
GET /render-runs/failures/recent
```

Требует session cookie.

Query:

- `limit`, default `10`.

Response `200`: `RenderRunList`.

## OpenAPI and Swagger

Документация генерируется из PHP attributes:

```php
#[Route('POST', '/benchmark-runs')]
#[OpenApi('Start benchmark run', ['Benchmark runs'], requestBody: 'StartBenchmarkRunRequest')]
```

Пути:

- `GET /openapi.json` - машинный OpenAPI 3.1 документ;
- `GET /docs` - Swagger UI.

Swagger UI использует CDN `unpkg.com` для assets. Для offline deployment нужно заменить это на локальные assets.

## Docker deployment contract

Сервисы:

- `db` - Postgres 16;
- `backend` - PHP CLI server на `8000`;
- `frontend` - production preview на `4173`.

Backend при старте:

1. ждет Postgres;
2. применяет SQL migrations;
3. запускает HTTP server.

Минимальный запуск:

```bash
docker compose up -d --build
```

Если порт Postgres занят:

```bash
POSTGRES_HOST_PORT=55432 docker compose up -d --build
```
