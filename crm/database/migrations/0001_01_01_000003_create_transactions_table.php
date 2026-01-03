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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->datetime('payment_date');
            $table->enum('purpose', [
                'vehicle', 'shipping', 'balance_topup', 
                'internal_transfer', 'other'
            ])->default('other');
            $table->text('comment')->nullable();

            $table->index(['user_id', 'payment_date']);
            $table->index(['car_id', 'payment_date']);
            $table->index('purpose');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
