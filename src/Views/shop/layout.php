<?php

declare(strict_types=1);

/**
 * Layout витрины. Оборачивает контент страницы в тему Fastkart.
 *
 * Ожидает переменные от страницы-вью:
 *   $content — готовый HTML основной части (буферизуется во вью);
 *   $title   — заголовок вкладки (необязательно).
 *
 * Вендорные CSS/JS — слой темы в /assets/vendor (AS IS). Свой слой —
 * /assets/css/app.css и /assets/js/app.js — подключается последним.
 */

$title = $title ?? 'Сдоба — маркетплейс кондитерских изделий';
$content = $content ?? '';
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>

    <link rel="icon" type="image/png" href="/assets/vendor/images/favicon/2.png">

    <link rel="stylesheet" href="/assets/vendor/css/fonts-google.css">
    <link rel="stylesheet" href="/assets/vendor/css/vendors/bootstrap.css">
    <link rel="stylesheet" href="/assets/vendor/css/animate.min.css">
    <link rel="stylesheet" href="/assets/vendor/css/bulk-style.css">
    <link rel="stylesheet" href="/assets/vendor/css/style.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="theme-color-1">

    <!-- Прелоадер темы. Его удаляет script.js по classList после загрузки
         страницы; без этого элемента loader-код темы падает с TypeError. -->
    <div class="fullpage-loader">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>

    <?php require __DIR__ . '/components/header.php'; ?>

    <main>
        <?= $content ?>
    </main>

    <?php require __DIR__ . '/components/footer.php'; ?>

    <script src="/assets/vendor/js/jquery-3.6.0.min.js"></script>
    <script src="/assets/vendor/js/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="/assets/vendor/js/feather/feather.min.js"></script>
    <script src="/assets/vendor/js/feather/feather-icon.js"></script>
    <script src="/assets/vendor/js/lazysizes.min.js"></script>
    <script src="/assets/vendor/js/script.js"></script>
    <script type="module" src="/assets/js/app.js"></script>
</body>
</html>
