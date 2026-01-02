<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable();
            $table->string('name');
            $table->boolean('is_loose')->default(false);
            $table->decimal('stock_quantity', 10, 3)->default(0);
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->index('barcode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};