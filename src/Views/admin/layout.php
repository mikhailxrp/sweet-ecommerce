<?php

declare(strict_types=1);

/**
 * Layout админки. Оборачивает контент страницы в тему Fastkart (back-end).
 *
 * Ожидает переменные от страницы-вью:
 *   $content — готовый HTML основной части (буферизуется во вью);
 *   $title   — заголовок вкладки (необязательно).
 *
 * Вендорные CSS/JS — слой темы админки в /assets/vendor/admin (AS IS). Свой
 * слой — /assets/css/admin.css и /assets/js/admin.js — подключается последним.
 * У витрины свой независимый набор ассетов, общего слоя нет.
 */

$title = $title ?? 'Админка — Сдоба';
$content = $content ?? '';
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>

    <link rel="icon" type="image/png" href="/assets/vendor/admin/images/favicon.png">

    <link rel="stylesheet" href="/assets/vendor/admin/css/fonts.css">
    <link rel="stylesheet" href="/assets/vendor/admin/css/linearicon.css">
    <link rel="stylesheet" href="/assets/vendor/admin/css/vendors/font-awesome.css">
    <link rel="stylesheet" href="/assets/vendor/admin/css/vendors/themify.css">
    <link rel="stylesheet" href="/assets/vendor/admin/css/ratio.css">
    <link rel="stylesheet" href="/assets/vendor/admin/css/remixicon.css">
    <link rel="stylesheet" href="/assets/vendor/admin/css/vendors/feather-icon.css">
    <link rel="stylesheet" href="/assets/vendor/admin/css/vendors/scrollbar.css">
    <link rel="stylesheet" href="/assets/vendor/admin/css/vendors/animate.css">
    <link rel="stylesheet" href="/assets/vendor/admin/css/vendors/bootstrap.css">
    <link rel="stylesheet" href="/assets/vendor/admin/css/style.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>

    <!-- кнопка «наверх» -->
    <div class="tap-top">
        <span class="lnr lnr-chevron-up"></span>
    </div>

    <div class="page-wrapper compact-wrapper" id="pageWrapper">

        <?php require __DIR__ . '/components/header.php'; ?>

        <div class="page-body-wrapper">

            <?php require __DIR__ . '/components/sidebar.php'; ?>

            <div class="page-body">
                <div class="container-fluid">
                    <?= $content ?>
                </div>

                <div class="container-fluid">
                    <footer class="footer">
                        <div class="row">
                            <div class="col-md-12 footer-copyright text-center">
                                <p class="mb-0">© <?= e(date('Y')) ?> «Сдоба» — панель управления</p>
                            </div>
                        </div>
                    </footer>
                </div>
            </div>
        </div>
    </div>

    <!-- Модалка выхода -->
    <div class="modal fade" id="logoutModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <h5 class="modal-title" id="logoutModalLabel">Выход</h5>
                    <p>Вы действительно хотите выйти?</p>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    <div class="button-box">
                        <button type="button" class="btn btn--no" data-bs-dismiss="modal">Нет</button>
                        <button type="button" onclick="location.href = '/login';"
                            class="btn btn--yes btn-primary">Да</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/vendor/admin/js/jquery-3.6.0.min.js"></script>
    <script src="/assets/vendor/admin/js/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="/assets/vendor/admin/js/icons/feather-icon/feather.min.js"></script>
    <script src="/assets/vendor/admin/js/icons/feather-icon/feather-icon.js"></script>
    <script src="/assets/vendor/admin/js/scrollbar/simplebar.js"></script>
    <script src="/assets/vendor/admin/js/scrollbar/custom.js"></script>
    <script src="/assets/vendor/admin/js/config.js"></script>
    <script src="/assets/vendor/admin/js/tooltip-init.js"></script>
    <script src="/assets/vendor/admin/js/sidebar-menu.js"></script>
    <script src="/assets/vendor/admin/js/sidebareffect.js"></script>
    <!-- ApexCharts — ядро подключено для дашборда (Фаза 5); инициализация графиков там же -->
    <script src="/assets/vendor/admin/js/chart/apex-chart/moment.min.js"></script>
    <script src="/assets/vendor/admin/js/chart/apex-chart/apex-chart.js"></script>
    <script src="/assets/vendor/admin/js/script.js"></script>
    <script type="module" src="/assets/js/admin.js"></script>
</body>
</html>
