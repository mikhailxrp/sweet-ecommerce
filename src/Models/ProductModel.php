<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Работа с таблицей `products`. Только SQL через PDO, возвращает плоские
 * массивы. `getPdo()` — глобальный хелпер из Core/Database.php.
 */

/**
 * Последние активные товары для превью-сетки (`/catalog`). Без
 * сортировки/фильтров/пагинации — та логика в Таске 3.
 *
 * Видимость: `is_active = 1`, `moderation_status = 'approved'`,
 * кондитер `status = 'approved'`. Главное фото гарантированно есть у
 * каждого товара (инвариант сида Таска 1), поэтому JOIN, не LEFT JOIN.
 *
 * @return list<array{
 *     id:int,name:string,slug:string,price:string,old_price:?string,
 *     has_variants:int,stock_quantity:int,vendor_name:string,
 *     image_path:string,image_alt:string
 * }>
 */
function findLatestActive(int $limit): array
{
    $stmt = getPdo()->prepare(
        "SELECT p.id, p.name, p.slug, p.price, p.old_price, p.has_variants, p.stock_quantity,
                v.name AS vendor_name, pi.path AS image_path, pi.alt AS image_alt
         FROM products p
         JOIN vendors v ON v.id = p.vendor_id AND v.status = 'approved'
         JOIN product_images pi ON pi.product_id = p.id AND pi.is_main = 1
         WHERE p.is_active = 1 AND p.moderation_status = 'approved'
         ORDER BY p.created_at DESC
         LIMIT :limit"
    );
    $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
    $stmt->execute();

    return array_map(
        static function (array $row): array {
            $row['id'] = (int) $row['id'];
            $row['has_variants'] = (int) $row['has_variants'];
            $row['stock_quantity'] = (int) $row['stock_quantity'];
            return $row;
        },
        $stmt->fetchAll()
    );
}

/**
 * Листинг категории (и её подкатегорий) — Таск 3. `$orderBySql` приходит
 * только из sortSql() (Core/catalog.php), уже провалидированного белым
 * списком в контроллере, поэтому подстановка в SQL безопасна.
 *
 * @param list<int> $categoryIds
 * @return list<array{
 *     id:int,name:string,slug:string,price:string,old_price:?string,
 *     has_variants:int,stock_quantity:int,vendor_name:string,
 *     image_path:string,image_alt:string
 * }>
 */
function findByCategoryIds(array $categoryIds, string $orderBySql, int $limit, int $offset): array
{
    if ($categoryIds === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));

    $stmt = getPdo()->prepare(
        "SELECT p.id, p.name, p.slug, p.price, p.old_price, p.has_variants, p.stock_quantity,
                v.name AS vendor_name, pi.path AS image_path, pi.alt AS image_alt
         FROM products p
         JOIN vendors v ON v.id = p.vendor_id AND v.status = 'approved'
         JOIN product_images pi ON pi.product_id = p.id AND pi.is_main = 1
         WHERE p.category_id IN ({$placeholders})
             AND p.is_active = 1 AND p.moderation_status = 'approved'
         ORDER BY {$orderBySql}
         LIMIT ? OFFSET ?"
    );

    $position = 1;
    foreach ($categoryIds as $categoryId) {
        $stmt->bindValue($position++, $categoryId, \PDO::PARAM_INT);
    }
    $stmt->bindValue($position++, $limit, \PDO::PARAM_INT);
    $stmt->bindValue($position, $offset, \PDO::PARAM_INT);
    $stmt->execute();

    return array_map(
        static function (array $row): array {
            $row['id'] = (int) $row['id'];
            $row['has_variants'] = (int) $row['has_variants'];
            $row['stock_quantity'] = (int) $row['stock_quantity'];
            return $row;
        },
        $stmt->fetchAll()
    );
}

/**
 * Число видимых товаров в списке категорий — для пагинации.
 *
 * @param list<int> $categoryIds
 */
function countByCategoryIds(array $categoryIds): int
{
    if ($categoryIds === []) {
        return 0;
    }

    $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));

    $stmt = getPdo()->prepare(
        "SELECT COUNT(*)
         FROM products p
         JOIN vendors v ON v.id = p.vendor_id AND v.status = 'approved'
         WHERE p.category_id IN ({$placeholders})
             AND p.is_active = 1 AND p.moderation_status = 'approved'"
    );
    $stmt->execute(array_values($categoryIds));

    return (int) $stmt->fetchColumn();
}
