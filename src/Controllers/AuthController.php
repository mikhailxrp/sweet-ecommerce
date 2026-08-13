<?php

declare(strict_types=1);

namespace App\Controllers;

require_once ROOT_PATH . '/src/Models/UserModel.php';

/**
 * Регистрация, вход и выход покупателя.
 *
 * Модель-функции — в App\Models (UserModel.php). Проверка пароля здесь через
 * password_verify(); ошибки входа/регистрации generic — не раскрывают,
 * существует ли email (защита от перебора). Все POST заканчиваются redirect
 * (PRG): ошибки и прошлый ввод переносятся через flashFormState().
 *
 * Гейт ролей и редирект уже авторизованного в кабинет — Таск 5; здесь после
 * успеха всегда redirect на '/'.
 */
final class AuthController
{
    /**
     * Постоянный bcrypt-хэш-заглушка. Прогоняем password_verify() даже когда
     * пользователя нет, чтобы время ответа не выдавало существование email.
     */
    private const DUMMY_HASH = '$2y$10$usesomethinglongenoughxxxxeI0k7Xr0iH6mS3pT8yq3Yy5r2Yy3W';

    public function showLogin(): void
    {
        if (isAuthenticated()) {
            redirect('/');
        }
        render('shop/auth/login', takeFormState());
    }

    public function login(): void
    {
        requireCsrf();
        if (isAuthenticated()) {
            redirect('/');
        }

        $email    = trim((string) input('email'));
        $password = (string) input('password');

        if (tooManyAttempts('login', 5, 60)) {
            logWarning('Login rate limit hit', ['email' => $email]);
            flashFormState(
                ['form' => 'Слишком много попыток входа. Подождите минуту и попробуйте снова.'],
                ['email' => $email]
            );
            redirect('/login');
        }

        $errors = validateLogin(['email' => $email, 'password' => $password]);
        if ($errors !== []) {
            flashFormState($errors, ['email' => $email]);
            redirect('/login');
        }

        $user = \App\Models\findByEmail($email);
        $hash = $user['password_hash'] ?? self::DUMMY_HASH;

        $passwordOk = password_verify($password, $hash);
        $ok = $user !== null && $passwordOk && (int) $user['is_active'] === 1;

        if (!$ok) {
            hitRateLimit('login');
            logWarning('Failed login attempt', ['email' => $email]);
            flashFormState(['form' => 'Неверный email или пароль.'], ['email' => $email]);
            redirect('/login');
        }

        clearRateLimit('login');
        loginUser($user);
        redirect('/');
    }

    public function showRegister(): void
    {
        if (isAuthenticated()) {
            redirect('/');
        }
        render('shop/auth/register', takeFormState());
    }

    public function register(): void
    {
        requireCsrf();
        if (isAuthenticated()) {
            redirect('/');
        }

        $name     = trim((string) input('name'));
        $email    = trim((string) input('email'));
        $password = (string) input('password');

        $errors = validateRegistration([
            'name'     => $name,
            'email'    => $email,
            'password' => $password,
        ]);

        // Занятый email не раскрываем прямым текстом — единый generic-ответ.
        if ($errors === [] && \App\Models\emailExists($email)) {
            $errors['form'] = 'Не удалось завершить регистрацию. Проверьте данные и попробуйте снова.';
        }

        if ($errors !== []) {
            flashFormState($errors, ['name' => $name, 'email' => $email]);
            redirect('/register');
        }

        $id = \App\Models\createCustomer(
            $name,
            $email,
            password_hash($password, PASSWORD_BCRYPT)
        );

        loginUser(['id' => $id, 'role' => 'customer']);
        redirect('/');
    }

    public function logout(): void
    {
        requireCsrf();
        logoutUser();
        redirect('/');
    }
}
