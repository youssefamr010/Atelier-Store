<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Schema::disableForeignKeyConstraints();

            $tables = [
                'mediables',
                'media_asset_product',
                'collection_product',
                'order_items',
                'abandoned_carts',
                'cart_items',
                'inventory_movements',
                'reviews',
                'wishlists',
                'product_variants',
                'products',
            ];

            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->delete();
                }
            }
        } catch (\Throwable $e) {
            // Silently log and ignore to prevent deployment 500
            \Illuminate\Support\Facades\Log::warning('Error during product clear migration: ' . $e->getMessage());
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
