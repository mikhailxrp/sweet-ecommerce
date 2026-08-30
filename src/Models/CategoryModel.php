<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Работа с таблицей `categories`. Только SQL через PDO, возвращает плоские
 * массивы. `getPdo()` — глобальный хелпер из Core/Database.php.
 */

/**
 * Корневые категории (`parent_id IS NULL`) со счётчиком их прямых
 * товаров — без учёта подкатегорий (см. решение таска в TASK.md).
 * Один запрос, без N+1.
 *
 * Считаются только товары, видимые на витрине: `is_active = 1`,
 * `moderation_status = 'approved'`, кондитер `status = 'approved'`.
 *
 * @return list<array{id:int,name:string,slug:string,image_path:string,product_count:int}>
 */
function findRootWithProductCounts(): array
{
    $sql = "SELECT c.id, c.name, c.slug, c.image_path,
                   COUNT(CASE WHEN p.id IS NOT NULL AND v.status = 'approved' THEN 1 END) AS product_count
            FROM categories c
            LEFT JOIN products p
                ON p.category_id = c.id
                AND p.is_active = 1
                AND p.moderation_status = 'approved'
            LEFT JOIN vendors v ON v.id = p.vendor_id
            WHERE c.parent_id IS NULL
                AND c.is_active = 1
            GROUP BY c.id
            ORDER BY c.sort_order";

    $rows = getPdo()->query($sql)->fetchAll();

    return array_map(
        static function (array $row): array {
            $row['id'] = (int) $row['id'];
            $row['product_count'] = (int) $row['product_count'];
            return $row;
        },
        $rows
    );
}

/**
 * Категория по slug вместе с родителем (для хлебных крошек). NULL, если
 * такого slug нет или категория неактивна.
 *
 * @return array{
 *     id:int,name:string,slug:string,description:?string,image_path:string,
 *     parent_id:?int,parent_name:?string,parent_slug:?string
 * }|null
 */
function findBySlug(string $slug): ?array
{
    $stmt = getPdo()->prepare(
        'SELECT c.id, c.name, c.slug, c.description, c.image_path,
                parent.id AS parent_id, parent.name AS parent_name, parent.slug AS parent_slug
         FROM categories c
         LEFT JOIN categories parent ON parent.id = c.parent_id
         WHERE c.slug = :slug AND c.is_active = 1
         LIMIT 1'
    );
    $stmt->execute(['slug' => $slug]);
    $row = $stmt->fetch();

    if ($row === false) {
        return null;
    }

    $row['id'] = (int) $row['id'];
    $row['parent_id'] = $row['parent_id'] !== null ? (int) $row['parent_id'] : null;

    return $row;
}

/**
 * Id самой категории и её прямых потомков — для листинга, который
 * должен включать товары подкатегорий. Вложенность в проекте
 * одноуровневая, рекурсия по дереву не нужна.
 *
 * @return list<int>
 */
function findChildIds(int $categoryId): array
{
    $stmt = getPdo()->prepare(
        'SELECT id FROM categories WHERE id = :id OR parent_id = :parentId'
    );
    $stmt->execute(['id' => $categoryId, 'parentId' => $categoryId]);

    return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
}

/**
 * Дерево категорий для сайдбара: корневые категории со вложенным
 * списком их подкатегорий. Один запрос, группировка по parent_id в PHP.
 *
 * @return list<array{id:int,name:string,slug:string,children:list<array{id:int,name:string,slug:string}>}>
 */
function findTreeForSidebar(): array
{
    $rows = getPdo()->query(
        'SELECT id, parent_id, name, slug
         FROM categories
         WHERE is_active = 1
         ORDER BY sort_order'
    )->fetchAll();

    $byParent = [];
    foreach ($rows as $row) {
        $parentKey = $row['parent_id'] === null ? 'root' : (string) $row['parent_id'];
        $byParent[$parentKey][] = [
            'id'   => (int) $row['id'],
            'name' => $row['name'],
            'slug' => $row['slug'],
        ];
    }

    $tree = [];
    foreach ($byParent['root'] ?? [] as $root) {
        $root['children'] = $byParent[(string) $root['id']] ?? [];
        $tree[] = $root;
    }

    return $tree;
}
