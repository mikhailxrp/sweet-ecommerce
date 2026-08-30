<?php

declare(strict_types=1);

namespace App\Controllers;

require_once ROOT_PATH . '/src/Core/catalog.php';
require_once ROOT_PATH . '/src/Models/ProductModel.php';
require_once ROOT_PATH . '/src/Models/SettingsModel.php';

/**
 * Поиск товаров (`/search`) и JSON-подсказки (`/search/suggest`) для
 * живого поиска в шапке.
 */
final class SearchController
{
    private const DEFAULT_PER_PAGE = '24';
    private const SUGGEST_LIMIT = 5;

    public function index(): void
    {
        $searchQuery = normalizeSearchQuery((string) input('q'));
        $strategy    = resolveSearchStrategy($searchQuery);

        $perPage       = (int) \App\Models\getSetting('products_per_page', self::DEFAULT_PER_PAGE);
        $sortKey       = resolveSortKey((string) input('sort'));
        $requestedPage = resolvePage((string) input('page'));

        $total      = \App\Models\countSearchResults($strategy, $searchQuery);
        $pagesCount = totalPages($total, $perPage);
        $page       = $requestedPage > $pagesCount ? 1 : $requestedPage;
        $offset     = ($page - 1) * $perPage;

        $products = \App\Models\searchProducts($strategy, $searchQuery, sortSql($sortKey), $perPage, $offset);

        render('shop/search', [
            'searchQuery' => $searchQuery,
            'products'    => $products,
            'sortKey'     => $sortKey,
            'sortOptions' => sortOptions(),
            'page'        => $page,
            'totalPages'  => $pagesCount,
            'total'       => $total,
            'query'       => $_GET,
        ]);
    }

    public function suggest(): void
    {
        $searchQuery = normalizeSearchQuery((string) input('q'));
        $strategy    = resolveSearchStrategy($searchQuery);

        $products = $strategy === 'empty'
            ? []
            : \App\Models\searchProducts($strategy, $searchQuery, sortSql('popular'), self::SUGGEST_LIMIT, 0);

        $suggestions = array_map(
            static fn (array $product): array => [
                'name'  => $product['name'],
                'slug'  => $product['slug'],
                'price' => formatPrice($product['price']),
                'image' => '/uploads/' . $product['image_path'],
            ],
            $products
        );

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($suggestions, JSON_UNESCAPED_UNICODE);
    }
}
