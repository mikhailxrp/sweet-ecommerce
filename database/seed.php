<?php

declare(strict_types=1);

/**
 * Демо-каталог Фазы 1: категории, кондитеры, товары, варианты, атрибуты.
 * Данные — в database/data/catalog-demo.php, копирует фото темы в
 * public/uploads/.
 *
 * Запускать после database/install.php и database/seed-core.php:
 *   php database/seed.php
 *
 * Идемпотентно: сущности ищутся по уникальному ключу (email / slug / sku)
 * перед вставкой — повторный запуск не создаёт дублей, только пропускает
 * уже существующие записи. Один сбой откатывает весь прогон — беcполезной
 * записи в БД не остаётся (частично созданные файлы в public/uploads/ не
 * страшны: следующий запуск просто не станет копировать их повторно).
 */

require_once dirname(__DIR__) . '/config/config.php';

/** Ищет файл в public/assets/vendor/images/ и копирует в public/uploads/, если там его ещё нет. */
function copyThemeImage(string $sourceRelative, string $destRelative): string
{
    $source = ROOT_PATH . '/public/assets/vendor/images/' . $sourceRelative;
    $dest   = ROOT_PATH . '/public/uploads/' . $destRelative;

    if (!is_file($dest)) {
        $destDir = dirname($dest);
        if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
            throw new RuntimeException("Не удалось создать директорию: {$destDir}");
        }
        if (!is_file($source)) {
            throw new RuntimeException("Исходное изображение не найдено: {$source}");
        }
        if (!copy($source, $dest)) {
            throw new RuntimeException("Не удалось скопировать изображение: {$source} → {$dest}");
        }
    }

    return $destRelative;
}

/** Характеристики товара наследуются от корневой категории вверх по дереву parent_id. */
function resolveCategoryDefaults(array $categoryDefaults, array $categoryParentMap, string $slug): array
{
    $current = $slug;
    while (($categoryParentMap[$current] ?? null) !== null) {
        $current = $categoryParentMap[$current];
    }

    if (!isset($categoryDefaults[$current])) {
        throw new RuntimeException("Нет дефолтов характеристик для корневой категории: {$current}");
    }

    return $categoryDefaults[$current];
}

$data = require __DIR__ . '/data/catalog-demo.php';
$pdo  = getPdo();

$stats = ['created' => 0, 'skipped' => 0];
$vendorIds = [];
$vendorLogins = [];

try {
    $pdo->beginTransaction();

    // ─── Кондитеры ──────────────────────────────────────────────────────
    $demoPassword = trim(env('SEED_DEMO_PASSWORD', ''));

    foreach ($data['vendors'] as $v) {
        $userStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
        $userStmt->execute(['email' => $v['email']]);
        $userId = $userStmt->fetchColumn();

        if ($userId === false) {
            $password = $demoPassword !== '' ? $demoPassword : bin2hex(random_bytes(16));
            $insertUser = $pdo->prepare(
                "INSERT INTO users (name, email, password_hash, role, is_active)
                 VALUES (:name, :email, :hash, 'vendor', 1)"
            );
            $insertUser->execute([
                'name'  => $v['name'],
                'email' => $v['email'],
                'hash'  => password_hash($password, PASSWORD_DEFAULT),
            ]);
            $userId = (int) $pdo->lastInsertId();
        } else {
            $userId = (int) $userId;
        }

        $vendorStmt = $pdo->prepare('SELECT id FROM vendors WHERE slug = :slug');
        $vendorStmt->execute(['slug' => $v['slug']]);
        $vendorId = $vendorStmt->fetchColumn();

        if ($vendorId === false) {
            $logoExt    = pathinfo($v['logo_source'], PATHINFO_EXTENSION);
            $bannerExt  = pathinfo($v['banner_source'], PATHINFO_EXTENSION);
            $logoPath   = copyThemeImage($v['logo_source'], "vendors/{$v['slug']}-logo.{$logoExt}");
            $bannerPath = copyThemeImage($v['banner_source'], "vendors/{$v['slug']}-banner.{$bannerExt}");

            $insertVendor = $pdo->prepare(
                "INSERT INTO vendors
                    (user_id, name, slug, description, logo_path, banner_path, city, address,
                     phone, email, inn, commission_percent, status, rating_avg, reviews_count, approved_at)
                 VALUES
                    (:user_id, :name, :slug, :description, :logo_path, :banner_path, :city, :address,
                     :phone, :email, :inn, :commission_percent, 'approved', :rating_avg, :reviews_count, NOW())"
            );
            $insertVendor->execute([
                'user_id'            => $userId,
                'name'               => $v['name'],
                'slug'               => $v['slug'],
                'description'        => $v['description'],
                'logo_path'          => $logoPath,
                'banner_path'        => $bannerPath,
                'city'               => $v['city'],
                'address'            => $v['address'],
                'phone'              => $v['phone'],
                'email'              => $v['email'],
                'inn'                => $v['inn'],
                'commission_percent' => $v['commission_percent'],
                'rating_avg'         => $v['rating_avg'],
                'reviews_count'      => $v['reviews_count'],
            ]);
            $vendorId = (int) $pdo->lastInsertId();
            $stats['created']++;
        } else {
            $vendorId = (int) $vendorId;
            $stats['skipped']++;
        }

        $vendorIds[$v['slug']] = $vendorId;
        $vendorLogins[] = $v['email'];
    }

    // ─── Категории ──────────────────────────────────────────────────────
    $categoryIds = [];
    $categoryParentMap = array_column($data['categories'], 'parent', 'slug');

    foreach ($data['categories'] as $c) {
        $stmt = $pdo->prepare('SELECT id FROM categories WHERE slug = :slug');
        $stmt->execute(['slug' => $c['slug']]);
        $categoryId = $stmt->fetchColumn();

        if ($categoryId === false) {
            $categoryExt = pathinfo($c['image_source'], PATHINFO_EXTENSION);
            $imagePath = copyThemeImage($c['image_source'], "categories/{$c['slug']}.{$categoryExt}");
            $parentId  = $c['parent'] !== null ? $categoryIds[$c['parent']] : null;

            $insert = $pdo->prepare(
                'INSERT INTO categories (parent_id, name, slug, description, image_path, sort_order)
                 VALUES (:parent_id, :name, :slug, :description, :image_path, :sort_order)'
            );
            $insert->execute([
                'parent_id'   => $parentId,
                'name'        => $c['name'],
                'slug'        => $c['slug'],
                'description' => $c['description'],
                'image_path'  => $imagePath,
                'sort_order'  => $c['sort_order'],
            ]);
            $categoryId = (int) $pdo->lastInsertId();
            $stats['created']++;
        } else {
            $categoryId = (int) $categoryId;
            $stats['skipped']++;
        }

        $categoryIds[$c['slug']] = $categoryId;
    }

    // ─── Товары ─────────────────────────────────────────────────────────
    $productImagePool = $data['product_image_pool'];
    $productIndex = 0;

    foreach ($data['products'] as $p) {
        $productIndex++;
        $sku = sprintf('SD-%05d', $productIndex);

        $stmt = $pdo->prepare('SELECT id FROM products WHERE sku = :sku');
        $stmt->execute(['sku' => $sku]);
        if ($stmt->fetchColumn() !== false) {
            $stats['skipped']++;
            continue;
        }

        $defaults = resolveCategoryDefaults($data['category_defaults'], $categoryParentMap, $p['category']);
        $slug = slugify($p['name']);

        $hasVariants = !empty($p['variants']);
        $price = $hasVariants
            ? (string) min(array_map(static fn (array $variant): float => (float) $variant['price'], $p['variants']))
            : $p['price'];
        $stockQuantity = $hasVariants ? 0 : ($p['stock'] ?? 20);

        $imageSource = $productImagePool[($productIndex - 1) % count($productImagePool)];
        $productExt = pathinfo($imageSource, PATHINFO_EXTENSION);
        $imagePath = copyThemeImage($imageSource, "products/{$sku}.{$productExt}");

        $insertProduct = $pdo->prepare(
            "INSERT INTO products
                (vendor_id, category_id, sku, name, slug, short_description, description,
                 price, old_price, stock_quantity, has_variants, unit, weight_grams,
                 composition, allergens, calories_per_100g, shelf_life_hours, storage_conditions,
                 lead_time_hours, is_sugar_free, moderation_status, rating_avg, reviews_count,
                 sales_count, is_active)
             VALUES
                (:vendor_id, :category_id, :sku, :name, :slug, :short_description, :description,
                 :price, :old_price, :stock_quantity, :has_variants, :unit, :weight_grams,
                 :composition, :allergens, :calories_per_100g, :shelf_life_hours, :storage_conditions,
                 :lead_time_hours, :is_sugar_free, 'approved', :rating_avg, :reviews_count,
                 :sales_count, 1)"
        );
        $insertProduct->execute([
            'vendor_id'          => $vendorIds[$p['vendor']],
            'category_id'        => $categoryIds[$p['category']],
            'sku'                => $sku,
            'name'               => $p['name'],
            'slug'               => $slug,
            'short_description'  => $p['short_description'],
            'description'        => $p['description'],
            'price'              => $price,
            'old_price'          => $p['old_price'] ?? null,
            'stock_quantity'     => $stockQuantity,
            'has_variants'       => $hasVariants ? 1 : 0,
            'unit'               => $p['unit'] ?? $defaults['unit'],
            'weight_grams'       => $hasVariants ? null : ($p['weight_grams'] ?? $defaults['weight_grams']),
            'composition'        => $p['composition'] ?? $defaults['composition'],
            'allergens'          => $p['allergens'] ?? $defaults['allergens'],
            'calories_per_100g'  => $p['calories_per_100g'] ?? $defaults['calories_per_100g'],
            'shelf_life_hours'   => $p['shelf_life_hours'] ?? $defaults['shelf_life_hours'],
            'storage_conditions' => $p['storage_conditions'] ?? $defaults['storage_conditions'],
            'lead_time_hours'    => $p['lead_time_hours'] ?? 0,
            'is_sugar_free'      => !empty($p['is_sugar_free']) ? 1 : 0,
            'rating_avg'         => $p['rating_avg'],
            'reviews_count'      => $p['reviews_count'],
            'sales_count'        => $p['sales_count'],
        ]);
        $productId = (int) $pdo->lastInsertId();

        $pdo->prepare(
            'INSERT INTO product_images (product_id, path, alt, sort_order, is_main)
             VALUES (:product_id, :path, :alt, 0, 1)'
        )->execute([
            'product_id' => $productId,
            'path'       => $imagePath,
            'alt'        => $p['name'],
        ]);

        if ($hasVariants) {
            foreach ($p['variants'] as $vIndex => $variant) {
                $pdo->prepare(
                    'INSERT INTO product_variants (product_id, sku, name, price, stock_quantity, sort_order)
                     VALUES (:product_id, :sku, :name, :price, :stock_quantity, :sort_order)'
                )->execute([
                    'product_id'     => $productId,
                    'sku'            => $sku . '-' . ($vIndex + 1),
                    'name'           => $variant['name'],
                    'price'          => $variant['price'],
                    'stock_quantity' => $variant['stock'],
                    'sort_order'     => $vIndex,
                ]);
            }
        }

        if (!empty($p['attributes'])) {
            foreach ($p['attributes'] as $aIndex => $attr) {
                $pdo->prepare(
                    'INSERT INTO product_attributes (product_id, attr_name, attr_value, sort_order)
                     VALUES (:product_id, :attr_name, :attr_value, :sort_order)'
                )->execute([
                    'product_id' => $productId,
                    'attr_name'  => $attr['name'],
                    'attr_value' => $attr['value'],
                    'sort_order' => $aIndex,
                ]);
            }
        }

        $stats['created']++;
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "⚠️  Сид прерван, изменения в БД откачены: {$e->getMessage()}\n";
    exit(1);
}

echo "✅ Демо-каталог готов. Создано записей: {$stats['created']}, пропущено (уже было): {$stats['skipped']}.\n";
echo "Кондитеры для входа (role = vendor):\n";
foreach ($vendorLogins as $email) {
    echo "  - {$email}\n";
}
echo "Пароль — значение SEED_DEMO_PASSWORD из .env.\n";
