# 00-06 Pinia stores

## Цель

Создать четыре store: auth, engines, sandbox, templates. Подключить Pinia в `main.ts`.

## Зачем

Stores — единственный источник истины для разделяемого состояния. Auth store хранит текущего пользователя и персистит его в localStorage для быстрого UI restore при перезагрузке. Sandbox store — центральная точка для всего состояния редактора: два слота, JSON-контекст, режим, метрики.

## Особенности

**Engines store:** список движков — хардкод на фронте, не запрашивается с бэкенда. Добавление нового движка = изменение фронтенда (новый npm-пакет + новая запись в store). Store нужен только чтобы EngineSelector и render-service читали единый список.

**Sandbox store:** хранит `isDirty` — флаг несохранённых изменений. Бэкенд получает данные только по явному действию пользователя (кнопка Save), никакой автоматической синхронизации с сервером.

## Use Cases

- Пользователь обновляет страницу — UI мгновенно показывает его имя (из localStorage), пока идёт проверка сессии
- Компонент читает `enginesStore.engines` — получает хардкодный список без запросов
- Monaco меняет код — sandbox store обновляется, `isDirty` становится `true`, live preview реагирует

## Критерии готовности

- [ ] Pinia подключена в `main.ts`
- [ ] `useAuthStore`: user, isAuthenticated, login/logout/register/fetchCurrentUser, персистит user в localStorage
- [ ] `useEnginesStore`: хардкодный список `{ id, name, syntaxAlias }` для handlebars, pug, ejs
- [ ] `useSandboxStore`: slotA, slotB, json, activeTab, mode, metricsA/B, iterations, isDirty, savedStateId
- [ ] `useTemplatesStore`: список шаблонов, CRUD actions
- [ ] `npm run type-check` без ошибок

## Зависимости

- `00-01` — pinia установлена
- `00-02` — типы
- `00-05` — API-модули
