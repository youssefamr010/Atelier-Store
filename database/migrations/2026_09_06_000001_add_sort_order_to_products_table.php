<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'sort_order')) {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('sort_order')->default(0)->after('status');
            });
        }
        if (Schema::hasTable('collections') && !Schema::hasColumn('collections', 'sort_order')) {
            Schema::table('collections', function (Blueprint $table) {
                $table->integer('sort_order')->default(0)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'sort_order')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
