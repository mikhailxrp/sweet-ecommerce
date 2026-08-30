<?php

declare(strict_types=1);

/**
 * Результаты поиска (`/search?q=...`). Разметка —
 * `example-template/front-end/search.html`, сетка результатов — на
 * `components/product-card.php`, как везде в каталоге (второй вид
 * карточки товара ради одной страницы не заводим).
 *
 * Данные от контроллера:
 *   $searchQuery — нормализованный поисковый запрос (может быть '');
 *   $products    — найденные товары для текущей страницы;
 *   $sortKey     — текущий ключ сортировки;
 *   $sortOptions — [ключ => подпись] для dropdown;
 *   $page        — текущая страница;
 *   $totalPages  — всего страниц;
 *   $total       — всего найдено;
 *   $query       — текущий $_GET, для ссылок сортировки/пагинации
 *                  (имя переменной как в catalog/category.php — этого
 *                  ждёт components/pagination.php).
 */

$title = $searchQuery !== '' ? 'Поиск: ' . $searchQuery . ' — Сдоба' : 'Поиск — Сдоба';
$basePath = '/search';

ob_start();
?>
<section class="breadcrumb-section pt-0">
    <div class="container-fluid-lg">
        <div class="row">
            <div class="col-12">
                <div class="breadcrumb-contain">
                    <h2 class="mb-2">Поиск</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="/">Главная</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Поиск</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="search-section">
    <div class="container-fluid-lg">
        <div class="row">
            <div class="col-xxl-6 col-xl-8 mx-auto">
                <div class="title d-block text-center">
                    <h2>Поиск товаров</h2>
                </div>

                <div class="search-box">
                    <form action="/search" method="get" role="search">
                        <div class="input-group">
                            <input type="search" name="q" class="form-control" value="<?= e($searchQuery) ?>"
                                placeholder="Поиск по товарам..." aria-label="Поиск по товарам">
                            <button class="btn theme-bg-color text-white m-0" type="submit">Найти</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-b-space">
    <div class="container-fluid-lg">
        <?php if ($searchQuery === ''): ?>
            <p class="text-content text-center">Введите запрос, чтобы найти товар.</p>
        <?php elseif ($products === []): ?>
            <p class="text-content text-center">
                По запросу «<?= e($searchQuery) ?>» ничего не найдено.
            </p>
        <?php else: ?>
            <div class="top-filter-menu justify-content-between">
                <div class="category-dropdown">
                    <h5 class="text-content">Сортировка:</h5>
                    <div class="dropdown">
                        <button class="dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown">
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

                <p class="text-content mb-0">
                    <?= (int) $total ?> товаров найдено по запросу «<?= e($searchQuery) ?>»
                </p>
            </div>

            <div class="row g-sm-4 g-3 row-cols-xxl-4 row-cols-lg-3 row-cols-sm-2 row-cols-1">
                <?php foreach ($products as $product): ?>
                    <div>
                        <?php $card = $product; require __DIR__ . '/components/product-card.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php
            $currentPage = $page;
            $baseUrl = $basePath;
            require __DIR__ . '/components/pagination.php';
            ?>
        <?php endif; ?>
    </div>
</section>
<?php
$content = ob_get_clean();

require __DIR__ . '/layout.php';
