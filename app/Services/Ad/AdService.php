<?php

declare(strict_types=1);

namespace App\Services\Ad;

use App\DTO\Ad\GetAdData;
use App\DTO\Ad\ShowAdData;
use App\DTO\Ad\StoreAdData;
use App\DTO\Ad\UpdateAdData;
use App\Enums\AdEnums\AdsStatusEnum;
use App\Enums\DealEnums\DealStatusEnum;
use App\Models\Ad;
use App\Models\User;
use App\Services\Media\MediaService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Throwable;

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
     * @throws Throwable
     */
    public function create(User $user, StoreAdData $data): Ad
    {
        return DB::transaction(function () use ($user, $data) {
            $ad = $user->ads()->create([
                'title'       => $data->title,
                'description' => $data->description,
                'price'       => $data->price,
                'slug'        => Str::slug($data->title) . '-' . uniqid('', true),
                'status'      => $data->status,
            ]);

            if (!empty($data->images)) {
                $this->saveImages($ad, $data->images);
            }

            return $ad;
        });
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws Throwable
     */
    public function update(Ad $ad, UpdateAdData $data): Ad
    {
        DB::transaction(function () use ($ad, $data) {
            $ad->update([
                'title'       => $data->title,
                'description' => $data->description,
                'price'       => $data->price,
                'status'      => $data->status,
            ]);

            // Удаляем только указанные изображения
            if (!empty($data->deletedMediaIds)) {
                $this->mediaService->removeMediaByIds($ad, $data->deletedMediaIds);
            }

            // Добавляем новые изображения
            if (!empty($data->newImages)) {
                foreach ($data->newImages as $file) {
                    $this->saveImages($ad, [$file]);
                }
            }
        });

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

    public function checkDeal(Ad $ad): bool
    {
        // Возвращает true, если есть хотя бы одна сделка не в статусе CANCELED
        return $ad->deals()
            ->where('status', '!=', DealStatusEnum::CANCELED->value)
            ->exists();
    }
}
