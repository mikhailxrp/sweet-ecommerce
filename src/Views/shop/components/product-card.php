<?php

declare(strict_types=1);

/**
 * Карточка товара. Переиспользуется во всех сетках каталога Фазы 1
 * (превью на /catalog, листинг категории — Таск 3, поиск — Таск 6,
 * главная — Таск 7).
 *
 * Разметка — `.product-box-2` темы Fastkart (index-2.html), без своих
 * CSS-классов: старая цена — нативный `<del>` внутри `<h5>`, как в
 * оригинальной вёрстке; бейдж «нет в наличии» и позиционирование — на
 * утилитах Bootstrap.
 *
 * Ожидает переменную $card — плоский массив товара из ProductModel
 * (findLatestActive() и её аналоги в следующих тасках):
 *   id, name, slug, price, old_price, has_variants, stock_quantity,
 *   vendor_name, image_path, image_alt.
 *
 * `has_variants = 1` товары в БД всегда имеют stock_quantity = 0
 * (источник истины — product_variants, Таск 1) — бейдж «нет в наличии»
 * поэтому показывается только простым товарам.
 */

$outOfStock = $card['has_variants'] === 0 && $card['stock_quantity'] === 0;
$productUrl = '/product/' . rawurlencode($card['slug']);
?>
<div class="product-box-2 h-100 position-relative">
    <?php if ($outOfStock): ?>
        <span class="badge bg-secondary position-absolute top-0 end-0 m-2">Нет в наличии</span>
    <?php endif; ?>

    <a href="<?= e($productUrl) ?>" class="product-image">
        <img src="<?= e('/uploads/' . $card['image_path']) ?>" class="img-fluid blur-up lazyload"
            alt="<?= e($card['image_alt']) ?>" loading="lazy">
    </a>

    <div class="product-detail">
        <a href="<?= e($productUrl) ?>">
            <h6><?= e($card['name']) ?></h6>
        </a>
        <p class="text-content small mb-1"><?= e($card['vendor_name']) ?></p>
        <h5>
            <?= e(formatPrice($card['price'])) ?>
            <?php if ($card['old_price'] !== null): ?>
                <del class="text-content"><?= e(formatPrice($card['old_price'])) ?></del>
            <?php endif; ?>
        </h5>
    </div>
</div>
