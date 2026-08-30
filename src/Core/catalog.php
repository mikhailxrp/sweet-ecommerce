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
