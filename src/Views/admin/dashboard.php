<?php

declare(strict_types=1);

/**
 * Дашборд админки — заглушка Фазы 0. Наполнение (карточки-KPI, графики
 * ApexCharts, таблицы заказов) появляется в Фазе 5.
 *
 * Страница буферизует свой HTML в $content и подключает admin-layout.
 */

$title = 'Дашборд — Админка «Сдоба»';

ob_start();
?>
<div class="admin-preview">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Дашборд</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">
                        Панель управления «Сдобы». Показатели, графики и таблицы заказов
                        появятся в Фазе 5 — здесь пока заглушка на теме Fastkart.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();

require __DIR__ . '/layout.php';
