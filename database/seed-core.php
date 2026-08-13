<?php

declare(strict_types=1);

/**
 * Сид фундамента — минимум, без которого не войти в админку.
 *
 * Запускать после database/install.php:
 *   php database/seed-core.php
 *
 * Создаёт одного пользователя-админа. Логин и пароль берутся из .env
 * (SEED_ADMIN_EMAIL / SEED_ADMIN_PASSWORD) — секрета в репозитории нет.
 *
 * Настройки (settings) и роль superadmin уже засеяны самим install.php —
 * здесь они намеренно не дублируются.
 *
 * Идемпотентно: повторный запуск не создаёт второго админа.
 * Демо-контент (категории, товары, кондитеры) — в database/seed.php (Фаза 1).
 */

require_once dirname(__DIR__) . '/config/config.php';

$email    = trim((string) env('SEED_ADMIN_EMAIL'));
$password = (string) env('SEED_ADMIN_PASSWORD');
$name     = trim((string) env('SEED_ADMIN_NAME', 'Администратор'));

if ($email === '' || $password === '') {
    echo "⚠️  Заполни SEED_ADMIN_EMAIL и SEED_ADMIN_PASSWORD в .env.\n";
    exit(1);
}

if (mb_strlen($password) < 8) {
    echo "⚠️  SEED_ADMIN_PASSWORD должен быть не короче 8 символов.\n";
    exit(1);
}

$pdo = getPdo();

$existing = $pdo->prepare('SELECT id FROM users WHERE email = :email');
$existing->execute(['email' => $email]);
$existingId = $existing->fetchColumn();

if ($existingId !== false) {
    echo "ℹ️  Админ с email {$email} уже существует (id {$existingId}) — пропуск.\n";
    exit(0);
}

// Привязываем к готовой системной роли superadmin, если она есть (её сеет install.php).
$roleId = $pdo->query("SELECT id FROM roles WHERE slug = 'superadmin'")->fetchColumn();

$insert = $pdo->prepare(
    'INSERT INTO users (name, email, password_hash, role, role_id, is_active)
     VALUES (:name, :email, :hash, :role, :role_id, 1)'
);

$insert->execute([
    'name'    => $name,
    'email'   => $email,
    'hash'    => password_hash($password, PASSWORD_DEFAULT),
    'role'    => 'admin',
    'role_id' => $roleId === false ? null : (int) $roleId,
]);

echo "✅ Админ создан: {$email} (id {$pdo->lastInsertId()}).\n";
