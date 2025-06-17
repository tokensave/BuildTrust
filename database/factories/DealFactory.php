<?php

namespace Database\Factories;

use App\Enums\DealEnums\DealStatusEnum;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;


class DealFactory extends Factory
{
    protected $model = Deal::class;
    public function definition(): array
    {
        return [
            'buyer_id' => User::factory()->create(),
            'seller_id' => User::factory()->create(),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'status' => $this->faker->randomElement([DealStatusEnum::cases()]),
            'notes' => $this->faker->realText(),
        ];
    }
}
