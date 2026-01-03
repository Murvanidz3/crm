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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Vehicle Information
            $table->string('vin', 17)->index();
            $table->string('make_model', 100);
            $table->year('year')->nullable();
            $table->string('lot_number', 50)->nullable()->index();
            $table->string('auction_name', 50)->nullable();
            $table->string('auction_location', 100)->nullable();
            $table->string('container_number', 50)->nullable()->index();
            
            // Status & Workflow
            $table->enum('status', [
                'purchased', 'warehouse', 'loaded', 
                'on_way', 'poti', 'green', 'delivered'
            ])->default('purchased')->index();
            
            // Financial Data
            $table->decimal('vehicle_cost', 12, 2)->default(0);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('additional_cost', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->date('purchase_date')->nullable();
            
            // Client Information (denormalized for quick access)
            $table->string('client_name', 100)->nullable();
            $table->string('client_phone', 20)->nullable();
            $table->string('client_id_number', 20)->nullable();
            
            // Media
            $table->string('main_photo')->nullable();
            
            $table->timestamps();

            // Composite indexes for common queries
            $table->index(['user_id', 'status']);
            $table->index(['client_user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
