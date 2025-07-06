<?php

namespace App\Models;

use App\Enums\AdEnums\AdsStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Ad extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'slug',
        'status'
    ];

    protected $casts = [
        'price'  => 'decimal:2',
        'status' => AdsStatusEnum::class,
    ];

    protected $appends = ['image_url'];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }


    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('public')
            ->useFallbackUrl(config('media.fallbacks.ad_image'))
            ->acceptsMimeTypes(config('media.mime_types.ad_images'));
    }

    public function getImageUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('images') ?: asset('images/default-ad.png');
    }
}
