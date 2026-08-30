<?php

declare(strict_types=1);

/**
 * Листинг категории (`/catalog/{slug}`) — товары самой категории и её
 * подкатегорий, сортировка, пагинация. Разметка — двухколоночный layout
 * `example-template/front-end/shop-left-sidebar.html`: сайдбар
 * (`.col-custom-3`) + контент (`.col-custom-`). Сами фильтры сайдбара —
 * Таск 4, здесь только дерево категорий для навигации.
 *
 * Данные от контроллера:
 *   $category    — текущая категория (id, name, slug, description,
 *                  parent_id, parent_name, parent_slug);
 *   $sidebarTree — корневые категории с подкатегориями;
 *   $products    — товары страницы для product-card.php;
 *   $sortKey     — текущий ключ сортировки;
 *   $sortOptions — [ключ => подпись] для dropdown;
 *   $page        — текущая страница;
 *   $totalPages  — всего страниц;
 *   $total       — всего найдено товаров;
 *   $query       — текущий $_GET, для ссылок сортировки/пагинации.
 */

$title = $category['name'] . ' — Сдоба';
$basePath = '/catalog/' . $category['slug'];

ob_start();
?>
<section class="breadcrumb-section pt-0">
    <div class="container-fluid-lg">
        <div class="row">
            <div class="col-12">
                <div class="breadcrumb-contain">
                    <h2 class="mb-2"><?= e($category['name']) ?></h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="/">Главная</a></li>
                            <li class="breadcrumb-item"><a href="/catalog">Каталог</a></li>
                            <?php if ($category['parent_id'] !== null): ?>
                                <li class="breadcrumb-item">
                                    <a href="/catalog/<?= e($category['parent_slug']) ?>">
                                        <?= e($category['parent_name']) ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li class="breadcrumb-item active" aria-current="page"><?= e($category['name']) ?></li>
                        </ol>
                    </nav>
                    <?php if (($category['description'] ?? '') !== ''): ?>
                        <p class="text-content mb-0 mt-2"><?= e($category['description']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-b-space shop-section">
    <div class="container-fluid-lg">
        <div class="row">
            <div class="col-custom-3">
                <div class="left-box wow fadeInUp">
                    <div class="list-group">
                        <?php foreach ($sidebarTree as $root): ?>
                            <a href="/catalog/<?= e($root['slug']) ?>"
                                class="list-group-item list-group-item-action<?= $root['id'] === $category['id'] ? ' active' : '' ?>">
                                <?= e($root['name']) ?>
                            </a>
                            <?php foreach ($root['children'] as $child): ?>
                                <a href="/catalog/<?= e($child['slug']) ?>"
                                    class="list-group-item list-group-item-action ps-4<?= $child['id'] === $category['id'] ? ' active' : '' ?>">
                                    <?= e($child['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-custom-">
                <div class="show-button">
                    <div class="top-filter-menu">
                        <div class="category-dropdown">
                            <h5 class="text-content">Сортировка:</h5>
                            <div class="dropdown">
                                <button class="dropdown-toggle" type="button" id="sortDropdown"
                                    data-bs-toggle="dropdown">
                                    <span><?= e($sortOptions[$sortKey]) ?></span>
                                    <i class="fa-solid fa-angle-down"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <?php foreach ($sortOptions as $key => $label): ?>
                                        <li>
                                            <a class="dropdown-item<?= $key === $sortKey ? ' active' : '' ?>"
                                                href="<?= e($basePath . buildQuery($query, ['sort' => $key, 'page' => null])) ?>">
                                                <?= e($label) ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>

                        <p class="text-content mb-0"><?= (int) $total ?> товаров найдено</p>
                    </div>
                </div>

                <?php if ($products === []): ?>
                    <p class="text-content">В этой категории пока нет товаров.</p>
                <?php else: ?>
                    <div class="row g-sm-4 g-3 row-cols-xxl-4 row-cols-xl-3 row-cols-lg-2 row-cols-md-3 row-cols-2">
                        <?php foreach ($products as $product): ?>
                            <div>
                                <?php $card = $product; require __DIR__ . '/../components/product-card.php'; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php
                    $currentPage = $page;
                    $baseUrl = $basePath;
                    require __DIR__ . '/../components/pagination.php';
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();

require __DIR__ . '/../layout.php';
