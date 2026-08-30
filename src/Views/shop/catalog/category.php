<?php

declare(strict_types=1);

/**
 * Листинг категории (`/catalog/{slug}`) — товары самой категории и её
 * подкатегорий, сортировка, пагинация, фильтры (Таск 4). Разметка —
 * двухколоночный layout `example-template/front-end/shop-left-sidebar.html`:
 * сайдбар (`.col-custom-3`, дерево категорий + `components/filters.php`)
 * + контент (`.col-custom-`).
 *
 * Данные от контроллера:
 *   $category     — текущая категория (id, name, slug, description,
 *                   parent_id, parent_name, parent_slug);
 *   $sidebarTree  — корневые категории с подкатегориями;
 *   $products     — товары страницы для product-card.php;
 *   $sortKey      — текущий ключ сортировки;
 *   $sortOptions  — [ключ => подпись] для dropdown;
 *   $page         — текущая страница;
 *   $totalPages   — всего страниц;
 *   $total        — всего найдено товаров;
 *   $query        — текущий $_GET, для ссылок сортировки/пагинации;
 *   $filters      — результат parseFilters() (Таск 4);
 *   $vendorFacets — кондитеры с счётчиком товаров для фильтра;
 *   $priceRange   — {min,max} границы цены категории для слайдера;
 *   $viewMode     — режим отображения `grid`|`list`.
 *
 * `ion.rangeSlider` — вендорный плагин, используемый только на этой
 * странице каталога: его CSS/JS подключены здесь, а не в общем
 * `layout.php`, чтобы не грузить их на каждой странице витрины.
 */

$title = $category['name'] . ' — Сдоба';
$basePath = '/catalog/' . $category['slug'];

ob_start();
?>
<link rel="stylesheet" href="/assets/vendor/css/vendors/ion.rangeSlider.min.css">

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
                    <!-- .back-button — закрывает офканвас на 320px (filter-sidebar.js,
                         тот же вендорный плагин, что открывает его кнопкой Filter Menu) -->
                    <div class="back-button d-lg-none">
                        <h3><i class="fa-solid fa-arrow-left"></i> Назад</h3>
                    </div>

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

                    <div class="filters-block">
                        <?php require __DIR__ . '/components/filters.php'; ?>
                    </div>
                </div>
            </div>

            <div class="col-custom-">
                <div class="show-button">
                    <div class="filter-button-group mt-0">
                        <div class="filter-button d-inline-block d-lg-none">
                            <a href="javascript:void(0)"><i class="fa-solid fa-filter"></i> Фильтры</a>
                        </div>
                    </div>

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

                        <div class="grid-option d-none d-md-block">
                            <ul>
                                <li class="grid-btn<?= $viewMode === 'grid' ? ' active' : '' ?>">
                                    <a href="javascript:void(0)" data-view-link="grid">
                                        <img src="/assets/vendor/svg/grid.svg" class="blur-up lazyload" alt="Плитка">
                                    </a>
                                </li>
                                <li class="list-btn<?= $viewMode === 'list' ? ' active' : '' ?>">
                                    <a href="javascript:void(0)" data-view-link="list">
                                        <img src="/assets/vendor/svg/list.svg" class="blur-up lazyload" alt="Список">
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <p class="text-content mb-0"><?= (int) $total ?> товаров найдено</p>
                    </div>
                </div>

                <?php if ($products === []): ?>
                    <p class="text-content">В этой категории пока нет товаров.</p>
                <?php else: ?>
                    <div class="row g-sm-4 g-3 row-cols-xxl-4 row-cols-xl-3 row-cols-lg-2 row-cols-md-3 row-cols-2 product-list-section<?= $viewMode === 'list' ? ' list-style' : '' ?>">
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

<!-- Затемнение фона при открытом офканвасе сайдбара на мобильных
     (filter-sidebar.js переключает класс .show и здесь, и на .left-box) -->
<div class="bg-overlay"></div>

<!-- defer: jQuery грузится позже, отдельными тегами в layout.php после
     <main> — без defer эти плагины выполнились бы раньше jQuery и упали бы -->
<script src="/assets/vendor/js/ion.rangeSlider.min.js" defer></script>
<script src="/assets/vendor/js/filter-sidebar.js" defer></script>
<script type="module" src="/assets/js/catalog.js"></script>
<?php
$content = ob_get_clean();

require __DIR__ . '/../layout.php';
