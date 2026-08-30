<?php

declare(strict_types=1);

namespace App\Controllers;

require_once ROOT_PATH . '/src/Core/catalog.php';
require_once ROOT_PATH . '/src/Models/CategoryModel.php';
require_once ROOT_PATH . '/src/Models/ProductModel.php';
require_once ROOT_PATH . '/src/Models/SettingsModel.php';

/**
 * Каталог витрины: «Все категории» с превью последних товаров и листинг
 * конкретной категории с сортировкой и пагинацией.
 */
final class CatalogController
{
    private const PREVIEW_LIMIT = 24;
    private const DEFAULT_PER_PAGE = '24';

    public function index(): void
    {
        render('shop/catalog/index', [
            'categories' => \App\Models\findRootWithProductCounts(),
            'products'   => \App\Models\findLatestActive(self::PREVIEW_LIMIT),
        ]);
    }

    public function category(string $slug): void
    {
        $category = \App\Models\findBySlug($slug);
        if ($category === null) {
            http_response_code(404);
            render('errors/404');
            return;
        }

        $childIds = \App\Models\findChildIds($category['id']);
        $perPage  = (int) \App\Models\getSetting('products_per_page', self::DEFAULT_PER_PAGE);

        $sortKey       = resolveSortKey((string) input('sort'));
        $requestedPage = resolvePage((string) input('page'));

        $total      = \App\Models\countByCategoryIds($childIds);
        $pagesCount = totalPages($total, $perPage);
        $page       = $requestedPage > $pagesCount ? 1 : $requestedPage;
        $offset     = ($page - 1) * $perPage;

        $products = \App\Models\findByCategoryIds($childIds, sortSql($sortKey), $perPage, $offset);

        render('shop/catalog/category', [
            'category'    => $category,
            'sidebarTree' => \App\Models\findTreeForSidebar(),
            'products'    => $products,
            'sortKey'     => $sortKey,
            'sortOptions' => sortOptions(),
            'page'        => $page,
            'totalPages'  => $pagesCount,
            'total'       => $total,
            'query'       => $_GET,
        ]);
    }
}
