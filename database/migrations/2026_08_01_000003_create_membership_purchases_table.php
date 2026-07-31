<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Таблица проданных абонементов (покупок клиентами).
     */
    public function up(): void
    {
        Schema::create('membership_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('membership_type_id')->constrained('membership_types');
            $table->decimal('amount_paid', 10, 2);
            $table->date('starts_at');
            $table->date('expires_at')->nullable();
            $table->unsignedInteger('visits_left')->nullable();
            $table->string('payment_method')->nullable()->comment('cash, card, transfer');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_purchases');
    }
};
