<?php

declare(strict_types=1);

/**
 * Галерея карточки товара — main + thumbnail слайдеры (`.product-main-2`/
 * `.left-slider-image-2`, синхронизация уже готова в вендорном
 * `custom_slick.js`). Классы `image_zoom_cls-{N}` на каждом фото — под
 * зум (`jquery.elevatezoom.js` + своя инициализация в `product.js`).
 *
 * Ожидает:
 *   $images  — list<array{path,alt}>, до 6 штук;
 *   $product — товар (для fallback alt, если у фото своего нет).
 *
 * Thumbnail-слайдер рендерится, только если фото больше одного — с одним
 * фото синхронизировать нечего.
 */
?>
<div class="product-left-box">
    <div class="row g-2">
        <div class="col-xxl-10 col-lg-12 col-md-10 order-xxl-2 order-lg-1 order-md-2">
            <div class="product-main-2 no-arrow">
                <?php foreach ($images as $index => $image): ?>
                    <div>
                        <div class="slider-image">
                            <img src="<?= e('/uploads/' . $image['path']) ?>"
                                data-zoom-image="<?= e('/uploads/' . $image['path']) ?>"
                                class="img-fluid image_zoom_cls-<?= (int) $index ?>"
                                alt="<?= e(($image['alt'] ?? '') !== '' ? $image['alt'] : $product['name']) ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (count($images) > 1): ?>
            <div class="col-xxl-2 col-lg-12 col-md-2 order-xxl-1 order-lg-2 order-md-1">
                <div class="left-slider-image-2 left-slider no-arrow slick-top">
                    <?php foreach ($images as $image): ?>
                        <div>
                            <div class="sidebar-image">
                                <img src="<?= e('/uploads/' . $image['path']) ?>" class="img-fluid"
                                    alt="<?= e(($image['alt'] ?? '') !== '' ? $image['alt'] : $product['name']) ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
