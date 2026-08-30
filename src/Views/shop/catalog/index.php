<?php

declare(strict_types=1);

/**
 * «Все категории» (`/catalog`). Плитки корневых категорий со счётчиком
 * их прямых товаров + превью последних активных товаров.
 *
 * Плитки категорий, а не сортировка/фильтры/пагинация листинга — их
 * добавляет отдельная страница `/catalog/{slug}` в Таске 3.
 *
 * Данные от контроллера:
 *   $categories — корневые категории (id, name, slug, image_path, product_count);
 *   $products   — превью товаров для product-card.php.
 */

$title = 'Каталог — Сдоба';
$categories = $categories ?? [];
$products = $products ?? [];

ob_start();
?>
<section class="breadcrumb-section pt-0">
    <div class="container-fluid-lg">
        <div class="row">
            <div class="col-12">
                <div class="breadcrumb-contain">
                    <h2 class="mb-2">Каталог</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="/">Главная</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Каталог</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-b-space">
    <div class="container-fluid-lg">
        <div class="row g-4">
            <?php foreach ($categories as $category): ?>
                <div class="col-xxl-3 col-lg-4 col-sm-6">
                    <a href="/catalog/<?= e($category['slug']) ?>" class="card h-100 text-center text-decoration-none">
                        <img src="<?= e('/uploads/' . $category['image_path']) ?>"
                            class="card-img-top category-card__image" alt="<?= e($category['name']) ?>"
                            loading="lazy">
                        <div class="card-body">
                            <h3 class="card-title h6 mb-1"><?= e($category['name']) ?></h3>
                            <p class="card-text text-content small mb-0">
                                <?= (int) $category['product_count'] ?> товаров
                            </p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-b-space pt-0">
    <div class="container-fluid-lg">
        <div class="title">
            <h2>Новые товары</h2>
        </div>

        <?php if ($products === []): ?>
            <p class="text-content">Товары скоро появятся.</p>
        <?php else: ?>
            <div class="row g-sm-4 g-3 row-cols-xxl-4 row-cols-lg-3 row-cols-sm-2 row-cols-1">
                <?php foreach ($products as $product): ?>
                    <div>
                        <?php $card = $product; require __DIR__ . '/../components/product-card.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php
$content = ob_get_clean();

require __DIR__ . '/../layout.php';
