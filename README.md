# mono-templating-render-lab

## K1 Backend Domain (фиксируем текущее)

## K2 Frontend Stack (фиксируем текущее)

- Основа frontend-реализации: `Vue` + `Composition API`.
- Зона ответственности K2: веб-интерфейс песочницы шаблонизаторов и замер времени рендера.

## Распределение задач (Backend)

### Василий (домен)
1. Описать и реализовать aggregate root: `Template`, `RenderRun`.
2. Зафиксировать инварианты домена (`duration_ms >= 0`, правила `status`).
3. Описать доменные команды и ошибки.

### Никита (API + infra)
1. Реализовать HTTP API для команд и запросов.
2. Реализовать инфраструктурный слой доступа к БД.


### Александр (use case / application layer)
1. Реализовать use case: `RegisterTemplate`, `UpdateTemplateBody`, `DeactivateTemplate`.
2. Реализовать use case: `StartRenderRun`, `CompleteRenderRunSuccess`, `CompleteRenderRunFailure`.
3. Реализовать query use case: `get_render_run`, `list_render_runs`, `get_template`, `get_template_stats`, `get_recent_failures`.
4. Связать use case с портами репозиториев.

### Aggregate Root 1: `Template`
Назначение: описание шаблона и движка, для которого он используется.

Поля:
- `template_id` - идентификатор шаблона.
- `engine_type` - движок шаблонизации.
- `created_at` - когда шаблон создан.

### Aggregate Root 2: `RenderRun`
Назначение: один запуск рендера с полным снимком входа и результата.

Поля:
- `run_id` - идентификатор запуска.
- `template_id` - по какому шаблону запущен рендер.
- `engine_type` - дублируем связь с движком для аудита запуска.
- `template_body_snapshot` - снимок тела шаблона на момент запуска.
- `context_json` - входной контекст.
- `started_at` - время старта.
- `finished_at` - время завершения.
- `status` - статус рендера (`RenderStatus`).
- `duration_ms` - длительность рендера в миллисекундах, `>= 0`.
- `output_text` - результат при успешном рендере.
- `error_code` - код ошибки.
- `error_message` - текст ошибки.

## Команды (Command Use Cases)

- `RegisterTemplate` - зарегистрировать новый шаблон для движка.
- `UpdateTemplateBody` - изменить тело шаблона.
- `DeactivateTemplate` - запретить новые рендеры по шаблону.
- `StartRenderRun` - открыть запуск рендера и зафиксировать snapshot шаблона.
- `CompleteRenderRunSuccess` - завершить запуск успешно.
- `CompleteRenderRunFailure` - завершить запуск с ошибкой.

## Запросы (Query Use Cases)

- `get_render_run` - детальный просмотр запуска.
- `list_render_runs` - журнал запусков по фильтрам.
- `get_template` - получить текущую карточку шаблона.
- `get_template_stats` - агрегаты производительности по рендерам.
- `get_recent_failures` - последние неуспешные рендеры.
