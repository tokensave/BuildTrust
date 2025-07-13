<?php

declare(strict_types=1);

namespace App\Services\Ad;

use App\DTO\Ad\GetAdData;
use App\DTO\Ad\ShowAdData;
use App\DTO\Ad\StoreAdData;
use App\DTO\Ad\UpdateAdData;
use App\Enums\AdEnums\AdsStatusEnum;
use App\Models\Ad;
use App\Models\User;
use App\Services\Media\MediaService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class AdService
{
    public function __construct(private MediaService $mediaService) {}
    /**
     * @return Collection
     */
    public function getPublishedAds(): Collection
    {
        $ads = Ad::with('media')
            ->where('status', AdsStatusEnum::PUBLISHED->value)
            ->orderBy('created_at', 'desc')
            ->get();

        return GetAdData::collect($ads);
    }
    /**
     * Получить список объявлений пользователя с медиа и привести к DTO
     * @param User $user
     * @return Collection|GetAdData[]
     */
    public function getUserAds(User $user): array|Collection
    {
        return $user->ads()
            ->with('media')
            ->get()
            ->map(fn ($ad) => GetAdData::fromModel($ad));
    }
    /**
     * @param int $id
     * @return ShowAdData
     */
    public function findByIdWithCompany(int $id): ShowAdData
    {
        return $this->findById($id, ['media', 'user.company']);
    }

    /**
     * @param int $id
     * @param array $relations
     * @return ShowAdData
     */
    public function findById(int $id, array $relations = ['media']): ShowAdData
    {
        return ShowAdData::fromModel(Ad::with($relations)->findOrFail($id));
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function create(User $user, StoreAdData $data): Ad
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
    public function update(Ad $ad, UpdateAdData $data): Ad
    {
        $ad->update([
            'title'       => $data->title,
            'description' => $data->description,
            'price'       => $data->price,
            'status'      => $data->status,
        ]);

        // Удаляем только указанные изображения
        if (!empty($data->deletedMediaIds)) {
            $ad->media()
                ->whereIn('id', $data->deletedMediaIds)
                ->each(fn ($media) => $media->delete());
        }

        // Добавляем новые изображения
        if (!empty($data->newImages)) {
            foreach ($data->newImages as $file) {
                $this->saveImages($ad, $file);
            }
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
        $this->mediaService->addMediaFiles(
            $ad,
            $images, // массив файлов
            'images',
            config('media.mime_types.ad_images')
        );
    }
}
