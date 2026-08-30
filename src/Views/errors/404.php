<?php

declare(strict_types=1);

/**
 * Страница 404 «Страница не найдена». Отдаётся Router::dispatch(), когда
 * запрошенный маршрут не найден в config/routes.php.
 *
 * HTTP-код 404 выставляет вызывающий (dispatch()). Оформление — на
 * shop-layout (страница видна прежде всего покупателю витрины).
 */

$title = 'Страница не найдена — Сдоба';

ob_start();
?>
<section class="section-b-space section-t-space">
    <div class="container-fluid-lg">
        <div class="text-center py-5">
            <h1 class="fw-bold mb-3">404 — Страница не найдена</h1>
            <p class="text-content mx-auto home-intro__lead">
                Такой страницы не существует или она была перемещена.
            </p>
            <a href="/" class="btn btn-animation mt-3">На главную</a>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();

require __DIR__ . '/../shop/layout.php';
