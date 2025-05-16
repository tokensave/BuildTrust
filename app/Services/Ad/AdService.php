<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\Services\Ad;

use App\DTO\Ads\AdData;
use App\Models\Ads;
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
    public function create(User $user, AdData $data): Ads
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
    public function update(Ads $ad, AdData $data): Ads
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

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    protected function saveImages(Ads $ad, ?array $images): void
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
