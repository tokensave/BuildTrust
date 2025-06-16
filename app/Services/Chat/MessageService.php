<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\Services\Chat;

use App\DTO\Message\StoreMessageData;
use App\Models\Message;
use App\Models\Thread;

class MessageService
{
    public function storeMessage(Thread $thread, StoreMessageData $message): Message
    {
        return Message::create([
            'thread_id' => $thread->id,
            'author_id' => $message->author_id,
            'content' => $message->content,
        ]);
    }

    public function markAsRead(Thread $thread, int $userId): void
    {
        $thread->messages()
            ->where('author_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
