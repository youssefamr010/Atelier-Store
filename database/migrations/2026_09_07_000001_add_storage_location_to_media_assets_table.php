<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_assets', function (Blueprint $table) {
            $table->string('disk', 64)->nullable()->after('url');
            $table->string('path', 1024)->nullable()->after('disk');
            $table->index('disk');
        });
    }

    public function down(): void
    {
        Schema::table('media_assets', function (Blueprint $table) {
            $table->dropIndex(['disk']);
            $table->dropColumn(['disk', 'path']);
        });
    }
};
