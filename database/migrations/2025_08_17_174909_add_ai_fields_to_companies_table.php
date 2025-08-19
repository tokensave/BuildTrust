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
        Schema::table('companies', function (Blueprint $table) {
            $table->text('ai_description')->nullable()->comment('Краткое описание компании от AI');
            $table->json('ai_analysis')->nullable()->comment('Полный анализ компании от AI в JSON формате');
            $table->timestamp('ai_last_check')->nullable()->comment('Дата последней проверки AI');
            $table->enum('ai_status', ['pending', 'processing', 'completed', 'failed'])
                ->default('pending')
                ->comment('Статус AI анализа');
            
            $table->index(['ai_status', 'ai_last_check']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex(['ai_status', 'ai_last_check']);
            $table->dropColumn(['ai_description', 'ai_analysis', 'ai_last_check', 'ai_status']);
        });
    }
};
