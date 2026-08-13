<?php

declare(strict_types=1);

/**
 * Подвал витрины. Разметка и классы — из темы Fastkart (index-2.html),
 * текст локализован, ссылки ведут на маршруты проекта.
 */
?>
<footer class="section-t-space">
    <div class="container-fluid-lg">
        <div class="service-section">
            <div class="row g-3">
                <div class="col-12">
                    <div class="service-contain">
                        <div class="service-box">
                            <div class="service-image">
                                <img src="/assets/vendor/svg/product.svg" alt="Свежая выпечка">
                            </div>
                            <div class="service-detail">
                                <h5>Всегда свежая выпечка</h5>
                            </div>
                        </div>

                        <div class="service-box">
                            <div class="service-image">
                                <img src="/assets/vendor/svg/delivery.svg" alt="Доставка">
                            </div>
                            <div class="service-detail">
                                <h5>Бесплатная доставка от 3 000 ₽</h5>
                            </div>
                        </div>

                        <div class="service-box">
                            <div class="service-image">
                                <img src="/assets/vendor/svg/discount.svg" alt="Скидки">
                            </div>
                            <div class="service-detail">
                                <h5>Ежедневные скидки</h5>
                            </div>
                        </div>

                        <div class="service-box">
                            <div class="service-image">
                                <img src="/assets/vendor/svg/market.svg" alt="Цены">
                            </div>
                            <div class="service-detail">
                                <h5>Лучшие цены на рынке</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="main-footer section-b-space section-t-space">
            <div class="row g-md-4 g-3">
                <div class="col-xl-3 col-lg-4 col-sm-6">
                    <div class="footer-logo">
                        <div class="theme-logo">
                            <a href="/" class="site-logo" aria-label="Сдоба — на главную">Сдоба</a>
                        </div>

                        <div class="footer-logo-contain">
                            <p>
                                «Сдоба» — маркетплейс кондитерских изделий. Собирайте заказ из
                                тортов, пирожных и капкейков от разных мастеров в одной корзине.
                            </p>

                            <ul class="address">
                                <li>
                                    <i data-feather="home"></i>
                                    <a href="javascript:void(0)">Москва, ул. Пекарская, 12</a>
                                </li>
                                <li>
                                    <i data-feather="mail"></i>
                                    <a href="mailto:support@sdoba.ru">support@sdoba.ru</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <div class="footer-title">
                        <h4>Категории</h4>
                    </div>
                    <div class="footer-contain">
                        <ul>
                            <li><a href="/catalog" class="text-content">Торты</a></li>
                            <li><a href="/catalog" class="text-content">Пирожные</a></li>
                            <li><a href="/catalog" class="text-content">Капкейки</a></li>
                            <li><a href="/catalog" class="text-content">Печенье и пряники</a></li>
                            <li><a href="/catalog" class="text-content">Круассаны и слойки</a></li>
                            <li><a href="/catalog" class="text-content">Макаруны</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-xl col-lg-2 col-sm-3">
                    <div class="footer-title">
                        <h4>Навигация</h4>
                    </div>
                    <div class="footer-contain">
                        <ul>
                            <li><a href="/" class="text-content">Главная</a></li>
                            <li><a href="/catalog" class="text-content">Каталог</a></li>
                            <li><a href="/vendors" class="text-content">Кондитеры</a></li>
                            <li><a href="/blog" class="text-content">Блог</a></li>
                            <li><a href="/contacts" class="text-content">Контакты</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-xl-2 col-sm-3">
                    <div class="footer-title">
                        <h4>Помощь</h4>
                    </div>
                    <div class="footer-contain">
                        <ul>
                            <li><a href="/account/orders" class="text-content">Мои заказы</a></li>
                            <li><a href="/account" class="text-content">Личный кабинет</a></li>
                            <li><a href="/order/track" class="text-content">Отследить заказ</a></li>
                            <li><a href="/wishlist" class="text-content">Избранное</a></li>
                            <li><a href="/faq" class="text-content">Вопросы и ответы</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-sm-6">
                    <div class="footer-title">
                        <h4>Контакты</h4>
                    </div>
                    <div class="footer-contact">
                        <ul>
                            <li>
                                <div class="footer-number">
                                    <i data-feather="phone"></i>
                                    <div class="contact-number">
                                        <h6 class="text-content">Горячая линия 24/7:</h6>
                                        <h5>+7 495 123-45-67</h5>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="footer-number">
                                    <i data-feather="mail"></i>
                                    <div class="contact-number">
                                        <h6 class="text-content">Электронная почта:</h6>
                                        <h5>support@sdoba.ru</h5>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="sub-footer section-small-space">
            <div class="reserve">
                <h6 class="text-content">© 2026 «Сдоба». Все права защищены</h6>
            </div>

            <div class="payment">
                <img src="/assets/vendor/images/payment/1.png" alt="Способы оплаты">
            </div>

            <div class="social-link">
                <h6 class="text-content">Мы в соцсетях:</h6>
                <ul>
                    <li>
                        <a href="https://vk.com/" target="_blank" rel="noopener" aria-label="ВКонтакте">
                            <i class="fa-brands fa-vk"></i>
                        </a>
                    </li>
                    <li>
                        <a href="https://t.me/" target="_blank" rel="noopener" aria-label="Telegram">
                            <i class="fa-brands fa-telegram"></i>
                        </a>
                    </li>
                    <li>
                        <a href="https://wa.me/" target="_blank" rel="noopener" aria-label="WhatsApp">
                            <i class="fa-brands fa-whatsapp"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</footer>
