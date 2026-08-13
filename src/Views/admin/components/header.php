<?php

declare(strict_types=1);

/**
 * Шапка админки (.page-header) темы Fastkart, локализованная.
 *
 * Имя администратора — заглушка Фазы 0 (реальный пользователь появится с
 * авторизацией в Фазе 4). Выводится через e() по правилу вывода динамики.
 */

$adminName = $adminName ?? 'Администратор';
?>
<div class="page-header">
    <div class="header-wrapper m-0">
        <div class="header-logo-wrapper p-0">
            <div class="logo-wrapper">
                <a class="admin-logo" href="/admin">Сдоба</a>
            </div>
            <div class="toggle-sidebar">
                <i class="status_toggle middle sidebar-toggle" data-feather="align-center"></i>
                <a class="admin-logo" href="/admin">Сдоба</a>
            </div>
        </div>

        <form class="form-inline search-full" action="javascript:void(0)" method="get" role="search">
            <div class="form-group w-100">
                <div class="u-posRelative">
                    <input class="demo-input form-control-plaintext w-100" type="search"
                        placeholder="Поиск по админке…" name="q" aria-label="Поиск по админке" title="">
                    <i class="close-search" data-feather="x"></i>
                </div>
            </div>
        </form>

        <div class="nav-right col-6 pull-right right-header p-0">
            <ul class="nav-menus">
                <li>
                    <span class="header-search">
                        <i class="ri-search-line"></i>
                    </span>
                </li>
                <li class="onhover-dropdown">
                    <div class="notification-box">
                        <i class="ri-notification-line"></i>
                        <span class="badge rounded-pill badge-theme">0</span>
                    </div>
                    <ul class="notification-dropdown onhover-show-div">
                        <li>
                            <i class="ri-notification-line"></i>
                            <h6 class="f-18 mb-0">Уведомления</h6>
                        </li>
                        <li>
                            <p class="mb-0">Новых уведомлений нет</p>
                        </li>
                    </ul>
                </li>
                <li class="profile-nav onhover-dropdown pe-0 me-0">
                    <div class="media profile-media">
                        <img class="user-profile rounded-circle" src="/assets/vendor/admin/images/users/4.jpg"
                            alt="Аватар администратора">
                        <div class="user-name-hide media-body">
                            <span><?= e($adminName) ?></span>
                            <p class="mb-0 font-roboto">Администратор<i class="middle ri-arrow-down-s-line"></i></p>
                        </div>
                    </div>
                    <ul class="profile-dropdown onhover-show-div">
                        <li>
                            <a href="#">
                                <i data-feather="settings"></i>
                                <span>Настройки</span>
                            </a>
                        </li>
                        <li>
                            <a data-bs-toggle="modal" data-bs-target="#logoutModal" href="javascript:void(0)">
                                <i data-feather="log-out"></i>
                                <span>Выйти</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
