<?php

declare(strict_types=1);

namespace App\Controllers;

/**
 * Главная витрины.
 */
final class HomeController
{
    public function index(): void
    {
        render('shop/home');
    }
}
