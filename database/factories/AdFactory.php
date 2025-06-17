<?php

namespace Database\Factories;

use App\Models\Ad;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ad>
 */
class AdFactory extends Factory
{
    protected $model = Ad::class;
    public function definition(): array
    {
        return [
            'user_id' => User::first(),
            'title' => $this->faker->text(5),
            'description' => $this->faker->text(10),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'slug' => $this->faker->slug(),
        ];
    }
}
