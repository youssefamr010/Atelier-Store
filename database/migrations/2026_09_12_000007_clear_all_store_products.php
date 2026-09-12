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
        // Safely wipe old demo products, variants, and attachments
        if (Schema::hasTable('collection_product')) {
            DB::table('collection_product')->delete();
        }
        if (Schema::hasTable('product_variants')) {
            DB::table('product_variants')->delete();
        }
        if (Schema::hasTable('inventory_movements')) {
            DB::table('inventory_movements')->delete();
        }
        if (Schema::hasTable('reviews')) {
            DB::table('reviews')->delete();
        }
        if (Schema::hasTable('wishlists')) {
            DB::table('wishlists')->delete();
        }
        if (Schema::hasTable('cart_items')) {
            DB::table('cart_items')->delete();
        }
        if (Schema::hasTable('products')) {
            DB::table('products')->delete();
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
