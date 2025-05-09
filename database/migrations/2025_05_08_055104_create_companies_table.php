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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('inn')->unique(); // ИНН, используется для проверки
            $table->string('name'); // Полное имя компании
            $table->string('email')->nullable(); // Почта компании (необязательно)
            $table->string('phone')->nullable(); // Телефон компании
            $table->string('city')->nullable(); // Город/регион
            $table->string('address')->nullable(); // Адрес
            $table->string('website')->nullable(); // Веб-сайт
            $table->boolean('verified')->default(false); // Проверена ли компания
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
