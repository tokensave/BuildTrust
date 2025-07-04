<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\Services\Media;

use App\Models\Ad;
use App\Models\Deal;
use App\Models\User;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class CustomPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        $model = $media->model;

        // Аватары пользователей
        if ($model instanceof User && $media->collection_name === 'avatars') {
            return "avatars/{$model->id}/";
        }

        // Картинки объявлений
        if ($model instanceof Ad && $media->collection_name === 'images') {
            // UUID и user_id должны быть доступны в модели
            return "ads/{$model->user_id}/{$model->slug}/";
        }

        // Документы сделок
        if ($model instanceof Deal && $media->collection_name === 'documents') {
            return "deals/{$model->uuid}/";
        }

        // Fallback для остальных случаев
        return "{$media->id}/";
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media) . 'conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media) . 'responsive/';
    }
}
