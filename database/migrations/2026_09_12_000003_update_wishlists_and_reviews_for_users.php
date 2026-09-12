<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('wishlists')) {
            Schema::table('wishlists', function (Blueprint $table) {
                if (!Schema::hasColumn('wishlists', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->cascadeOnDelete();
                }
                if (Schema::hasColumn('wishlists', 'customer_id')) {
                    $table->foreignId('customer_id')->nullable()->change();
                }
            });
        }

        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                if (!Schema::hasColumn('reviews', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->cascadeOnDelete();
                }
                if (Schema::hasColumn('reviews', 'customer_id')) {
                    $table->foreignId('customer_id')->nullable()->change();
                }
                if (!Schema::hasColumn('reviews', 'title')) {
                    $table->string('title', 255)->nullable()->after('rating');
                }
            });
        }
    }

    public function down(): void {
        if (Schema::hasTable('wishlists') && Schema::hasColumn('wishlists', 'user_id')) {
            Schema::table('wishlists', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
        if (Schema::hasTable('reviews') && Schema::hasColumn('reviews', 'user_id')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};
