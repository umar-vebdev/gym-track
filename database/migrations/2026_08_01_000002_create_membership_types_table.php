<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Таблица типов абонементов (тарифных планов).
     *
     * duration_type:
     * - 'days' (по дням, напр. 30 дней)
     * - 'visits' (по визитам, напр. 12 посещений)
     */
    public function up(): void
    {
        Schema::create('membership_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('duration_type', ['days', 'visits']);
            $table->unsignedInteger('duration_value');
            $table->decimal('price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_types');
    }
};
