<?php

declare(strict_types=1);

/**
 * Данные демо-каталога Фазы 1. Только данные — без обращений к БД, без
 * вычислений цены/slug (это делает database/seed.php).
 *
 * Изображения — пути внутри public/assets/vendor/images/, копируются
 * сид-скриптом в public/uploads/. Реальных фото в теме только 11
 * (cake/product/) — ими же циклически покрыты категории и баннеры
 * кондитеров. cake/pro/ и cake/banner/ — не фото, а плейсхолдер-графика
 * темы с зашитой в файл надписью «150×150» и т.п., не переносим.
 * Логотипы кондитеров (vendor-page/logo/, 7 файлов) — настоящие.
 *
 * category_defaults — характеристики (состав, аллергены, срок годности и
 * т.п.) наследуются от корневой категории; товар может переопределить
 * любое поле явно (см. resolveCategoryDefaults() в seed.php).
 */

return [
    'vendors' => [
        [
            'slug'               => 'sdoba-kuhnya',
            'name'               => 'Кухня «Сдобы»',
            'email'              => 'kuhnya@sdoba-demo.local',
            'description'        => 'Собственное производство площадки: хлеб, круассаны и базовая '
                . 'выпечка каждый день.',
            'city'               => 'Москва',
            'address'            => 'ул. Пекарная, 1',
            'phone'              => '+7 (495) 100-00-01',
            'inn'                => '7700000001',
            'commission_percent' => '10.00',
            'rating_avg'         => '4.6',
            'reviews_count'      => 180,
            'logo_source'        => 'vendor-page/logo/1.png',
            'banner_source'      => 'cake/product/1.png',
        ],
        [
            'slug'               => 'medovik-i-ko',
            'name'               => 'Медовик и Ко',
            'email'              => 'medovik@sdoba-demo.local',
            'description'        => 'Классические и свадебные торты на заказ. Работаем с 2015 года.',
            'city'               => 'Москва',
            'address'            => 'Кондитерский пер., 5',
            'phone'              => '+7 (495) 100-00-02',
            'inn'                => '7700000002',
            'commission_percent' => '15.00',
            'rating_avg'         => '4.8',
            'reviews_count'      => 220,
            'logo_source'        => 'vendor-page/logo/2.png',
            'banner_source'      => 'cake/product/2.png',
        ],
        [
            'slug'               => 'dom-eklerov',
            'name'               => 'Дом Эклеров',
            'email'              => 'eklery@sdoba-demo.local',
            'description'        => 'Французские пирожные и эклеры ручной работы.',
            'city'               => 'Санкт-Петербург',
            'address'            => 'Невский пр-т, 20',
            'phone'              => '+7 (812) 200-00-03',
            'inn'                => '7800000003',
            'commission_percent' => '15.00',
            'rating_avg'         => '4.7',
            'reviews_count'      => 165,
            'logo_source'        => 'vendor-page/logo/3.png',
            'banner_source'      => 'cake/product/3.png',
        ],
        [
            'slug'               => 'pryanichny-dvor',
            'name'               => 'Пряничный Двор',
            'email'              => 'pryaniki@sdoba-demo.local',
            'description'        => 'Печенье и расписные пряники по традиционным рецептам.',
            'city'               => 'Тула',
            'address'            => 'ул. Пряничная, 3',
            'phone'              => '+7 (487) 300-00-04',
            'inn'                => '7100000004',
            'commission_percent' => '18.00',
            'rating_avg'         => '4.7',
            'reviews_count'      => 140,
            'logo_source'        => 'vendor-page/logo/4.png',
            'banner_source'      => 'cake/product/4.png',
        ],
        [
            'slug'               => 'cupcake-studio',
            'name'               => 'CupcakeStudio',
            'email'              => 'cupcake@sdoba-demo.local',
            'description'        => 'Капкейки, макаруны и детские торты на заказ.',
            'city'               => 'Москва',
            'address'            => 'ул. Сладкая, 8',
            'phone'              => '+7 (495) 100-00-05',
            'inn'                => '7700000005',
            'commission_percent' => '20.00',
            'rating_avg'         => '4.6',
            'reviews_count'      => 195,
            'logo_source'        => 'vendor-page/logo/5.png',
            'banner_source'      => 'cake/product/5.png',
        ],
        [
            'slug'               => 'chizkeyk-haus',
            'name'               => 'Чизкейк Хаус',
            'email'              => 'chizkeyk@sdoba-demo.local',
            'description'        => 'Чизкейки нью-йоркского стиля и ягодные вариации.',
            'city'               => 'Казань',
            'address'            => 'ул. Баумана, 12',
            'phone'              => '+7 (843) 400-00-06',
            'inn'                => '1600000006',
            'commission_percent' => '15.00',
            'rating_avg'         => '4.7',
            'reviews_count'      => 130,
            'logo_source'        => 'vendor-page/logo/6.png',
            'banner_source'      => 'cake/product/6.png',
        ],
    ],

    // Порядок важен: родитель должен идти раньше потомка.
    'categories' => [
        [
            'slug' => 'torty', 'name' => 'Торты', 'parent' => null, 'sort_order' => 10,
            'description' => 'Классические и заказные торты на любой повод.',
            'image_source' => 'cake/product/1.png',
        ],
        [
            'slug' => 'torty-svadebnye', 'name' => 'Свадебные торты', 'parent' => 'torty', 'sort_order' => 10,
            'description' => 'Многоярусные торты для свадебного стола.',
            'image_source' => 'cake/product/2.png',
        ],
        [
            'slug' => 'torty-detskie', 'name' => 'Детские торты', 'parent' => 'torty', 'sort_order' => 20,
            'description' => 'Яркие торты по мотивам любимых персонажей.',
            'image_source' => 'cake/product/3.png',
        ],
        [
            'slug' => 'pirozhnye', 'name' => 'Пирожные', 'parent' => null, 'sort_order' => 20,
            'description' => 'Порционные десерты к чаю.',
            'image_source' => 'cake/product/4.png',
        ],
        [
            'slug' => 'pirozhnye-eklery', 'name' => 'Эклеры', 'parent' => 'pirozhnye', 'sort_order' => 10,
            'description' => 'Заварные эклеры с разными начинками.',
            'image_source' => 'cake/product/5.png',
        ],
        [
            'slug' => 'kapkeyki', 'name' => 'Капкейки', 'parent' => null, 'sort_order' => 30,
            'description' => 'Капкейки с кремом — поштучно и наборами.',
            'image_source' => 'cake/product/6.png',
        ],
        [
            'slug' => 'pechenye-i-pryaniki', 'name' => 'Печенье и пряники', 'parent' => null, 'sort_order' => 40,
            'description' => 'Домашнее печенье и пряники к чаю.',
            'image_source' => 'cake/product/7.png',
        ],
        [
            'slug' => 'imbirnye-pryaniki', 'name' => 'Имбирные пряники', 'parent' => 'pechenye-i-pryaniki', 'sort_order' => 10,
            'description' => 'Расписные имбирные пряники ручной работы.',
            'image_source' => 'cake/product/8.png',
        ],
        [
            'slug' => 'kruassany-i-sloyki', 'name' => 'Круассаны и слойки', 'parent' => null, 'sort_order' => 50,
            'description' => 'Слоёная выпечка из масляного теста.',
            'image_source' => 'cake/product/9.png',
        ],
        [
            'slug' => 'khleb-i-bulochki', 'name' => 'Хлеб и булочки', 'parent' => null, 'sort_order' => 60,
            'description' => 'Свежий хлеб и сдобные булочки.',
            'image_source' => 'cake/product/10.png',
        ],
        [
            'slug' => 'makaruny', 'name' => 'Макаруны', 'parent' => null, 'sort_order' => 70,
            'description' => 'Французское миндальное печенье макарун.',
            'image_source' => 'cake/product/11.png',
        ],
        [
            'slug' => 'chizkeyki', 'name' => 'Чизкейки', 'parent' => null, 'sort_order' => 80,
            'description' => 'Чизкейки нью-йоркского стиля и с ягодами.',
            'image_source' => 'cake/product/1.png',
        ],
    ],

    // Наследуются подкатегориями через родителя (см. resolveCategoryDefaults()).
    'category_defaults' => [
        'torty' => [
            'composition' => 'Мука пшеничная, сахар, яйца, сливочное масло, сметана, какао, разрыхлитель',
            'allergens' => 'Глютен, яйца, молоко',
            'calories_per_100g' => 340,
            'shelf_life_hours' => 72,
            'storage_conditions' => 'Хранить при +2…+6 °C',
            'unit' => 'kg',
            'weight_grams' => 1000,
        ],
        'pirozhnye' => [
            'composition' => 'Мука пшеничная, сахар, яйца, сливочный крем, ягодное пюре',
            'allergens' => 'Глютен, яйца, молоко',
            'calories_per_100g' => 310,
            'shelf_life_hours' => 48,
            'storage_conditions' => 'Хранить при +2…+6 °C',
            'unit' => 'piece',
            'weight_grams' => 90,
        ],
        'kapkeyki' => [
            'composition' => 'Мука пшеничная, сахар, яйца, сливочное масло, крем-чиз',
            'allergens' => 'Глютен, яйца, молоко',
            'calories_per_100g' => 360,
            'shelf_life_hours' => 48,
            'storage_conditions' => 'Хранить при +2…+6 °C',
            'unit' => 'piece',
            'weight_grams' => 80,
        ],
        'pechenye-i-pryaniki' => [
            'composition' => 'Мука пшеничная, сахар, мёд, яйца, специи',
            'allergens' => 'Глютен, яйца',
            'calories_per_100g' => 400,
            'shelf_life_hours' => 720,
            'storage_conditions' => 'Хранить в сухом месте при +18…+22 °C',
            'unit' => 'piece',
            'weight_grams' => 60,
        ],
        'kruassany-i-sloyki' => [
            'composition' => 'Мука пшеничная, сливочное масло, яйца, дрожжи',
            'allergens' => 'Глютен, яйца, молоко',
            'calories_per_100g' => 400,
            'shelf_life_hours' => 24,
            'storage_conditions' => 'Хранить при +2…+6 °C',
            'unit' => 'piece',
            'weight_grams' => 75,
        ],
        'khleb-i-bulochki' => [
            'composition' => 'Мука пшеничная, вода, дрожжи, соль',
            'allergens' => 'Глютен',
            'calories_per_100g' => 260,
            'shelf_life_hours' => 48,
            'storage_conditions' => 'Хранить при +18…+22 °C',
            'unit' => 'piece',
            'weight_grams' => 400,
        ],
        'makaruny' => [
            'composition' => 'Миндальная мука, сахарная пудра, яичный белок, пищевые красители',
            'allergens' => 'Орехи (миндаль), яйца',
            'calories_per_100g' => 380,
            'shelf_life_hours' => 96,
            'storage_conditions' => 'Хранить при +2…+6 °C',
            'unit' => 'piece',
            'weight_grams' => 15,
        ],
        'chizkeyki' => [
            'composition' => 'Творожный сыр, сливки, яйца, сахар, песочное печенье',
            'allergens' => 'Глютен, яйца, молоко',
            'calories_per_100g' => 320,
            'shelf_life_hours' => 72,
            'storage_conditions' => 'Хранить при +2…+6 °C',
            'unit' => 'kg',
            'weight_grams' => 800,
        ],
    ],

    // Копируются циклически: продукт с индексом N (с 1) получает
    // product_image_pool[(N - 1) % count].
    'product_image_pool' => [
        'cake/product/1.png', 'cake/product/2.png', 'cake/product/3.png',
        'cake/product/4.png', 'cake/product/5.png', 'cake/product/6.png',
        'cake/product/7.png', 'cake/product/8.png', 'cake/product/9.png',
        'cake/product/10.png', 'cake/product/11.png',
    ],

    'products' => [
        // ─── Торты ──────────────────────────────────────────────────────
        [
            'category' => 'torty', 'vendor' => 'medovik-i-ko',
            'name' => 'Медовик классический',
            'short_description' => 'Медовые коржи со сметанным кремом.',
            'description' => 'Классический медовик из семи коржей со сметанным кремом. Готовится за сутки для настоя.',
            'variants' => [
                ['name' => '1 кг', 'price' => '1200.00', 'stock' => 15],
                ['name' => '2 кг', 'price' => '2200.00', 'stock' => 8],
                ['name' => '3 кг', 'price' => '3100.00', 'stock' => 4],
            ],
            'rating_avg' => '4.80', 'reviews_count' => 42, 'sales_count' => 210,
        ],
        [
            'category' => 'torty', 'vendor' => 'sdoba-kuhnya',
            'name' => 'Наполеон слоёный',
            'short_description' => 'Слоёное тесто с заварным кремом.',
            'description' => 'Хрустящие слои теста, заварной крем, лёгкая карамельная нотка сверху.',
            'price' => '1100.00', 'old_price' => '1300.00', 'stock' => 12,
            'rating_avg' => '4.60', 'reviews_count' => 30, 'sales_count' => 150,
        ],
        [
            'category' => 'torty', 'vendor' => 'medovik-i-ko',
            'name' => 'Красный бархат',
            'short_description' => 'Бархатные коржи с крем-чизом.',
            'description' => 'Какао-коржи насыщенного красного цвета с кремом на основе крем-чиза.',
            'variants' => [
                ['name' => '1 кг', 'price' => '1400.00', 'stock' => 10],
                ['name' => '2 кг', 'price' => '2600.00', 'stock' => 6],
            ],
            'rating_avg' => '4.90', 'reviews_count' => 55, 'sales_count' => 280,
        ],
        [
            'category' => 'torty', 'vendor' => 'sdoba-kuhnya',
            'name' => 'Шоколадный трюфельный торт',
            'short_description' => 'Тёмный шоколад и трюфельный крем.',
            'description' => 'Насыщенные шоколадные коржи, трюфельный крем, шоколадная глазурь сверху.',
            'price' => '1600.00', 'stock' => 9,
            'rating_avg' => '4.70', 'reviews_count' => 25, 'sales_count' => 95,
        ],

        // ─── Свадебные торты ────────────────────────────────────────────
        [
            'category' => 'torty-svadebnye', 'vendor' => 'medovik-i-ko',
            'name' => 'Свадебный торт «Ваниль»',
            'short_description' => 'Многоярусный торт с ванильным кремом.',
            'description' => 'Классический многоярусный торт для свадебного стола с ванильным муслином.',
            'variants' => [
                ['name' => '2 кг', 'price' => '4200.00', 'stock' => 3],
                ['name' => '3 кг', 'price' => '6000.00', 'stock' => 2],
                ['name' => '4 кг', 'price' => '7800.00', 'stock' => 1],
            ],
            'lead_time_hours' => 48,
            'rating_avg' => '5.00', 'reviews_count' => 12, 'sales_count' => 20,
        ],
        [
            'category' => 'torty-svadebnye', 'vendor' => 'medovik-i-ko',
            'name' => 'Свадебный торт «Ягодный»',
            'short_description' => 'Свежие ягоды и лёгкий крем-чиз.',
            'description' => 'Ярус со свежими сезонными ягодами и лёгким кремом на основе крем-чиза.',
            'price' => '5200.00', 'weight_grams' => 3000, 'stock' => 2, 'lead_time_hours' => 48,
            'rating_avg' => '4.90', 'reviews_count' => 9, 'sales_count' => 15,
        ],
        [
            'category' => 'torty-svadebnye', 'vendor' => 'sdoba-kuhnya',
            'name' => 'Свадебный торт «Шоколадный мрамор»',
            'short_description' => 'Мраморные шоколадно-ванильные коржи.',
            'description' => 'Контрастные мраморные коржи из шоколадного и ванильного теста, ганаш снаружи.',
            'price' => '5600.00', 'weight_grams' => 3000, 'stock' => 2, 'lead_time_hours' => 48,
            'rating_avg' => '4.80', 'reviews_count' => 7, 'sales_count' => 11,
        ],

        // ─── Детские торты ──────────────────────────────────────────────
        [
            'category' => 'torty-detskie', 'vendor' => 'cupcake-studio',
            'name' => 'Торт «Единорог»',
            'short_description' => 'Радужные коржи, фигурка единорога.',
            'description' => 'Разноцветные коржи и декор в виде единорога — праздничный торт для детского дня рождения.',
            'variants' => [
                ['name' => '1 кг', 'price' => '1800.00', 'stock' => 6],
                ['name' => '1.5 кг', 'price' => '2500.00', 'stock' => 4],
            ],
            'lead_time_hours' => 24,
            'rating_avg' => '4.90', 'reviews_count' => 33, 'sales_count' => 70,
        ],
        [
            'category' => 'torty-detskie', 'vendor' => 'cupcake-studio',
            'name' => 'Торт «Машинка»',
            'short_description' => 'Торт в форме гоночной машины.',
            'description' => 'Бисквитный торт в форме машины с мастичным декором — понравится юным гонщикам.',
            'price' => '1700.00', 'weight_grams' => 1000, 'stock' => 5, 'lead_time_hours' => 24,
            'rating_avg' => '4.70', 'reviews_count' => 20, 'sales_count' => 44,
        ],
        [
            'category' => 'torty-detskie', 'vendor' => 'medovik-i-ko',
            'name' => 'Торт «Принцесса»',
            'short_description' => 'Торт с юбкой из вафельных лепестков.',
            'description' => 'Бисквитный торт с многослойной «юбкой» из вафельных лепестков и фигуркой наверху.',
            'price' => '1900.00', 'weight_grams' => 1200, 'stock' => 4, 'lead_time_hours' => 24,
            'rating_avg' => '4.80', 'reviews_count' => 18, 'sales_count' => 39,
        ],

        // ─── Пирожные ───────────────────────────────────────────────────
        [
            'category' => 'pirozhnye', 'vendor' => 'sdoba-kuhnya',
            'name' => 'Пирожное «Картошка»',
            'short_description' => 'Шоколадное пирожное из бисквитной крошки.',
            'description' => 'Классическая «Картошка» из бисквитной крошки с масляным шоколадным кремом.',
            'price' => '180.00', 'stock' => 0,
            'rating_avg' => '4.50', 'reviews_count' => 40, 'sales_count' => 300,
        ],
        [
            'category' => 'pirozhnye', 'vendor' => 'dom-eklerov',
            'name' => 'Профитроли с кремом',
            'short_description' => 'Заварные шарики с ванильным кремом.',
            'description' => 'Небольшие заварные пирожные, наполненные лёгким ванильным кремом.',
            'variants' => [
                ['name' => '6 шт', 'price' => '220.00', 'stock' => 20],
                ['name' => '12 шт', 'price' => '400.00', 'stock' => 10],
            ],
            'rating_avg' => '4.60', 'reviews_count' => 28, 'sales_count' => 180,
        ],
        [
            'category' => 'pirozhnye', 'vendor' => 'dom-eklerov',
            'name' => 'Пирожное «Павлова»',
            'short_description' => 'Безе со свежими ягодами и сливками.',
            'description' => 'Хрустящее безе с воздушной серединой, взбитые сливки и свежие ягоды.',
            'price' => '260.00', 'old_price' => '300.00', 'stock' => 14,
            'rating_avg' => '4.70', 'reviews_count' => 19, 'sales_count' => 95,
        ],

        // ─── Эклеры ─────────────────────────────────────────────────────
        [
            'category' => 'pirozhnye-eklery', 'vendor' => 'dom-eklerov',
            'name' => 'Эклер классический с ванильным кремом',
            'short_description' => 'Заварное тесто, ванильный крем.',
            'description' => 'Хрустящее заварное тесто с нежным ванильным кремом внутри.',
            'price' => '190.00', 'stock' => 25,
            'rating_avg' => '4.80', 'reviews_count' => 60, 'sales_count' => 420,
        ],
        [
            'category' => 'pirozhnye-eklery', 'vendor' => 'dom-eklerov',
            'name' => 'Эклер шоколадный',
            'short_description' => 'Заварное тесто, шоколадный крем.',
            'description' => 'Классический эклер с насыщенным шоколадным кремом и глазурью.',
            'price' => '190.00', 'stock' => 22,
            'rating_avg' => '4.70', 'reviews_count' => 48, 'sales_count' => 360,
        ],
        [
            'category' => 'pirozhnye-eklery', 'vendor' => 'dom-eklerov',
            'name' => 'Эклер фисташковый',
            'short_description' => 'Заварное тесто, фисташковый крем.',
            'description' => 'Эклер с кремом на основе фисташковой пасты и дроблёными орехами сверху.',
            'price' => '220.00', 'weight_grams' => 85, 'stock' => 16,
            'rating_avg' => '4.90', 'reviews_count' => 22, 'sales_count' => 88,
        ],

        // ─── Капкейки ───────────────────────────────────────────────────
        [
            'category' => 'kapkeyki', 'vendor' => 'cupcake-studio',
            'name' => 'Капкейк «Красный бархат»',
            'short_description' => 'Бархатный бисквит, крем-чиз.',
            'description' => 'Капкейк из бисквита «красный бархат» с шапочкой крем-чиза.',
            'price' => '220.00', 'stock' => 30,
            'attributes' => [['name' => 'Начинка', 'value' => 'Сливочный крем']],
            'rating_avg' => '4.60', 'reviews_count' => 50, 'sales_count' => 260,
        ],
        [
            'category' => 'kapkeyki', 'vendor' => 'cupcake-studio',
            'name' => 'Капкейк шоколадный',
            'short_description' => 'Шоколадный бисквит, ганаш.',
            'description' => 'Капкейк на какао с шапочкой из шоколадного ганаша.',
            'price' => '200.00', 'stock' => 28,
            'attributes' => [['name' => 'Начинка', 'value' => 'Шоколадный ганаш']],
            'rating_avg' => '4.50', 'reviews_count' => 44, 'sales_count' => 230,
        ],
        [
            'category' => 'kapkeyki', 'vendor' => 'cupcake-studio',
            'name' => 'Капкейк лимонный',
            'short_description' => 'Бисквит с лимонной цедрой, курд.',
            'description' => 'Лёгкий капкейк с лимонной цедрой и начинкой из лимонного курда.',
            'price' => '210.00', 'stock' => 0,
            'attributes' => [['name' => 'Начинка', 'value' => 'Лимонный курд']],
            'rating_avg' => '4.40', 'reviews_count' => 30, 'sales_count' => 150,
        ],
        [
            'category' => 'kapkeyki', 'vendor' => 'cupcake-studio',
            'name' => 'Капкейк «Ред-Вельвет мини»',
            'short_description' => 'Мини-формат, крем-чиз.',
            'description' => 'Уменьшенная версия капкейка «красный бархат» — удобно для фуршета.',
            'price' => '150.00', 'weight_grams' => 50, 'stock' => 35,
            'rating_avg' => '4.60', 'reviews_count' => 25, 'sales_count' => 120,
        ],
        [
            'category' => 'kapkeyki', 'vendor' => 'cupcake-studio',
            'name' => 'Капкейк ванильный без сахара',
            'short_description' => 'На заменителе сахара, для диабетиков.',
            'description' => 'Ванильный капкейк на эритрите — сладкий вкус без сахара в составе.',
            'price' => '230.00', 'stock' => 18, 'is_sugar_free' => true,
            'rating_avg' => '4.30', 'reviews_count' => 15, 'sales_count' => 60,
        ],

        // ─── Печенье и пряники ──────────────────────────────────────────
        [
            'category' => 'pechenye-i-pryaniki', 'vendor' => 'sdoba-kuhnya',
            'name' => 'Печенье овсяное с шоколадом',
            'short_description' => 'Овсяное печенье с шоколадной крошкой.',
            'description' => 'Рассыпчатое овсяное печенье с кусочками тёмного шоколада.',
            'price' => '90.00', 'stock' => 60,
            'rating_avg' => '4.50', 'reviews_count' => 35, 'sales_count' => 300,
        ],
        [
            'category' => 'pechenye-i-pryaniki', 'vendor' => 'pryanichny-dvor',
            'name' => 'Песочное печенье с джемом',
            'short_description' => 'Песочная основа, начинка из джема.',
            'description' => 'Рассыпчатое песочное печенье с прослойкой из ягодного джема.',
            'price' => '95.00', 'weight_grams' => 55, 'stock' => 45,
            'rating_avg' => '4.40', 'reviews_count' => 20, 'sales_count' => 140,
        ],
        [
            'category' => 'pechenye-i-pryaniki', 'vendor' => 'pryanichny-dvor',
            'name' => 'Печенье без сахара с изюмом',
            'short_description' => 'На заменителе сахара, с изюмом.',
            'description' => 'Овсяное печенье на эритрите с изюмом — без сахара в составе.',
            'price' => '110.00', 'weight_grams' => 55, 'stock' => 20, 'is_sugar_free' => true,
            'rating_avg' => '4.20', 'reviews_count' => 12, 'sales_count' => 45,
        ],

        // ─── Имбирные пряники ───────────────────────────────────────────
        [
            'category' => 'imbirnye-pryaniki', 'vendor' => 'pryanichny-dvor',
            'name' => 'Имбирный пряник классический',
            'short_description' => 'Пряное тесто с имбирём и корицей.',
            'description' => 'Мягкий пряник на меду с имбирём и корицей, без глазури.',
            'price' => '70.00', 'stock' => 80,
            'rating_avg' => '4.70', 'reviews_count' => 60, 'sales_count' => 500,
        ],
        [
            'category' => 'imbirnye-pryaniki', 'vendor' => 'pryanichny-dvor',
            'name' => 'Пряник расписной «Домик»',
            'short_description' => 'Пряник с айсинговой росписью.',
            'description' => 'Имбирный пряник в форме домика с ручной росписью сахарной глазурью.',
            'price' => '150.00', 'weight_grams' => 90, 'stock' => 30,
            'rating_avg' => '4.90', 'reviews_count' => 28, 'sales_count' => 95,
        ],
        [
            'category' => 'imbirnye-pryaniki', 'vendor' => 'pryanichny-dvor',
            'name' => 'Пряничный набор «Новогодний»',
            'short_description' => 'Набор расписных пряников в коробке.',
            'description' => 'Набор имбирных пряников с новогодней росписью в подарочной упаковке.',
            'unit' => 'set',
            'variants' => [
                ['name' => 'Мини (200 г)', 'price' => '350.00', 'stock' => 25],
                ['name' => 'Стандарт (400 г)', 'price' => '650.00', 'stock' => 12],
            ],
            'lead_time_hours' => 24,
            'rating_avg' => '4.80', 'reviews_count' => 14, 'sales_count' => 30,
        ],

        // ─── Круассаны и слойки ─────────────────────────────────────────
        [
            'category' => 'kruassany-i-sloyki', 'vendor' => 'sdoba-kuhnya',
            'name' => 'Круассан классический',
            'short_description' => 'Масляное слоёное тесто.',
            'description' => 'Классический французский круассан из масляного слоёного теста.',
            'price' => '140.00', 'stock' => 70,
            'rating_avg' => '4.60', 'reviews_count' => 70, 'sales_count' => 600,
        ],
        [
            'category' => 'kruassany-i-sloyki', 'vendor' => 'sdoba-kuhnya',
            'name' => 'Круассан миндальный',
            'short_description' => 'С миндальным кремом и пластинками миндаля.',
            'description' => 'Круассан с прослойкой миндального крема, посыпан пластинками миндаля.',
            'price' => '170.00', 'weight_grams' => 85, 'stock' => 40,
            'rating_avg' => '4.70', 'reviews_count' => 40, 'sales_count' => 280,
        ],
        [
            'category' => 'kruassany-i-sloyki', 'vendor' => 'sdoba-kuhnya',
            'name' => 'Круассан шоколадный',
            'short_description' => 'С шоколадной начинкой внутри.',
            'description' => 'Круассан с плиткой тёмного шоколада внутри слоёного теста.',
            'price' => '160.00', 'weight_grams' => 80, 'stock' => 0,
            'rating_avg' => '4.50', 'reviews_count' => 33, 'sales_count' => 210,
        ],
        [
            'category' => 'kruassany-i-sloyki', 'vendor' => 'dom-eklerov',
            'name' => 'Слойка с яблоком',
            'short_description' => 'Слоёное тесто, яблочная начинка.',
            'description' => 'Слойка с корицей и начинкой из карамелизированных яблок.',
            'price' => '130.00', 'old_price' => '150.00', 'stock' => 35,
            'rating_avg' => '4.40', 'reviews_count' => 25, 'sales_count' => 160,
        ],
        [
            'category' => 'kruassany-i-sloyki', 'vendor' => 'dom-eklerov',
            'name' => 'Слойка с вишней',
            'short_description' => 'Слоёное тесто, вишнёвая начинка.',
            'description' => 'Слойка с начинкой из вишни без косточки, лёгкая кислинка.',
            'price' => '140.00', 'stock' => 32,
            'rating_avg' => '4.50', 'reviews_count' => 22, 'sales_count' => 140,
        ],

        // ─── Хлеб и булочки ─────────────────────────────────────────────
        [
            'category' => 'khleb-i-bulochki', 'vendor' => 'sdoba-kuhnya',
            'name' => 'Хлеб пшеничный на закваске',
            'short_description' => 'На натуральной закваске, без дрожжей.',
            'description' => 'Пшеничный хлеб на натуральной закваске длительного брожения.',
            'variants' => [
                ['name' => '400 г', 'price' => '130.00', 'stock' => 30],
                ['name' => '800 г', 'price' => '220.00', 'stock' => 18],
            ],
            'rating_avg' => '4.60', 'reviews_count' => 50, 'sales_count' => 400,
        ],
        [
            'category' => 'khleb-i-bulochki', 'vendor' => 'sdoba-kuhnya',
            'name' => 'Хлеб ржаной',
            'short_description' => 'Ржаная мука, плотный мякиш.',
            'description' => 'Ржаной хлеб на закваске с плотным ароматным мякишем.',
            'variants' => [
                ['name' => '400 г', 'price' => '140.00', 'stock' => 25],
                ['name' => '800 г', 'price' => '240.00', 'stock' => 14],
            ],
            'rating_avg' => '4.50', 'reviews_count' => 38, 'sales_count' => 300,
        ],
        [
            'category' => 'khleb-i-bulochki', 'vendor' => 'sdoba-kuhnya',
            'name' => 'Булочки с корицей',
            'short_description' => 'Сдобное тесто, корица, сахарная глазурь.',
            'description' => 'Сдобные булочки с корицей, покрытые тонкой сахарной глазурью.',
            'price' => '110.00', 'weight_grams' => 90, 'stock' => 45,
            'rating_avg' => '4.70', 'reviews_count' => 45, 'sales_count' => 260,
        ],
        [
            'category' => 'khleb-i-bulochki', 'vendor' => 'sdoba-kuhnya',
            'name' => 'Булочки для бургера',
            'short_description' => 'Сдобные булочки с кунжутом.',
            'description' => 'Мягкие сдобные булочки с кунжутом, подходят для бургеров.',
            'price' => '60.00', 'weight_grams' => 70, 'stock' => 0,
            'rating_avg' => '4.30', 'reviews_count' => 15, 'sales_count' => 90,
        ],
        [
            'category' => 'khleb-i-bulochki', 'vendor' => 'sdoba-kuhnya',
            'name' => 'Чиабатта',
            'short_description' => 'Итальянский хлеб с хрустящей корочкой.',
            'description' => 'Пористая чиабатта с хрустящей корочкой, готовится на пару.',
            'price' => '180.00', 'weight_grams' => 350, 'stock' => 20,
            'rating_avg' => '4.40', 'reviews_count' => 20, 'sales_count' => 110,
        ],

        // ─── Макаруны ───────────────────────────────────────────────────
        [
            'category' => 'makaruny', 'vendor' => 'cupcake-studio',
            'name' => 'Макарун ванильный',
            'short_description' => 'Миндальное печенье, ванильный ганаш.',
            'description' => 'Классический макарун с начинкой из ванильного ганаша.',
            'price' => '120.00', 'stock' => 50,
            'attributes' => [['name' => 'Вкус', 'value' => 'Ваниль']],
            'rating_avg' => '4.60', 'reviews_count' => 40, 'sales_count' => 220,
        ],
        [
            'category' => 'makaruny', 'vendor' => 'cupcake-studio',
            'name' => 'Макарун малиновый',
            'short_description' => 'Миндальное печенье, малиновый конфитюр.',
            'description' => 'Макарун с начинкой из малинового конфитюра, лёгкая кислинка.',
            'price' => '120.00', 'stock' => 45,
            'attributes' => [['name' => 'Вкус', 'value' => 'Малина']],
            'rating_avg' => '4.70', 'reviews_count' => 38, 'sales_count' => 210,
        ],
        [
            'category' => 'makaruny', 'vendor' => 'cupcake-studio',
            'name' => 'Макарун фисташковый',
            'short_description' => 'Миндальное печенье, фисташковый крем.',
            'description' => 'Макарун с кремом на основе фисташковой пасты.',
            'price' => '140.00', 'stock' => 30,
            'attributes' => [['name' => 'Вкус', 'value' => 'Фисташка']],
            'rating_avg' => '4.80', 'reviews_count' => 25, 'sales_count' => 130,
        ],
        [
            'category' => 'makaruny', 'vendor' => 'cupcake-studio',
            'name' => 'Набор макарун (12 шт)',
            'short_description' => 'Ассорти вкусов в подарочной коробке.',
            'description' => 'Набор из 12 макарун ассорти вкусов в подарочной упаковке.',
            'unit' => 'set', 'weight_grams' => 180,
            'price' => '1300.00', 'old_price' => '1500.00', 'stock' => 15,
            'rating_avg' => '4.90', 'reviews_count' => 33, 'sales_count' => 95,
        ],

        // ─── Чизкейки ───────────────────────────────────────────────────
        [
            'category' => 'chizkeyki', 'vendor' => 'chizkeyk-haus',
            'name' => 'Чизкейк Нью-Йорк',
            'short_description' => 'Классический запечённый чизкейк.',
            'description' => 'Плотный запечённый чизкейк на песочной основе, классический рецепт.',
            'variants' => [
                ['name' => 'Целый (800 г)', 'price' => '1400.00', 'stock' => 10],
                ['name' => 'Половина (400 г)', 'price' => '750.00', 'stock' => 20],
            ],
            'rating_avg' => '4.80', 'reviews_count' => 44, 'sales_count' => 230,
        ],
        [
            'category' => 'chizkeyki', 'vendor' => 'chizkeyk-haus',
            'name' => 'Чизкейк ягодный',
            'short_description' => 'С ягодным соусом и свежими ягодами.',
            'description' => 'Чизкейк со свежими ягодами и соусом на основе лесных ягод.',
            'price' => '1350.00', 'old_price' => '1500.00', 'stock' => 8,
            'rating_avg' => '4.70', 'reviews_count' => 30, 'sales_count' => 150,
        ],
        [
            'category' => 'chizkeyki', 'vendor' => 'chizkeyk-haus',
            'name' => 'Чизкейк шоколадный',
            'short_description' => 'С шоколадной начинкой и ганашем.',
            'description' => 'Чизкейк с добавлением тёмного шоколада и шоколадным ганашем сверху.',
            'price' => '1400.00', 'stock' => 7,
            'rating_avg' => '4.60', 'reviews_count' => 26, 'sales_count' => 120,
        ],
        [
            'category' => 'chizkeyki', 'vendor' => 'chizkeyk-haus',
            'name' => 'Чизкейк без сахара с ягодами',
            'short_description' => 'На заменителе сахара, с ягодами.',
            'description' => 'Чизкейк на эритрите со свежими ягодами — без сахара в составе.',
            'price' => '1500.00', 'stock' => 5, 'is_sugar_free' => true,
            'rating_avg' => '4.40', 'reviews_count' => 18, 'sales_count' => 60,
        ],
    ],
];
