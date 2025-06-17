<?php

namespace App\Http\Controllers;

use App\DTO\Message\StoreMessageData;
use App\DTO\Thread\StoreThreadData;
use App\Events\MessageSent;
use App\Http\Requests\Message\StoreMessageRequest;
use App\Models\Ad;
use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use App\Services\Chat\MessageService;
use App\Services\Chat\ThreadService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function index(User $user): Response
    {
        $threads = Thread::forUser(auth()->id())
            ->with([
                'participants:id,username,email',
                'latestMessage.author:id,username',
                'ad:id,title,user_id',
                'ad.media',
            ])
            ->orderByDesc('updated_at')
            ->get();

        return Inertia::render('chat/Index', [
            'threads' => $threads,
        ]);
    }

    public function show(Thread $thread, MessageService $messageService): Response
    {
        $messageService->markAsRead($thread, auth()->id());

        $thread->load([
            'participants:id,username,email',
            'messages' => fn ($query) => $query->with('author')->latest()->take(50),
            'ad:id,title,user_id',
            'ad.media',
        ]);

        return Inertia::render('chat/Show', ['thread' => $thread]);
    }

    public function storeMessage(StoreMessageRequest $request, Ad $ad, User $recipient, ThreadService $threadService, MessageService $messageService): RedirectResponse
    {
        $messageData = StoreMessageData::fromRequest($request);
        $threadData = StoreThreadData::fromRequest($request);

        $thread = $threadService->getOrCreateThread($threadData);

        $messageService->storeMessage($thread, $messageData);

        return to_route('chats.show', ['thread' => $thread])->with('success', 'Сообщение отправлено!');
    }

    public function deleteThread(Thread $thread): RedirectResponse
    {
        $thread->delete();
        return to_route('chats.index')->with('success', 'Чат удален!');
    }

    public function deleteMessage(Message $message): RedirectResponse
    {
        if ($message->author_id !== auth()->id()) {
            abort(403, 'Вы не можете удалить это сообщение');
        }
        $thread = $message->thread;

        $message->delete();

        return to_route('chats.show', ['thread' => $thread]);
    }

}
