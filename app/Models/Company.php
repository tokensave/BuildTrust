<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'inn',
        'name',
        'email',
        'phone',
        'city',
        'address',
        'website',
        'verified',
        'ai_description',
        'ai_analysis',
        'ai_last_check',
        'ai_status',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'ai_analysis' => 'array',
        'ai_last_check' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all users belonging to this company.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Проверяет, нуждается ли компания в обновлении AI анализа
     */
    public function needsAiUpdate(): bool
    {
        if ($this->ai_status === 'failed' || $this->ai_status === 'pending') {
            return true;
        }

        if (!$this->ai_last_check) {
            return true;
        }

        // Обновляем, если последняя проверка была больше суток назад
        return $this->ai_last_check->lt(now()->subDay());
    }

    /**
     * Маркирует AI анализ как начатый
     */
    public function markAiAnalysisAsStarted(): void
    {
        $this->update([
            'ai_status' => 'processing',
            'ai_last_check' => now()
        ]);
    }

    /**
     * Маркирует AI анализ как завершенный
     */
    public function markAiAnalysisAsCompleted(array $analysisData): void
    {
        $this->update([
            'ai_analysis' => $analysisData,
            'ai_description' => $analysisData['summary'] ?? null,
            'ai_status' => 'completed',
            'ai_last_check' => now()
        ]);
    }

    /**
     * Маркирует AI анализ как неудавшийся
     */
    public function markAiAnalysisAsFailed(string $error = null): void
    {
        $analysisData = $this->ai_analysis ?? [];
        $analysisData['error'] = $error;
        
        $this->update([
            'ai_analysis' => $analysisData,
            'ai_status' => 'failed',
            'ai_last_check' => now()
        ]);
    }

    /**
     * Скоп для компаний, которым нужен AI анализ
     */
    public function scopeNeedsAiUpdate($query)
    {
        return $query->where(function ($query) {
            $query->where('ai_status', 'pending')
                  ->orWhere('ai_status', 'failed')
                  ->orWhereNull('ai_last_check')
                  ->orWhere('ai_last_check', '<', now()->subDay());
        });
    }

    /**
     * Получить уровень риска компании на основе AI анализа
     */
    public function getRiskLevelAttribute(): ?string
    {
        if (!$this->ai_analysis || $this->ai_status !== 'completed') {
            return null;
        }

        return $this->ai_analysis['risk_level'] ?? 'medium';
    }

    /**
     * Получить AI рекомендации
     */
    public function getAiRecommendationsAttribute(): ?string
    {
        if (!$this->ai_analysis || $this->ai_status !== 'completed') {
            return null;
        }

        return $this->ai_analysis['recommendations'] ?? null;
    }
}
