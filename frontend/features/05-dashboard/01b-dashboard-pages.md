# 05-01b Страницы Dashboard

## Цель

DashboardPage (обзор), MyTemplatesPage (CRUD), MyRunsPage (история запусков) + `useRenderRunsStore`.

## Use Cases

**Dashboard (обзор):**
- Авторизованный пользователь открывает `/dashboard` — видит количество шаблонов, количество запусков и последние 5 бенчмарков

**Мои шаблоны:**
- Пользователь видит карточки своих шаблонов с движком и датой создания
- Нажимает "Open in Sandbox" — шаблон загружается в слот A, редирект на `/sandbox`
- Нажимает "Clone" — появляется копия с суффиксом "(копия)"
- Нажимает "Delete" — появляется ConfirmDialog, после подтверждения карточка исчезает
- Нет шаблонов — empty state с CTA "Открыть Sandbox"

**Мои запуски:**
- Таблица бенчмарков с колонками: движок, итерации, avg, min, p95, размер, дата
- Нет запусков — empty state

## Критерии готовности

- [ ] DashboardPage: счётчики шаблонов + запусков, последние 5 запусков
- [ ] MyTemplatesPage: CRUD — create → sandbox, clone, delete + ConfirmDialog
- [ ] MyRunsPage: таблица с форматированием метрик (ms, KB)
- [ ] `useRenderRunsStore`: `fetchRuns`, `addRun`

## Зависимости

- `05-01a` — ConfirmDialog, TemplateCard, TemplateList
- `00-05` — API модули
- `00-06` — stores
- `01-01` — requiresAuth guard
