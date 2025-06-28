<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\Services\Chat;

use App\DTO\Message\StoreMessageData;
use App\Models\Message;
use App\Models\Thread;

/**
 *
 */
class MessageService
{
    /**
     * @param Thread $thread
     * @param StoreMessageData $message
     * @return Message
     */
    public function storeMessage(Thread $thread, StoreMessageData $message): Message
    {
        return Message::create([
            'thread_id' => $thread->id,
            'author_id' => $message->author_id,
            'content' => $message->content,
        ]);
    }

    /**
     * @param Thread $thread
     * @param int $userId
     * @return void
     */
    public function markAsRead(Thread $thread, int $userId): void
    {
        $thread->messages()
            ->where('author_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * @param Message $message
     * @param int $userId
     * @return void
     */
    public function deleteMessage(Message $message, int $userId): void
    {
        if ($message->author_id !== $userId) {
            abort(403, 'Вы не можете удалить это сообщение');
        }
        $message->delete();
    }
}
