<?php

declare(strict_types=1);

/**
 * ВРЕМЕННАЯ страница-заглушка для ручной проверки layout админки (Таск 3).
 * Настоящий маршрут /admin, гейт роли admin и дашборд появятся в Таске 5 —
 * тогда этот файл, временный контроллер и роут /admin/_preview удаляются.
 *
 * Страница буферизует свой HTML в $content и подключает общий admin-layout.
 */

$title = 'Проверка layout — Админка «Сдоба»';

ob_start();
?>
<div class="admin-preview">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Layout админки</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">
                        Временная страница для проверки шапки, сайдбара и подвала в теме
                        Fastkart. Наполнение дашборда (карточки, графики ApexCharts)
                        появится в Фазе 5.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();

require __DIR__ . '/layout.php';
