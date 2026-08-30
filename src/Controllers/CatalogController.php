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
    private const VIEW_COOKIE = 'view';
    private const VIEW_COOKIE_LIFETIME = 60 * 60 * 24 * 365;

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

        $filters    = parseFilters($_GET);
        $conditions = filterConditions($filters);

        $total      = \App\Models\countByCategoryIds($childIds, $conditions);
        $pagesCount = totalPages($total, $perPage);
        $page       = $requestedPage > $pagesCount ? 1 : $requestedPage;
        $offset     = ($page - 1) * $perPage;

        $products = \App\Models\findByCategoryIds($childIds, sortSql($sortKey), $perPage, $offset, $conditions);

        $viewMode = $this->resolveAndPersistView();

        // Ключ 'viewMode', не 'view' — у render() уже есть параметр $view
        // (путь к шаблону); extract(..., EXTR_SKIP) молча отбросил бы
        // одноимённый элемент данных и в шаблоне остался бы путь к файлу.
        render('shop/catalog/category', [
            'category'     => $category,
            'sidebarTree'  => \App\Models\findTreeForSidebar(),
            'products'     => $products,
            'sortKey'      => $sortKey,
            'sortOptions'  => sortOptions(),
            'page'         => $page,
            'totalPages'   => $pagesCount,
            'total'        => $total,
            'query'        => $_GET,
            'filters'      => $filters,
            'vendorFacets' => \App\Models\findVendorFacets($childIds),
            'priceRange'   => \App\Models\findPriceRange($childIds),
            'viewMode'     => $viewMode,
        ]);
    }

    /**
     * `?view=` в URL важнее cookie (для прямых ссылок); если параметра нет —
     * берётся ранее сохранённое значение. Cookie обновляется каждый запрос,
     * чтобы срок жизни не истекал у активных посетителей.
     */
    private function resolveAndPersistView(): string
    {
        $requestedView = trim((string) input(self::VIEW_COOKIE));
        $cookieView    = isset($_COOKIE[self::VIEW_COOKIE]) ? (string) $_COOKIE[self::VIEW_COOKIE] : '';

        $view = resolveView($requestedView !== '' ? $requestedView : $cookieView);

        if (!headers_sent()) {
            setcookie(self::VIEW_COOKIE, $view, [
                'expires'  => time() + self::VIEW_COOKIE_LIFETIME,
                'path'     => '/',
                'samesite' => 'Lax',
            ]);
        }

        return $view;
    }
}
