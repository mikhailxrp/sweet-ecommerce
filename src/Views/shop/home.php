<?php

declare(strict_types=1);

/**
 * Главная витрины — пустая заглушка Фазы 0. Наполнение (слайдеры, блоки
 * товаров и категорий) появляется в Фазе 1.
 *
 * Страница буферизует свой HTML в $content и подключает общий layout.
 */

$title = 'Сдоба — маркетплейс кондитерских изделий';

ob_start();
?>
<section class="section-b-space section-t-space">
    <div class="container-fluid-lg">
        <div class="text-center py-5">
            <h1 class="fw-bold mb-3">Добро пожаловать в «Сдобу»</h1>
            <p class="text-content mx-auto home-intro__lead">
                Маркетплейс кондитерских изделий. Витрина наполняется — скоро здесь появятся
                торты, пирожные и капкейки от лучших кондитеров города.
            </p>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();

require __DIR__ . '/layout.php';
