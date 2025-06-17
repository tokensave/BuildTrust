<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;
    public function definition(): array
    {
        return [
            'inn' => 1231231231,
            'name' => $this->faker->company,
        ];
    }
}
