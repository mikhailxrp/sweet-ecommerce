<?php

declare(strict_types=1);

namespace App\Controllers;

require_once ROOT_PATH . '/src/Models/ProductModel.php';

/**
 * Карточка товара (`/product/{slug}`).
 */
final class ProductController
{
    private const RELATED_LIMIT = 8;

    public function show(string $slug): void
    {
        $product = \App\Models\findBySlugForDetail($slug);
        if ($product === null) {
            http_response_code(404);
            render('errors/404');
            return;
        }

        \App\Models\incrementViewsCount($product['id']);

        render('shop/product/show', [
            'product'    => $product,
            'images'     => \App\Models\findImagesByProductId($product['id']),
            'variants'   => \App\Models\findVariantsByProductId($product['id']),
            'attributes' => \App\Models\findAttributesByProductId($product['id']),
            'related'    => \App\Models\findRelatedByCategory(
                $product['category_id'],
                $product['id'],
                self::RELATED_LIMIT
            ),
        ]);
    }
}
