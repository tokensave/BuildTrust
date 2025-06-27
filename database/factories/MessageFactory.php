<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;
    public function definition(): array
    {
        $thread = Thread::factory()->create();
        $author = User::factory()->create();

        return [
            'thread_id' => $thread->id,
            'author_id' => $author->id,
            'content' => $this->faker->words(10),
        ];
    }
}
