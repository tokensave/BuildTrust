<?php

declare(strict_types=1);

namespace Tests\Feature\Chat;

use App\Models\Ad;
use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_chat_index(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Создадим несколько чатов для этого пользователя
        Thread::factory()->count(2)->create()->each(function ($thread) use ($user) {
            $thread->participants()->attach($user);
        });

        $response = $this->get(route('chats.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
        $page->component('chat/Index')
            ->has('threads')
        );
    }

    public function test_user_can_view_specific_thread(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $thread = Thread::factory()->create();
        $thread->participants()->attach($user);

        $response = $this->get(route('chats.show', ['thread' => $thread]));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
        $page->component('chat/Show')
            ->where('thread.id', $thread->id)
        );
    }

    public function test_user_can_store_message(): void
    {
        $user = User::factory()->create();
        $recipient = User::factory()->create();
        $ad = Ad::factory()->create();

        $this->actingAs($user);

        $data = [
            'content' => 'Привет, интересует объявление!',
        ];

        $response = $this->post(
            route('chats.messages.store', ['ad' => $ad->id, 'recipient' => $recipient->id]),
            $data
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('messages', [
            'content' => 'Привет, интересует объявление!',
        ]);
    }

    public function test_user_can_delete_own_message(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $thread = Thread::factory()->create();
        $thread->participants()->attach($user);

        $message = Message::factory()->create([
            'author_id' => $user->id,
            'thread_id' => $thread->id,
            'content' => 'Тестовое сообщение',
        ]);

        $response = $this->delete(route('chats.messages.delete', ['message' => $message->id]));

        $response->assertRedirect();
        $this->assertSoftDeleted('messages', [
            'id' => $message->id,
        ]);
    }

    public function test_user_cannot_delete_someone_elses_message(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->actingAs($user);

        $thread = Thread::factory()->create();
        $thread->participants()->attach([$user->id, $otherUser->id]);

        $message = Message::factory()->create([
            'author_id' => $otherUser->id,
            'thread_id' => $thread->id,
            'content' => 'Тестовое сообщение другого пользователя',
        ]);

        $response = $this->delete(route('chats.messages.delete', ['message' => $message->id]));

        $response->assertForbidden();
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
        ]);
    }

    public function test_user_can_delete_thread(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $thread = Thread::factory()->create();
        $thread->participants()->attach($user);

        $response = $this->delete(route('chats.delete-thread', ['thread' => $thread->id]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('threads', [
            'id' => $thread->id,
        ]);
    }
}
