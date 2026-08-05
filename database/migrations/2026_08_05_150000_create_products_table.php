<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Название товара');
            $table->decimal('price', 10, 2)->comment('Цена товара');
            $table->string('category')->nullable()->comment('Категория (спортпит, вода)');
            $table->boolean('is_active')->default(true)->comment('Активен ли для продажи');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
