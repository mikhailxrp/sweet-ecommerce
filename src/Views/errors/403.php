<?php

declare(strict_types=1);

/**
 * Страница 403 «Доступ запрещён». Отдаётся гейтом requireRole(), когда
 * авторизованный пользователь пытается открыть чужой по роли раздел
 * (например, customer → /admin или /vendor-panel).
 *
 * HTTP-код 403 выставляет вызывающий (requireRole). Оформление — на
 * shop-layout (страница видна прежде всего покупателю витрины).
 */

$title = 'Доступ запрещён — Сдоба';

ob_start();
?>
<section class="section-b-space section-t-space">
    <div class="container-fluid-lg">
        <div class="text-center py-5">
            <h1 class="fw-bold mb-3">403 — Доступ запрещён</h1>
            <p class="text-content mx-auto home-intro__lead">
                У вас нет прав для просмотра этой страницы.
            </p>
            <a href="/" class="btn btn-animation mt-3">На главную</a>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();

require __DIR__ . '/../shop/layout.php';
