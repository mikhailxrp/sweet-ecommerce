<?php

declare(strict_types=1);

/**
 * Страница входа. Вёрстка — секция `.log-in-section` темы Fastkart
 * (`front-end/login.html`), локализована. Соц-вход и «запомнить меня» не
 * переносились (внешний OAuth / persistent-токены вне скоупа Фазы 0).
 *
 * Данные от контроллера:
 *   $errors — массив ошибок (ключи полей + 'form' для общей ошибки);
 *   $old    — прошлый ввод (email).
 */

$title  = 'Вход — Сдоба';
$errors = $errors ?? [];
$old    = $old ?? [];

ob_start();
?>
<section class="breadcrumb-section pt-0">
    <div class="container-fluid-lg">
        <div class="row">
            <div class="col-12">
                <div class="breadcrumb-contain">
                    <h2 class="mb-2">Вход</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="/">Главная</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Вход</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="log-in-section section-b-space">
    <div class="container-fluid-lg w-100">
        <div class="row">
            <div class="col-xxl-6 col-xl-5 col-lg-6 d-lg-block d-none ms-auto">
                <div class="image-contain">
                    <img src="/assets/vendor/images/inner-page/log-in.png" class="img-fluid"
                        alt="Иллюстрация входа">
                </div>
            </div>

            <div class="col-xxl-4 col-xl-5 col-lg-6 col-sm-8 mx-auto">
                <div class="log-in-box">
                    <div class="log-in-title">
                        <h3>Добро пожаловать в «Сдобу»</h3>
                        <h4>Войдите в аккаунт</h4>
                    </div>

                    <div class="input-box">
                        <form class="row g-4" action="/login" method="post" novalidate>
                            <?= csrfField() ?>

                            <?php if (!empty($errors['form'])): ?>
                                <div class="col-12">
                                    <div class="alert alert-danger mb-0" role="alert">
                                        <?= e($errors['form']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="col-12">
                                <div class="form-floating theme-form-floating log-in-form">
                                    <input type="email" name="email"
                                        class="form-control<?= isset($errors['email']) ? ' is-invalid' : '' ?>"
                                        id="email" placeholder="Email" value="<?= e($old['email'] ?? '') ?>">
                                    <label for="email">Email</label>
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback"><?= e($errors['email']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-floating theme-form-floating log-in-form">
                                    <input type="password" name="password"
                                        class="form-control<?= isset($errors['password']) ? ' is-invalid' : '' ?>"
                                        id="password" placeholder="Пароль">
                                    <label for="password">Пароль</label>
                                    <?php if (isset($errors['password'])): ?>
                                        <div class="invalid-feedback"><?= e($errors['password']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-12">
                                <button class="btn btn-animation w-100 justify-content-center" type="submit">
                                    Войти
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="sign-up-box">
                        <h4>Нет аккаунта?</h4>
                        <a href="/register">Зарегистрироваться</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();

require __DIR__ . '/../layout.php';
