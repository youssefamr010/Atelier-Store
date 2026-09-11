<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add is_new and is_bestseller boolean flags to products.
     * Part 4 of Master Prompt — New/Bestseller badge support.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_new')->default(false)->after('catalog_display_mode');
            $table->boolean('is_bestseller')->default(false)->after('is_new');

            // Index so admin queries (filter by new/bestseller) stay fast
            $table->index('is_new');
            $table->index('is_bestseller');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_new']);
            $table->dropIndex(['is_bestseller']);
            $table->dropColumn(['is_new', 'is_bestseller']);
        });
    }
};
