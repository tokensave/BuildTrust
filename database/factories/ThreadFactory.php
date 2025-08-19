<?php

namespace Database\Factories;

use App\Models\Thread;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Thread>
 */
class ThreadFactory extends Factory
{
    protected $model = Thread::class;
    public function definition(): array
    {
        return [
            'ad_id' => \App\Models\Ad::factory(),
        ];
    }
}
