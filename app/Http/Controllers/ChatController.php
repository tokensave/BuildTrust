<?php

namespace App\Http\Controllers;

use App\DTO\Message\StoreMessageData;
use App\DTO\Thread\StoreThreadData;
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
    public function index(User $user, ThreadService $service): Response
    {
        $threads = $service->getThreadsForUser();
        return Inertia::render('chat/Index', ['threads' => $threads]);
    }

    public function show(Thread $thread, MessageService $messageService, ThreadService $threadService): Response
    {
        $messageService->markAsRead($thread, auth()->id());
        $thread = $threadService->loadThreadWithRelations($thread);
        return Inertia::render('chat/Show', ['thread' => $thread]);
    }

    public function storeMessage(StoreMessageRequest $request, Ad $ad, User $recipient, ThreadService $threadService, MessageService $messageService): RedirectResponse
    {
        $thread = $threadService->getOrCreateThread(StoreThreadData::fromRequest($request));
        $messageService->storeMessage($thread, StoreMessageData::fromRequest($request));
        return to_route('chats.show', ['thread' => $thread])->with('success', 'Сообщение отправлено!');
    }

    public function deleteThread(Thread $thread, ThreadService $service): RedirectResponse
    {
        $service->deleteThread($thread);
        return to_route('chats.index')->with('success', 'Чат удален!');
    }

    public function deleteMessage(Message $message, MessageService $service): RedirectResponse
    {
        $thread = $message->thread;
        $service->deleteMessage($message, auth()->id());
        return to_route('chats.show', ['thread' => $thread]);
    }

}
