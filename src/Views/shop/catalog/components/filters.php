<?php

declare(strict_types=1);

/**
 * Фильтры сайдбара листинга категории (Таск 4). Разметка — аккордеон
 * `example-template/front-end/shop-left-sidebar.html`. Отправка — обычная
 * GET-форма (перезагрузка страницы), без AJAX.
 *
 * Ожидает переменные:
 *   $basePath     — `/catalog/{slug}`;
 *   $sortKey      — текущая сортировка, чтобы не сбрасывалась при отправке формы;
 *   $filters      — результат parseFilters() (Core/catalog.php);
 *   $vendorFacets — [{id,name,count}] кондитеры с товарами в категории;
 *   $priceRange   — {min,max} границы цены категории для слайдера.
 */

$hasActiveFilters = $filters['price_min'] !== null
    || $filters['price_max'] !== null
    || $filters['vendor_ids'] !== []
    || $filters['in_stock']
    || $filters['weight_bucket'] !== null
    || $filters['sugar_free']
    || $filters['min_rating'] !== null;

$weightBuckets = [
    'lt500'     => 'до 500 г',
    '500to1000' => '500–1000 г',
    'gt1000'    => 'свыше 1000 г',
];
?>
<form method="get" action="<?= e($basePath) ?>" class="filters-form">
    <?php if ($sortKey !== 'popular'): ?>
        <input type="hidden" name="sort" value="<?= e($sortKey) ?>">
    <?php endif; ?>

    <div class="accordion custom-accordion" id="filterAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="filterHeadingPrice">
                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                    data-bs-target="#filterCollapsePrice">
                    <span>Цена</span>
                </button>
            </h2>
            <div id="filterCollapsePrice" class="accordion-collapse collapse show">
                <div class="accordion-body">
                    <div class="range-slider">
                        <input type="text" class="js-range-slider" id="priceRangeSlider"
                            data-min="<?= e($priceRange['min']) ?>"
                            data-max="<?= e($priceRange['max']) ?>"
                            data-from="<?= e($filters['price_min'] ?? $priceRange['min']) ?>"
                            data-to="<?= e($filters['price_max'] ?? $priceRange['max']) ?>">
                    </div>
                    <input type="hidden" name="price_min" id="priceMinInput"
                        value="<?= e($filters['price_min'] ?? '') ?>">
                    <input type="hidden" name="price_max" id="priceMaxInput"
                        value="<?= e($filters['price_max'] ?? '') ?>">
                </div>
            </div>
        </div>

        <?php if ($vendorFacets !== []): ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="filterHeadingVendor">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#filterCollapseVendor">
                        <span>Кондитер</span>
                    </button>
                </h2>
                <div id="filterCollapseVendor" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <ul class="category-list custom-padding">
                            <?php foreach ($vendorFacets as $vendor): ?>
                                <li>
                                    <div class="form-check ps-0 m-0 category-list-box">
                                        <input class="checkbox_animated" type="checkbox" name="vendor[]"
                                            id="vendor-<?= (int) $vendor['id'] ?>" value="<?= (int) $vendor['id'] ?>"
                                            <?= in_array($vendor['id'], $filters['vendor_ids'], true) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="vendor-<?= (int) $vendor['id'] ?>">
                                            <span class="name"><?= e($vendor['name']) ?></span>
                                            <span class="number">(<?= (int) $vendor['count'] ?>)</span>
                                        </label>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="accordion-item">
            <h2 class="accordion-header" id="filterHeadingAvailability">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#filterCollapseAvailability">
                    <span>Наличие</span>
                </button>
            </h2>
            <div id="filterCollapseAvailability" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <div class="form-check ps-0 m-0 category-list-box">
                        <input class="checkbox_animated" type="checkbox" name="in_stock" id="filterInStock"
                            value="1" <?= $filters['in_stock'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="filterInStock">
                            <span class="name">Только в наличии</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="filterHeadingWeight">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#filterCollapseWeight">
                    <span>Вес</span>
                </button>
            </h2>
            <div id="filterCollapseWeight" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <ul class="category-list custom-padding">
                        <li>
                            <div class="form-check ps-0 m-0 category-list-box">
                                <input type="radio" name="weight" id="weight-any" value=""
                                    <?= $filters['weight_bucket'] === null ? 'checked' : '' ?>>
                                <label class="form-check-label" for="weight-any">
                                    <span class="name">Любой</span>
                                </label>
                            </div>
                        </li>
                        <?php foreach ($weightBuckets as $value => $label): ?>
                            <li>
                                <div class="form-check ps-0 m-0 category-list-box">
                                    <input type="radio" name="weight" id="weight-<?= e($value) ?>"
                                        value="<?= e($value) ?>"
                                        <?= $filters['weight_bucket'] === $value ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="weight-<?= e($value) ?>">
                                        <span class="name"><?= e($label) ?></span>
                                    </label>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="filterHeadingSugarFree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#filterCollapseSugarFree">
                    <span>Особенности</span>
                </button>
            </h2>
            <div id="filterCollapseSugarFree" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <div class="form-check ps-0 m-0 category-list-box">
                        <input class="checkbox_animated" type="checkbox" name="sugar_free" id="filterSugarFree"
                            value="1" <?= $filters['sugar_free'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="filterSugarFree">
                            <span class="name">Без сахара</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="filterHeadingRating">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#filterCollapseRating">
                    <span>Рейтинг</span>
                </button>
            </h2>
            <div id="filterCollapseRating" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <ul class="category-list custom-padding">
                        <?php for ($stars = 5; $stars >= 1; $stars--): ?>
                            <li>
                                <div class="form-check ps-0 m-0 category-list-box">
                                    <input class="checkbox_animated" type="checkbox" name="rating[]"
                                        id="rating-<?= $stars ?>" value="<?= $stars ?>"
                                        <?= $filters['min_rating'] !== null && $stars >= $filters['min_rating']
                                            ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="rating-<?= $stars ?>">
                                        <span class="text-content">от <?= $stars ?> <?= e($stars === 1 ? 'звезды' : 'звёзд') ?></span>
                                    </label>
                                </div>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn theme-bg-color text-white w-100 mt-3">Применить фильтры</button>
    <?php if ($hasActiveFilters): ?>
        <a href="<?= e($basePath . ($sortKey !== 'popular' ? '?sort=' . rawurlencode($sortKey) : '')) ?>"
            class="btn btn-outline-secondary w-100 mt-2">Сбросить фильтры</a>
    <?php endif; ?>
</form>
