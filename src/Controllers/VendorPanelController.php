<?php

declare(strict_types=1);

namespace App\Controllers;

/**
 * Кабинет кондитера. В Фазе 0 — только гейт роли `vendor` (гость → /login,
 * чужая роль → 403) и заглушка «раздел в разработке». Настоящий кабинет
 * (витрины, товары, суб-заказы, комиссии) — Фаза 7; этот контроллер там
 * расширяется.
 */
final class VendorPanelController
{
    public function index(): void
    {
        requireRole('vendor');
        render('shop/vendor-panel');
    }
}
