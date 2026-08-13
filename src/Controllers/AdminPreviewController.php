<?php

declare(strict_types=1);

namespace App\Controllers;

/**
 * ВРЕМЕННЫЙ контроллер (Таск 3) — рендерит заглушку для ручной проверки
 * layout админки. В Таске 5 заменяется на App\Controllers\Admin\DashboardController
 * с реальным маршрутом /admin и проверкой роли; этот файл и роут /admin/_preview
 * удаляются.
 */
final class AdminPreviewController
{
    public function index(): void
    {
        render('admin/_preview');
    }
}
