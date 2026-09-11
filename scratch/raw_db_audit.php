<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== RAW DATABASE INSPECTION FOR ALL PRODUCTS & VARIANTS ===\n\n";

$products = \App\Models\Product::with(['variants.mediaAssets', 'mediaAssets'])->get();

foreach ($products as $p) {
    echo "PRODUCT ID: {$p->id}\n";
    echo "  Title: {$p->title}\n";
    echo "  SKU: {$p->sku}\n";
    echo "  image_url: " . var_export($p->image_url, true) . "\n";
    echo "  Media Assets Count: " . $p->mediaAssets->count() . "\n";
    foreach ($p->mediaAssets as $ma) {
        $filePath = public_path(ltrim($ma->url ?: ('/storage/media/'.$ma->filename), '/'));
        $exists = file_exists($filePath);
        echo "    - Product MediaAsset ID {$ma->id}: url='{$ma->url}', file='{$ma->filename}' [Pivot: group={$ma->pivot->group}, sort={$ma->pivot->sort_order}] => " . ($exists ? "FILE_EXISTS" : "FILE_MISSING ($filePath)") . "\n";
    }

    echo "  Variants Count: " . $p->variants->count() . "\n";
    foreach ($p->variants as $v) {
        echo "    VARIANT ID {$v->id} [{$v->title}]:\n";
        echo "      SKU: {$v->sku}\n";
        echo "      image_url: " . var_export($v->image_url, true) . "\n";
        echo "      attributes_json: " . json_encode($v->attributes_json) . "\n";
        echo "      price_override_minor: {$v->price_override_minor}\n";
        echo "      inventory: {$v->inventory}\n";
        echo "      status: {$v->status}\n";
        
        $vImageUrl = $v->image_url;
        if ($vImageUrl) {
            $vFilePath = public_path(ltrim(parse_url($vImageUrl, PHP_URL_PATH), '/'));
            $vExists = file_exists($vFilePath);
            echo "      Cover File Check: {$vImageUrl} => " . ($vExists ? "FILE_EXISTS" : "FILE_MISSING ($vFilePath)") . "\n";
        } else {
            echo "      Cover File Check: NULL / EMPTY\n";
        }

        echo "      Variant Media Assets Count: " . $v->mediaAssets->count() . "\n";
        foreach ($v->mediaAssets as $vma) {
            $vmaUrl = $vma->url ?: ('/storage/media/'.$vma->filename);
            $vmaFilePath = public_path(ltrim(parse_url($vmaUrl, PHP_URL_PATH), '/'));
            $vmaExists = file_exists($vmaFilePath);
            echo "        - Variant MediaAsset ID {$vma->id}: url='{$vma->url}', file='{$vma->filename}' [Pivot: group={$vma->pivot->group}, sort={$vma->pivot->sort_order}] => " . ($vmaExists ? "FILE_EXISTS" : "FILE_MISSING ($vmaFilePath)") . "\n";
        }
    }
    echo "------------------------------------------------------------\n";
}
