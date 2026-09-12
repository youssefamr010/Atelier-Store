<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'pinned_related_ids')) {
                $table->json('pinned_related_ids')->nullable()->after('seo_description');
            }
        });
    }

    public function down(): void {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'pinned_related_ids')) {
                $table->dropColumn('pinned_related_ids');
            }
        });
    }
};
