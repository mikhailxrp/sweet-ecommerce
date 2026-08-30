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
 * Листинг категории (и её подкатегорий) — Таск 3, фильтры — Таск 4.
 * `$orderBySql` приходит только из sortSql() (Core/catalog.php), уже
 * провалидированного белым списком в контроллере, поэтому подстановка в
 * SQL безопасна. `$filterConditions` — результат filterConditions()
 * (Core/catalog.php): готовые `AND`-фрагменты с `?` и bound-параметры в
 * том же порядке, строка из query сюда никогда не подставляется напрямую.
 *
 * @param list<int> $categoryIds
 * @param array{sql:list<string>,params:list<array{value:int|string,type:int}>} $filterConditions
 * @return list<array{
 *     id:int,name:string,slug:string,price:string,old_price:?string,
 *     has_variants:int,stock_quantity:int,vendor_name:string,
 *     image_path:string,image_alt:string
 * }>
 */
function findByCategoryIds(
    array $categoryIds,
    string $orderBySql,
    int $limit,
    int $offset,
    array $filterConditions = ['sql' => [], 'params' => []]
): array {
    if ($categoryIds === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
    $filterSql = implode('', array_map(static fn (string $c): string => " AND {$c}", $filterConditions['sql']));

    $stmt = getPdo()->prepare(
        "SELECT p.id, p.name, p.slug, p.price, p.old_price, p.has_variants, p.stock_quantity,
                v.name AS vendor_name, pi.path AS image_path, pi.alt AS image_alt
         FROM products p
         JOIN vendors v ON v.id = p.vendor_id AND v.status = 'approved'
         JOIN product_images pi ON pi.product_id = p.id AND pi.is_main = 1
         WHERE p.category_id IN ({$placeholders})
             AND p.is_active = 1 AND p.moderation_status = 'approved'
             {$filterSql}
         ORDER BY {$orderBySql}
         LIMIT ? OFFSET ?"
    );

    $position = 1;
    foreach ($categoryIds as $categoryId) {
        $stmt->bindValue($position++, $categoryId, \PDO::PARAM_INT);
    }
    foreach ($filterConditions['params'] as $param) {
        $stmt->bindValue($position++, $param['value'], $param['type']);
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
 * Число видимых товаров в списке категорий — для пагинации. `$filterConditions` —
 * см. findByCategoryIds().
 *
 * @param list<int> $categoryIds
 * @param array{sql:list<string>,params:list<array{value:int|string,type:int}>} $filterConditions
 */
function countByCategoryIds(array $categoryIds, array $filterConditions = ['sql' => [], 'params' => []]): int
{
    if ($categoryIds === []) {
        return 0;
    }

    $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
    $filterSql = implode('', array_map(static fn (string $c): string => " AND {$c}", $filterConditions['sql']));

    $stmt = getPdo()->prepare(
        "SELECT COUNT(*)
         FROM products p
         JOIN vendors v ON v.id = p.vendor_id AND v.status = 'approved'
         WHERE p.category_id IN ({$placeholders})
             AND p.is_active = 1 AND p.moderation_status = 'approved'
             {$filterSql}"
    );

    $position = 1;
    foreach ($categoryIds as $categoryId) {
        $stmt->bindValue($position++, $categoryId, \PDO::PARAM_INT);
    }
    foreach ($filterConditions['params'] as $param) {
        $stmt->bindValue($position++, $param['value'], $param['type']);
    }
    $stmt->execute();

    return (int) $stmt->fetchColumn();
}

/**
 * Кондитеры с видимыми товарами в списке категорий — чекбоксы фильтра
 * «Кондитер» со счётчиком. Не учитывает уже выбранные фильтры (решение
 * таска в TASK.md) — считается по всей категории.
 *
 * @param list<int> $categoryIds
 * @return list<array{id:int,name:string,count:int}>
 */
function findVendorFacets(array $categoryIds): array
{
    if ($categoryIds === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));

    $stmt = getPdo()->prepare(
        "SELECT v.id, v.name, COUNT(p.id) AS count
         FROM vendors v
         JOIN products p ON p.vendor_id = v.id
             AND p.category_id IN ({$placeholders})
             AND p.is_active = 1 AND p.moderation_status = 'approved'
         WHERE v.status = 'approved'
         GROUP BY v.id, v.name
         ORDER BY v.name"
    );
    $stmt->execute(array_values($categoryIds));

    return array_map(
        static function (array $row): array {
            $row['id'] = (int) $row['id'];
            $row['count'] = (int) $row['count'];
            return $row;
        },
        $stmt->fetchAll()
    );
}

/**
 * Границы цены видимых товаров категории — для инициализации слайдера
 * фильтра. Не учитывает уже выбранные фильтры, чтобы диапазон слайдера не
 * "сжимался" вслед за собственным же выбором.
 *
 * @param list<int> $categoryIds
 * @return array{min:string,max:string}
 */
function findPriceRange(array $categoryIds): array
{
    if ($categoryIds === []) {
        return ['min' => '0.00', 'max' => '0.00'];
    }

    $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));

    $stmt = getPdo()->prepare(
        "SELECT MIN(p.price) AS min_price, MAX(p.price) AS max_price
         FROM products p
         JOIN vendors v ON v.id = p.vendor_id AND v.status = 'approved'
         WHERE p.category_id IN ({$placeholders})
             AND p.is_active = 1 AND p.moderation_status = 'approved'"
    );
    $stmt->execute(array_values($categoryIds));
    $row = $stmt->fetch();

    if ($row === false || $row['min_price'] === null) {
        return ['min' => '0.00', 'max' => '0.00'];
    }

    return ['min' => (string) $row['min_price'], 'max' => (string) $row['max_price']];
}
