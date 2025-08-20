<?php

namespace App\Models;

use App\Enums\AdEnums\AdCategoryEnum;
use App\Enums\AdEnums\AdsStatusEnum;
use App\Enums\AdEnums\AdSubcategoryEnum;
use App\Enums\AdEnums\AdTypeEnum;
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
        'type',
        'category',
        'subcategory',
        'location',
        'description',
        'price',
        'slug',
        'status',
        'is_urgent',
        'features'
    ];

    protected $casts = [
        'price'  => 'decimal:2',
        'status' => AdsStatusEnum::class,
        'type' => AdTypeEnum::class,
        'category' => AdCategoryEnum::class,
        'subcategory' => AdSubcategoryEnum::class,
        'is_urgent' => 'boolean',
        'features' => 'array',
    ];

    protected $appends = ['image_url', 'formatted_category', 'is_service'];
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

    /**
     * Получить форматированную строку категории
     */
    public function getFormattedCategoryAttribute(): string
    {
        $parts = [];

        if ($this->type) {
            $parts[] = $this->type->label();
        }

        if ($this->category) {
            $parts[] = $this->category->label();
        }

        if ($this->subcategory) {
            $parts[] = $this->subcategory->label();
        }

        return implode(' → ', $parts);
    }

    /**
     * Проверить, является ли объявление услугой
     */
    public function getIsServiceAttribute(): bool
    {
        return $this->type === AdTypeEnum::SERVICES;
    }

    /**
     * Скоупы для фильтрации
     */
    public function scopeByType($query, AdTypeEnum $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCategory($query, AdCategoryEnum $category)
    {
        return $query->where('category', $category);
    }

    public function scopeBySubcategory($query, AdSubcategoryEnum $subcategory)
    {
        return $query->where('subcategory', $subcategory);
    }

    public function scopeByLocation($query, string $location)
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    public function scopeUrgent($query)
    {
        return $query->where('is_urgent', true);
    }

    public function scopeInPriceRange($query, ?float $minPrice = null, ?float $maxPrice = null)
    {
        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }

        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }

        return $query;
    }

    /**
     * Получить объявления с фильтрами
     */
    public static function filtered(array $filters = [])
    {
        $query = self::query()->where('status', AdsStatusEnum::PUBLISHED);

        if (!empty($filters['type'])) {
            $query->byType(AdTypeEnum::from($filters['type']));
        }

        if (!empty($filters['category'])) {
            $query->byCategory(AdCategoryEnum::from($filters['category']));
        }

        if (!empty($filters['subcategory'])) {
            $query->bySubcategory(AdSubcategoryEnum::from($filters['subcategory']));
        }

        if (!empty($filters['location'])) {
            $query->byLocation($filters['location']);
        }

        if (!empty($filters['urgent'])) {
            $query->urgent();
        }

        if (!empty($filters['min_price']) || !empty($filters['max_price'])) {
            $query->inPriceRange(
                !empty($filters['min_price']) ? (float) $filters['min_price'] : null,
                !empty($filters['max_price']) ? (float) $filters['max_price'] : null
            );
        }

        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $search = $filters['search'];
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['features'])) {
            $features = is_array($filters['features']) ? $filters['features'] : [$filters['features']];
            $query->where(function($q) use ($features) {
                foreach ($features as $feature) {
                    $q->whereJsonContains('features', $feature);
                }
            });
        }

        return $query;
    }
}
