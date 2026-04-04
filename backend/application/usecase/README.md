# Application / Use Case

Здесь слой сценариев: принимает команду, вызывает домен, сохраняет результат через репозитории.
Обычно вызывается из `infrastructure/presentation` (HTTP-контроллеры/роуты).

## Что реализовать

- Командные use case:
  - `RegisterTemplate`
  - `UpdateTemplateBody`
  - `DeactivateTemplate`
  - `StartRenderRun`
  - `CompleteRenderRunSuccess`
  - `CompleteRenderRunFailure`
  - `StartBenchmarkRun`
  - `CompleteBenchmarkRunSuccess`
  - `CompleteBenchmarkRunFailure`
  - `RegisterUser`
  - `LoginUser`
  - `LogoutUser`
- Query use case:
  - `GetTemplate`, `ListTemplates`
  - `GetRenderRun`, `ListRenderRuns`
  - `GetTemplateStats`, `GetRecentFailures`
  - при необходимости `GetBenchmarkRun`, `ListBenchmarkRuns`

## Что делает use case внутри

- Валидирует вход на уровне приложения (формат, обязательные поля).
- Загружает агрегаты из репозиториев.
- Вызывает доменные методы (вся предметная логика там).
- Фиксирует изменения через.
- Возвращает DTO/response model для API.

## Что не делаем в этом слое

- Не пишем SQL.
- Не дублируем доменные инварианты.
- Не смешиваем transport-детали HTTP (cookies, headers) с бизнес-сценарием.

## Когда считать готовым

- Use case не зависит от конкретной БД/фреймворка.
- Через API-слой только прокидываем вход/выход и не тащит на себя бизнес-логику.
