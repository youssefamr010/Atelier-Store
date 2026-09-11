<?php
/**
 * Atelier Store — Database Integrity Audit Script
 * Part 2 of Master Prompt
 *
 * Run via:  php artisan tinker --execute="require base_path('scripts/db_audit.php');"
 * Or:       php scripts/db_audit.php  (from project root, with .env loaded)
 */

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$report = [];
$errors = 0;

echo "\n╔══════════════════════════════════════════════════════════╗\n";
echo "║       ATELIER DB INTEGRITY AUDIT — " . now()->format('Y-m-d H:i') . "        ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// ── 1. Orphaned product_variants ──────────────────────────────────────────────
$orphanVariants = DB::select("
    SELECT pv.id, pv.product_id, pv.title, pv.sku
    FROM product_variants pv
    LEFT JOIN products p ON pv.product_id = p.id
    WHERE p.id IS NULL
");
echo "[1] ORPHANED VARIANTS (product_id doesn't exist):\n";
if (empty($orphanVariants)) {
    echo "    ✓ None found.\n\n";
} else {
    $errors += count($orphanVariants);
    foreach ($orphanVariants as $r) {
        echo "    ✗ variant_id={$r->id}  product_id={$r->product_id}  title={$r->title}  sku={$r->sku}\n";
    }
    echo "    Total: " . count($orphanVariants) . " orphaned variants.\n\n";
}

// ── 2. Orphaned mediables (pivot) ─────────────────────────────────────────────
// mediable_type = App\Models\Product
$orphanMediaProduct = DB::select("
    SELECT m.id, m.media_asset_id, m.mediable_id
    FROM mediables m
    LEFT JOIN products p ON m.mediable_id = p.id
    WHERE m.mediable_type = 'App\\\\Models\\\\Product' AND p.id IS NULL
");
echo "[2a] ORPHANED MEDIABLES → Product (product deleted):\n";
if (empty($orphanMediaProduct)) {
    echo "    ✓ None found.\n\n";
} else {
    $errors += count($orphanMediaProduct);
    foreach ($orphanMediaProduct as $r) {
        echo "    ✗ mediable_id={$r->mediable_id}  media_asset_id={$r->media_asset_id}\n";
    }
    echo "    Total: " . count($orphanMediaProduct) . " orphaned pivot rows.\n\n";
}

// mediable_type = App\Models\ProductVariant
$orphanMediaVariant = DB::select("
    SELECT m.id, m.media_asset_id, m.mediable_id
    FROM mediables m
    LEFT JOIN product_variants pv ON m.mediable_id = pv.id
    WHERE m.mediable_type = 'App\\\\Models\\\\ProductVariant' AND pv.id IS NULL
");
echo "[2b] ORPHANED MEDIABLES → Variant (variant deleted):\n";
if (empty($orphanMediaVariant)) {
    echo "    ✓ None found.\n\n";
} else {
    $errors += count($orphanMediaVariant);
    foreach ($orphanMediaVariant as $r) {
        echo "    ✗ mediable_id={$r->mediable_id}  media_asset_id={$r->media_asset_id}\n";
    }
    echo "    Total: " . count($orphanMediaVariant) . " orphaned pivot rows.\n\n";
}

// ── 3. Orphaned cart items ────────────────────────────────────────────────────
$orphanCartItems = DB::select("
    SELECT ci.id, ci.cart_id, ci.product_id, ci.variant_id
    FROM cart_items ci
    LEFT JOIN products p ON ci.product_id = p.id
    WHERE p.id IS NULL
");
echo "[3a] ORPHANED CART ITEMS (product_id deleted):\n";
if (empty($orphanCartItems)) {
    echo "    ✓ None found.\n\n";
} else {
    $errors += count($orphanCartItems);
    foreach ($orphanCartItems as $r) {
        echo "    ✗ cart_item_id={$r->id}  product_id={$r->product_id}  variant_id={$r->variant_id}\n";
    }
    echo "    Total: " . count($orphanCartItems) . " orphaned cart items.\n\n";
}

$orphanCartVariants = DB::select("
    SELECT ci.id, ci.cart_id, ci.product_id, ci.variant_id
    FROM cart_items ci
    LEFT JOIN product_variants pv ON ci.variant_id = pv.id
    WHERE ci.variant_id IS NOT NULL AND pv.id IS NULL
");
echo "[3b] ORPHANED CART ITEMS (variant_id deleted):\n";
if (empty($orphanCartVariants)) {
    echo "    ✓ None found.\n\n";
} else {
    $errors += count($orphanCartVariants);
    foreach ($orphanCartVariants as $r) {
        echo "    ✗ cart_item_id={$r->id}  variant_id={$r->variant_id}\n";
    }
    echo "    Total: " . count($orphanCartVariants) . " cart items with deleted variant.\n\n";
}

// ── 4. Order items referencing deleted variants (without archived safeguard) ──
$columns = Schema::getColumnListing('order_items');
$hasVariantId = in_array('variant_id', $columns);
if ($hasVariantId) {
    $orphanOrderItems = DB::select("
        SELECT oi.id, oi.order_id, oi.product_id, oi.variant_id, oi.product_title
        FROM order_items oi
        LEFT JOIN product_variants pv ON oi.variant_id = pv.id
        WHERE oi.variant_id IS NOT NULL AND pv.id IS NULL
    ");
    echo "[4] ORDER ITEMS referencing deleted variants:\n";
    if (empty($orphanOrderItems)) {
        echo "    ✓ None found.\n\n";
    } else {
        // These are NOT necessarily bugs — archived variants may be intentionally deleted.
        // Report for awareness only.
        foreach ($orphanOrderItems as $r) {
            echo "    ⚠ order_item_id={$r->id}  order_id={$r->order_id}  variant_id={$r->variant_id}  title={$r->product_title}\n";
        }
        echo "    Total: " . count($orphanOrderItems) . " order items (variant since deleted — review if product_title is preserved).\n\n";
    }
} else {
    echo "[4] ORDER ITEMS variant_id column: column not found, skipping.\n\n";
}

// ── 5. Published variants missing price / stock / cover image ─────────────────
$unpublishableVariants = DB::select("
    SELECT id, product_id, title, sku, status, retail_price_minor, price_override_minor, inventory, image_url
    FROM product_variants
    WHERE status IN ('published', 'active')
      AND (
           (COALESCE(price_override_minor, 0) = 0 AND COALESCE(retail_price_minor, 0) = 0)
        OR COALESCE(inventory, 0) <= 0
        OR (image_url IS NULL OR image_url = '')
      )
");
echo "[5] PUBLISHED VARIANTS missing price / stock / image:\n";
if (empty($unpublishableVariants)) {
    echo "    ✓ None found — publish validation is enforced.\n\n";
} else {
    $errors += count($unpublishableVariants);
    foreach ($unpublishableVariants as $r) {
        $issues = [];
        if ((int)($r->price_override_minor ?? 0) === 0 && (int)($r->retail_price_minor ?? 0) === 0) $issues[] = 'NO_PRICE';
        if ((int)($r->inventory ?? 0) <= 0) $issues[] = 'NO_STOCK';
        if (empty($r->image_url)) $issues[] = 'NO_IMAGE';
        echo "    ✗ variant_id={$r->id}  product_id={$r->product_id}  sku={$r->sku}  issues=[" . implode(', ', $issues) . "]\n";
    }
    echo "    Total: " . count($unpublishableVariants) . " published variants violating publish rules.\n\n";
}

// ── 6. Duplicate SKUs ─────────────────────────────────────────────────────────
$dupSkuVariants = DB::select("
    SELECT sku, COUNT(*) as cnt, GROUP_CONCAT(id) as ids
    FROM product_variants
    WHERE sku IS NOT NULL AND sku != ''
    GROUP BY sku
    HAVING cnt > 1
");
echo "[6a] DUPLICATE SKUs (product_variants):\n";
if (empty($dupSkuVariants)) {
    echo "    ✓ None found.\n\n";
} else {
    $errors += count($dupSkuVariants);
    foreach ($dupSkuVariants as $r) {
        echo "    ✗ sku={$r->sku}  count={$r->cnt}  variant_ids=[{$r->ids}]\n";
    }
    echo "    Total: " . count($dupSkuVariants) . " duplicate SKU groups.\n\n";
}

$dupSkuProducts = DB::select("
    SELECT sku, COUNT(*) as cnt, GROUP_CONCAT(id) as ids
    FROM products
    WHERE sku IS NOT NULL AND sku != ''
    GROUP BY sku
    HAVING cnt > 1
");
echo "[6b] DUPLICATE SKUs (products):\n";
if (empty($dupSkuProducts)) {
    echo "    ✓ None found.\n\n";
} else {
    $errors += count($dupSkuProducts);
    foreach ($dupSkuProducts as $r) {
        echo "    ✗ sku={$r->sku}  count={$r->cnt}  product_ids=[{$r->ids}]\n";
    }
    echo "    Total: " . count($dupSkuProducts) . " duplicate product SKU groups.\n\n";
}

// ── 7. Duplicate slugs ────────────────────────────────────────────────────────
$dupSlugs = DB::select("
    SELECT slug, COUNT(*) as cnt, GROUP_CONCAT(id) as ids
    FROM products
    WHERE slug IS NOT NULL AND slug != ''
    GROUP BY slug
    HAVING cnt > 1
");
echo "[7] DUPLICATE SLUGS (products):\n";
if (empty($dupSlugs)) {
    echo "    ✓ None found.\n\n";
} else {
    $errors += count($dupSlugs);
    foreach ($dupSlugs as $r) {
        echo "    ✗ slug={$r->slug}  count={$r->cnt}  product_ids=[{$r->ids}]\n";
    }
    echo "    Total: " . count($dupSlugs) . " duplicate slug groups.\n\n";
}

// ── 8. Missing database indexes ───────────────────────────────────────────────
echo "[8] CHECKING INDEXES:\n";
$indexChecks = [
    ['table' => 'product_variants', 'column' => 'product_id'],
    ['table' => 'products',         'column' => 'slug'],
    ['table' => 'products',         'column' => 'status'],
    ['table' => 'products',         'column' => 'catalog_display_mode'],
    ['table' => 'mediables',        'column' => 'mediable_id'],
    ['table' => 'mediables',        'column' => 'media_asset_id'],
    ['table' => 'cart_items',       'column' => 'product_id'],
    ['table' => 'order_items',      'column' => 'product_id'],
];

foreach ($indexChecks as $check) {
    // MySQL-compatible index lookup via information_schema
    $results = DB::select("
        SELECT INDEX_NAME
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
        LIMIT 1
    ", [$check['table'], $check['column']]);
    if (empty($results)) {
        $errors++;
        echo "    ✗ MISSING INDEX: {$check['table']}.{$check['column']}\n";
    } else {
        echo "    ✓ {$check['table']}.{$check['column']} → {$results[0]->INDEX_NAME}\n";
    }
}
echo "\n";

// ── SUMMARY ───────────────────────────────────────────────────────────────────
echo "══════════════════════════════════════════════════════════\n";
if ($errors === 0) {
    echo "  ✅  ALL CHECKS PASSED — database integrity is clean.\n";
} else {
    echo "  ⚠️   AUDIT COMPLETE — {$errors} issue(s) found. Review above before fixing.\n";
    echo "  Do NOT delete or modify any data without explicit confirmation.\n";
}
echo "══════════════════════════════════════════════════════════\n\n";
