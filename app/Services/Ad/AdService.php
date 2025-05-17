<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\Services\Ad;

use App\DTO\Ads\AdData;
use App\Models\Ad;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class AdService
{
    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function create(User $user, AdData $data): Ad
    {
        $ad = $user->ads()->create([
            'title'       => $data->title,
            'description' => $data->description,
            'price'       => $data->price,
            'slug'        => Str::slug($data->title) . '-' . uniqid('', true),
            'status'      => $data->status,
        ]);

        $this->saveImages($ad, $data->images);

        return $ad;
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function update(Ad $ad, AdData $data): Ad
    {
        $ad->update([
            'title'       => $data->title,
            'description' => $data->description,
            'price'       => $data->price,
            'status'      => $data->status,
        ]);

        if ($data->images) {
            $ad->clearMediaCollection('images');
            $this->saveImages($ad, $data->images);
        }

        return $ad;
    }

    public function delete(Ad $ad): void
    {
        $ad->clearMediaCollection('images')->delete(); // Если нужно, можно добавить удаление медиа и прочее
    }

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    protected function saveImages(Ad $ad, ?array $images): void
    {
        if (! $images) {
            return;
        }

        foreach ($images as $file) {
            $ad->addMedia($file)
                ->usingFileName(uniqid('', true) . '.' . $file->extension())
                ->toMediaCollection('images');
        }
    }
}
