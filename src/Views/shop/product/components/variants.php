<?php

declare(strict_types=1);

/**
 * Селектор вариантов товара. Первый активный вариант отмечен выбранным
 * по умолчанию — его цена/остаток уже показаны в show.php при первой
 * отрисовке. `data-*` на каждом пункте — единственный источник данных для
 * product.js при переключении (цена, старая цена, остаток, артикул,
 * фото), значения приходят из PHP, не хардкодятся в JS.
 *
 * Ожидает $variants — list<array{
 *   id,sku,name,price,old_price,stock_quantity,image_path
 * }>, непустой (show.php подключает компонент только когда есть варианты).
 */
?>
<div class="product-package">
    <div class="product-title">
        <h4>Вариант</h4>
    </div>
    <ul class="select-package" id="variantSelector">
        <?php foreach ($variants as $index => $variant): ?>
            <li>
                <a href="javascript:void(0)" class="<?= $index === 0 ? 'active' : '' ?>"
                    data-variant-id="<?= (int) $variant['id'] ?>" data-sku="<?= e($variant['sku']) ?>"
                    data-price="<?= e(formatPrice($variant['price'])) ?>"
                    data-old-price="<?= $variant['old_price'] !== null ? e(formatPrice($variant['old_price'])) : '' ?>"
                    data-stock="<?= (int) $variant['stock_quantity'] ?>"
                    <?php if ($variant['image_path'] !== null): ?>
                        data-image="<?= e('/uploads/' . $variant['image_path']) ?>"
                    <?php endif; ?>>
                    <?= e($variant['name']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
