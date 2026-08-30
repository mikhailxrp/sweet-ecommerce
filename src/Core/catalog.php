<?php

declare(strict_types=1);

/**
 * Чистые хелперы листинга категории: сортировка, пагинация, сборка
 * query-строки. Без обращений к БД — только преобразования данных.
 */

/**
 * Единый список сортировок: ключ → подпись для UI. Источник истины и
 * для валидации (resolveSortKey()), и для рендера dropdown — один
 * список сортировок не разъезжается с другим.
 *
 * @return array<string,string>
 */
function sortOptions(): array
{
    return [
        'popular'    => 'Популярные',
        'new'        => 'Новые',
        'price_asc'  => 'Цена ↑',
        'price_desc' => 'Цена ↓',
        'rating'     => 'Рейтинг',
    ];
}

/**
 * Валидирует ключ сортировки по белому списку sortOptions(). Неизвестное
 * значение (в том числе из URL) тихо падает на дефолт — не ошибка.
 */
function resolveSortKey(string $input): string
{
    return array_key_exists($input, sortOptions()) ? $input : 'popular';
}

/**
 * SQL ORDER BY для уже провалидированного ключа сортировки. Строка из
 * URL сюда не попадает — только ключ, прошедший resolveSortKey().
 */
function sortSql(string $key): string
{
    $map = [
        'popular'    => 'p.sales_count DESC',
        'new'        => 'p.created_at DESC',
        'price_asc'  => 'p.price ASC',
        'price_desc' => 'p.price DESC',
        'rating'     => 'p.rating_avg DESC',
    ];

    return $map[$key] ?? $map['popular'];
}

/**
 * Нормализует номер страницы из query-параметра: нечисловое значение,
 * ноль или отрицательное число → 1-я страница.
 */
function resolvePage(string $input): int
{
    if (!ctype_digit($input)) {
        return 1;
    }

    $page = (int) $input;

    return $page >= 1 ? $page : 1;
}

/** Количество страниц по общему числу товаров и размеру страницы. Минимум 1. */
function totalPages(int $totalItems, int $perPage): int
{
    if ($totalItems <= 0 || $perPage <= 0) {
        return 1;
    }

    return (int) ceil($totalItems / $perPage);
}

/**
 * Query-строка с заменой/удалением части параметров, остальные из
 * $currentQuery сохраняются как есть. Значение null в $overrides удаляет
 * параметр (например сброс `page` при смене сортировки).
 *
 * @param array<string,mixed> $currentQuery
 * @param array<string,mixed> $overrides
 */
function buildQuery(array $currentQuery, array $overrides): string
{
    $merged = array_merge($currentQuery, $overrides);
    $merged = array_filter(
        $merged,
        static fn (mixed $value): bool => $value !== null && $value !== ''
    );

    return $merged === [] ? '' : '?' . http_build_query($merged);
}

// ─── Фильтры листинга категории (Таск 4) ────────────────────────────────
// Валидация значений из query по белому списку/границам, без обращения к
// БД. Результат parseFilters() уходит в filterConditions() — единственное
// место, где имена SQL-фрагментов и bound-параметры собираются вместе.

/** Ценовая граница: числовая строка ≥ 0 → `"1234.00"`; иначе null. */
function parsePriceBound(mixed $value): ?string
{
    if (!is_string($value) && !is_int($value) && !is_float($value)) {
        return null;
    }

    $normalized = is_string($value) ? trim($value) : (string) $value;
    if ($normalized === '' || !is_numeric($normalized)) {
        return null;
    }

    $float = (float) $normalized;

    return $float >= 0 ? number_format($float, 2, '.', '') : null;
}

/** Id кондитеров из `vendor[]`: только положительные целые, без дублей. */
function parseVendorIds(mixed $value): array
{
    if (!is_array($value)) {
        return [];
    }

    $ids = [];
    foreach ($value as $item) {
        if (is_int($item) && $item > 0) {
            $ids[] = $item;
            continue;
        }
        if (is_string($item) && ctype_digit($item) && (int) $item > 0) {
            $ids[] = (int) $item;
        }
    }

    return array_values(array_unique($ids));
}

/** Чекбокс-флаг (`in_stock`, `sugar_free`): истина только на строгое `"1"`. */
function parseBoolFlag(mixed $value): bool
{
    return $value === '1' || $value === 1 || $value === true;
}

/** Весовой бакет по белому списку; неизвестное значение → null (фильтр не применяется). */
function parseWeightBucket(mixed $value): ?string
{
    $allowed = ['lt500', '500to1000', 'gt1000'];

    return is_string($value) && in_array($value, $allowed, true) ? $value : null;
}

/**
 * Минимальный рейтинг из отмеченных чекбоксов `rating[]` (каждый — «от N
 * звёзд»): несколько отметок сводятся к одному условию по наименьшей.
 * Значения вне 1..5 отбрасываются.
 */
function parseMinRating(mixed $value): ?int
{
    if (!is_array($value)) {
        return null;
    }

    $valid = [];
    foreach ($value as $item) {
        $intValue = match (true) {
            is_int($item) => $item,
            is_string($item) && ctype_digit($item) => (int) $item,
            default => null,
        };
        if ($intValue !== null && $intValue >= 1 && $intValue <= 5) {
            $valid[] = $intValue;
        }
    }

    return $valid === [] ? null : min($valid);
}

/**
 * Разбирает и валидирует все фильтры листинга из query (`$_GET`).
 * Каждое поле — результат отдельного парсера по белому списку/границам;
 * недопустимое значение тихо превращается в null/false/[] и просто не
 * попадёт в filterConditions().
 *
 * @param array<string,mixed> $query
 * @return array{
 *     price_min:?string,price_max:?string,vendor_ids:list<int>,
 *     in_stock:bool,weight_bucket:?string,sugar_free:bool,min_rating:?int
 * }
 */
function parseFilters(array $query): array
{
    $priceMin = parsePriceBound($query['price_min'] ?? null);
    $priceMax = parsePriceBound($query['price_max'] ?? null);

    if ($priceMin !== null && $priceMax !== null && (float) $priceMin > (float) $priceMax) {
        [$priceMin, $priceMax] = [$priceMax, $priceMin];
    }

    return [
        'price_min'     => $priceMin,
        'price_max'     => $priceMax,
        'vendor_ids'    => parseVendorIds($query['vendor'] ?? null),
        'in_stock'      => parseBoolFlag($query['in_stock'] ?? null),
        'weight_bucket' => parseWeightBucket($query['weight'] ?? null),
        'sugar_free'    => parseBoolFlag($query['sugar_free'] ?? null),
        'min_rating'    => parseMinRating($query['rating'] ?? null),
    ];
}

/**
 * SQL-условия провалидированных фильтров: список `AND`-фрагментов с `?` и
 * параллельный список bound-параметров (значение + PDO-тип) в том же
 * порядке, в котором `?` встречаются в `sql`. Строка из query сюда не
 * попадает — только уже проверенные значения из parseFilters().
 *
 * @param array{
 *     price_min:?string,price_max:?string,vendor_ids:list<int>,
 *     in_stock:bool,weight_bucket:?string,sugar_free:bool,min_rating:?int
 * } $filters
 * @return array{sql:list<string>,params:list<array{value:int|string,type:int}>}
 */
function filterConditions(array $filters): array
{
    $sql = [];
    $params = [];

    if ($filters['price_min'] !== null) {
        $sql[] = 'p.price >= ?';
        $params[] = ['value' => $filters['price_min'], 'type' => \PDO::PARAM_STR];
    }

    if ($filters['price_max'] !== null) {
        $sql[] = 'p.price <= ?';
        $params[] = ['value' => $filters['price_max'], 'type' => \PDO::PARAM_STR];
    }

    if ($filters['vendor_ids'] !== []) {
        $placeholders = implode(',', array_fill(0, count($filters['vendor_ids']), '?'));
        $sql[] = "p.vendor_id IN ({$placeholders})";
        foreach ($filters['vendor_ids'] as $vendorId) {
            $params[] = ['value' => $vendorId, 'type' => \PDO::PARAM_INT];
        }
    }

    if ($filters['in_stock']) {
        $sql[] = '((p.has_variants = 0 AND p.stock_quantity > 0)'
            . ' OR (p.has_variants = 1 AND EXISTS ('
            . 'SELECT 1 FROM product_variants pv WHERE pv.product_id = p.id AND pv.stock_quantity > 0'
            . ')))';
    }

    if ($filters['weight_bucket'] !== null) {
        $sql[] = match ($filters['weight_bucket']) {
            'lt500'      => 'p.weight_grams < 500',
            '500to1000'  => 'p.weight_grams BETWEEN 500 AND 1000',
            'gt1000'     => 'p.weight_grams > 1000',
        };
    }

    if ($filters['sugar_free']) {
        $sql[] = 'p.is_sugar_free = 1';
    }

    if ($filters['min_rating'] !== null) {
        $sql[] = 'p.rating_avg >= ?';
        $params[] = ['value' => $filters['min_rating'], 'type' => \PDO::PARAM_INT];
    }

    return ['sql' => $sql, 'params' => $params];
}

/** Нормализует `?view=` → `grid`|`list`. Неизвестное значение → `grid`. */
function resolveView(string $input): string
{
    return trim($input) === 'list' ? 'list' : 'grid';
}
