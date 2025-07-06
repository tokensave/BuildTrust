<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\Services\Media;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class MediaService
{
    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function addMediaFiles(HasMedia&Model $model, array $files, string $collection, ?array $mimeTypes = null): void
    {
        foreach ($files as $file) {
            if ($mimeTypes && !in_array($file->getMimeType(), $mimeTypes, true)) {
                continue;
            }
            $model->addMedia($file)
                ->usingFileName(uniqid('', true) . '.' . $file->extension())
                ->toMediaCollection($collection);
        }
    }

    public function removeMediaByIds(HasMedia&Model $model, array $ids): void
    {
        $model->media()->whereIn('id', $ids)->each(fn ($media) => $media->delete());
    }

    public function getMediaUrls(HasMedia&Model $model, string $collection, string $fallback = ''): array
    {
        $media = $model->getMedia($collection);
        if ($media->isEmpty() && $fallback) {
            return [$fallback];
        }
        return $media->map(fn ($m) => $m->getUrl())->toArray();
    }
}
