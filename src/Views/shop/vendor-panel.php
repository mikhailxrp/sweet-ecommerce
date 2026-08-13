<?php

declare(strict_types=1);

/**
 * Заглушка кабинета кондитера (Фаза 0). Показывается только роли `vendor`
 * (гейт — в VendorPanelController). Настоящий кабинет со своим layout —
 * Фаза 7.
 *
 * Страница буферизует свой HTML в $content и подключает shop-layout.
 */

$title = 'Кабинет кондитера — Сдоба';

ob_start();
?>
<section class="section-b-space section-t-space">
    <div class="container-fluid-lg">
        <div class="text-center py-5">
            <h1 class="fw-bold mb-3">Кабинет кондитера</h1>
            <p class="text-content mx-auto home-intro__lead">
                Раздел в разработке. Управление витриной, товарами и суб-заказами
                появится в одной из следующих фаз.
            </p>
            <a href="/" class="btn btn-animation mt-3">На главную</a>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();

require __DIR__ . '/layout.php';
