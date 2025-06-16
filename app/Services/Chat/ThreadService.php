<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\Services\Chat;

use App\DTO\Thread\StoreThreadData;
use App\Models\Thread;

class ThreadService
{
    public function getOrCreateThread(StoreThreadData $data): Thread
    {
        // Поиск существующей темы для указанного объявления и участников
        $existingThread = Thread::hasUsers($data->ad_id, $data->author_id, $data->recipient_id)->first();

        if ($existingThread) {
            return $existingThread;
        }

        // Создание новой темы, если существующей нет
        $newThread = Thread::create(['ad_id' => $data->ad_id]);
        $newThread->participants()->attach([$data->author_id, $data->recipient_id], ['joined_at' => now()]);

        return $newThread;
    }

    public function addAdmin(Thread $thread, int $adminId): void
    {
        $thread->participants()->syncWithoutDetaching([$adminId]);
    }
}
