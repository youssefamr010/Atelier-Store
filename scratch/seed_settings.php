<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Setting;

$defaults = [
    'storeName' => 'ATELIER',
    'storefront_lang' => 'ar',
    'homepage_banner_title' => 'أتيليه',
    'homepage_banner_subtitle' => 'ديكور منزلي فاخر',
    'homepage_banner_height' => 'medium',
    'homepage_banner_overlay' => 'medium',
    'homepage_banner_position' => 'center center',
    'homepage_banner_fit' => 'cover',
    'homepage_banner_zoom' => '100',
    'maintenance_mode' => '0',
    'urgency_stock_threshold' => '5',
];

foreach ($defaults as $key => $value) {
    Setting::set($key, $value);
}

echo "Settings seeded successfully!\n";
