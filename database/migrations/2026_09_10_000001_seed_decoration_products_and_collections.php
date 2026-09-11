<?php

use Database\Seeders\CollectionSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Run seeders automatically during production deployment
        $collectionSeeder = new CollectionSeeder();
        $collectionSeeder->run();

        $productSeeder = new ProductSeeder();
        $productSeeder->run();

        $settingSeeder = new SettingSeeder();
        $settingSeeder->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
