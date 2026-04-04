# Infrastructure / Repository

Здесь делаем адаптеры к БД для доменных репозиториев.

Отдельно: слой `presentation` тоже может жить в `infrastructure` (например, `infrastructure/presentation/http`).

## Что реализовать

- Реализации интерфейсов из `domain/*/repository/*Interface.php`:
  - `TemplateRepositoryInterface`
  - `RenderRunRepositoryInterface`
  - `BenchmarkRunRepositoryInterface`
  - `UserRepositoryInterface`
  - `AuthSessionRepositoryInterface`
  - `PasswordResetTokenRepositoryInterface`
- Маппинг тоже тут делаем

## Важно держать границу

- Проверки инвариантов, статусов и переходов состояний остаются в `domain`.
- В репозиториях не пишем если статус такой, то....
- Репозиторий отвечает за сохранение и извлечение, не за бизнес-решения.

## Про presentation в infra

- `presentation` принимает HTTP-запрос, валидирует transport-формат, вызывает use case.
- В `presentation` делаем маппинг `request -> command` и `result -> response`.
- Здесь же ставим/читаем cookies, HTTP-коды, headers.
- Бизнес-правила в `presentation` не размещаем, они только в `domain`. В случае чего дорабатываем `domain` если что-то нехватает.

## Когда считать готовым

- Любой use case может работать только через интерфейсы репозиториев.
- Замена способа хранения (например, с PostgreSQL на другой тип можно все это потестировать) не ломает `domain` и `application`.
