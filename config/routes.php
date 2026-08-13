<?php

declare(strict_types=1);

/**
 * Все маршруты приложения.
 * Формат: 'МЕТОД' => ['/путь' => ['ИмяКонтроллера', 'метод']]
 */

return [
    'GET' => [
        '/'         => ['HomeController', 'index'],
        '/login'    => ['AuthController', 'showLogin'],
        '/register' => ['AuthController', 'showRegister'],

        // ВРЕМЕННО (Таск 3): проверка layout админки. Удаляется в Таске 5
        // вместе с AdminPreviewController — там появится настоящий GET /admin.
        '/admin/_preview' => ['AdminPreviewController', 'index'],
    ],
    'POST' => [
        '/login'    => ['AuthController', 'login'],
        '/register' => ['AuthController', 'register'],
        '/logout'   => ['AuthController', 'logout'],
    ],
];
