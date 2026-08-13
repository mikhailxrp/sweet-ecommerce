<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

/**
 * Дашборд админки. Доступ только для роли admin — гейт на входе.
 * Наполнение (KPI, графики ApexCharts, таблицы) — Фаза 5; здесь заглушка.
 */
final class DashboardController
{
    public function index(): void
    {
        requireRole('admin');
        render('admin/dashboard');
    }
}
