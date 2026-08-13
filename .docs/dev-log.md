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

**Дата:** 13.08.2026
**Что сделано:** Фаза 0, Таск 4 — авторизация (регистрация, вход, выход).

- `src/Models/UserModel.php` — функции `findByEmail`, `findById`,
  `emailExists`, `createCustomer` (PDO prepared, плоские массивы, только
  SQL). Первая модель проекта — контроллер подключает её через
  `require_once` и зовёт квалифицированно (`\App\Models\...`), т.к.
  автозагрузчик классовый, а модели по правилам — функции.
- `src/Core/auth.php` — `currentUser` (кэш на запрос), `loginUser`
  (через `regenerateSession`), `logoutUser` (очистка сессии + куки +
  destroy), чистые валидаторы `validateRegistration`/`validateLogin` и
  PRG-хелперы `flashFormState`/`takeFormState`. `require_once` добавлен
  в `config.php`. `isAuthenticated`/`normalizeUserId` не дублируются —
  переиспользованы из `functions.php`.
- `src/Controllers/AuthController.php` — `showLogin/login/showRegister/
  register/logout`. Все POST: `requireCsrf()` → валидация → Model →
  `redirect()` (PRG). Регистрация создаёт `role='customer'` и сразу
  логинит; вход — `tooManyAttempts('login',5,60)` + `hitRateLimit`/
  `clearRateLimit` + `logWarning` на неудаче; успех/уже-авторизован →
  `/`.
- Вьюхи `src/Views/shop/auth/{login,register}.php` на shop-layout из
  Таска 2 (локализованы, `csrfField()`, подсветка ошибок `is-invalid`,
  сохранение прошлого ввода через `e()`).
- Юнит-тест `tests/Unit/AuthValidationTest.php` (8 кейсов на валидаторы);
  `composer test` — 26/26 зелёный.
- Проверено встроенным сервером (полный прогон curl + cookie jar):
  регистрация создаёт `customer` (`password_verify=1`), авто-логин и
  редирект на `/`; верный вход → `/`; неверный → generic «Неверный
  email или пароль», после 5 попыток — «Слишком много попыток»;
  повторный email → generic (без «существует»); короткий пароль →
  ошибка поля + сохранён email; выход → `/`, без CSRF → 419. Тестовые
  строки из БД удалены.

Решения и отклонения:
- Проверка пароля вынесена из модели в контроллер (`password_verify`) —
  модель осталась SQL-only. Для защиты от перебора по времени ответа
  `password_verify` прогоняется и при отсутствии юзера (константный
  хэш-заглушка), а `is_active=0` даёт тот же generic-ответ.
- POST завершается redirect (правило проекта): ошибки/ввод переносятся
  через сессию (`flashFormState`/`takeFormState`), а не рендерятся
  прямо из POST.
- Редирект после входа/регистрации — на `/` (у покупателя нет кабинета
  в Фазе 0); `functions.php`/`redirectIfAuthenticated` не трогали —
  роле-зависимые редиректы в Таске 5.
- Соц-вход, «запомнить меня», чекбокс согласия, «забыли пароль», welcome-
  email — не в скоупе (вне Фазы 0 / позже). UI-кнопки выхода в витрине
  нет — эндпоинт `/logout` готов, триггер появится с аккаунт-меню в
  Фазе 4.

**Что следующее:** ручная проверка DoD Таск 4 в браузере (формы, 320px,
консоль). Затем Фаза 0, Таск 5 — гейт ролей, 403, дашборд-заглушка.

Правка вне таска (найдено при ручной проверке входа):
- В консоли витрины падал `TypeError: Cannot read properties of
  undefined (reading 'classList')` в `vendor/js/script.js:320`. Причина
  — при сборке shop-layout (Таск 2) не был перенесён прелоадер темы
  `.fullpage-loader`, а loader-код темы берёт этот элемент по классу.
  Vendor-файл не трогаем (AS IS) — добавили недостающую разметку
  прелоадера (6 `<span>`) в `src/Views/shop/layout.php`. Стили
  `.fullpage-loader`/`--invisible` уже есть в `style.css`; ошибка ушла,
  прелоадер сам скрывается после загрузки. Затрагивает все страницы
  витрины, включая формы входа/регистрации.

---

**Дата:** 14.08.2026
**Что сделано:** Фаза 0, Таск 5 — гейт ролей, 403, дашборд-заглушка.
**Фаза 0 завершена.**

- `src/Core/auth.php` — `requireRole(string $role)`: гость →
  `redirect('/login')`; роль ≠ требуемой → `http_response_code(403)` +
  `render('errors/403')` + `exit`. Строгая проверка одной роли по
  `$_SESSION['user_role']`.
- `src/Controllers/Admin/DashboardController.php` — `index()` →
  `requireRole('admin')` → `render('admin/dashboard')`; вьюха
  `src/Views/admin/dashboard.php` (заглушка на admin-layout).
- `src/Controllers/VendorPanelController.php` — `index()` →
  `requireRole('vendor')` → `render('shop/vendor-panel')`; вьюха-
  заглушка `src/Views/shop/vendor-panel.php` (кабинет кондитера — Фаза 7).
- `src/Views/errors/403.php` — страница «Доступ запрещён» на shop-layout.
- `config/routes.php` — убран временный `/admin/_preview`, добавлены
  `GET /admin` → `Admin\DashboardController` и `GET /vendor-panel` →
  `VendorPanelController`.
- Удалены временные `AdminPreviewController.php` и `_preview.php` (Таск 3).
  В шапке/сайдбаре админки ссылки `/admin/_preview` → `/admin`.
- `tests/Unit/RouterTest.php` — кейсы на реальный `config/routes.php`:
  `/admin` и `/vendor-panel` резолвятся в нужные хендлеры, `/admin/_preview`
  больше не матчится. `composer test` — 28/28 зелёный.
- Проверено встроенным сервером (полная матрица): гость на `/admin` и
  `/vendor-panel` → 302 на `/login`; `customer` → 403 (страница
  «Доступ запрещён»); админ → `/admin` 200 (дашборд), `/vendor-panel`
  403 (строгая роль — админ не vendor); `/` → 200; `/admin/_preview` →
  404. Тестовый customer удалён, новых ошибок в `app.log` нет.

Решения:
- Проверка строго по одной роли: админ на `/vendor-panel` тоже получает
  403 (управление кондитерами — через `/admin`, а не их кабинет).
- Редирект после логина оставлен на `/` (Таск 4); роле-зависимого
  авто-редиректа в кабинет не вводили — не требуется DoD фазы.
- `redirectIfAuthenticated()` (ведёт на `/dashboard`) остался
  неиспользуемым мёртвым хелпером — по правилу «предсуществующий
  мёртвый код не трогаем», только упоминаю.

**Что следующее:** ручная проверка DoD Таск 5 в браузере (403/дашборд,
консоль, 320px). Фаза 0 закрыта — дальше Фаза 1 «Каталог».

---
