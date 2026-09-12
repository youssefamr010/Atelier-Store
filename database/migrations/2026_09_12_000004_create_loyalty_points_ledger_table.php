<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('loyalty_points_ledger')) {
            Schema::create('loyalty_points_ledger', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->integer('points'); // positive for earn/manual, negative for redeem/refund
                $table->string('type', 50); // earn, redeem, manual_adjust, refund
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->string('notes', 255)->nullable();
                $table->timestamps();

                $table->index(['user_id', 'created_at']);
            });
        }
    }

    public function down(): void {
        Schema::dropIfExists('loyalty_points_ledger');
    }
};
