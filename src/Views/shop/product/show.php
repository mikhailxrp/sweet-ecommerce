<?php

declare(strict_types=1);

/**
 * Карточка товара (`/product/{slug}`). Разметка —
 * `example-template/front-end/product-left-thumbnail.html`.
 *
 * Данные от контроллера:
 *   $product    — товар + категория + кондитер (findBySlugForDetail());
 *   $images     — фото для галереи (до 6);
 *   $variants   — активные варианты (пусто, если has_variants = 0);
 *   $attributes — доп. атрибуты без своей цены (начинка, вкус и т.п.);
 *   $related    — похожие товары той же категории, текущий исключён.
 *
 * Слайдеры галереи (`custom_slick.js`) и зум (`jquery.elevatezoom.js` +
 * своя инициализация в product.js) подключены прямо здесь, а не в общем
 * `layout.php` — используются только на этой странице.
 */

$title = $product['name'] . ' — Сдоба';

$hasVariants = $product['has_variants'] === 1;
$initialVariant = $hasVariants && $variants !== [] ? $variants[0] : null;

$displayPrice = $initialVariant !== null ? $initialVariant['price'] : $product['price'];
$displayOldPrice = $initialVariant !== null ? $initialVariant['old_price'] : $product['old_price'];
$displaySku = $initialVariant !== null ? $initialVariant['sku'] : $product['sku'];
$displayStock = $initialVariant !== null ? $initialVariant['stock_quantity'] : $product['stock_quantity'];

$filledStars = (int) round((float) $product['rating_avg']);

$characteristics = array_filter([
    'Состав' => $product['composition'],
    'Аллергены' => $product['allergens'],
    'Вес' => $product['weight_grams'] !== null ? $product['weight_grams'] . ' г' : null,
    'Калорийность' => $product['calories_per_100g'] !== null ? $product['calories_per_100g'] . ' ккал / 100 г' : null,
    'Срок годности' => $product['shelf_life_hours'] !== null ? $product['shelf_life_hours'] . ' ч.' : null,
    'Условия хранения' => $product['storage_conditions'],
    'Срок изготовления' => $product['lead_time_hours'] > 0 ? $product['lead_time_hours'] . ' ч.' : 'Готовится сразу',
], static fn (?string $value): bool => $value !== null && $value !== '');

ob_start();
?>
<link rel="stylesheet" href="/assets/vendor/css/vendors/slick/slick.css">
<link rel="stylesheet" href="/assets/vendor/css/vendors/slick/slick-theme.css">

<section class="breadcrumb-section pt-0">
    <div class="container-fluid-lg">
        <div class="row">
            <div class="col-12">
                <div class="breadcrumb-contain">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="/">Главная</a></li>
                            <li class="breadcrumb-item"><a href="/catalog">Каталог</a></li>
                            <li class="breadcrumb-item">
                                <a href="/catalog/<?= e($product['category_slug']) ?>">
                                    <?= e($product['category_name']) ?>
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page"><?= e($product['name']) ?></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="product-section">
    <div class="container-fluid-lg">
        <div class="row g-4">
            <div class="col-xl-6 wow fadeInUp">
                <?php require __DIR__ . '/components/gallery.php'; ?>
            </div>

            <div class="col-xl-6 wow fadeInUp" data-wow-delay="0.1s">
                <div class="right-box-contain">
                    <h2 class="name"><?= e($product['name']) ?></h2>

                    <div class="price-rating">
                        <h3 class="theme-color price">
                            <span id="productPriceCurrent"><?= e(formatPrice($displayPrice)) ?></span>
                            <del class="text-content" id="productPriceOld" <?= $displayOldPrice === null ? 'hidden' : '' ?>><?= e($displayOldPrice !== null ? formatPrice($displayOldPrice) : '') ?></del>
                        </h3>

                        <?php if ((int) $product['reviews_count'] > 0): ?>
                            <div class="product-rating custom-rate">
                                <ul class="rating">
                                    <?php for ($star = 1; $star <= 5; $star++): ?>
                                        <li><i data-feather="star" class="<?= $star <= $filledStars ? 'fill' : '' ?>"></i></li>
                                    <?php endfor; ?>
                                </ul>
                                <span class="review"><?= (int) $product['reviews_count'] ?> отзывов</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <p class="text-content mb-0">Кондитер: <?= e($product['vendor_name']) ?></p>

                    <?php if (($product['short_description'] ?? '') !== ''): ?>
                        <div class="product-contain">
                            <p><?= e($product['short_description']) ?></p>
                        </div>
                    <?php endif; ?>

                    <p class="mt-2">
                        <span id="productStock" class="<?= $displayStock > 0 ? '' : 'text-danger' ?>">
                            <?= $displayStock > 0 ? 'В наличии: ' . (int) $displayStock . ' шт.' : 'Нет в наличии' ?>
                        </span>
                    </p>

                    <?php if ($hasVariants && $variants !== []): ?>
                        <?php require __DIR__ . '/components/variants.php'; ?>
                    <?php endif; ?>

                    <div class="note-box product-package">
                        <div class="cart_qty qty-box product-qty">
                            <div class="input-group">
                                <button type="button" class="qty-right-plus" data-type="plus"><i class="fa fa-plus"></i></button>
                                <input class="form-control input-number qty-input" type="text" value="1">
                                <button type="button" class="qty-left-minus" data-type="minus"><i class="fa fa-minus"></i></button>
                            </div>
                        </div>

                        <button type="button" class="btn btn-md bg-dark cart-button text-white w-100">Добавить в корзину</button>
                    </div>

                    <div class="product-info">
                        <ul class="product-info-list product-info-list-2">
                            <li>Артикул: <span id="productSku"><?= e($displaySku) ?></span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="product-section-box">
                    <ul class="nav nav-tabs custom-nav" id="productTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="description-tab" data-bs-toggle="tab"
                                data-bs-target="#description" type="button" role="tab">Описание</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="characteristics-tab" data-bs-toggle="tab"
                                data-bs-target="#characteristics" type="button" role="tab">Характеристики</button>
                        </li>
                    </ul>

                    <div class="tab-content custom-tab" id="productTabContent">
                        <div class="tab-pane fade show active" id="description" role="tabpanel">
                            <div class="product-description">
                                <div class="nav-desh">
                                    <p><?= e($product['description'] ?? '') ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="characteristics" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table info-table">
                                    <tbody>
                                        <?php foreach ($characteristics as $label => $value): ?>
                                            <tr>
                                                <td><?= e($label) ?></td>
                                                <td><?= e($value) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php foreach ($attributes as $attribute): ?>
                                            <tr>
                                                <td><?= e($attribute['attr_name']) ?></td>
                                                <td><?= e($attribute['attr_value']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($related !== []): ?>
    <section class="product-list-section section-b-space related-products">
        <div class="container-fluid-lg">
            <div class="title">
                <h2>Похожие товары</h2>
            </div>
            <div class="row g-sm-4 g-3 row-cols-xxl-4 row-cols-lg-3 row-cols-sm-2 row-cols-1">
                <?php foreach ($related as $relatedProduct): ?>
                    <div>
                        <?php $card = $relatedProduct; require __DIR__ . '/../components/product-card.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- defer: jQuery грузится позже, отдельными тегами в layout.php после
     <main> — без defer эти плагины выполнились бы раньше jQuery и упали бы -->
<script src="/assets/vendor/js/slick/slick.js" defer></script>
<script src="/assets/vendor/js/slick/slick-animation.min.js" defer></script>
<script src="/assets/vendor/js/slick/custom_slick.js" defer></script>
<script src="/assets/vendor/js/jquery.elevatezoom.js" defer></script>
<script type="module" src="/assets/js/product.js"></script>
<?php
$content = ob_get_clean();

require __DIR__ . '/../layout.php';
