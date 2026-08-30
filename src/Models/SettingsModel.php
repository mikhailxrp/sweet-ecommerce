<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Работа с таблицей `settings` (только чтение). `getPdo()` — глобальный
 * хелпер из Core/Database.php.
 */

/** Значение настройки по ключу. $default, если ключ не засеян. */
function getSetting(string $key, string $default): string
{
    $stmt = getPdo()->prepare('SELECT setting_value FROM settings WHERE setting_key = :key');
    $stmt->execute(['key' => $key]);
    $value = $stmt->fetchColumn();

    return $value === false || $value === null ? $default : (string) $value;
}
