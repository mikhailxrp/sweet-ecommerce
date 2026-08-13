# Схема БД

> Этот файл — детальное описание всех таблиц.
> Агент читает его при планировании тасков связанных с БД.
> Синхронизируй с `database/install.php`.

**Проект:** маркетплейс кондитерских изделий (см. `.docs/prd.md`)
**СУБД:** MySQL 8, InnoDB, `utf8mb4_unicode_ci`

---

## Как устроен этот файл

Схема выросла из базового набора шаблона (каталог → корзина → заказ),
рассчитанного на 10 000–50 000 товаров. Базовые таблицы **расширены
колонками**, а не переписаны.

Разделы:

1. **Базовые таблицы** — были в шаблоне, дополнены под проект
2. **Мультивендор** — кондитеры, суб-заказы, комиссии, выплаты
3. **Каталог** — варианты и характеристики товаров
4. **Заказы и доставка** — зоны, история статусов, промокоды
5. **Аккаунт** — адреса, избранное, сброс пароля, роли
6. **Отзывы**
7. **Контент** — блог, страницы, баннеры, меню
8. **Коммуникации** — тикеты, рассылка, напоминания о корзине
9. **Служебное** — настройки

При добавлении новой таблицы: сначала описать здесь по формату
(колонка | тип | назначение), потом продублировать в
`database/install.php` в порядке зависимостей — родитель раньше
потомка.

---

# 1. Базовые таблицы

### `users`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| name | VARCHAR(100) NOT NULL | |
| email | VARCHAR(150) NOT NULL UNIQUE | |
| password_hash | VARCHAR(255) NOT NULL | |
| phone | VARCHAR(20) NULL | нужен для доставки и отслеживания заказа |
| avatar_path | VARCHAR(255) NULL | |
| role | ENUM('customer','vendor','admin') NOT NULL DEFAULT 'customer' | **основной гейт доступа** — проверяется в контроллерах |
| role_id | INT NULL, FK → roles.id, ON DELETE SET NULL | детализация прав внутри админки, только для `role = 'admin'` |
| is_active | TINYINT(1) NOT NULL DEFAULT 1 | блокировка аккаунта без удаления |
| created_at | TIMESTAMP DEFAULT NOW | |
| updated_at | TIMESTAMP DEFAULT NOW ON UPDATE CURRENT_TIMESTAMP | |

**Индексы:** `INDEX(role)`, `INDEX(phone)`

> Два механизма прав намеренно разделены: `role` решает «пустить или
> нет» (это и проверяет `dod-global.md`), `role_id` — «какие разделы
> админки показать сотруднику». Покупателя и кондитера `role_id` не
> касается, у них он всегда NULL.

---

### `categories`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| parent_id | INT NULL, FK → categories.id, ON DELETE SET NULL | подкатегории; NULL = корневая |
| name | VARCHAR(150) NOT NULL | |
| slug | VARCHAR(160) NOT NULL UNIQUE | для человекопонятных URL |
| description | TEXT NULL | текст под листингом, для SEO |
| image_path | VARCHAR(255) NULL | плитка категории на главной |
| is_active | TINYINT(1) NOT NULL DEFAULT 1 | |
| sort_order | INT NOT NULL DEFAULT 0 | порядок в меню |
| meta_title | VARCHAR(255) NULL | |
| meta_description | VARCHAR(500) NULL | |
| created_at | TIMESTAMP DEFAULT NOW | |

**Индексы:** `INDEX(parent_id)`, `INDEX(is_active, sort_order)`

---

### `products`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| vendor_id | INT NOT NULL, FK → vendors.id, ON DELETE RESTRICT | владелец товара |
| category_id | INT NOT NULL, FK → categories.id, ON DELETE RESTRICT | нельзя удалить категорию, пока в ней есть товары |
| sku | VARCHAR(64) NOT NULL UNIQUE | артикул |
| name | VARCHAR(200) NOT NULL | |
| slug | VARCHAR(220) NOT NULL UNIQUE | |
| short_description | VARCHAR(500) NULL | текст в карточке листинга |
| description | TEXT NULL | |
| price | DECIMAL(10,2) NOT NULL | никогда FLOAT — см. `php.md` |
| old_price | DECIMAL(10,2) NULL | зачёркнутая цена; NULL = скидки нет |
| stock_quantity | INT NOT NULL DEFAULT 0 | **не используется, если `has_variants = 1`** |
| has_variants | TINYINT(1) NOT NULL DEFAULT 0 | флаг вместо подзапроса в каждом листинге |
| unit | ENUM('piece','kg','set') NOT NULL DEFAULT 'piece' | штука / вес / набор |
| weight_grams | INT NULL | |
| composition | TEXT NULL | состав |
| allergens | VARCHAR(255) NULL | обязательное поле для пищевого товара |
| calories_per_100g | INT NULL | |
| shelf_life_hours | INT NULL | срок годности |
| storage_conditions | VARCHAR(255) NULL | «хранить при +2…+6 °C» |
| lead_time_hours | INT NOT NULL DEFAULT 0 | минимальный срок изготовления; ограничивает выбор даты доставки |
| is_sugar_free | TINYINT(1) NOT NULL DEFAULT 0 | фильтр «без сахара» |
| moderation_status | ENUM('draft','pending','approved','rejected') NOT NULL DEFAULT 'draft' | товар кондитера публикуется после проверки |
| rejection_reason | VARCHAR(500) NULL | что исправить кондитеру |
| rating_avg | DECIMAL(3,2) NOT NULL DEFAULT 0.00 | денормализация, пересчёт при смене статуса отзыва |
| reviews_count | INT NOT NULL DEFAULT 0 | денормализация |
| sales_count | INT NOT NULL DEFAULT 0 | сортировка «популярные» |
| views_count | INT NOT NULL DEFAULT 0 | |
| is_active | TINYINT(1) NOT NULL DEFAULT 1 | товар не удаляют физически, а деактивируют |
| meta_title | VARCHAR(255) NULL | |
| meta_description | VARCHAR(500) NULL | |
| created_at | TIMESTAMP DEFAULT NOW | |
| updated_at | TIMESTAMP DEFAULT NOW ON UPDATE CURRENT_TIMESTAMP | |

**Индексы:**
- `INDEX(category_id, is_active, moderation_status)` — основной листинг каталога
- `INDEX(vendor_id, is_active)` — витрина кондитера и его кабинет
- `INDEX(price)` — фильтр и сортировка по цене
- `INDEX(rating_avg)`, `INDEX(sales_count)`, `INDEX(created_at)` — сортировки листинга
- `INDEX(moderation_status)` — очередь модерации в админке
- `FULLTEXT(name, description)` — поиск; `LIKE '%...%'` не использует индекс и на 50k строк деградирует

> **Товар всегда принадлежит кондитеру**, даже собственный товар
> платформы: для него заводится служебная запись в `vendors`. Иначе
> `vendor_id` пришлось бы делать nullable и городить `IS NULL` в каждом
> запросе каталога.
>
> **Цена и остаток при вариантах.** Если `has_variants = 1`,
> источник истины — `product_variants`; `products.price` хранит
> минимальную цену варианта (для сортировки и вывода «от 1 200 ₽»),
> `products.stock_quantity` не используется и остаётся 0.
> Пересчитывается при любом изменении вариантов.

---

### `product_images`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| product_id | INT NOT NULL, FK → products.id, ON DELETE CASCADE | |
| path | VARCHAR(255) NOT NULL | относительный путь в `public/uploads/products/` |
| alt | VARCHAR(255) NULL | требование доступности из PRD §6 |
| sort_order | INT NOT NULL DEFAULT 0 | |
| is_main | TINYINT(1) NOT NULL DEFAULT 0 | главное фото карточки |

**Индексы:** `INDEX(product_id, sort_order)`

---

### `cart_items`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| session_id | VARCHAR(64) NULL | корзина гостя (PHP session id) |
| user_id | INT NULL, FK → users.id, ON DELETE CASCADE | корзина авторизованного |
| product_id | INT NOT NULL, FK → products.id, ON DELETE CASCADE | |
| variant_id | INT NULL, FK → product_variants.id, ON DELETE CASCADE | NULL, если у товара нет вариантов |
| quantity | INT NOT NULL DEFAULT 1 | |
| created_at | TIMESTAMP DEFAULT NOW | |
| updated_at | TIMESTAMP DEFAULT NOW ON UPDATE CURRENT_TIMESTAMP | нужен для писем о брошенной корзине |

**Индексы:** `INDEX(session_id)`, `INDEX(user_id)`, `INDEX(updated_at)`

> Цена здесь никогда не хранится — при чекауте всегда перечитывается из
> `products` / `product_variants` (см. «server is the source of truth» в
> `general.md`).
> Ровно одно из `session_id` / `user_id` должно быть заполнено —
> проверяется в Model, а не constraint'ом БД.
> Уникальность пары «корзина + товар + вариант» тоже держится в Model:
> в MySQL два NULL считаются разными значениями, поэтому UNIQUE-индекс
> с nullable `variant_id` и `user_id` работать не будет.

---

### `orders`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| order_number | VARCHAR(20) NOT NULL UNIQUE | человекочитаемый номер, `SD-2026-004317` |
| user_id | INT NULL, FK → users.id, **ON DELETE SET NULL** | гостевой заказ = NULL |
| customer_name | VARCHAR(100) NOT NULL | снэпшот — заказ оформляется и без регистрации |
| customer_phone | VARCHAR(20) NOT NULL | |
| customer_email | VARCHAR(150) NOT NULL | |
| status | ENUM('created','paid','processing','shipped','delivered','cancelled','refunded') NOT NULL DEFAULT 'created' | меняется только через одну функцию-переход, см. `php.md` |
| payment_method | ENUM('online','cash_on_delivery') NOT NULL | |
| payment_status | ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending' | |
| payment_id | VARCHAR(64) NULL | идентификатор платежа в ЮKassa |
| delivery_type | ENUM('courier','pickup') NOT NULL | |
| delivery_zone_id | INT NULL, FK → delivery_zones.id, ON DELETE SET NULL | |
| delivery_address | VARCHAR(500) NULL | снэпшот адреса строкой |
| delivery_date | DATE NULL | |
| delivery_slot | VARCHAR(20) NULL | `10:00-14:00` |
| delivery_cost | DECIMAL(10,2) NOT NULL DEFAULT 0.00 | |
| subtotal | DECIMAL(10,2) NOT NULL | сумма позиций до скидки и доставки |
| discount_total | DECIMAL(10,2) NOT NULL DEFAULT 0.00 | |
| coupon_id | INT NULL, FK → coupons.id, ON DELETE SET NULL | |
| coupon_code | VARCHAR(50) NULL | снэпшот — промокод могут удалить |
| total | DECIMAL(10,2) NOT NULL | |
| comment | TEXT NULL | пожелания покупателя |
| call_before | TINYINT(1) NOT NULL DEFAULT 0 | «позвонить перед доставкой» |
| cancel_reason | VARCHAR(255) NULL | |
| paid_at | TIMESTAMP NULL | |
| created_at | TIMESTAMP DEFAULT NOW | |
| updated_at | TIMESTAMP DEFAULT NOW ON UPDATE CURRENT_TIMESTAMP | |

**Индексы:** `UNIQUE(order_number)`, `INDEX(user_id)`, `INDEX(status)`,
`INDEX(created_at)`, `INDEX(customer_phone)` — отслеживание заказа
гостем, `INDEX(payment_id)` — поиск заказа при обработке вебхука

> **Инвариант суммы:** `total = subtotal − discount_total +
> delivery_cost`. Проверяется в Model при создании заказа, а не
> собирается из данных клиента.
>
> Исключение из правила «`user_id` → CASCADE»: заказы должны пережить
> удаление аккаунта (бухгалтерия и история), поэтому `SET NULL` —
> контакты покупателя остаются в снэпшот-колонках.
>
> `order_number` отделён от `id`, чтобы номер заказа не раскрывал
> обороты магазина.

---

### `order_items`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| order_id | INT NOT NULL, FK → orders.id, ON DELETE CASCADE | |
| vendor_order_id | INT NOT NULL, FK → vendor_orders.id, ON DELETE CASCADE | к какому суб-заказу относится позиция |
| product_id | INT NULL, FK → products.id, ON DELETE SET NULL | NULL, если товар позже удалили физически |
| variant_id | INT NULL, FK → product_variants.id, ON DELETE SET NULL | |
| product_name | VARCHAR(200) NOT NULL | снэпшот названия на момент заказа |
| variant_name | VARCHAR(100) NULL | снэпшот варианта, `2 кг` |
| product_image | VARCHAR(255) NULL | снэпшот пути к главному фото |
| price | DECIMAL(10,2) NOT NULL | снэпшот цены на момент заказа |
| quantity | INT NOT NULL | |

**Индексы:** `INDEX(order_id)`, `INDEX(vendor_order_id)`, `INDEX(product_id)`

> `order_id` хранится рядом с `vendor_order_id` намеренно: состав всего
> заказа показывается покупателю так же часто, как состав суб-заказа —
> кондитеру. Без этой колонки каждый показ заказа требовал бы лишнего
> JOIN через `vendor_orders`.
>
> Снэпшоты названия, варианта, фото и цены дублируются намеренно — если
> товар подорожает или исчезнет из каталога, история заказа не должна
> измениться задним числом.

---

### `payment_logs`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| order_id | INT NOT NULL, FK → orders.id, ON DELETE CASCADE | |
| provider | VARCHAR(50) NOT NULL | `yookassa` |
| event | VARCHAR(50) NULL | тип события вебхука |
| signature_valid | TINYINT(1) NOT NULL | результат проверки подписи вебхука |
| payload | TEXT NOT NULL | сырое тело запроса — для разбора спорных платежей |
| created_at | TIMESTAMP DEFAULT NOW | |

**Индексы:** `INDEX(order_id)`, `INDEX(created_at)`

> Обязательна, потому что `php.md` требует: «верифицировать подпись
> вебхука и логировать каждый вызов». Без этой таблицы требование нечем
> подтвердить при разборе спорного платежа.
>
> Вебхук идемпотентен: перед сменой статуса проверяется, не обработан ли
> уже этот `payment_id` (PRD FR-ORD-13).

---

# 2. Мультивендор

### `vendors`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| user_id | INT NOT NULL UNIQUE, FK → users.id, **ON DELETE RESTRICT** | владелец кабинета |
| name | VARCHAR(150) NOT NULL | название кондитерской |
| slug | VARCHAR(160) NOT NULL UNIQUE | `/vendor/{slug}` |
| description | TEXT NULL | |
| logo_path | VARCHAR(255) NULL | |
| banner_path | VARCHAR(255) NULL | шапка витрины |
| city | VARCHAR(100) NULL | |
| address | VARCHAR(255) NULL | адрес самовывоза |
| phone | VARCHAR(20) NULL | |
| email | VARCHAR(150) NULL | |
| inn | VARCHAR(12) NULL | реквизиты для выплат |
| commission_percent | DECIMAL(5,2) NOT NULL DEFAULT 15.00 | индивидуальная ставка; дефолт из `settings` |
| min_order_amount | DECIMAL(10,2) NULL | минимальная сумма заказа у кондитера |
| status | ENUM('pending','approved','rejected','suspended') NOT NULL DEFAULT 'pending' | модерация заявки |
| rejection_reason | VARCHAR(500) NULL | |
| rating_avg | DECIMAL(3,2) NOT NULL DEFAULT 0.00 | денормализация по одобренным отзывам |
| reviews_count | INT NOT NULL DEFAULT 0 | |
| approved_at | TIMESTAMP NULL | |
| created_at | TIMESTAMP DEFAULT NOW | |
| updated_at | TIMESTAMP DEFAULT NOW ON UPDATE CURRENT_TIMESTAMP | |

**Индексы:** `UNIQUE(user_id)`, `UNIQUE(slug)`, `INDEX(status)`,
`INDEX(city)`

> **Второе исключение из «`user_id` → CASCADE».** Удаление аккаунта
> кондитера не должно уносить его товары — на них ссылается история
> заказов. `RESTRICT` заставляет сначала перевести кондитера в
> `suspended`, и это правильный порядок действий, а не техническое
> неудобство.
>
> `commission_percent` здесь — **текущая** ставка. Ставка, по которой
> реально посчитали деньги, лежит в `vendor_orders`.

---

### `vendor_orders`

Суб-заказ: часть заказа, относящаяся к одному кондитеру.

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| order_id | INT NOT NULL, FK → orders.id, ON DELETE CASCADE | |
| vendor_id | INT NOT NULL, FK → vendors.id, ON DELETE RESTRICT | |
| status | ENUM('created','processing','ready','shipped','delivered','cancelled') NOT NULL DEFAULT 'created' | свой статус приготовления у каждого кондитера |
| subtotal | DECIMAL(10,2) NOT NULL | сумма позиций этого кондитера |
| commission_percent | DECIMAL(5,2) NOT NULL | **снэпшот ставки на момент оплаты** |
| commission_amount | DECIMAL(10,2) NOT NULL | удержано платформой |
| payout_amount | DECIMAL(10,2) NOT NULL | `subtotal − commission_amount` |
| payout_id | INT NULL, FK → vendor_payouts.id, ON DELETE SET NULL | в какую выплату вошёл |
| created_at | TIMESTAMP DEFAULT NOW | |
| updated_at | TIMESTAMP DEFAULT NOW ON UPDATE CURRENT_TIMESTAMP | |

**Индексы:** `UNIQUE(order_id, vendor_id)` — один суб-заказ на кондитера
в заказе, `INDEX(vendor_id, status)`, `INDEX(payout_id)`

> Отдельная сущность, а не флаг в `order_items` — иначе невозможно
> показать «торт отправлен, эклеры ещё готовятся» (PRD §7).
>
> `commission_percent` копируется сюда, потому что админ может поменять
> ставку кондитеру завтра, а реестр выплат за прошлый месяц пересчитаться
> не должен.
>
> Стоимость доставки в суб-заказ не входит — она принадлежит заказу
> целиком и в комиссию не попадает.

---

### `vendor_payouts`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| vendor_id | INT NOT NULL, FK → vendors.id, ON DELETE RESTRICT | |
| period_from | DATE NOT NULL | |
| period_to | DATE NOT NULL | |
| orders_count | INT NOT NULL | сколько суб-заказов вошло |
| gross_amount | DECIMAL(10,2) NOT NULL | сумма `subtotal` суб-заказов |
| commission_amount | DECIMAL(10,2) NOT NULL | |
| payout_amount | DECIMAL(10,2) NOT NULL | к выплате |
| status | ENUM('pending','paid') NOT NULL DEFAULT 'pending' | |
| paid_at | TIMESTAMP NULL | |
| note | VARCHAR(255) NULL | номер платёжного поручения |
| created_at | TIMESTAMP DEFAULT NOW | |

**Индексы:** `INDEX(vendor_id, status)`, `INDEX(period_from, period_to)`

> В v1.0 деньги переводятся вручную вне системы (PRD §9) — таблица
> ведёт реестр, а не проводит платежи. В выплату попадают только
> суб-заказы в статусе `delivered`.

---

# 3. Каталог

### `product_variants`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| product_id | INT NOT NULL, FK → products.id, ON DELETE CASCADE | |
| sku | VARCHAR(64) NOT NULL UNIQUE | |
| name | VARCHAR(100) NOT NULL | `1 кг`, `2 кг`, `с вишней` |
| price | DECIMAL(10,2) NOT NULL | |
| old_price | DECIMAL(10,2) NULL | |
| stock_quantity | INT NOT NULL DEFAULT 0 | остаток именно этого варианта |
| weight_grams | INT NULL | |
| image_path | VARCHAR(255) NULL | подмена фото при выборе варианта |
| sort_order | INT NOT NULL DEFAULT 0 | |
| is_active | TINYINT(1) NOT NULL DEFAULT 1 | |

**Индексы:** `INDEX(product_id, is_active, sort_order)`, `UNIQUE(sku)`

> Вариант — это то, что имеет **свою цену и свой остаток**. Всё, что
> цену не меняет (начинка на выбор без доплаты, страна какао-бобов), —
> в `product_attributes`. Смешивать нельзя: иначе списание остатка
> станет неоднозначным.

---

### `product_attributes`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| product_id | INT NOT NULL, FK → products.id, ON DELETE CASCADE | |
| attr_name | VARCHAR(100) NOT NULL | `Начинка`, `Тип теста` |
| attr_value | VARCHAR(255) NOT NULL | `Сливочный крем` |
| sort_order | INT NOT NULL DEFAULT 0 | |

**Индексы:** `INDEX(product_id)`, `INDEX(attr_name, attr_value)` — фильтры каталога

---

# 4. Заказы и доставка

### `delivery_zones`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| name | VARCHAR(100) NOT NULL | `В пределах МКАД` |
| city | VARCHAR(100) NOT NULL | |
| cost | DECIMAL(10,2) NOT NULL DEFAULT 0.00 | |
| free_from | DECIMAL(10,2) NULL | бесплатно от суммы; NULL = никогда |
| min_lead_days | INT NOT NULL DEFAULT 0 | минимальный срок доставки в зону |
| is_active | TINYINT(1) NOT NULL DEFAULT 1 | |
| sort_order | INT NOT NULL DEFAULT 0 | |

**Индексы:** `INDEX(is_active, sort_order)`, `INDEX(city)`

> Стоимость доставки считается **на сервере** по этой таблице и
> записывается в `orders.delivery_cost`. Значение, присланное с формы
> чекаута, игнорируется.

---

### `order_status_history`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| order_id | INT NOT NULL, FK → orders.id, ON DELETE CASCADE | |
| vendor_order_id | INT NULL, FK → vendor_orders.id, ON DELETE CASCADE | NULL = смена статуса всего заказа |
| from_status | VARCHAR(20) NULL | NULL при создании заказа |
| to_status | VARCHAR(20) NOT NULL | |
| changed_by | INT NULL, FK → users.id, ON DELETE SET NULL | NULL = система (вебхук, cron) |
| comment | VARCHAR(255) NULL | |
| created_at | TIMESTAMP DEFAULT NOW | |

**Индексы:** `INDEX(order_id, created_at)`, `INDEX(vendor_order_id)`

> Пишется **той же функцией-переходом**, что меняет статус. Если запись
> в историю появилась без соответствующего изменения статуса — значит,
> кто-то обошёл функцию и сделал сырой `UPDATE`, что `php.md` запрещает.
>
> `from_status` / `to_status` — VARCHAR, а не ENUM: таблица хранит
> переходы и заказов, и суб-заказов, а наборы статусов у них разные.

---

### `coupons`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| code | VARCHAR(50) NOT NULL UNIQUE | |
| description | VARCHAR(255) NULL | |
| type | ENUM('percent','fixed') NOT NULL | |
| value | DECIMAL(10,2) NOT NULL | проценты или рубли — по `type` |
| min_order_amount | DECIMAL(10,2) NULL | |
| max_discount | DECIMAL(10,2) NULL | потолок для процентных |
| starts_at | DATETIME NULL | |
| expires_at | DATETIME NULL | NULL = бессрочный |
| usage_limit | INT NULL | общий лимит применений |
| usage_limit_per_user | INT NULL | лимит на пользователя |
| used_count | INT NOT NULL DEFAULT 0 | денормализация для списка в админке |
| is_active | TINYINT(1) NOT NULL DEFAULT 1 | |
| created_at | TIMESTAMP DEFAULT NOW | |

**Индексы:** `UNIQUE(code)`, `INDEX(is_active, expires_at)`

---

### `coupon_usages`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| coupon_id | INT NOT NULL, FK → coupons.id, ON DELETE CASCADE | |
| order_id | INT NOT NULL, FK → orders.id, ON DELETE CASCADE | |
| user_id | INT NULL, FK → users.id, ON DELETE SET NULL | NULL = гостевой заказ |
| discount_amount | DECIMAL(10,2) NOT NULL | сколько реально скинули |
| created_at | TIMESTAMP DEFAULT NOW | |

**Индексы:** `UNIQUE(coupon_id, order_id)`, `INDEX(coupon_id)`,
`INDEX(user_id)`

> `used_count` в `coupons` — кэш для списка. Лимиты проверяются по
> этой таблице внутри той же транзакции, что создаёт заказ: иначе два
> одновременных чекаута пробьют лимит последнего применения.

---

# 5. Аккаунт

### `addresses`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| user_id | INT NOT NULL, FK → users.id, ON DELETE CASCADE | |
| title | VARCHAR(50) NULL | `Дом`, `Работа` |
| recipient_name | VARCHAR(100) NOT NULL | |
| phone | VARCHAR(20) NOT NULL | |
| city | VARCHAR(100) NOT NULL | |
| street | VARCHAR(150) NOT NULL | |
| house | VARCHAR(20) NOT NULL | |
| apartment | VARCHAR(20) NULL | |
| entrance | VARCHAR(10) NULL | |
| floor | VARCHAR(10) NULL | |
| postcode | VARCHAR(10) NULL | |
| comment | VARCHAR(255) NULL | код домофона и т.п. |
| is_default | TINYINT(1) NOT NULL DEFAULT 0 | |
| created_at | TIMESTAMP DEFAULT NOW | |

**Индексы:** `INDEX(user_id, is_default)`

> В заказ адрес попадает **строкой-снэпшотом** (`orders.delivery_address`),
> а не по FK: покупатель может отредактировать или удалить адрес после
> доставки, и старый заказ обязан помнить, куда его везли.
>
> Единственность адреса по умолчанию держится в Model: при установке
> нового флага старый снимается в той же транзакции.

---

### `wishlists`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| user_id | INT NOT NULL, FK → users.id, ON DELETE CASCADE | |
| product_id | INT NOT NULL, FK → products.id, ON DELETE CASCADE | |
| created_at | TIMESTAMP DEFAULT NOW | |

**Индексы:** `UNIQUE(user_id, product_id)`, `INDEX(user_id)`

> Сравнение товаров таблицы не имеет — оно живёт в localStorage
> (PRD §7).

---

### `password_resets`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| user_id | INT NOT NULL, FK → users.id, ON DELETE CASCADE | |
| token_hash | CHAR(64) NOT NULL UNIQUE | SHA-256 от токена, не сам токен |
| expires_at | DATETIME NOT NULL | час с момента запроса |
| used_at | TIMESTAMP NULL | одноразовость |
| created_at | TIMESTAMP DEFAULT NOW | |

**Индексы:** `UNIQUE(token_hash)`, `INDEX(user_id)`, `INDEX(expires_at)`

> Хранится **хеш** токена. Утечка дампа БД не должна давать
> возможность сбросить чужой пароль — по той же причине, по которой не
> хранятся пароли.

---

### `roles`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| name | VARCHAR(50) NOT NULL | `Контент-менеджер` |
| slug | VARCHAR(50) NOT NULL UNIQUE | |
| permissions | JSON NOT NULL | список разрешённых разделов админки |
| is_system | TINYINT(1) NOT NULL DEFAULT 0 | системную роль нельзя удалить из UI |
| created_at | TIMESTAMP DEFAULT NOW | |

**Индексы:** `UNIQUE(slug)`

> `permissions` — JSON, а не таблица связей: набор прав читается
> целиком при входе в админку и никогда не участвует в выборках по
> отдельному праву. Нормализация здесь дала бы две таблицы и JOIN ради
> данных, которые всё равно грузятся одним куском.

---

# 6. Отзывы

### `reviews`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| product_id | INT NOT NULL, FK → products.id, ON DELETE CASCADE | |
| vendor_id | INT NOT NULL, FK → vendors.id, ON DELETE CASCADE | денормализация — рейтинг витрины кондитера без JOIN |
| user_id | INT NULL, FK → users.id, ON DELETE SET NULL | |
| order_id | INT NULL, FK → orders.id, ON DELETE SET NULL | подтверждение покупки |
| author_name | VARCHAR(100) NOT NULL | снэпшот имени |
| rating | TINYINT NOT NULL | 1–5, диапазон проверяется в Model |
| text | TEXT NULL | |
| status | ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending' | премодерация |
| vendor_reply | TEXT NULL | ответ кондитера |
| vendor_replied_at | TIMESTAMP NULL | |
| created_at | TIMESTAMP DEFAULT NOW | |
| updated_at | TIMESTAMP DEFAULT NOW ON UPDATE CURRENT_TIMESTAMP | |

**Индексы:** `UNIQUE(product_id, user_id, order_id)` — один отзыв на
товар в рамках одного заказа, `INDEX(product_id, status)`,
`INDEX(vendor_id, status)`, `INDEX(status, created_at)` — очередь
модерации

> Отзыв переживает удаление аккаунта: `user_id` → `SET NULL`, имя
> сохранено снэпшотом. Иначе удаление одного пользователя молча
> изменило бы рейтинги товаров.
>
> `rating_avg` и `reviews_count` в `products` и `vendors`
> пересчитываются **при смене `status`**, а не при создании отзыва —
> в среднее попадают только одобренные.

---

### `review_images`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| review_id | INT NOT NULL, FK → reviews.id, ON DELETE CASCADE | |
| path | VARCHAR(255) NOT NULL | |
| sort_order | INT NOT NULL DEFAULT 0 | |

**Индексы:** `INDEX(review_id)`

> До 3 файлов на отзыв — ограничение проверяется в Model и при
> загрузке (тип по содержимому, лимит размера — `dod-global.md`).

---

# 7. Контент

### `banners`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| title | VARCHAR(150) NULL | |
| subtitle | VARCHAR(255) NULL | |
| image_path | VARCHAR(255) NOT NULL | |
| link_url | VARCHAR(255) NULL | |
| position | ENUM('hero','promo','category','sidebar') NOT NULL | место на главной |
| starts_at | DATETIME NULL | |
| ends_at | DATETIME NULL | |
| sort_order | INT NOT NULL DEFAULT 0 | |
| is_active | TINYINT(1) NOT NULL DEFAULT 1 | |

**Индексы:** `INDEX(position, is_active, sort_order)`

---

### `blog_categories`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| name | VARCHAR(100) NOT NULL | |
| slug | VARCHAR(120) NOT NULL UNIQUE | |
| sort_order | INT NOT NULL DEFAULT 0 | |

---

### `blog_posts`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| category_id | INT NULL, FK → blog_categories.id, ON DELETE SET NULL | |
| author_id | INT NULL, FK → users.id, ON DELETE SET NULL | |
| title | VARCHAR(200) NOT NULL | |
| slug | VARCHAR(220) NOT NULL UNIQUE | |
| excerpt | VARCHAR(500) NULL | анонс в списке |
| content | MEDIUMTEXT NOT NULL | |
| cover_path | VARCHAR(255) NULL | |
| status | ENUM('draft','published') NOT NULL DEFAULT 'draft' | |
| published_at | DATETIME NULL | |
| views_count | INT NOT NULL DEFAULT 0 | |
| meta_title | VARCHAR(255) NULL | |
| meta_description | VARCHAR(500) NULL | |
| created_at | TIMESTAMP DEFAULT NOW | |
| updated_at | TIMESTAMP DEFAULT NOW ON UPDATE CURRENT_TIMESTAMP | |

**Индексы:** `UNIQUE(slug)`, `INDEX(status, published_at)`,
`INDEX(category_id)`

---

### `pages`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| title | VARCHAR(200) NOT NULL | |
| slug | VARCHAR(220) NOT NULL UNIQUE | `about`, `faq`, `delivery`, `offer` |
| content | MEDIUMTEXT NOT NULL | |
| meta_title | VARCHAR(255) NULL | |
| meta_description | VARCHAR(500) NULL | |
| is_active | TINYINT(1) NOT NULL DEFAULT 1 | |
| updated_at | TIMESTAMP DEFAULT NOW ON UPDATE CURRENT_TIMESTAMP | |

**Индексы:** `UNIQUE(slug)`

> `slug` не должен совпадать с системными маршрутами (`cart`,
> `checkout`, `admin`, `account`…) — список зарезервированных проверяется
> в Model при сохранении, иначе страница перекроет рабочий раздел.

---

### `menu_items`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| parent_id | INT NULL, FK → menu_items.id, ON DELETE CASCADE | выпадающие пункты |
| location | ENUM('header','footer_1','footer_2','footer_3') NOT NULL | |
| title | VARCHAR(100) NOT NULL | |
| url | VARCHAR(255) NOT NULL | |
| sort_order | INT NOT NULL DEFAULT 0 | |
| is_active | TINYINT(1) NOT NULL DEFAULT 1 | |

**Индексы:** `INDEX(location, is_active, sort_order)`, `INDEX(parent_id)`

---

# 8. Коммуникации

### `support_tickets`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| user_id | INT NULL, FK → users.id, ON DELETE SET NULL | NULL = обращение гостя |
| order_id | INT NULL, FK → orders.id, ON DELETE SET NULL | тикет по конкретному заказу |
| guest_name | VARCHAR(100) NULL | заполняется, если `user_id` пуст |
| guest_email | VARCHAR(150) NULL | |
| subject | VARCHAR(200) NOT NULL | |
| source | ENUM('support','contact_form') NOT NULL DEFAULT 'support' | форма обратной связи падает сюда же |
| status | ENUM('open','answered','closed') NOT NULL DEFAULT 'open' | |
| created_at | TIMESTAMP DEFAULT NOW | |
| updated_at | TIMESTAMP DEFAULT NOW ON UPDATE CURRENT_TIMESTAMP | |

**Индексы:** `INDEX(user_id)`, `INDEX(status, created_at)`

> Форма обратной связи не получила отдельную таблицу: это тот же тред
> «вопрос → ответ», отличается только источником. Две таблицы с
> одинаковой структурой рано или поздно разъедутся.

---

### `ticket_messages`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| ticket_id | INT NOT NULL, FK → support_tickets.id, ON DELETE CASCADE | |
| user_id | INT NULL, FK → users.id, ON DELETE SET NULL | |
| is_staff | TINYINT(1) NOT NULL DEFAULT 0 | сообщение от поддержки |
| message | TEXT NOT NULL | |
| created_at | TIMESTAMP DEFAULT NOW | |

**Индексы:** `INDEX(ticket_id, created_at)`

---

### `newsletter_subscribers`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| id | INT PK AUTO_INCREMENT | |
| email | VARCHAR(150) NOT NULL UNIQUE | |
| confirm_token | CHAR(64) NULL | double opt-in |
| confirmed_at | TIMESTAMP NULL | подписка активна только после подтверждения |
| unsubscribed_at | TIMESTAMP NULL | |
| created_at | TIMESTAMP DEFAULT NOW | |

**Индексы:** `UNIQUE(email)`, `INDEX(confirmed_at)`

> Рассылка уходит только по строкам с заполненным `confirmed_at` и
> пустым `unsubscribed_at`. Запись при отписке не удаляется — иначе
> повторная подписка чужими руками пройдёт незамеченной.

---

### `cart_reminders`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| user_id | INT PK, FK → users.id, ON DELETE CASCADE | |
| last_sent_at | TIMESTAMP NOT NULL | |

> Письмо о брошенной корзине уходит только авторизованным — у гостя нет
> известного email, пока он не дошёл до чекаута. Таблица нужна, чтобы
> напоминание не отправлялось каждый запуск cron.
>
> Кандидаты: `cart_items` с `user_id IS NOT NULL`, где
> `MAX(updated_at) < NOW() − 24 ч`, и напоминание либо не отправлялось,
> либо отправлялось раньше последнего изменения корзины.

---

# 9. Служебное

### `settings`

| Колонка | Тип | Назначение |
|---------|-----|------------|
| setting_key | VARCHAR(100) PK | `shop_name`, `default_commission_percent`, `vat_rate`, `free_delivery_from` |
| setting_value | TEXT NULL | |
| updated_at | TIMESTAMP DEFAULT NOW ON UPDATE CURRENT_TIMESTAMP | |

> Только **не-секретные** настройки, которые админ меняет через UI.
> Ключи ЮKassa, пароль SMTP и данные подключения к БД живут в `.env` и
> в эту таблицу не попадают никогда (`php.md`, «секреты не в
> репозитории»).
>
> `key` — зарезервированное слово MySQL, поэтому колонки названы
> `setting_key` / `setting_value`, а не `key` / `value`.

---

## Карта связей

```
users (1)
  ├──< orders            (1:N, SET NULL — история переживает удаление)
  ├──< reviews           (1:N, SET NULL — рейтинги не должны меняться)
  ├──< support_tickets   (1:N, SET NULL)
  ├──< blog_posts        (1:N, SET NULL)
  ├──< addresses         (1:N, CASCADE)
  ├──< wishlists         (1:N, CASCADE)
  ├──< cart_items        (1:N, CASCADE)
  ├──< password_resets   (1:N, CASCADE)
  ├──< cart_reminders    (1:1, CASCADE)
  ├──── roles            (N:1, SET NULL — только для админов)
  └──── vendors          (1:1, RESTRICT — кондитера нельзя удалить)

vendors (1)
  ├──< products          (1:N, RESTRICT)
  ├──< vendor_orders     (1:N, RESTRICT)
  ├──< vendor_payouts    (1:N, RESTRICT)
  └──< reviews           (1:N, CASCADE)

categories (1)
  ├──< categories        (self, parent_id, SET NULL)
  └──< products          (1:N, RESTRICT)

products (1)
  ├──< product_images     (1:N, CASCADE)
  ├──< product_variants   (1:N, CASCADE)
  ├──< product_attributes (1:N, CASCADE)
  ├──< cart_items         (1:N, CASCADE)
  ├──< wishlists          (1:N, CASCADE)
  ├──< reviews            (1:N, CASCADE)
  └──< order_items        (1:N, SET NULL — снэпшот сохраняет историю)

orders (1)
  ├──< vendor_orders        (1:N, CASCADE)   ← разбивка по кондитерам
  ├──< order_items          (1:N, CASCADE)
  ├──< order_status_history (1:N, CASCADE)
  ├──< payment_logs         (1:N, CASCADE)
  └──< coupon_usages        (1:N, CASCADE)

vendor_orders (1)
  ├──< order_items          (1:N, CASCADE)
  ├──< order_status_history (1:N, CASCADE)
  └──── vendor_payouts      (N:1, SET NULL)

reviews (1) ──< review_images   (1:N, CASCADE)
support_tickets (1) ──< ticket_messages (1:N, CASCADE)
coupons (1) ──< coupon_usages   (1:N, CASCADE)
menu_items (1) ──< menu_items   (self, parent_id, CASCADE)
```

---

## Правила

### Целостность

- **`user_id` → `CASCADE`** там, где запись без пользователя
  бессмысленна: `cart_items`, `wishlists`, `addresses`,
  `password_resets`, `cart_reminders`
- **`user_id` → `SET NULL` + снэпшот имени** там, где запись часть
  истории и обязана пережить удаление аккаунта: `orders`, `reviews`,
  `support_tickets`, `ticket_messages`, `blog_posts`,
  `order_status_history`, `coupon_usages`
- **`vendors.user_id` → `RESTRICT`** — единственный случай, когда
  удаление блокируется: сначала кондитер переводится в `suspended`
- Всё, что ссылается на `products` из истории заказов, — `SET NULL` со
  снэпшотом; всё, что живёт «при товаре» (фото, варианты, корзина), —
  `CASCADE`
- Все таблицы → `ENGINE=InnoDB`, `utf8mb4_unicode_ci`

### Деньги

- Только `DECIMAL(10,2)`, никогда `FLOAT` / `DOUBLE`
- `orders.total = subtotal − discount_total + delivery_cost`
- `vendor_orders.payout_amount = subtotal − commission_amount`
- Ставка комиссии копируется снэпшотом в `vendor_orders` на момент
  оплаты; изменение `vendors.commission_percent` не пересчитывает
  прошлое
- Форматирование сумм — только во Views

### Транзакции и остатки

- Создание заказа — **одна транзакция**: `orders` → `vendor_orders` →
  `order_items` → списание остатков → `coupon_usages` →
  `order_status_history`. Любая ошибка = полный откат
- Остаток списывается условным запросом с проверкой `rowCount()`:
  ```sql
  UPDATE product_variants SET stock_quantity = stock_quantity - :qty
  WHERE id = :id AND stock_quantity >= :qty
  ```
  (или по `products`, если `has_variants = 0`). `rowCount() = 0`
  означает «не хватило» → откат. Голая транзакция от гонки не спасает
- Отмена заказа возвращает остатки в той же транзакции, что меняет
  статус

### Каталог и производительность

- Товары не удаляются физически — `is_active = 0`
- Листинг всегда с пагинацией; вглубь каталога — keyset-пагинация
  (`WHERE id > :last_id LIMIT :n`) вместо `LIMIT :offset, :n`
- Поиск — только `FULLTEXT`, не `LIKE '%...%'`
- Денормализованные счётчики (`rating_avg`, `reviews_count`,
  `sales_count`, `used_count`, `has_variants`) обновляются в той же
  транзакции, что и породившее их событие

> **Ограничение FULLTEXT на shared-хостинге.** InnoDB по умолчанию
> индексирует слова от 3 символов (`innodb_ft_min_token_size = 3`), и
> изменить это без рестарта сервера нельзя. Запросы в 1–2 буквы ничего
> не найдут — в таких случаях Model отдаёт результат по префиксу
> `name LIKE 'абв%'` (этот вариант, в отличие от `%...%`, индекс
> использует).

### Безопасность

- Пароли — только `password_hash()`, никогда в открытом виде
- Токены сброса пароля хранятся хешем, не в открытом виде
- Секреты (ключи ЮKassa, SMTP, доступ к БД) — в `.env`, не в `settings`
- Данные банковских карт не хранятся никогда — только `payment_id`
  от шлюза
