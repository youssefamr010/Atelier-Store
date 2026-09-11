<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== DEEP DATA AUDIT & CLEANUP ACROSS ENTIRE CATALOG ===\n\n";

$products = \App\Models\Product::with(['variants.mediaAssets', 'mediaAssets'])->get();
$corruptedVariants = [];
$missingFiles = [];

foreach ($products as $p) {
    // Check product main image
    if ($p->image_url) {
        $pPath = public_path(ltrim(parse_url($p->image_url, PHP_URL_PATH), '/'));
        if (!file_exists($pPath)) {
            $missingFiles[] = "Product #{$p->id} [{$p->title}] main image missing on disk: {$p->image_url} ($pPath)";
        }
    }

    foreach ($p->variants as $v) {
        $vAttr = $v->attributes_json ?? [];
        $isCorrupted = false;
        $reason = [];

        // Check if image_url in attributes_json disagrees with column
        if (isset($vAttr['image_url']) && $vAttr['image_url'] !== $v->image_url) {
            $isCorrupted = true;
            $reason[] = "attributes_json.image_url ('{$vAttr['image_url']}') != column image_url ('{$v->image_url}')";
        }

        // Check if image_url points to missing file on disk
        if ($v->image_url) {
            $vPath = public_path(ltrim(parse_url($v->image_url, PHP_URL_PATH), '/'));
            if (!file_exists($vPath)) {
                $isCorrupted = true;
                $reason[] = "Variant cover file missing on disk: {$v->image_url}";
            }
        }

        // Check media assets attached to variant
        foreach ($v->mediaAssets as $ma) {
            $maUrl = $ma->url ?: ('/storage/media/'.$ma->filename);
            $maPath = public_path(ltrim(parse_url($maUrl, PHP_URL_PATH), '/'));
            if (!file_exists($maPath)) {
                $isCorrupted = true;
                $reason[] = "Variant MediaAsset #{$ma->id} file missing on disk: {$maUrl}";
            }
        }

        if ($isCorrupted) {
            $corruptedVariants[] = [
                'product_id' => $p->id,
                'product_title' => $p->title,
                'variant_id' => $v->id,
                'variant_title' => $v->title,
                'current_image_url' => $v->image_url,
                'attributes_json' => $v->attributes_json,
                'reasons' => $reason
            ];
        }
    }
}

echo "Found " . count($corruptedVariants) . " corrupted variant records across the catalog:\n";
foreach ($corruptedVariants as $cv) {
    echo "\n⚠️  Product #{$cv['product_id']} [{$cv['product_title']}] -> Variant #{$cv['variant_id']} [{$cv['variant_title']}]:\n";
    foreach ($cv['reasons'] as $r) {
        echo "     - $r\n";
    }
}

if (!empty($missingFiles)) {
    echo "\nProduct Main Missing Files:\n";
    foreach ($missingFiles as $mf) {
        echo "  - $mf\n";
    }
}
