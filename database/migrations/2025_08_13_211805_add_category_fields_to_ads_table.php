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
        Schema::table('ads', function (Blueprint $table) {
            $table->string('type')->default('goods')->after('title'); // товары/услуги
            $table->string('category')->nullable()->after('type'); // основная категория
            $table->string('subcategory')->nullable()->after('category'); // подкатегория
            $table->string('location')->nullable()->after('subcategory'); // местоположение
            $table->boolean('is_urgent')->default(false)->after('location'); // срочное объявление
            $table->json('features')->nullable()->after('is_urgent'); // дополнительные характеристики
            
            // Индексы для быстрого поиска
            $table->index(['type', 'category']);
            $table->index(['status', 'type']);
            $table->index('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropIndex(['type', 'category']);
            $table->dropIndex(['status', 'type']);
            $table->dropIndex(['location']);
            
            $table->dropColumn([
                'type',
                'category', 
                'subcategory',
                'location',
                'is_urgent',
                'features'
            ]);
        });
    }
};
