<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Работа с таблицей `users`. Только SQL через PDO prepared statements,
 * возвращает плоские массивы. Никакого HTML, редиректов и бизнес-логики
 * (проверка пароля/хэширование — на слое контроллера и Core/auth).
 *
 * `getPdo()` — глобальный хелпер из Core/Database.php.
 */

/**
 * Найти пользователя по email. NULL, если такого нет.
 *
 * @return array{id:int,name:string,email:string,password_hash:string,role:string,is_active:int}|null
 */
function findByEmail(string $email): ?array
{
    $stmt = getPdo()->prepare(
        'SELECT id, name, email, password_hash, role, is_active
         FROM users WHERE email = :email LIMIT 1'
    );
    $stmt->execute(['email' => $email]);
    $row = $stmt->fetch();

    return $row === false ? null : $row;
}

/**
 * Найти пользователя по id. NULL, если такого нет.
 *
 * @return array{id:int,name:string,email:string,role:string,is_active:int}|null
 */
function findById(int $id): ?array
{
    $stmt = getPdo()->prepare(
        'SELECT id, name, email, role, is_active
         FROM users WHERE id = :id LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row === false ? null : $row;
}

/** Есть ли пользователь с таким email. */
function emailExists(string $email): bool
{
    $stmt = getPdo()->prepare('SELECT 1 FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);

    return $stmt->fetchColumn() !== false;
}

/**
 * Создать покупателя (`role = 'customer'`). Возвращает id новой записи.
 * Пароль приходит уже захэшированным — модель хэшированием не занимается.
 */
function createCustomer(string $name, string $email, string $passwordHash): int
{
    $stmt = getPdo()->prepare(
        'INSERT INTO users (name, email, password_hash, role)
         VALUES (:name, :email, :password_hash, :role)'
    );
    $stmt->execute([
        'name'          => $name,
        'email'         => $email,
        'password_hash' => $passwordHash,
        'role'          => 'customer',
    ]);

    return (int) getPdo()->lastInsertId();
}
