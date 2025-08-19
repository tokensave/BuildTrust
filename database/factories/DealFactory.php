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
            'ad_id' => \App\Models\Ad::factory(),
            'buyer_id' => User::factory(),
            'seller_id' => User::factory(), 
            'price' => $this->faker->randomFloat(2, 1000, 100000),
            'status' => $this->faker->randomElement(DealStatusEnum::cases()),
            'notes' => $this->faker->optional(0.7)->realText(200),
            'signed_at' => $this->faker->optional(0.3)->dateTimeBetween('-1 month', 'now'),
            'on_chain_id' => $this->faker->optional(0.1)->uuid(), // Только 10% сделок на блокчейне
        ];
    }
}
