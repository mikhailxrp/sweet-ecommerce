# Phase 0 — Фундамент

## Цель
Гость может зарегистрироваться, войти и выйти; видит пустую витрину и
админку в теме Fastkart; покупатель, зная URL, получает 403 на `/admin`
и `/vendor-panel`. Схема БД развёрнута.

## Статус
🔄 В работе

## Решения фазы
- Единый вход `/login` для всех ролей. Отдельная страница входа в
  админку (`/admin/login` из макета) — в Фазе 5.
- Служебный vendor платформы (под `products.vendor_id`) в сид Фазы 0 не
  входит — появится в сид-скрипте Фазы 1. Здесь сидим только строки
  `settings` и одного админа.
- Неавторизованный на `/admin` → редирект на `/login`; авторизованный
  `customer` → 403.

## Таски

| # | Название | Статус |
|---|----------|--------|
| 1 | Развёртывание схемы БД + минимальный сид фундамента | ✅ Завершена |
| 2 | Layout витрины (shop) на теме Fastkart | ✅ Завершена |
| 3 | Layout админки (admin) на теме Fastkart | ⏳ Ожидает |
| 4 | Авторизация: регистрация, вход, выход | ⏳ Ожидает |
| 5 | Гейт ролей + пустая витрина, дашборд-заглушка, 403 | ⏳ Ожидает |

---

## Таск 1 — Развёртывание схемы БД + минимальный сид фундамента

**Статус:** ✅ Завершена

**Цель таска:**
`install.php` разворачивает все 33 таблицы на `mikhail700_sweet`; есть
стартовый админ и дефолтные настройки для входа в админку.

**Что нужно создать/изменить:**
- `database/install.php` — сверка с `database.md`, идемпотентность
  (повторный прогон не падает), порядок FK (родитель раньше потомка)
- `database/seed-core.php` — строки `settings` (`shop_name`,
  `default_commission_percent=15`, `vat_rate`, `free_delivery_from`) +
  один пользователь `admin`
- `.docs/dev-log.md` — запись о развёртывании

**Definition of Done:**
- [ ] `SHOW TABLES` = 33, список совпадает с `database.md`
- [ ] Повторный прогон `install.php` не роняет ошибку
- [ ] Админ создан, `password_verify()` проходит; строки `settings` на месте
- [ ] Проверить `.docs/dod-global.md`

---

## Таск 2 — Layout витрины (shop) на теме Fastkart

**Статус:** ✅ Завершена

**Цель таска:**
Любой shop-маршрут рендерится в общий layout с шапкой и подвалом темы;
ассеты локальные; консоль и Network чистые.

Источник вёрстки: `example-template/front-end/index-2.html`.

**Что нужно создать/изменить:**
- `public/assets/vendor/` — нужные CSS/JS фронта AS IS (Bootstrap,
  Swiper, иконки)
- `public/assets/css/app.css` — свой слой, `--theme-color: #d99f46`, BEM
- `public/assets/js/app.js` — ES-модуль, инициализация вендорных плагинов
- `src/Views/shop/layout.php`
- `src/Views/shop/components/header.php`
- `src/Views/shop/components/footer.php`

**Definition of Done:**
- [ ] Тестовая пустая страница открывается в теме; шапка и подвал:
      `lang="ru"`, `dir` убран, текст переведён
- [ ] `@font-face` без `.eot/.ttf/.svg`, шрифты и CSS/JS локальные —
      ноль 404 в Network
- [ ] Консоль чистая, корректно на 320px
- [ ] Проверить `.docs/dod-global.md`

---

## Таск 3 — Layout админки (admin) на теме Fastkart

**Статус:** ⏳ Ожидает

**Цель таска:**
Admin-маршрут рендерится в свой независимый layout (сайдбар + шапка),
свои ассеты, ApexCharts подключён.

Источник вёрстки: `example-template/back-end/index.html`.

**Что нужно создать/изменить:**
- `public/assets/vendor/admin/` — CSS/JS бэкенда AS IS (свой Bootstrap,
  иконки, ApexCharts)
- `public/assets/css/admin.css`
- `public/assets/js/admin.js`
- `src/Views/admin/layout.php`
- `src/Views/admin/components/sidebar.php`
- `src/Views/admin/components/header.php`

**Definition of Done:**
- [ ] Тестовая страница админки открывается в теме; сайдбар и шапка
      локализованы
- [ ] Ассеты локальные, ноль 404, консоль чистая
- [ ] Витрина и админка используют разные наборы ассетов (общего CSS нет)
- [ ] Проверить `.docs/dod-global.md`

---

## Таск 4 — Авторизация: регистрация, вход, выход

**Статус:** ⏳ Ожидает

**Цель таска:**
Гость регистрируется как `customer`, входит и выходит; сессия
ротируется, ошибки generic, есть rate-limit и CSRF.

**Что нужно создать/изменить:**
- `src/Models/UserModel.php` — `findByEmail`, `create`, проверка пароля
  (PDO, возвращает массивы)
- `src/Controllers/AuthController.php` — `showLogin`, `login`,
  `showRegister`, `register`, `logout` (роуты уже в `routes.php`)
- `src/Core/auth.php` — `currentUser`, `isAuthenticated`, `login`/`logout`
  хелперы (require в `config.php`)
- `src/Views/shop/auth/login.php` (макет `login.html`)
- `src/Views/shop/auth/register.php` (макет `sign-up.html`)

**Definition of Done:**
- [ ] Регистрация создаёт `users.role='customer'`, пароль через
      `password_hash()` (минимум 8 символов)
- [ ] Вход: `regenerateSession()` после успеха; `tooManyAttempts('login',5,60)`
      + `hitRateLimit` / `clearRateLimit`; `logWarning()` на неудаче
- [ ] Ошибки входа/регистрации не раскрывают, существует ли email
      (FR-AUTH-03)
- [ ] CSRF на обеих формах (`csrfField()` + `requireCsrf()`)
- [ ] Проверить `.docs/dod-global.md`

---

## Таск 5 — Гейт ролей + пустая витрина, дашборд-заглушка, 403

**Статус:** ⏳ Ожидает

**Цель таска:**
Закрыть DoD фазы — `customer` получает 403 на `/admin` и `/vendor-panel`,
админ видит заглушку дашборда, главная отдаёт пустую витрину.

**Что нужно создать/изменить:**
- `src/Core/auth.php` — доработка `requireRole()` → 403 / редирект на `/login`
- `src/Controllers/HomeController.php` — `index` (пустая витрина в shop-layout)
- `src/Controllers/Admin/DashboardController.php` — `index`
  (`requireRole('admin')`, admin-layout)
- `src/Views/shop/home.php`
- `src/Views/admin/dashboard.php`
- `src/Views/errors/403.php`
- `config/routes.php` — `GET /`, `GET /admin`, заглушка-guard `GET /vendor-panel`

**Definition of Done:**
- [ ] Неавторизованный на `/admin` → `/login`; авторизованный `customer`
      на `/admin` и `/vendor-panel` → 403
- [ ] Админ входит и видит `/admin` в теме; главная `/` открывается
      пустой витриной
- [ ] Юнит-тест `RouterTest`: добавлен кейс на новые роуты
      (`composer test` зелёный)
- [ ] Проверить `.docs/dod-global.md`
