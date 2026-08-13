# Current Task

## Фаза
Phase 0 — Фундамент

## Задача
Собрать независимый layout админки на теме Fastkart (back-end):
сайдбар + шапка, свой вендорный слой ассетов (свой Bootstrap,
иконочные шрифты, ApexCharts), локализация. Любой admin-маршрут
рендерится в этот layout с пустым `main`.

Источник вёрстки: `example-template/back-end/index.html`.

## Scope — что трогаем

**Вендорный слой (перенос AS IS в отдельную ветку `public/assets/vendor/admin/`):**
- [ ] `public/assets/vendor/admin/css/` — из `<head>` index.html:
      `vendors/bootstrap.css`, `vendors/font-awesome.css`,
      `vendors/themify.css`, `vendors/feather-icon.css`,
      `vendors/scrollbar.css`, `vendors/animate.css`, `linearicon.css`,
      `remixicon.css`, `ratio.css`, `style.css` (+ зависимые из `vendors/`)
- [ ] `public/assets/vendor/admin/js/` — `jquery-3.6.0.min.js`,
      `bootstrap/bootstrap.bundle.min.js`, `icons/feather-icon/feather.min.js`
      + `feather-icon.js`, `scrollbar/simplebar.js` + `custom.js`,
      `config.js`, `tooltip-init.js`, `sidebar-menu.js`, `sidebareffect.js`,
      `script.js`, **+ ApexCharts ядро** `chart/apex-chart/apex-chart.js`
      + `moment.min.js` (DoD требует «ApexCharts подключён»; init-скрипты
      графиков — не здесь, см. out of scope)
- [ ] `public/assets/vendor/admin/fonts/` — иконочные шрифты темы
      (linearicon / font-awesome / themify / remixicon / feather) в
      `.woff2/.woff` + локальный **Public Sans** (в макете он с Google
      CDN) с `@font-face`. Из всех `@font-face` вычищаются `.eot/.ttf/.svg`
- [ ] `public/assets/vendor/admin/images/` — только логотип шапки
      (`logo/1.png`, `logo/1-white.png`) и `favicon.png`

**Свой слой:**
- [ ] `public/assets/css/admin.css` — создать:
      `:root { --theme-color:#d99f46; --theme-color-rgb:217,159,70 }`,
      BEM-переопределения поверх темы админки
- [ ] `public/assets/js/admin.js` — создать: ES-модуль, точка входа админки

**Представление:**
- [ ] `src/Views/admin/layout.php` — создать: HTML-скелет (`<head>` с
      локальными admin-CSS и шрифтами, `page-wrapper`, `<main>` с
      `$content`, подключение admin-JS + `admin.js`)
- [ ] `src/Views/admin/components/sidebar.php` — создать: сайдбар из
      index.html, локализованный. Полное меню админки; активный пункт
      **Дашборд → `/admin/_preview`** (перецепится на `/admin` в Таске 5),
      остальные пункты — `href="#"` (оживают по мере фаз)
- [ ] `src/Views/admin/components/header.php` — создать: шапка из
      index.html, локализованная

**Временное — для ручной проверки (удаляется/заменяется в Таске 5):**
- [ ] `config/routes.php` — временный роут `GET /admin/_preview` →
      тестовый рендер admin-layout (в Таске 5 появится настоящий `GET /admin`)
- [ ] `src/Views/admin/_preview.php` — временная пустая admin-страница
      (буферизует `$content`, подключает `layout.php`); в Таске 5
      заменяется на `dashboard.php`

## Out of scope — не трогаем
- Контент дашборда: KPI-карточки, графики ApexCharts (init-скрипты
  `apex-chart1.js`, `chart-custom1.js`, `stock-prices.js`), vector-map,
  slick-карусели, таблицы DataTables — наполняется в Фазе 5; здесь `main`
  пустой
- Демо-заглушки макета: `customizer.js` + виджет темы, `notify/*`
  (тосты форм), `select2`, `dropzone`, `datatables`, `daterange-picker`,
  typeahead — переносятся со своими страницами в следующих фазах
- `back-end/assets/ajax/*.php` — демо-заглушки DataTables, не переносим
- Layout витрины (Таск 2 — завершён), общий слой представления shop/admin
  не заводим
- Настоящий роут `/admin`, гейт ролей, 403, дашборд-контроллер — Таск 5
- Авторизация, формы `login` / `register` — Таск 4

## Definition of Done
- [ ] Тестовая admin-страница (`GET /admin/_preview`) открывается:
      рендерится layout админки в теме Fastkart (сайдбар + пустой `main`
      + шапка)
- [ ] Сайдбар и шапка: `lang="ru"`, `dir` убран, весь текст переведён
      на русский
- [ ] Все CSS/JS/шрифты локальные — в Network ноль 404 и ноль запросов
      к `googleapis` / `gstatic` / любому CDN (Public Sans self-hosted)
- [ ] `@font-face` без `.eot/.ttf/.svg` (только `.woff2/.woff`)
- [ ] Консоль браузера чистая (нет JS-ошибок)
- [ ] Витрина и админка используют разные наборы ассетов — admin-layout
      не подключает ничего из `vendor/` витрины (общего CSS нет)
- [ ] Корректно на 320px (mobile-first)
- [ ] `storage/logs/app.log` без PHP-ошибок и warnings
- [ ] Вывод динамики в шапке и сайдбаре через `e()`
- [ ] Проверить `.docs/dod-global.md` (юнит-тест не требуется — это
      Views/HTML без чистой логики)

## Важные правила
- Следовать `CLAUDE.md`
- Работать только в рамках Scope
- Не менять файлы вне Scope
- Не рефакторить попутно
- Вендорные файлы в `public/assets/vendor/admin/` — AS IS, не
  форматировать и не чинить
