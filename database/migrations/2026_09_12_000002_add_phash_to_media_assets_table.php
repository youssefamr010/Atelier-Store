<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('media_assets', function (Blueprint $table) {
            if (!Schema::hasColumn('media_assets', 'phash')) {
                $table->string('phash', 64)->nullable()->index()->after('mime_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_assets', function (Blueprint $table) {
            if (Schema::hasColumn('media_assets', 'phash')) {
                $table->dropIndex(['phash']);
                $table->dropColumn('phash');
            }
        });
    }
};
