<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\Services\Chat;

use App\DTO\Thread\StoreThreadData;
use App\Models\Thread;
use Illuminate\Support\Collection;

class ThreadService
{
    /**
     * @return Collection
     */
    public function getThreadsForUser(): Collection
    {
        return Thread::forUser(auth()->id())
            ->with([
                'participants:id,username,email',
                'latestMessage.author:id,username',
                'ad:id,title,user_id',
                'ad.media',
            ])
            ->orderByDesc('updated_at')
            ->get();
    }

    /**
     * @param Thread $thread
     * @return Thread
     */
    public function loadThreadWithRelations(Thread $thread): Thread
    {
        return $thread->load([
            'participants:id,username,email',
            'messages' => fn ($query) => $query->with('author')->latest()->take(50),
            'ad:id,title,user_id',
            'ad.media',
        ]);
    }

    /**
     * @param StoreThreadData $data
     * @return Thread
     */
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

    /**
     * @param Thread $thread
     * @param int $adminId
     * @return void
     */
    public function addAdmin(Thread $thread, int $adminId): void
    {
        $thread->participants()->syncWithoutDetaching([$adminId]);
    }

    /**
     * @param Thread $thread
     * @return void
     */
    public function deleteThread(Thread $thread): void
    {
        $thread->delete();
    }
}
