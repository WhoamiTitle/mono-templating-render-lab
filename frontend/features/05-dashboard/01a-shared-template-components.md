# 05-01a Переиспользуемые компоненты шаблонов

## Цель

ConfirmDialog, TemplateCard, TemplateList — переиспользуемые компоненты без привязки к конкретным страницам.

## Use Cases

- TemplateCard рендерится в MyTemplatesPage и TemplateLibraryPage одинаково
- TemplateList показывает skeleton во время загрузки, empty state когда список пуст
- ConfirmDialog вызывается из любой страницы при удалении шаблона

## Критерии готовности

- [ ] ConfirmDialog: принимает `title`, `message`, emits `confirm` / `cancel`
- [ ] TemplateCard: показывает имя, движок (badge), дату, слот для кнопок действий (через props: `showClone`, `showDelete`, `showOpen`)
- [ ] TemplateList: skeleton loading (3 карточки-placeholder), empty state со слотом для CTA

## Зависимости

- `00-06` — stores (для типов)
- Vuetify компоненты

## Примечание

Эти компоненты нужны `06-01` и `06-02` — выносятся первыми, чтобы не блокировать Dev A на Template Library.
