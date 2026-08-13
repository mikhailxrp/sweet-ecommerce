<?php

declare(strict_types=1);

/**
 * Сайдбар админки (.sidebar-wrapper) темы Fastkart, локализованный.
 *
 * Фаза 0: реальный маршрут только «Дашборд» (/admin). Остальные пункты меню
 * сохранены из макета как каркас разделов админки и ведут на «#» — оживают
 * по мере фаз.
 * Пункты вырезанных из скоупа фич (мультиязычность/валюты) не переносятся.
 */
?>
<div class="sidebar-wrapper">
    <div id="sidebarEffect"></div>
    <div>
        <div class="logo-wrapper logo-wrapper-center">
            <a class="admin-logo" href="/admin">Сдоба</a>
            <div class="back-btn">
                <i class="fa fa-angle-left"></i>
            </div>
            <div class="toggle-sidebar">
                <i class="ri-apps-line status_toggle middle sidebar-toggle"></i>
            </div>
        </div>
        <div class="logo-icon-wrapper">
            <a class="admin-logo admin-logo--icon" href="/admin">С</a>
        </div>
        <nav class="sidebar-main">
            <div class="left-arrow" id="left-arrow">
                <i data-feather="arrow-left"></i>
            </div>

            <div id="sidebar-menu">
                <ul class="sidebar-links" id="simple-bar">
                    <li class="back-btn"></li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="/admin">
                            <i class="ri-home-line"></i>
                            <span>Дашборд</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="linear-icon-link sidebar-link sidebar-title" href="javascript:void(0)">
                            <i class="ri-store-3-line"></i>
                            <span>Товары</span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="#">Список товаров</a></li>
                            <li><a href="#">Добавить товар</a></li>
                        </ul>
                    </li>

                    <li class="sidebar-list">
                        <a class="linear-icon-link sidebar-link sidebar-title" href="javascript:void(0)">
                            <i class="ri-list-check-2"></i>
                            <span>Категории</span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="#">Список категорий</a></li>
                            <li><a href="#">Добавить категорию</a></li>
                        </ul>
                    </li>

                    <li class="sidebar-list">
                        <a class="linear-icon-link sidebar-link sidebar-title" href="javascript:void(0)">
                            <i class="ri-list-settings-line"></i>
                            <span>Атрибуты</span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="#">Атрибуты</a></li>
                            <li><a href="#">Добавить атрибут</a></li>
                        </ul>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title" href="javascript:void(0)">
                            <i class="ri-archive-line"></i>
                            <span>Заказы</span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="#">Список заказов</a></li>
                            <li><a href="#">Создать заказ</a></li>
                        </ul>
                    </li>

                    <li class="sidebar-list">
                        <a class="linear-icon-link sidebar-link sidebar-title" href="javascript:void(0)">
                            <i class="ri-price-tag-3-line"></i>
                            <span>Промокоды</span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="#">Список промокодов</a></li>
                            <li><a href="#">Создать промокод</a></li>
                        </ul>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title" href="javascript:void(0)">
                            <i class="ri-user-3-line"></i>
                            <span>Пользователи</span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="#">Все пользователи</a></li>
                            <li><a href="#">Добавить пользователя</a></li>
                        </ul>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title" href="javascript:void(0)">
                            <i class="ri-user-settings-line"></i>
                            <span>Роли</span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="#">Все роли</a></li>
                            <li><a href="#">Создать роль</a></li>
                        </ul>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="#">
                            <i class="ri-image-line"></i>
                            <span>Медиа</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="#">
                            <i class="ri-star-line"></i>
                            <span>Отзывы</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="#">
                            <i class="ri-price-tag-3-line"></i>
                            <span>Налоги и доставка</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="#">
                            <i class="ri-phone-line"></i>
                            <span>Тикеты поддержки</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="#">
                            <i class="ri-file-list-3-line"></i>
                            <span>Страницы</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="#">
                            <i class="ri-file-chart-line"></i>
                            <span>Отчёты</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="linear-icon-link sidebar-link sidebar-title" href="javascript:void(0)">
                            <i class="ri-settings-line"></i>
                            <span>Настройки</span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="#">Настройки профиля</a></li>
                        </ul>
                    </li>
                </ul>
            </div>

            <div class="right-arrow" id="right-arrow">
                <i data-feather="arrow-right"></i>
            </div>
        </nav>
    </div>
</div>
