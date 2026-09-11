<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== PRODUCTS & VARIANTS MEDIA CHECK ===\n";
foreach (App\Models\Product::with(['variants.mediaAssets', 'mediaAssets'])->get() as $p) {
    echo "Product #{$p->id}: {$p->title} (image_url: {$p->image_url})\n";
    echo "  Product mediaAssets ({$p->mediaAssets->count()}):\n";
    foreach ($p->mediaAssets as $ma) {
        echo "    [ID {$ma->id}] {$ma->url} (group: " . ($ma->pivot->group ?? 'none') . ")\n";
    }
    echo "  Variants ({$p->variants->count()}):\n";
    foreach ($p->variants as $v) {
        echo "    Variant #{$v->id} ({$v->title}) | image_url: " . ($v->image_url ?? 'NULL') . "\n";
        echo "      Variant mediaAssets ({$v->mediaAssets->count()}):\n";
        foreach ($v->mediaAssets as $vma) {
            echo "        [ID {$vma->id}] {$vma->url} (group: " . ($vma->pivot->group ?? 'none') . ")\n";
        }
    }
}

echo "\n=== MEDIABLES RAW TABLE ===\n";
foreach (DB::table('mediables')->get() as $row) {
    echo "mediable_type: {$row->mediable_type} | mediable_id: {$row->mediable_id} | media_asset_id: {$row->media_asset_id} | group: " . ($row->group ?? 'null') . "\n";
}
