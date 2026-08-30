<?php

declare(strict_types=1);

/**
 * Пагинация листинга. Разметка `.custom-pagination` темы (аналог
 * `shop-left-sidebar.html`), ссылки строятся через buildQuery() —
 * сохраняют все текущие query-параметры (в т.ч. `sort` из Таска 3 и
 * будущие фильтры Таска 4), меняя только `page`.
 *
 * Ожидает переменные:
 *   $currentPage — текущая страница (>= 1);
 *   $totalPages  — всего страниц (>= 1);
 *   $baseUrl     — путь без query-строки, например /catalog/torty;
 *   $query       — текущий $_GET.
 *
 * Ничего не выводит, если страница ровно одна.
 */

if ($totalPages <= 1) {
    return;
}

$isFirst = $currentPage <= 1;
$isLast  = $currentPage >= $totalPages;
?>
<nav class="custom-pagination" aria-label="Страницы">
    <ul class="pagination justify-content-center">
        <li class="page-item<?= $isFirst ? ' disabled' : '' ?>">
            <a class="page-link"
                href="<?= $isFirst ? 'javascript:void(0)' : e($baseUrl . buildQuery($query, ['page' => $currentPage - 1])) ?>"
                tabindex="<?= $isFirst ? '-1' : '0' ?>" aria-label="Предыдущая страница">
                <i class="fa-solid fa-angles-left"></i>
            </a>
        </li>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item<?= $i === $currentPage ? ' active' : '' ?>">
                <a class="page-link" href="<?= e($baseUrl . buildQuery($query, ['page' => $i])) ?>">
                    <?= $i ?>
                </a>
            </li>
        <?php endfor; ?>

        <li class="page-item<?= $isLast ? ' disabled' : '' ?>">
            <a class="page-link"
                href="<?= $isLast ? 'javascript:void(0)' : e($baseUrl . buildQuery($query, ['page' => $currentPage + 1])) ?>"
                aria-label="Следующая страница">
                <i class="fa-solid fa-angles-right"></i>
            </a>
        </li>
    </ul>
</nav>
