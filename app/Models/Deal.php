<?php

namespace App\Models;

use App\Enums\DealEnums\DealStatusEnum;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Deal extends Model implements HasMedia
{
    use HasFactory, HasUuid, InteractsWithMedia;

    protected $fillable = [
        'ad_id',
        'buyer_id',
        'seller_id',
        'price',
        'status',
        'notes',
        'signed_at',
        'on_chain_id',
    ];

    protected $casts = [
        'status' => DealStatusEnum::class,
    ];

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('documents')
            ->useDisk('public') // или 's3', если потом будешь использовать AWS
            ->acceptsMimeTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
            ->useFallbackUrl('/documents/fallback.pdf'); // можно не указывать
    }

    public function getDocumentsUrlsAttribute(): array
    {
        return $this->getMedia('documents')->map(fn($media) => $media->getUrl())->toArray();
    }

}
