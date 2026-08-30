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

// ─── Карточка товара (Таск 5) ────────────────────────────────────────────

/**
 * Товар по slug для карточки — с категорией (для крошек) и кондитером.
 * Видимость: `is_active = 1`, `moderation_status = 'approved'`, кондитер
 * `status = 'approved'` — как и везде в каталоге.
 *
 * @return array{
 *     id:int,sku:string,name:string,slug:string,short_description:?string,
 *     description:?string,price:string,old_price:?string,has_variants:int,
 *     stock_quantity:int,unit:string,weight_grams:?int,composition:?string,
 *     allergens:?string,calories_per_100g:?int,shelf_life_hours:?int,
 *     storage_conditions:?string,lead_time_hours:int,rating_avg:string,
 *     reviews_count:int,vendor_name:string,category_id:int,
 *     category_name:string,category_slug:string
 * }|null
 */
function findBySlugForDetail(string $slug): ?array
{
    $stmt = getPdo()->prepare(
        "SELECT p.id, p.sku, p.name, p.slug, p.short_description, p.description,
                p.price, p.old_price, p.has_variants, p.stock_quantity, p.unit,
                p.weight_grams, p.composition, p.allergens, p.calories_per_100g,
                p.shelf_life_hours, p.storage_conditions, p.lead_time_hours,
                p.rating_avg, p.reviews_count,
                v.name AS vendor_name,
                c.id AS category_id, c.name AS category_name, c.slug AS category_slug
         FROM products p
         JOIN vendors v ON v.id = p.vendor_id AND v.status = 'approved'
         JOIN categories c ON c.id = p.category_id
         WHERE p.slug = :slug AND p.is_active = 1 AND p.moderation_status = 'approved'
         LIMIT 1"
    );
    $stmt->execute(['slug' => $slug]);
    $row = $stmt->fetch();

    if ($row === false) {
        return null;
    }

    $row['id'] = (int) $row['id'];
    $row['has_variants'] = (int) $row['has_variants'];
    $row['stock_quantity'] = (int) $row['stock_quantity'];
    $row['weight_grams'] = $row['weight_grams'] !== null ? (int) $row['weight_grams'] : null;
    $row['calories_per_100g'] = $row['calories_per_100g'] !== null ? (int) $row['calories_per_100g'] : null;
    $row['shelf_life_hours'] = $row['shelf_life_hours'] !== null ? (int) $row['shelf_life_hours'] : null;
    $row['lead_time_hours'] = (int) $row['lead_time_hours'];
    $row['reviews_count'] = (int) $row['reviews_count'];
    $row['category_id'] = (int) $row['category_id'];

    return $row;
}

/**
 * Фото товара для галереи, до 6 штук, по порядку.
 *
 * @return list<array{path:string,alt:?string}>
 */
function findImagesByProductId(int $productId): array
{
    $stmt = getPdo()->prepare(
        'SELECT path, alt
         FROM product_images
         WHERE product_id = :productId
         ORDER BY sort_order
         LIMIT 6'
    );
    $stmt->execute(['productId' => $productId]);

    return $stmt->fetchAll();
}

/**
 * Активные варианты товара для селектора — своя цена, остаток, опционально
 * своё фото. `image_path` нормализован в null, если в БД пустая строка.
 *
 * @return list<array{
 *     id:int,sku:string,name:string,price:string,old_price:?string,
 *     stock_quantity:int,image_path:?string
 * }>
 */
function findVariantsByProductId(int $productId): array
{
    $stmt = getPdo()->prepare(
        'SELECT id, sku, name, price, old_price, stock_quantity, image_path
         FROM product_variants
         WHERE product_id = :productId AND is_active = 1
         ORDER BY sort_order'
    );
    $stmt->execute(['productId' => $productId]);

    return array_map(
        static function (array $row): array {
            $row['id'] = (int) $row['id'];
            $row['stock_quantity'] = (int) $row['stock_quantity'];
            $row['image_path'] = ($row['image_path'] ?? '') !== '' ? $row['image_path'] : null;
            return $row;
        },
        $stmt->fetchAll()
    );
}

/**
 * Атрибуты товара без своей цены (начинка, вкус и т.п.) — доп. строки в
 * блоке характеристик.
 *
 * @return list<array{attr_name:string,attr_value:string}>
 */
function findAttributesByProductId(int $productId): array
{
    $stmt = getPdo()->prepare(
        'SELECT attr_name, attr_value
         FROM product_attributes
         WHERE product_id = :productId
         ORDER BY sort_order'
    );
    $stmt->execute(['productId' => $productId]);

    return $stmt->fetchAll();
}

/**
 * Похожие товары — та же категория, текущий товар исключён. Форма строки
 * совпадает с findByCategoryIds() — совместимо с components/product-card.php.
 *
 * @return list<array{
 *     id:int,name:string,slug:string,price:string,old_price:?string,
 *     has_variants:int,stock_quantity:int,vendor_name:string,
 *     image_path:string,image_alt:string
 * }>
 */
function findRelatedByCategory(int $categoryId, int $excludeProductId, int $limit): array
{
    $stmt = getPdo()->prepare(
        "SELECT p.id, p.name, p.slug, p.price, p.old_price, p.has_variants, p.stock_quantity,
                v.name AS vendor_name, pi.path AS image_path, pi.alt AS image_alt
         FROM products p
         JOIN vendors v ON v.id = p.vendor_id AND v.status = 'approved'
         JOIN product_images pi ON pi.product_id = p.id AND pi.is_main = 1
         WHERE p.category_id = :categoryId AND p.id != :excludeProductId
             AND p.is_active = 1 AND p.moderation_status = 'approved'
         ORDER BY p.sales_count DESC
         LIMIT :limit"
    );
    $stmt->bindValue('categoryId', $categoryId, \PDO::PARAM_INT);
    $stmt->bindValue('excludeProductId', $excludeProductId, \PDO::PARAM_INT);
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

/** Инкремент счётчика просмотров при показе карточки товара. */
function incrementViewsCount(int $productId): void
{
    $stmt = getPdo()->prepare('UPDATE products SET views_count = views_count + 1 WHERE id = :productId');
    $stmt->execute(['productId' => $productId]);
}
