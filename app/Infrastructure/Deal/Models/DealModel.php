<?php

declare(strict_types=1);

namespace App\Infrastructure\Deal\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Eloquent модель для сделок (Infrastructure слой)
 * 
 * Содержит ТОЛЬКО:
 * - Настройки БД (fillable, casts, table)
 * - Отношения Eloquent
 * - Работу с медиа-файлами
 * - UUID поддержка (из старой модели)
 * 
 * НЕ содержит бизнес-логику! Это делает Domain Entity.
 */
final class DealModel extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'deals';

    protected $fillable = [
        'ad_id',
        'buyer_id', 
        'seller_id',
        'price',
        'status',
        'notes',
        'on_chain_id',
        'uuid',
        'created_at',
        'signed_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'created_at' => 'datetime',
        'signed_at' => 'datetime',
    ];

    // === ОТНОШЕНИЯ ELOQUENT ===

    public function ad(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Ad::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'seller_id');
    }

    // === МЕДИА-КОЛЛЕКЦИИ ===

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('documents')
            ->useDisk('public')
            ->acceptsMimeTypes([
                'application/pdf', 
                'application/msword', 
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/png'
            ]);
    }

    // === ТОЛЬКО ТЕХНИЧЕСКИЕ МЕТОДЫ ===
    // НЕТ бизнес-логики типа canBeCompleted(), confirm() и т.д.
    // Вся бизнес-логика в Domain/Deal/Entities/Deal.php
}
