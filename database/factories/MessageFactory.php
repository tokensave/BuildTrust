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
        return [
            'thread_id' => Thread::factory(),
            'author_id' => User::factory(),
            'content' => $this->faker->realText($this->faker->numberBetween(50, 300)),
            'read_at' => $this->faker->optional(0.6)->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
