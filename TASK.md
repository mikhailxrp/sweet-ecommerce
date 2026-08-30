# Current Task

## Фаза
Phase 1 — Каталог

## Задача
`/search?q=...` ищет по названию и описанию товара через
`MATCH ... AGAINST`; в шапке работают живые подсказки по мере ввода.

Источник вёрстки: `example-template/front-end/search.html`.

## Scope — что трогаем

- [x] `src/Core/catalog.php` — изменить, добавить:
      `normalizeSearchQuery(string $raw): string` (trim, схлопывание
      повторных пробелов, обрезка длины до разумного максимума),
      `resolveSearchStrategy(string $query): string` — `'empty'`
      (пустая строка), `'prefix'` (1–2 символа, `mb_strlen`) или
      `'fulltext'` (3+ символов); порог — из-за
      `innodb_ft_min_token_size = 3` на shared-хостинге
- [x] `tests/Unit/SearchTest.php` — создать: тесты обеих функций —
      кириллица, пробелы по краям/внутри, пустая строка, 1–2 символа,
      мусорный/сверхдлинный ввод
- [x] `src/Models/ProductModel.php` — изменить, добавить:
      `searchProducts(string $strategy, string $query, string $orderBySql, int $limit, int $offset): array`
      и `countSearchResults(string $strategy, string $query): int`.
      `fulltext` → `MATCH(p.name, p.description) AGAINST(:query IN NATURAL LANGUAGE MODE)`;
      `prefix` → `p.name LIKE :prefix` (`'%'` дописывается в PHP перед
      биндингом, не конкатенацией строки запроса в SQL). Форма строки
      результата — как у `findByCategoryIds()`, для `product-card.php`
- [x] `src/Controllers/SearchController.php` — создать:
      `index()` (страница результатов: нормализация → стратегия →
      сортировка/пагинация как в Таске 3) и `suggest()` (JSON, до 5
      результатов, `Content-Type: application/json`)
- [x] `src/Views/shop/search.php` — создать: заголовок, форма (значение
      `q` сохраняется), сетка на `product-card.php`, пагинация;
      отдельные состояния для пустого запроса и «ничего не найдено»
- [x] `public/assets/js/search.js` — создать: debounce на вводе в поле
      шапки, `fetch('/search/suggest?q=...')`, рендер выпадающего
      списка через `textContent` (не `innerHTML`), закрытие по клику вне
      и по `Escape`
- [x] `src/Views/shop/components/header.php` — изменить: контейнер под
      подсказки рядом с полем поиска, подключение
      `<script type="module" src="/assets/js/search.js">` — модуль
      грузится всегда (форма поиска в шапке есть на каждой странице),
      `layout.php` не трогаем
- [x] `config/routes.php` — изменить: `GET /search` →
      `['SearchController', 'index']`, `GET /search/suggest` →
      `['SearchController', 'suggest']`

## Out of scope — не трогаем
- Фильтры сайдбара (цена/кондитер/наличие/вес/без сахара/рейтинг,
  Таск 4) на странице поиска — только сортировка и пагинация
- Quick View — Таск 7
- Опечатки, морфология, синонимы — не входят в FR-CAT-06
- Главная (`/`) — Таск 7
- `/catalog`, `/catalog/{slug}`, `/product/{slug}` — не трогаем
- `layout.php` — общий для всей витрины; скрипт поиска подключается
  локально из `header.php`, не там

## Решения таска

**FULLTEXT — только `NATURAL LANGUAGE MODE`.** `BOOLEAN MODE` требует
экранировать операторы (`+`, `-`, `"`, `*`) во введённой пользователем
строке — лишняя сложность без явной потребности. `NATURAL LANGUAGE MODE`
безопасен как обычный bound-параметр и не нуждается в экранировании.

**Стратегия — по длине всего запроса, не по длине отдельных слов.**
`innodb_ft_min_token_size = 3` ограничивает именно длину слова в
FULLTEXT-индексе; на shared-хостинге это значение менять нельзя (см.
`database.md`). Порог `resolveSearchStrategy()` — 3 символа на весь
нормализованный запрос: это соответствует прямому смыслу пункта DoD
фазы («запрос 1–2 символа») и проще, чем разбирать запрос на слова.

**Подсказки переиспользуют ту же функцию поиска**, что и страница
результатов (`searchProducts()` с маленьким `LIMIT` и `offset = 0`,
сортировка по умолчанию) — не заводим второй SQL-запрос ради того же
результата в другом объёме.

**Результаты — на `product-card.php`, не на карточке из макета
(`product-box-3`).** Второй вид карточки товара ради одной страницы
поиска не нужен — тот же компонент, что в каталоге и «Похожих товарах».

## Definition of Done
- [x] Запрос 3+ символа ищет через `MATCH ... AGAINST`, не
      `LIKE '%...%'` (FR-CAT-06) — подтверждено `EXPLAIN`:
      `type: fulltext`, `key: ft_products_name_description`
- [x] Запрос 1–2 символа обслуживается префиксным `name LIKE 'абв%'`
      (проверено: `q=т` и `q=то` возвращают только товары с таким
      началом названия)
- [x] Пустой запрос (`/search` без `q` или `q=""`) — понятная страница
      с приглашением ввести запрос, не ошибка и не пустой экран
- [x] Запрос без результатов — понятное сообщение «ничего не найдено»
- [x] Подсказки отдаются `/search/suggest` с `LIMIT` (5); на клиенте —
      debounce; вставка результата в DOM через `textContent`, не
      `innerHTML`
- [x] Результаты пагинируются (`products_per_page` из `settings`, как в
      Таске 3), сортировка — тот же whitelist (`popular/new/price_asc/
      price_desc/rating`), значение `q` сохраняется в ссылках сортировки
      и пагинации (проверено на `q=торт`)
- [x] SQL только в `ProductModel`; весь вывод во View — через `e()`
- [x] `composer test` зелёный: юнит-тесты на `normalizeSearchQuery()` и
      `resolveSearchStrategy()` (87/87, 10 новых тестов)
- [ ] Консоль браузера чистая — **не проверено визуально**: в этой
      сессии нет браузера, только встроенный PHP-сервер и `curl`.
      `search.js` синтаксически корректен, `/search/suggest` возвращает
      корректный JSON на реальных запросах (проверено `curl`), но
      реальную работу debounce/выпадающего списка в браузере нужно
      посмотреть руками
- [x] `storage/logs/app.log` без ошибок и warnings после ручной проверки
- [ ] Проверить `.docs/dod-global.md` — пункт «нет ошибок в консоли
      браузера» и 320px — только руками, остальное закрыто

## Важные правила
- Следовать `CLAUDE.md`
- Работать только в рамках Scope
- Не менять файлы вне Scope
- Не рефакторить попутно
