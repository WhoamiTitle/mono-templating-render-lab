# 05-01 Компоненты шаблонов и страницы Dashboard

## Цель

ConfirmDialog (переиспользуемый), TemplateCard, TemplateList, DashboardPage (обзор), MyTemplatesPage (CRUD), MyRunsPage (история запусков).

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

- [ ] ConfirmDialog: переиспользуемый, принимает title/message, emits confirm/cancel
- [ ] TemplateCard: показывает имя, движок (badge), дату, кнопки действий
- [ ] TemplateList: skeleton loading, empty state со слотом для CTA
- [ ] DashboardPage: счётчики + последние 5 запусков
- [ ] MyTemplatesPage: CRUD работает (create → sandbox, clone, delete + confirm)
- [ ] MyRunsPage: таблица с форматированием метрик (ms, KB)
- [ ] Добавить `useRenderRunsStore` с fetchRuns и addRun

## Зависимости

- `00-05` — API модули
- `00-06` — stores
- `01-01` — requiresAuth guard
