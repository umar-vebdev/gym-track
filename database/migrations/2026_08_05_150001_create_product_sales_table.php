<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(1)->comment('Количество товара');
            $table->decimal('total_price', 10, 2)->comment('Общая стоимость (шт * цена)');
            $table->string('payment_method')->nullable()->comment('Способ оплаты');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_sales');
    }
};
