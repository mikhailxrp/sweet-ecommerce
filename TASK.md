# Current Task

## Фаза
Phase 0 — Фундамент

## Задача
Собрать layout витрины на теме Fastkart: общий шаблон с шапкой и
подвалом, вендорные ассеты локально, локализация; `GET /` рендерит
пустую витрину в теме.

## Scope — что трогаем

**Вендорный слой (перенос AS IS из `example-template/assets/`):**
- [ ] `public/assets/vendor/css/` — `vendors/bootstrap.css`,
      `animate.min.css`, `bulk-style.css`, `style.css` (+ зависимые
      из `vendors/`)
- [ ] `public/assets/vendor/js/` — jQuery, `bootstrap.bundle`+`popper`,
      feather, slick + `custom_slick`, `lazysizes`, `wow.min`+`custom-wow`,
      `script.js` (плагины отдельных страниц — не здесь, см. out of scope)
- [ ] `public/assets/vendor/fonts/` — иконочные шрифты темы
      (feather / font-awesome / Iconly) в `.woff2/.woff` + локальные
      Russo One / Pacifico / Kaushan Script / Exo 2 с `@font-face`
- [ ] `public/assets/vendor/images/` — только картинки шапки и подвала
      (логотип, favicon, svg-иконки, иконки оплаты)

**Свой слой:**
- [ ] `public/assets/css/app.css` — создать:
      `:root { --theme-color:#d99f46; --theme-color-rgb:217,159,70 }`,
      BEM-переопределения поверх темы
- [ ] `public/assets/js/app.js` — создать: ES-модуль, точка входа витрины

**Представление и рендер:**
- [ ] `src/Views/shop/layout.php` — создать: HTML-скелет (`<head>` с
      локальными CSS и шрифтами, `<body>`, `<main>` с `$content`,
      подключение вендорного JS + `app.js`)
- [ ] `src/Views/shop/components/header.php` — создать: шапка из
      `index-2.html`, локализованная
- [ ] `src/Views/shop/components/footer.php` — создать: подвал из
      `index-2.html`, локализованный
- [ ] `src/Views/shop/home.php` — создать: пустая витрина (буферизует
      контент в `$content`, подключает `layout.php`)
- [ ] `src/Controllers/HomeController.php` — создать: `index()` →
      `render('shop/home')`

*(`config/routes.php` не трогаем — `GET / → HomeController::index` уже
прописан.)*

## Out of scope — не трогаем
- Контент главной: слайдеры, деал-таймеры, блоки товаров и категорий —
  наполняется в Фазе 1; здесь `main` пустой
- Плагины отдельных страниц (`fly-cart`, `timer*`, `quantity*`,
  `filter-sidebar`, `ion.rangeSlider`, `elevatezoom`) — переносятся со
  своими страницами (корзина / каталог / товар) в следующих фазах
- Демо-панель переключателя темы (`theme-setting.js` + виджет
  `.theme-setting`, `dark.css`) — инструмент макета, не переносим
- Layout админки — Таск 3
- Авторизация, формы `login` / `register` — Таск 4
- Гейт ролей, 403, дашборд админа, `/vendor-panel` — Таск 5
  (`HomeController` там уже будет существовать)

## Definition of Done
- [ ] `GET /` открывается: рендерится layout витрины в теме Fastkart
      (шапка + пустой `main` + подвал)
- [ ] Шапка и подвал: `lang="ru"`, `dir` убран, весь текст переведён
      на русский
- [ ] Все CSS/JS/шрифты локальные — в Network ноль 404 и ноль запросов
      к `googleapis` / `gstatic` / любому CDN
- [ ] `@font-face` без `.eot/.ttf/.svg` (только `.woff2/.woff`)
- [ ] Консоль браузера чистая (нет JS-ошибок)
- [ ] Корректно на 320px (mobile-first)
- [ ] `storage/logs/app.log` без PHP-ошибок и warnings
- [ ] Вывод динамики в шапке и подвале через `e()`
- [ ] Проверить `.docs/dod-global.md` (юнит-тест не требуется — это
      Views/HTML без чистой логики)

## Важные правила
- Следовать `CLAUDE.md`
- Работать только в рамках Scope
- Не менять файлы вне Scope
- Не рефакторить попутно
- Вендорные файлы в `public/assets/vendor/` — AS IS, не форматировать
  и не чинить
