<?php

declare(strict_types=1);

/**
 * Установка БД — создание таблиц.
 * Запускать один раз через браузер: /install-temp.php
 * После выполнения — удалить install-temp.php из public/
 *
 * Схема соответствует .docs/database.md — при добавлении своей таблицы
 * сначала опиши её там, потом продублируй сюда в порядке зависимостей
 * (родитель раньше потомка).
 *
 * ВАЖНО: скрипт рассчитан на ЧИСТУЮ базу. CREATE TABLE IF NOT EXISTS не
 * добавляет колонки в уже существующую таблицу — если база создана
 * прошлой версией схемы, накатывай изменения отдельным ALTER-скриптом,
 * а не повторным запуском этого файла.
 *
 * Порядок разделов повторяет .docs/database.md, но внутри переставлен
 * под зависимости FK: сначала roles и vendors, потом products и заказы.
 */

require_once dirname(__DIR__) . '/config/config.php';

$pdo = getPdo();

// ─── Справочники без внешних ключей ───────────────────────────────────────

$pdo->exec("
    CREATE TABLE IF NOT EXISTS roles (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        name        VARCHAR(50) NOT NULL,
        slug        VARCHAR(50) NOT NULL UNIQUE,
        permissions JSON NOT NULL,
        is_system   TINYINT(1) NOT NULL DEFAULT 0,
        created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS delivery_zones (
        id            INT AUTO_INCREMENT PRIMARY KEY,
        name          VARCHAR(100) NOT NULL,
        city          VARCHAR(100) NOT NULL,
        cost          DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        free_from     DECIMAL(10, 2) NULL,
        min_lead_days INT NOT NULL DEFAULT 0,
        is_active     TINYINT(1) NOT NULL DEFAULT 1,
        sort_order    INT NOT NULL DEFAULT 0,
        KEY idx_delivery_zones_active (is_active, sort_order),
        KEY idx_delivery_zones_city (city)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS coupons (
        id                   INT AUTO_INCREMENT PRIMARY KEY,
        code                 VARCHAR(50) NOT NULL UNIQUE,
        description          VARCHAR(255) NULL,
        type                 ENUM('percent', 'fixed') NOT NULL,
        value                DECIMAL(10, 2) NOT NULL,
        min_order_amount     DECIMAL(10, 2) NULL,
        max_discount         DECIMAL(10, 2) NULL,
        starts_at            DATETIME NULL,
        expires_at           DATETIME NULL,
        usage_limit          INT NULL,
        usage_limit_per_user INT NULL,
        used_count           INT NOT NULL DEFAULT 0,
        is_active            TINYINT(1) NOT NULL DEFAULT 1,
        created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_coupons_active (is_active, expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS settings (
        setting_key   VARCHAR(100) PRIMARY KEY,
        setting_value TEXT NULL,
        updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// ─── Пользователи и кондитеры ─────────────────────────────────────────────

$pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id            INT AUTO_INCREMENT PRIMARY KEY,
        name          VARCHAR(100) NOT NULL,
        email         VARCHAR(150) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        phone         VARCHAR(20) NULL,
        avatar_path   VARCHAR(255) NULL,
        role          ENUM('customer', 'vendor', 'admin') NOT NULL DEFAULT 'customer',
        role_id       INT NULL,
        is_active     TINYINT(1) NOT NULL DEFAULT 1,
        created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_users_role (role),
        KEY idx_users_phone (phone),
        CONSTRAINT fk_users_role
            FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS password_resets (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        user_id    INT NOT NULL,
        token_hash CHAR(64) NOT NULL UNIQUE,
        expires_at DATETIME NOT NULL,
        used_at    TIMESTAMP NULL DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_password_resets_user (user_id),
        KEY idx_password_resets_expires (expires_at),
        CONSTRAINT fk_password_resets_user
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS addresses (
        id             INT AUTO_INCREMENT PRIMARY KEY,
        user_id        INT NOT NULL,
        title          VARCHAR(50) NULL,
        recipient_name VARCHAR(100) NOT NULL,
        phone          VARCHAR(20) NOT NULL,
        city           VARCHAR(100) NOT NULL,
        street         VARCHAR(150) NOT NULL,
        house          VARCHAR(20) NOT NULL,
        apartment      VARCHAR(20) NULL,
        entrance       VARCHAR(10) NULL,
        floor          VARCHAR(10) NULL,
        postcode       VARCHAR(10) NULL,
        comment        VARCHAR(255) NULL,
        is_default     TINYINT(1) NOT NULL DEFAULT 0,
        created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_addresses_user (user_id, is_default),
        CONSTRAINT fk_addresses_user
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS cart_reminders (
        user_id      INT PRIMARY KEY,
        last_sent_at TIMESTAMP NOT NULL,
        CONSTRAINT fk_cart_reminders_user
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS vendors (
        id                 INT AUTO_INCREMENT PRIMARY KEY,
        user_id            INT NOT NULL UNIQUE,
        name               VARCHAR(150) NOT NULL,
        slug               VARCHAR(160) NOT NULL UNIQUE,
        description        TEXT NULL,
        logo_path          VARCHAR(255) NULL,
        banner_path        VARCHAR(255) NULL,
        city               VARCHAR(100) NULL,
        address            VARCHAR(255) NULL,
        phone              VARCHAR(20) NULL,
        email              VARCHAR(150) NULL,
        inn                VARCHAR(12) NULL,
        commission_percent DECIMAL(5, 2) NOT NULL DEFAULT 15.00,
        min_order_amount   DECIMAL(10, 2) NULL,
        status             ENUM('pending', 'approved', 'rejected', 'suspended') NOT NULL DEFAULT 'pending',
        rejection_reason   VARCHAR(500) NULL,
        rating_avg         DECIMAL(3, 2) NOT NULL DEFAULT 0.00,
        reviews_count      INT NOT NULL DEFAULT 0,
        approved_at        TIMESTAMP NULL DEFAULT NULL,
        created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_vendors_status (status),
        KEY idx_vendors_city (city),
        CONSTRAINT fk_vendors_user
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// ─── Каталог ──────────────────────────────────────────────────────────────

$pdo->exec("
    CREATE TABLE IF NOT EXISTS categories (
        id               INT AUTO_INCREMENT PRIMARY KEY,
        parent_id        INT NULL,
        name             VARCHAR(150) NOT NULL,
        slug             VARCHAR(160) NOT NULL UNIQUE,
        description      TEXT NULL,
        image_path       VARCHAR(255) NULL,
        is_active        TINYINT(1) NOT NULL DEFAULT 1,
        sort_order       INT NOT NULL DEFAULT 0,
        meta_title       VARCHAR(255) NULL,
        meta_description VARCHAR(500) NULL,
        created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_categories_parent (parent_id),
        KEY idx_categories_active (is_active, sort_order),
        CONSTRAINT fk_categories_parent
            FOREIGN KEY (parent_id) REFERENCES categories (id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS products (
        id                 INT AUTO_INCREMENT PRIMARY KEY,
        vendor_id          INT NOT NULL,
        category_id        INT NOT NULL,
        sku                VARCHAR(64) NOT NULL UNIQUE,
        name               VARCHAR(200) NOT NULL,
        slug               VARCHAR(220) NOT NULL UNIQUE,
        short_description  VARCHAR(500) NULL,
        description        TEXT NULL,
        price              DECIMAL(10, 2) NOT NULL,
        old_price          DECIMAL(10, 2) NULL,
        stock_quantity     INT NOT NULL DEFAULT 0,
        has_variants       TINYINT(1) NOT NULL DEFAULT 0,
        unit               ENUM('piece', 'kg', 'set') NOT NULL DEFAULT 'piece',
        weight_grams       INT NULL,
        composition        TEXT NULL,
        allergens          VARCHAR(255) NULL,
        calories_per_100g  INT NULL,
        shelf_life_hours   INT NULL,
        storage_conditions VARCHAR(255) NULL,
        lead_time_hours    INT NOT NULL DEFAULT 0,
        is_sugar_free      TINYINT(1) NOT NULL DEFAULT 0,
        moderation_status  ENUM('draft', 'pending', 'approved', 'rejected') NOT NULL DEFAULT 'draft',
        rejection_reason   VARCHAR(500) NULL,
        rating_avg         DECIMAL(3, 2) NOT NULL DEFAULT 0.00,
        reviews_count      INT NOT NULL DEFAULT 0,
        sales_count        INT NOT NULL DEFAULT 0,
        views_count        INT NOT NULL DEFAULT 0,
        is_active          TINYINT(1) NOT NULL DEFAULT 1,
        meta_title         VARCHAR(255) NULL,
        meta_description   VARCHAR(500) NULL,
        created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_products_category_active (category_id, is_active, moderation_status),
        KEY idx_products_vendor_active (vendor_id, is_active),
        KEY idx_products_price (price),
        KEY idx_products_rating (rating_avg),
        KEY idx_products_sales (sales_count),
        KEY idx_products_created (created_at),
        KEY idx_products_moderation (moderation_status),
        FULLTEXT KEY ft_products_name_description (name, description),
        CONSTRAINT fk_products_vendor
            FOREIGN KEY (vendor_id) REFERENCES vendors (id) ON DELETE RESTRICT,
        CONSTRAINT fk_products_category
            FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS product_images (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        path       VARCHAR(255) NOT NULL,
        alt        VARCHAR(255) NULL,
        sort_order INT NOT NULL DEFAULT 0,
        is_main    TINYINT(1) NOT NULL DEFAULT 0,
        KEY idx_product_images_product (product_id, sort_order),
        CONSTRAINT fk_product_images_product
            FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS product_variants (
        id             INT AUTO_INCREMENT PRIMARY KEY,
        product_id     INT NOT NULL,
        sku            VARCHAR(64) NOT NULL UNIQUE,
        name           VARCHAR(100) NOT NULL,
        price          DECIMAL(10, 2) NOT NULL,
        old_price      DECIMAL(10, 2) NULL,
        stock_quantity INT NOT NULL DEFAULT 0,
        weight_grams   INT NULL,
        image_path     VARCHAR(255) NULL,
        sort_order     INT NOT NULL DEFAULT 0,
        is_active      TINYINT(1) NOT NULL DEFAULT 1,
        KEY idx_product_variants_product (product_id, is_active, sort_order),
        CONSTRAINT fk_product_variants_product
            FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS product_attributes (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        attr_name  VARCHAR(100) NOT NULL,
        attr_value VARCHAR(255) NOT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        KEY idx_product_attributes_product (product_id),
        KEY idx_product_attributes_filter (attr_name, attr_value),
        CONSTRAINT fk_product_attributes_product
            FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS wishlists (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        user_id    INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_wishlists_user_product (user_id, product_id),
        KEY idx_wishlists_user (user_id),
        CONSTRAINT fk_wishlists_user
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
        CONSTRAINT fk_wishlists_product
            FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS cart_items (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        session_id VARCHAR(64) NULL,
        user_id    INT NULL,
        product_id INT NOT NULL,
        variant_id INT NULL,
        quantity   INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_cart_items_session (session_id),
        KEY idx_cart_items_user (user_id),
        KEY idx_cart_items_updated (updated_at),
        CONSTRAINT fk_cart_items_user
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
        CONSTRAINT fk_cart_items_product
            FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
        CONSTRAINT fk_cart_items_variant
            FOREIGN KEY (variant_id) REFERENCES product_variants (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// ─── Заказы, суб-заказы, платежи ──────────────────────────────────────────

$pdo->exec("
    CREATE TABLE IF NOT EXISTS orders (
        id               INT AUTO_INCREMENT PRIMARY KEY,
        order_number     VARCHAR(20) NOT NULL UNIQUE,
        user_id          INT NULL,
        customer_name    VARCHAR(100) NOT NULL,
        customer_phone   VARCHAR(20) NOT NULL,
        customer_email   VARCHAR(150) NOT NULL,
        status           ENUM('created', 'paid', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') NOT NULL DEFAULT 'created',
        payment_method   ENUM('online', 'cash_on_delivery') NOT NULL,
        payment_status   ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
        payment_id       VARCHAR(64) NULL,
        delivery_type    ENUM('courier', 'pickup') NOT NULL,
        delivery_zone_id INT NULL,
        delivery_address VARCHAR(500) NULL,
        delivery_date    DATE NULL,
        delivery_slot    VARCHAR(20) NULL,
        delivery_cost    DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        subtotal         DECIMAL(10, 2) NOT NULL,
        discount_total   DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        coupon_id        INT NULL,
        coupon_code      VARCHAR(50) NULL,
        total            DECIMAL(10, 2) NOT NULL,
        comment          TEXT NULL,
        call_before      TINYINT(1) NOT NULL DEFAULT 0,
        cancel_reason    VARCHAR(255) NULL,
        paid_at          TIMESTAMP NULL DEFAULT NULL,
        created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_orders_user (user_id),
        KEY idx_orders_status (status),
        KEY idx_orders_created (created_at),
        KEY idx_orders_phone (customer_phone),
        KEY idx_orders_payment (payment_id),
        CONSTRAINT fk_orders_user
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL,
        CONSTRAINT fk_orders_zone
            FOREIGN KEY (delivery_zone_id) REFERENCES delivery_zones (id) ON DELETE SET NULL,
        CONSTRAINT fk_orders_coupon
            FOREIGN KEY (coupon_id) REFERENCES coupons (id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS vendor_payouts (
        id                INT AUTO_INCREMENT PRIMARY KEY,
        vendor_id         INT NOT NULL,
        period_from       DATE NOT NULL,
        period_to         DATE NOT NULL,
        orders_count      INT NOT NULL,
        gross_amount      DECIMAL(10, 2) NOT NULL,
        commission_amount DECIMAL(10, 2) NOT NULL,
        payout_amount     DECIMAL(10, 2) NOT NULL,
        status            ENUM('pending', 'paid') NOT NULL DEFAULT 'pending',
        paid_at           TIMESTAMP NULL DEFAULT NULL,
        note              VARCHAR(255) NULL,
        created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_vendor_payouts_vendor (vendor_id, status),
        KEY idx_vendor_payouts_period (period_from, period_to),
        CONSTRAINT fk_vendor_payouts_vendor
            FOREIGN KEY (vendor_id) REFERENCES vendors (id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS vendor_orders (
        id                 INT AUTO_INCREMENT PRIMARY KEY,
        order_id           INT NOT NULL,
        vendor_id          INT NOT NULL,
        status             ENUM('created', 'processing', 'ready', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'created',
        subtotal           DECIMAL(10, 2) NOT NULL,
        commission_percent DECIMAL(5, 2) NOT NULL,
        commission_amount  DECIMAL(10, 2) NOT NULL,
        payout_amount      DECIMAL(10, 2) NOT NULL,
        payout_id          INT NULL,
        created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_vendor_orders_order_vendor (order_id, vendor_id),
        KEY idx_vendor_orders_vendor (vendor_id, status),
        KEY idx_vendor_orders_payout (payout_id),
        CONSTRAINT fk_vendor_orders_order
            FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
        CONSTRAINT fk_vendor_orders_vendor
            FOREIGN KEY (vendor_id) REFERENCES vendors (id) ON DELETE RESTRICT,
        CONSTRAINT fk_vendor_orders_payout
            FOREIGN KEY (payout_id) REFERENCES vendor_payouts (id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS order_items (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        order_id        INT NOT NULL,
        vendor_order_id INT NOT NULL,
        product_id      INT NULL,
        variant_id      INT NULL,
        product_name    VARCHAR(200) NOT NULL,
        variant_name    VARCHAR(100) NULL,
        product_image   VARCHAR(255) NULL,
        price           DECIMAL(10, 2) NOT NULL,
        quantity        INT NOT NULL,
        KEY idx_order_items_order (order_id),
        KEY idx_order_items_vendor_order (vendor_order_id),
        KEY idx_order_items_product (product_id),
        CONSTRAINT fk_order_items_order
            FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
        CONSTRAINT fk_order_items_vendor_order
            FOREIGN KEY (vendor_order_id) REFERENCES vendor_orders (id) ON DELETE CASCADE,
        CONSTRAINT fk_order_items_product
            FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE SET NULL,
        CONSTRAINT fk_order_items_variant
            FOREIGN KEY (variant_id) REFERENCES product_variants (id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS order_status_history (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        order_id        INT NOT NULL,
        vendor_order_id INT NULL,
        from_status     VARCHAR(20) NULL,
        to_status       VARCHAR(20) NOT NULL,
        changed_by      INT NULL,
        comment         VARCHAR(255) NULL,
        created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_order_status_history_order (order_id, created_at),
        KEY idx_order_status_history_vendor_order (vendor_order_id),
        CONSTRAINT fk_order_status_history_order
            FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
        CONSTRAINT fk_order_status_history_vendor_order
            FOREIGN KEY (vendor_order_id) REFERENCES vendor_orders (id) ON DELETE CASCADE,
        CONSTRAINT fk_order_status_history_user
            FOREIGN KEY (changed_by) REFERENCES users (id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS payment_logs (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        order_id        INT NOT NULL,
        provider        VARCHAR(50) NOT NULL,
        event           VARCHAR(50) NULL,
        signature_valid TINYINT(1) NOT NULL,
        payload         TEXT NOT NULL,
        created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_payment_logs_order (order_id),
        KEY idx_payment_logs_created (created_at),
        CONSTRAINT fk_payment_logs_order
            FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS coupon_usages (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        coupon_id       INT NOT NULL,
        order_id        INT NOT NULL,
        user_id         INT NULL,
        discount_amount DECIMAL(10, 2) NOT NULL,
        created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_coupon_usages_coupon_order (coupon_id, order_id),
        KEY idx_coupon_usages_coupon (coupon_id),
        KEY idx_coupon_usages_user (user_id),
        CONSTRAINT fk_coupon_usages_coupon
            FOREIGN KEY (coupon_id) REFERENCES coupons (id) ON DELETE CASCADE,
        CONSTRAINT fk_coupon_usages_order
            FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
        CONSTRAINT fk_coupon_usages_user
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// ─── Отзывы ───────────────────────────────────────────────────────────────

$pdo->exec("
    CREATE TABLE IF NOT EXISTS reviews (
        id                INT AUTO_INCREMENT PRIMARY KEY,
        product_id        INT NOT NULL,
        vendor_id         INT NOT NULL,
        user_id           INT NULL,
        order_id          INT NULL,
        author_name       VARCHAR(100) NOT NULL,
        rating            TINYINT NOT NULL,
        text              TEXT NULL,
        status            ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
        vendor_reply      TEXT NULL,
        vendor_replied_at TIMESTAMP NULL DEFAULT NULL,
        created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_reviews_product_user_order (product_id, user_id, order_id),
        KEY idx_reviews_product (product_id, status),
        KEY idx_reviews_vendor (vendor_id, status),
        KEY idx_reviews_moderation (status, created_at),
        CONSTRAINT fk_reviews_product
            FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
        CONSTRAINT fk_reviews_vendor
            FOREIGN KEY (vendor_id) REFERENCES vendors (id) ON DELETE CASCADE,
        CONSTRAINT fk_reviews_user
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL,
        CONSTRAINT fk_reviews_order
            FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS review_images (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        review_id  INT NOT NULL,
        path       VARCHAR(255) NOT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        KEY idx_review_images_review (review_id),
        CONSTRAINT fk_review_images_review
            FOREIGN KEY (review_id) REFERENCES reviews (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// ─── Контент ──────────────────────────────────────────────────────────────

$pdo->exec("
    CREATE TABLE IF NOT EXISTS banners (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        title      VARCHAR(150) NULL,
        subtitle   VARCHAR(255) NULL,
        image_path VARCHAR(255) NOT NULL,
        link_url   VARCHAR(255) NULL,
        position   ENUM('hero', 'promo', 'category', 'sidebar') NOT NULL,
        starts_at  DATETIME NULL,
        ends_at    DATETIME NULL,
        sort_order INT NOT NULL DEFAULT 0,
        is_active  TINYINT(1) NOT NULL DEFAULT 1,
        KEY idx_banners_position (position, is_active, sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS blog_categories (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        name       VARCHAR(100) NOT NULL,
        slug       VARCHAR(120) NOT NULL UNIQUE,
        sort_order INT NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS blog_posts (
        id               INT AUTO_INCREMENT PRIMARY KEY,
        category_id      INT NULL,
        author_id        INT NULL,
        title            VARCHAR(200) NOT NULL,
        slug             VARCHAR(220) NOT NULL UNIQUE,
        excerpt          VARCHAR(500) NULL,
        content          MEDIUMTEXT NOT NULL,
        cover_path       VARCHAR(255) NULL,
        status           ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
        published_at     DATETIME NULL,
        views_count      INT NOT NULL DEFAULT 0,
        meta_title       VARCHAR(255) NULL,
        meta_description VARCHAR(500) NULL,
        created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_blog_posts_status (status, published_at),
        KEY idx_blog_posts_category (category_id),
        CONSTRAINT fk_blog_posts_category
            FOREIGN KEY (category_id) REFERENCES blog_categories (id) ON DELETE SET NULL,
        CONSTRAINT fk_blog_posts_author
            FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS pages (
        id               INT AUTO_INCREMENT PRIMARY KEY,
        title            VARCHAR(200) NOT NULL,
        slug             VARCHAR(220) NOT NULL UNIQUE,
        content          MEDIUMTEXT NOT NULL,
        meta_title       VARCHAR(255) NULL,
        meta_description VARCHAR(500) NULL,
        is_active        TINYINT(1) NOT NULL DEFAULT 1,
        updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS menu_items (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        parent_id  INT NULL,
        location   ENUM('header', 'footer_1', 'footer_2', 'footer_3') NOT NULL,
        title      VARCHAR(100) NOT NULL,
        url        VARCHAR(255) NOT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        is_active  TINYINT(1) NOT NULL DEFAULT 1,
        KEY idx_menu_items_location (location, is_active, sort_order),
        KEY idx_menu_items_parent (parent_id),
        CONSTRAINT fk_menu_items_parent
            FOREIGN KEY (parent_id) REFERENCES menu_items (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// ─── Коммуникации ─────────────────────────────────────────────────────────

$pdo->exec("
    CREATE TABLE IF NOT EXISTS support_tickets (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        user_id     INT NULL,
        order_id    INT NULL,
        guest_name  VARCHAR(100) NULL,
        guest_email VARCHAR(150) NULL,
        subject     VARCHAR(200) NOT NULL,
        source      ENUM('support', 'contact_form') NOT NULL DEFAULT 'support',
        status      ENUM('open', 'answered', 'closed') NOT NULL DEFAULT 'open',
        created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_support_tickets_user (user_id),
        KEY idx_support_tickets_status (status, created_at),
        CONSTRAINT fk_support_tickets_user
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL,
        CONSTRAINT fk_support_tickets_order
            FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS ticket_messages (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        ticket_id  INT NOT NULL,
        user_id    INT NULL,
        is_staff   TINYINT(1) NOT NULL DEFAULT 0,
        message    TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_ticket_messages_ticket (ticket_id, created_at),
        CONSTRAINT fk_ticket_messages_ticket
            FOREIGN KEY (ticket_id) REFERENCES support_tickets (id) ON DELETE CASCADE,
        CONSTRAINT fk_ticket_messages_user
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS newsletter_subscribers (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        email           VARCHAR(150) NOT NULL UNIQUE,
        confirm_token   CHAR(64) NULL,
        confirmed_at    TIMESTAMP NULL DEFAULT NULL,
        unsubscribed_at TIMESTAMP NULL DEFAULT NULL,
        created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_newsletter_confirmed (confirmed_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// ─── Системные справочники ────────────────────────────────────────────────
// Только то, без чего приложение не стартует. Демо-контент (категории,
// товары, кондитеры, зоны доставки) — в database/seed.php.

$pdo->exec("
    INSERT IGNORE INTO roles (slug, name, permissions, is_system) VALUES
    ('superadmin', 'Суперадминистратор', '[\"*\"]', 1)
");

$defaultSettings = [
    'shop_name'                  => 'Сдоба',
    'contact_phone'              => '',
    'contact_email'              => '',
    'order_number_prefix'        => 'SD',
    'default_commission_percent' => '15.00',
    'vat_rate'                   => '20',
    'free_delivery_from'         => '3000.00',
    'products_per_page'          => '24',
    'reviews_premoderation'      => '1',
];

$stmt = $pdo->prepare(
    'INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (:k, :v)'
);

foreach ($defaultSettings as $key => $value) {
    $stmt->execute(['k' => $key, 'v' => $value]);
}

// ─── Проверка ─────────────────────────────────────────────────────────────

$expected = 33;
$actual = (int) $pdo->query('SELECT COUNT(*) FROM information_schema.tables
    WHERE table_schema = DATABASE()')->fetchColumn();

if ($actual < $expected) {
    echo "⚠️  Создано таблиц: {$actual}, ожидалось {$expected}. Проверь логи.\n";
    exit(1);
}

echo "✅ Таблицы созданы успешно: {$actual}.\n";
echo "Дальше: залей демо-контент через database/seed.php и удали install-temp.php из public/.\n";
