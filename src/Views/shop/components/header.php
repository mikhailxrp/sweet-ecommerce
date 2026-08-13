<?php

declare(strict_types=1);

/**
 * Шапка витрины. Разметка и классы — из темы Fastkart (index-2.html),
 * текст локализован, ссылки ведут на маршруты проекта.
 *
 * Демо-контент макета (мега-меню с хардкодом категорий, переключатель
 * языка/валюты, товары в мини-корзине) не переносится: категории и
 * корзина наполняются динамически в следующих фазах.
 */
?>
<header class="pb-md-4 pb-0">
    <div class="header-top bg-dark">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-xxl-4 d-xxl-block d-none">
                    <div class="top-left-header">
                        <span class="text-white">Кондитерские изделия ручной работы от проверенных мастеров</span>
                    </div>
                </div>

                <div class="col-xxl-8">
                    <div class="header-offer justify-content-xxl-end justify-content-center">
                        <div class="timer-notification">
                            <h6 class="text-white mb-0">Добро пожаловать в «Сдобу» — свежая выпечка с доставкой каждый день</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="top-nav top-header sticky-header">
        <div class="container-fluid-lg">
        <div class="row">
            <div class="col-12">
                <div class="navbar-top">
                    <button class="navbar-toggler d-xl-none d-inline navbar-menu-button" type="button"
                        data-bs-toggle="offcanvas" data-bs-target="#primaryMenu">
                        <span class="navbar-toggler-icon">
                            <i class="fa-solid fa-bars"></i>
                        </span>
                    </button>

                    <a href="/" class="nav-logo site-logo" aria-label="Сдоба — на главную">Сдоба</a>

                    <div class="middle-box">
                        <div class="search-box">
                            <form action="/search" method="get" role="search">
                                <div class="input-group">
                                    <input type="search" name="q" class="form-control"
                                        placeholder="Поиск по товарам..." aria-label="Поиск по товарам">
                                    <button class="btn search-button-2" type="submit" aria-label="Искать">
                                        <i data-feather="search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="rightside-box">
                        <ul class="right-side-menu">
                            <li class="right-side">
                                <a href="tel:+74951234567" class="delivery-login-box">
                                    <div class="delivery-icon">
                                        <i data-feather="phone-call"></i>
                                    </div>
                                    <div class="delivery-detail">
                                        <h6>Доставка 24/7</h6>
                                        <h5>+7 495 123-45-67</h5>
                                    </div>
                                </a>
                            </li>

                            <li class="right-side">
                                <a href="/wishlist" class="btn p-0 position-relative header-wishlist"
                                    aria-label="Избранное">
                                    <i data-feather="heart"></i>
                                </a>
                            </li>

                            <li class="right-side">
                                <div class="onhover-dropdown header-badge">
                                    <button type="button" class="btn p-0 position-relative header-wishlist"
                                        aria-label="Корзина">
                                        <i data-feather="shopping-cart"></i>
                                        <span class="position-absolute top-0 start-100 translate-middle badge">0
                                            <span class="visually-hidden">товаров в корзине</span>
                                        </span>
                                    </button>

                                    <div class="onhover-div">
                                        <ul class="cart-list">
                                            <li class="product-box-contain">
                                                <p class="mb-0 text-content">Ваша корзина пуста</p>
                                            </li>
                                        </ul>

                                        <div class="button-group">
                                            <a href="/cart" class="btn btn-sm cart-button">Перейти в корзину</a>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            <li class="right-side onhover-dropdown">
                                <div class="delivery-login-box">
                                    <div class="delivery-icon">
                                        <i data-feather="user"></i>
                                    </div>
                                    <div class="delivery-detail">
                                        <h6>Здравствуйте,</h6>
                                        <h5>Мой аккаунт</h5>
                                    </div>
                                </div>

                                <div class="onhover-div onhover-div-login">
                                    <ul class="user-box-name">
                                        <li class="product-box-contain">
                                            <a href="/login">Войти</a>
                                        </li>
                                        <li class="product-box-contain">
                                            <a href="/register">Регистрация</a>
                                        </li>
                                        <li class="product-box-contain">
                                            <a href="/forgot">Забыли пароль</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

    <div class="container-fluid-lg">
        <div class="row">
            <div class="col-12">
                <div class="header-nav">
                    <div class="header-nav-left">
                        <a href="/catalog" class="dropdown-category dropdown-category-2">
                            <i data-feather="align-left"></i>
                            <span>Все категории</span>
                        </a>

                        <div class="category-dropdown">
                            <div class="category-title">
                                <h5>Категории</h5>
                                <button type="button" class="btn p-0 close-button text-content" aria-label="Закрыть">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            <ul class="category-list">
                                <li class="onhover-category-list">
                                    <a href="/catalog" class="category-name">
                                        <h6>Торты</h6>
                                        <i class="fa-solid fa-angle-right"></i>
                                    </a>
                                    <div class="onhover-category-box">
                                        <div class="list-1">
                                            <div class="category-title-box"><h5>По коржам</h5></div>
                                            <ul>
                                                <li><a href="/catalog">Бисквитные</a></li>
                                                <li><a href="/catalog">Муссовые</a></li>
                                                <li><a href="/catalog">Медовые</a></li>
                                                <li><a href="/catalog">Наполеон</a></li>
                                            </ul>
                                        </div>
                                        <div class="list-2">
                                            <div class="category-title-box"><h5>По поводу</h5></div>
                                            <ul>
                                                <li><a href="/catalog">Свадебные</a></li>
                                                <li><a href="/catalog">Детские</a></li>
                                                <li><a href="/catalog">Праздничные</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </li>
                                <li class="onhover-category-list">
                                    <a href="/catalog" class="category-name">
                                        <h6>Пирожные</h6>
                                        <i class="fa-solid fa-angle-right"></i>
                                    </a>
                                    <div class="onhover-category-box">
                                        <div class="list-1">
                                            <div class="category-title-box"><h5>Виды</h5></div>
                                            <ul>
                                                <li><a href="/catalog">Эклеры</a></li>
                                                <li><a href="/catalog">Профитроли</a></li>
                                                <li><a href="/catalog">Картошка</a></li>
                                                <li><a href="/catalog">Корзиночки</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </li>
                                <li><a href="/catalog" class="category-name"><h6>Капкейки</h6></a></li>
                                <li><a href="/catalog" class="category-name"><h6>Печенье и пряники</h6></a></li>
                                <li><a href="/catalog" class="category-name"><h6>Круассаны и слойки</h6></a></li>
                                <li><a href="/catalog" class="category-name"><h6>Макаруны</h6></a></li>
                                <li><a href="/catalog" class="category-name"><h6>Чизкейки</h6></a></li>
                                <li><a href="/catalog" class="category-name"><h6>Хлеб и булочки</h6></a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="header-nav-middle">
                        <div class="main-nav navbar navbar-expand-xl navbar-light">
                            <div class="offcanvas offcanvas-collapse order-xl-2" id="primaryMenu">
                                <div class="offcanvas-header navbar-shadow">
                                    <h5>Меню</h5>
                                    <button class="btn-close lead" type="button"
                                        data-bs-dismiss="offcanvas" aria-label="Закрыть"></button>
                                </div>

                                <div class="offcanvas-body">
                                    <ul class="navbar-nav">
                                        <li class="nav-item">
                                            <a class="nav-link nav-link--plain" href="/">Главная</a>
                                        </li>
                                        <li class="nav-item dropdown">
                                            <a class="nav-link dropdown-toggle" href="/catalog"
                                                data-bs-toggle="dropdown">Каталог</a>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="/catalog">Все товары</a></li>
                                                <li><a class="dropdown-item" href="/catalog">Торты</a></li>
                                                <li><a class="dropdown-item" href="/catalog">Пирожные</a></li>
                                                <li><a class="dropdown-item" href="/catalog">Капкейки</a></li>
                                                <li><a class="dropdown-item" href="/catalog">Печенье и пряники</a></li>
                                                <li><a class="dropdown-item" href="/catalog">Круассаны и слойки</a></li>
                                                <li><a class="dropdown-item" href="/catalog">Макаруны</a></li>
                                                <li><a class="dropdown-item" href="/catalog">Чизкейки</a></li>
                                                <li><a class="dropdown-item" href="/catalog">Хлеб и булочки</a></li>
                                            </ul>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link nav-link--plain" href="/vendors">Кондитеры</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link nav-link--plain" href="/blog">Блог</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link nav-link--plain" href="/about">О нас</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link nav-link--plain" href="/contacts">Контакты</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
