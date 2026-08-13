# Dev Log

Журнал рабочих сессий. Дописывай в конец после каждой сессии.

---

## Формат записи

**Дата:** ДД.ММ.ГГГГ
**Что сделано:** ...
**Что следующее:** ...

---

**Дата:** 13.08.2026
**Что сделано:** Фаза 0, Таск 1 — развёртывание фундамента.

- Прогнан `database/install.php` на БД `mikhail700_sweet` — создано 33
  таблицы, идемпотентность подтверждена (повторный прогон без ошибок).
  Настройки (`settings`, 9 ключей) и роль `superadmin` сеет сам
  `install.php`.
- Добавлен `database/seed-core.php` — создаёт одного админа, логин/пароль
  из `.env` (`SEED_ADMIN_EMAIL` / `SEED_ADMIN_PASSWORD`), привязка к роли
  `superadmin`, идемпотентно по email. Настройки в сиде не дублируются —
  уже за `install.php`.
- В `.env.example` добавлены `SEED_ADMIN_EMAIL/PASSWORD/NAME`.
- Проверено: 33 таблицы, 4 ключевые строки `settings`, админ с
  `password_verify=true`, `app.log` чист.

Отклонение от исходного TASK.md: сид настроек убран из `seed-core.php`,
т.к. `install.php` уже их сеет (иначе дублирование факта).

**Что следующее:** Фаза 0, Таск 2 — layout витрины (shop) на теме
Fastkart.

---

**Дата:** 13.08.2026
**Что сделано:** Фаза 0, Таск 2 — layout витрины на теме Fastkart.
- Перенесён вендорный слой в `public/assets/vendor/` (css/js/fonts/svg/
  images, ~13 МБ) AS IS. JS — только layout-набор (jQuery, bootstrap
  bundle, feather, lazysizes, script.js).
- Google Fonts (Russo One, Pacifico, Kaushan Script, Exo 2)
  сселф-хостнуты: 15 woff2 в `vendor/fonts/google/`, локальный
  `vendor/css/fonts-google.css` — CDN-ссылок в `<head>` больше нет.
- Создан layout витрины: `src/Views/shop/layout.php` +
  `components/header.php` + `components/footer.php`, локализованы
  (`lang="ru"`, текст переведён, цена/контакты в формате RU).
- `src/Views/shop/home.php` (пустая витрина, буфер → `$content` →
  layout) + `src/Controllers/HomeController.php` (`render('shop/home')`).
- Свой слой: `public/assets/css/app.css` (`--theme-color:#d99f46`,
  BEM-класс `.home-intro__lead`), `public/assets/js/app.js` (ES-модуль,
  пока пустой).
- Проверено: `GET /` → 200, все прямые `/assets`-ссылки → 200,
  `composer test` 18/18.

Решения и отклонения:
- Вендорный `style.css`/`bulk-style.css` НЕ редактировался (правило
  «vendor AS IS»). Внутри остались ленивые `url()` на неиспользуемые
  фичи темы (Iconly/slick, фоны других тем, 2 внешних фона лендинга
  Pixelstrap) — браузер их не запрашивает, т.к. соответствующие классы/
  шрифты на странице не применяются. Поэтому чистка `@font-face` из
  PRD §3.2 здесь не требуется — она про CSS админки (Таск 3), где
  легаси-файлы вырезаны; во front-end они на месте.
- Шапка/подвал перенесены без демо-контента макета (хардкод-категорий
  в мега-меню, переключателя языка/валюты, товаров в мини-корзине) —
  этот контент динамический, появляется в Фазе 1+.
- В `public/assets/vendor/images/` скопирована вся папка темы, включая
  неиспользуемые чужетематичные картинки (fashion/vegetable). Кандидат
  на чистку в Фазе 9, если размер репозитория станет проблемой.
- В `storage/logs/app.log` есть старые записи «Контроллер не найден:
  HomeController» — они датированы ДО создания контроллера; текущий
  рендер `GET /` ошибок не пишет.

Правка после первой визуальной проверки:
- Шапка «рассыпалась» в столбик — при переносе была пропущена обёртка
  `<div class="top-nav top-header sticky-header">`, к которой привязано
  правило флекса `header .top-nav .navbar-top`. Обёртка добавлена.
- Логотип-картинку темы (Fastkart) заменил на текстовый логотип «Сдоба»
  через класс темы `.nav-logo` (двухцветный: «Сдо» + `<span>ба</span>`).
  Картинка `logo/2.png` в шапке больше не используется.

**Что следующее:** ручная проверка DoD Таск 2 в браузере (внешний вид,
консоль, 320px). Затем Фаза 0, Таск 3 — layout админки.

---

**Дата:** 13.08.2026
**Что сделано:** Фаза 0, Таск 3 — layout админки на теме Fastkart (back-end).

- Перенесён независимый вендорный слой админки в
  `public/assets/vendor/admin/` (css/js/fonts/images) — отдельный от
  витрины набор (свой Bootstrap, иконки, ApexCharts-ядро). Общего слоя
  с витриной нет, как и требует архитектура.
- Из головы макета взяты только ассеты каркаса: bootstrap, иконочные
  CSS (linearicon, font-awesome, themify, remixicon, feather-icon),
  scrollbar, animate, ratio, style. JS каркаса: jquery, bootstrap,
  feather, simplebar+custom, config, tooltip-init, sidebar-menu,
  sidebareffect, script + ядро ApexCharts (moment + apex-chart).
  Плагины контента дашборда (slick, vector-map, notify, customizer,
  init-скрипты графиков, DataTables) не переносились — Фаза 5.
- `@font-face` вычищены от `.eot/.ttf/.svg` в linearicon/remixicon/
  font-awesome/themify; `linearicon.css` ссылался на внешний CDN
  `cdn.linearicons.com` — переписан на локальные woff2/woff.
- Созданы `src/Views/admin/layout.php`, `components/header.php`,
  `components/sidebar.php` (локализованы на русский, `lang="ru"`),
  свой `public/assets/css/admin.css` (`--theme-color:#d99f46`) и
  `public/assets/js/admin.js` (ES-модуль-заглушка).
- Сайдбар — полное меню-каркас разделов админки; активен только
  «Дашборд» (временно → `/admin/_preview`), остальные пункты на `#`.
  Пункты вырезанных фич (мультиязычность/валюты) не переносились.
- Проверка: временный `AdminPreviewController` + роут `GET /admin/_preview`
  (оба удаляются в Таске 5). Рендер через встроенный сервер PHP — 200,
  все 33 ассета + шрифты отдают 200, ноль ссылок на CDN, `app.log`
  без новых ошибок.

Решения и отклонения:
- **Шрифт body заменён с «Public Sans» на self-hosted «Exo 2».** Макет
  тянул Public Sans с Google CDN (запрещено), а у Public Sans нет
  кириллицы — весь текст админки русский. Exo 2 уже self-hosted для
  витрины и покрывает кириллицу; её 5 woff2 (по сабсетам) скопированы
  в `vendor/admin/fonts/exo2/`, `fonts.css` сгенерирован из блоков
  витрины с переписанным путём. Независимость фронтов сохранена
  (копия, а не общий файл). Переопределение — в `admin.css`.
- Из шапки убран нефункциональный переключатель тёмной темы (завязан
  на `customizer.js`, вне скоупа); мини-поиск оставлен как статический
  input (typeahead-плагин не переносился).
- Логотипы-картинки Fastkart заменены на текстовый бренд «Сдоба» (класс
  `.admin-logo` в `admin.css`, Exo 2, `--theme-color`; монограмма «С»
  для свёрнутого сайдбара). Неиспользуемые PNG темы (`logo/1.png` и др.)
  удалены — в шапке/сайдбаре на них ссылок больше нет.

**Что следующее:** ручная проверка DoD Таск 3 в браузере (консоль JS,
внешний вид, 320px). Затем Фаза 0, Таск 4 — авторизация.

---
