# Current Task

## Фаза
Phase 0 — Фундамент

## Задача
Гость регистрируется как `customer`, входит и выходит. Сессия
ротируется после входа, ошибки generic (не раскрывают существование
email), есть rate-limit и CSRF. Формы — на shop-layout из Таска 2.

Источник вёрстки: `example-template/front-end/login.html` (секция
`.log-in-section`: email + пароль) и `sign-up.html` (имя + email + пароль).

## Scope — что трогаем

**Модель:**
- [ ] `src/Models/UserModel.php` — создать: `findByEmail(string): ?array`
      (SELECT по email, возвращает строку с `password_hash`, `role`,
      `is_active`), `createCustomer(string $name, string $email,
      string $passwordHash): int` (INSERT `role='customer'`, возвращает
      id), `emailExists(string): bool`. Только PDO prepared, возвращает
      массивы; никакого HTML/редиректов.

**Core:**
- [ ] `src/Core/auth.php` — создать: `currentUser(): ?array` (грузит
      юзера по `$_SESSION['user_id']`, кэширует на запрос),
      `loginUser(array $user): void` (`regenerateSession()` + пишет
      `user_id`/`role` в сессию), `logoutUser(): void` (чистит сессию),
      плюс чистые валидаторы `validateRegistration(array): array` /
      `validateLogin(array): array` (возвращают массив ошибок; без БД,
      без HTTP — под unit-тест). `isAuthenticated()` / `normalizeUserId()`
      уже есть в `functions.php` — не дублируем, переиспользуем.

**Контроллер:**
- [ ] `src/Controllers/AuthController.php` — создать: `showLogin`,
      `login`, `showRegister`, `register`, `logout`. Все POST:
      `requireCsrf()` → валидация → Model → `redirect()`.
      `login`: `tooManyAttempts('login',5,60)` до обработки,
      `hitRateLimit('login')` + `logWarning()` на неудаче,
      `clearRateLimit('login')` + `loginUser()` на успехе. Проверка
      пароля — `password_verify()` здесь (Model остаётся SQL-only).
      Успех входа/регистрации → `redirect('/')`.

**Представление:**
- [ ] `src/Views/shop/auth/login.php` — создать: секция `.log-in-section`
      из `login.html`, локализована, `<form action="/login" method="post">`
      + `csrfField()`, вывод ошибок и старого email через `e()`.
      Подключает shop-layout (буфер `$content`).
- [ ] `src/Views/shop/auth/register.php` — создать: из `sign-up.html`,
      поля имя/email/пароль (без подтверждения пароля, как в макете),
      `action="/register"`, CSRF, ошибки/старый ввод через `e()`.

**Конфиг:**
- [ ] `config/config.php` — изменить: добавить
      `require_once .../src/Core/auth.php`.

**Тест:**
- [ ] `tests/Unit/AuthValidationTest.php` — создать: кейсы на
      `validateRegistration` / `validateLogin` (пустые поля, кривой
      email, пароль < 8 символов) — `composer test`.

*(`config/routes.php` не трогаем — маршруты `GET/POST /login`,
`GET/POST /register`, `POST /logout` уже прописаны.)*

## Out of scope — не трогаем
- Гейт ролей, 403, редирект `customer` из `/admin` и `/vendor-panel`,
  дашборды — Таск 5
- Отдельный вход в админку (`/admin/login`) — Фаза 5
- Восстановление/сброс пароля, OTP (`forgot.html`, `otp.html`, таблица
  `password_resets`) — позже
- Welcome-email после регистрации (PHPMailer / `src/Services/`) —
  почтовый слой в этом таске не строим
- «Запомнить меня» (persistent-токены), редактирование профиля, аватар
- Правка `functions.php` — `redirectIfAuthenticated()` с его `/dashboard`
  оставляем Таску 5 (роле-зависимые редиректы там)
- Личный кабинет покупателя — Фаза 4

## Definition of Done
- [ ] Регистрация создаёт `users` с `role='customer'`, пароль через
      `password_hash()` (валидация min 8 до хэша); запись реально
      появляется в БД
- [ ] Повторная регистрация на занятый email — generic-ошибка, не
      «email уже существует» (FR-AUTH-03)
- [ ] Вход верным паролем: `regenerateSession()`, `clearRateLimit('login')`,
      сессия содержит `user_id`; редирект на `/`
- [ ] Вход неверным паролем / несуществующим email: одинаковое
      «Неверный email или пароль»; `hitRateLimit` + `logWarning()`
- [ ] После 5 быстрых неудач за 60 с — блок попыток (`tooManyAttempts`),
      проверить серией быстрых запросов
- [ ] Заблокированный аккаунт (`is_active=0`) — тоже generic-ошибка,
      вход не даётся
- [ ] Обе формы: `csrfField()` в HTML и `requireCsrf()` в контроллере до
      обращения к Model
- [ ] Выход чистит сессию, редирект на `/`; повторный заход — гость
- [ ] Пустые/кривые поля: форма не отправляется, поля с ошибкой
      подсвечены, старый email сохранён (через `e()`)
- [ ] `composer test` зелёный (unit на валидаторы)
- [ ] Ассеты локальные, консоль браузера чистая, `storage/logs/app.log`
      без ошибок и warnings, корректно на 320px
- [ ] Проверить `.docs/dod-global.md`

## Важные правила
- Следовать `CLAUDE.md`
- Models — только SQL через PDO prepared; POST → redirect; вывод в
  Views через `e()`
- Работать только в рамках Scope
- Не менять файлы вне Scope
- Не рефакторить попутно
