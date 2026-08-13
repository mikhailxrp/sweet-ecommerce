# Current Task

## Фаза
Phase 0 — Фундамент

## Задача
`customer`/гость не попадают в `/admin` и `/vendor-panel` (гость →
`/login`, чужая роль → 403); админ открывает `/admin` с дашбордом-
заглушкой в теме; главная `/` — пустая витрина. Временный
`/admin/_preview` из Таска 3 заменяется на настоящий `/admin`.

## Scope — что трогаем

**Гейт:**
- [ ] `src/Core/auth.php` — добавить `requireRole(string $role)`: гость →
      `redirect('/login')`; роль ≠ требуемой → `http_response_code(403)`
      + `render('errors/403')` + `exit`. Читает `$_SESSION['user_role']`
      (кладёт `loginUser`). Строгая проверка одной роли.

**Админка (замена временной заглушки на настоящую):**
- [ ] `src/Controllers/Admin/DashboardController.php` — создать:
      `index()` → `requireRole('admin')` → `render('admin/dashboard')`
- [ ] `src/Views/admin/dashboard.php` — создать: дашборд-заглушка на
      admin-layout (контент из нынешнего `_preview.php`, заголовок
      «Дашборд»)
- [ ] `src/Views/admin/components/header.php` — изменить: 2 ссылки
      логотипа `/admin/_preview` → `/admin`
- [ ] `src/Views/admin/components/sidebar.php` — изменить: ссылки
      логотипа/монограммы и пункт «Дашборд» `/admin/_preview` → `/admin`;
      поправить комментарий
- [ ] удалить `src/Controllers/AdminPreviewController.php` и
      `src/Views/admin/_preview.php` (временные из Таска 3)

**Гейт кондитера (заглушка-guard):**
- [ ] `src/Controllers/VendorPanelController.php` — создать: `index()` →
      `requireRole('vendor')` → `render('shop/vendor-panel')`
- [ ] `src/Views/shop/vendor-panel.php` — создать: минимальная заглушка
      «Раздел в разработке» на shop-layout (кабинет кондитера — Фаза 7)

**403:**
- [ ] `src/Views/errors/403.php` — создать: страница «Доступ запрещён»
      на shop-layout + ссылка на `/`

**Маршруты:**
- [ ] `config/routes.php` — убрать `/admin/_preview`; добавить
      `GET /admin` → `['Admin\DashboardController','index']`,
      `GET /vendor-panel` → `['VendorPanelController','index']`

**Витрина (уже готово из Таска 2 — проверить, правок не требуется):**
- [ ] `src/Controllers/HomeController.php` + `src/Views/shop/home.php` —
      уже отдают пустую витрину; не меняем

**Тест:**
- [ ] `tests/Unit/RouterTest.php` — изменить: добавить кейсы, грузящие
      реальный `config/routes.php` (`loadRoutes`): `GET /admin` и
      `GET /vendor-panel` резолвятся в нужные хендлеры, `/admin/_preview`
      больше не матчится

## Out of scope — не трогаем
- Наполнение дашборда (KPI, графики ApexCharts, таблицы) — Фаза 5
- Реальный кабинет кондитера (`seller-dashboard`, свой layout) — Фаза 7
- Отдельный вход в админку `/admin/login` — Фаза 5
- Личный кабинет покупателя, редирект после логина по роли — оставляем
  `/` (Таск 4)
- `redirectIfAuthenticated()` в `functions.php` (ведёт на `/dashboard`) —
  сейчас не используется (Таск 4 редиректит на `/` напрямую);
  предсуществующий мёртвый хелпер, не трогаю, только упоминаю
- 404-страница темы (`404.html`) — отдельно, не в этом таске

## Definition of Done
- [ ] Гость на `/admin` и `/vendor-panel` → редирект на `/login`
- [ ] Авторизованный `customer` на `/admin` и `/vendor-panel` → 403
      (страница «Доступ запрещён», HTTP 403)
- [ ] Админ на `/admin` → дашборд-заглушка в admin-теме (200)
- [ ] Админ на `/vendor-panel` → 403 (он не `vendor`) — проверка строгая
      по роли
- [ ] Главная `/` открывается пустой витриной (200)
- [ ] Временный `/admin/_preview` больше не отвечает (404), файлы-
      заглушки удалены
- [ ] `composer test` зелёный (новые кейсы RouterTest на `/admin`,
      `/vendor-panel`)
- [ ] Ассеты локальные, консоль браузера чистая, `storage/logs/app.log`
      без ошибок и warnings, корректно на 320px
- [ ] Проверить `.docs/dod-global.md`

## Важные правила
- Следовать `CLAUDE.md`
- Работать только в рамках Scope
- Не менять файлы вне Scope
- Не рефакторить попутно
