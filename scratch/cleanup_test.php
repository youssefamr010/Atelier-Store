<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\MediaAsset;

DB::table('mediables')->where('mediable_type', 'App\Models\ProductVariant')->delete();
MediaAsset::where('filename', 'like', 'test-%')->delete();
echo 'Cleaned up test records. Variant mediables count: ' . DB::table('mediables')->where('mediable_type', 'App\Models\ProductVariant')->count() . PHP_EOL;
