<?php

declare(strict_types=1);

/**
 * Хелперы авторизации. Подключается из config/config.php после functions.php
 * и Database.php.
 *
 * `isAuthenticated()` и `normalizeUserId()` живут в functions.php — здесь не
 * дублируются, а переиспользуются. Проверка пароля и хэширование — на слое
 * контроллера (`password_verify` / `password_hash`), не здесь и не в модели.
 */

/**
 * Текущий пользователь (по `$_SESSION['user_id']`) или NULL для гостя.
 * Кэшируется на запрос, чтобы не ходить в БД несколько раз.
 *
 * @return array{id:int,name:string,email:string,role:string,is_active:int}|null
 */
function currentUser(): ?array
{
    static $cache = null;
    static $loaded = false;

    if ($loaded) {
        return $cache;
    }
    $loaded = true;

    ensureSessionStarted();
    $id = normalizeUserId($_SESSION['user_id'] ?? null);
    if ($id === null) {
        return $cache = null;
    }

    require_once ROOT_PATH . '/src/Models/UserModel.php';
    $cache = \App\Models\findById($id);

    return $cache;
}

/**
 * Залогинить пользователя: ротировать сессию (новый id + CSRF-токен) и
 * записать идентификатор с ролью. Принимает строку пользователя (нужны
 * ключи `id` и `role`).
 */
function loginUser(array $user): void
{
    regenerateSession();
    $_SESSION['user_id']   = (int) $user['id'];
    $_SESSION['user_role'] = (string) $user['role'];
}

/** Полный выход: очистка данных сессии, куки и уничтожение сессии. */
function logoutUser(): void
{
    ensureSessionStarted();
    $_SESSION = [];

    if (ini_get('session.use_cookies') && !headers_sent()) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            [
                'expires'  => time() - 42000,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]
        );
    }

    session_destroy();
}

/**
 * Гейт доступа по роли. Гость → redirect на /login; авторизованный с другой
 * ролью → 403 (страница «Доступ запрещён»). Строгая проверка одной роли:
 * `requireRole('admin')` не пускает vendor/customer, `requireRole('vendor')`
 * не пускает admin/customer. Основной гейт — по `users.role` (в сессии
 * `user_role`), а не по факту логина.
 */
function requireRole(string $role): void
{
    if (!isAuthenticated()) {
        redirect('/login');
    }

    ensureSessionStarted();
    if (($_SESSION['user_role'] ?? null) !== $role) {
        http_response_code(403);
        render('errors/403');
        exit;
    }
}

// ─── Валидаторы форм (чистая логика, без БД и без HTTP — под unit-тест) ────

/**
 * Ошибки формы регистрации. Пустой массив = данные валидны.
 * Ключи ошибок совпадают с именами полей: name / email / password.
 *
 * @param array<string,mixed> $input
 * @return array<string,string>
 */
function validateRegistration(array $input): array
{
    $errors = [];

    $name     = trim((string) ($input['name'] ?? ''));
    $email    = trim((string) ($input['email'] ?? ''));
    $password = (string) ($input['password'] ?? '');

    if ($name === '') {
        $errors['name'] = 'Укажите имя.';
    } elseif (mb_strlen($name) > 100) {
        $errors['name'] = 'Имя слишком длинное (максимум 100 символов).';
    }

    if ($email === '') {
        $errors['email'] = 'Укажите email.';
    } elseif (mb_strlen($email) > 150 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Некорректный email.';
    }

    if ($password === '') {
        $errors['password'] = 'Укажите пароль.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Пароль должен быть не короче 8 символов.';
    }

    return $errors;
}

/**
 * Ошибки формы входа — только проверка наличия полей. Неверные учётные
 * данные проверяет контроллер и отдаёт единый generic-текст (не раскрываем,
 * что именно не так — email или пароль).
 *
 * @param array<string,mixed> $input
 * @return array<string,string>
 */
function validateLogin(array $input): array
{
    $errors = [];

    if (trim((string) ($input['email'] ?? '')) === '') {
        $errors['email'] = 'Укажите email.';
    }
    if ((string) ($input['password'] ?? '') === '') {
        $errors['password'] = 'Укажите пароль.';
    }

    return $errors;
}

// ─── Перенос ошибок формы через redirect (PRG: POST → redirect → GET) ──────

/**
 * Сохранить ошибки и предыдущий ввод в сессию перед redirect обратно на
 * форму. Пароль в `$old` не кладём.
 *
 * @param array<string,string> $errors
 * @param array<string,string> $old
 */
function flashFormState(array $errors, array $old): void
{
    ensureSessionStarted();
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_old']    = $old;
}

/**
 * Забрать и очистить сохранённое состояние формы (для показа во вью после
 * redirect). Всегда возвращает ключи errors/old.
 *
 * @return array{errors:array<string,string>,old:array<string,string>}
 */
function takeFormState(): array
{
    ensureSessionStarted();
    $errors = $_SESSION['form_errors'] ?? [];
    $old    = $_SESSION['form_old'] ?? [];
    unset($_SESSION['form_errors'], $_SESSION['form_old']);

    return [
        'errors' => is_array($errors) ? $errors : [],
        'old'    => is_array($old) ? $old : [],
    ];
}
