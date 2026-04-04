# 02-03 Табы редактора, engine selector, live preview

## Цель

Три таба (Template A / Template B / JSON) с inline-селектором движка, PreviewPanel с iframe и composable для debounced рендера.

## Зачем

Два слота — независимые шаблоны с разными движками. Переключение таба мгновенно показывает соответствующий preview. Debounce 150ms защищает от рендера на каждый keystroke. iframe безопаснее `v-html` — изолирует скрипты и стили шаблона от основного приложения.

## Use Cases

- Пользователь пишет код в Template A — preview обновляется через ~150ms
- Пользователь переключается на Template B — preview показывает рендер слота B
- Пользователь переключается на JSON — preview не меняется (JSON это данные, не шаблон)
- Пользователь меняет движок в Template A — подсветка Monaco меняется, preview перерендеривается
- Шаблон содержит ошибку — preview показывает сообщение об ошибке, не белый экран
- Клик на EngineSelector не переключает таб

## Критерии готовности

- [ ] Три таба переключаются, содержимое Monaco синхронизируется с нужным слотом
- [ ] EngineSelector в каждом таб-заголовке, компактный
- [ ] Live preview обновляется с debounce ~150ms
- [ ] Ошибка рендера → сообщение в preview, не краш
- [ ] PreviewPanel использует `<iframe srcdoc>` с `sandbox="allow-same-origin"`

## Зависимости

- `02-01` — render-service
- `02-02` — MonacoEditorWrapper
- `00-06` — sandbox store, engines store
