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
        Schema::create('car_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->onDelete('cascade');
            $table->string('file_path');
            $table->enum('file_type', ['image', 'video', 'document'])->default('image');
            $table->enum('category', ['auction', 'port', 'terminal'])->default('auction');
            $table->timestamps();

            $table->index(['car_id', 'category']);
            $table->index('file_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_files');
    }
};
