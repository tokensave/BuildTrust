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
            'inn' => $this->faker->numerify('##########'), // 10-значный ИНН
            'name' => $this->faker->company(),
            'email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'city' => $this->faker->city(),
            'address' => $this->faker->address(),
            'website' => $this->faker->optional(0.7)->url(), // 70% шанс иметь сайт
            'verified' => $this->faker->boolean(0.3), // 30% шанс быть верифицированным
            'ai_description' => $this->faker->optional(0.5)->sentence(20),
            'ai_analysis' => $this->faker->optional(0.4)->randomElement([
                [
                    'risk_level' => 'low',
                    'summary' => 'Надежная компания с хорошей репутацией',
                    'recommendations' => 'Рекомендована к сотрудничеству',
                    'score' => 85,
                ],
                [
                    'risk_level' => 'medium',
                    'summary' => 'Компания со средним уровнем риска',
                    'recommendations' => 'Требуется дополнительная проверка',
                    'score' => 65,
                ],
                [
                    'risk_level' => 'high',
                    'summary' => 'Высокий уровень риска',
                    'recommendations' => 'Не рекомендуется к сотрудничеству',
                    'score' => 35,
                ]
            ]),
            'ai_last_check' => $this->faker->optional(0.6)->dateTimeBetween('-1 month', 'now'),
            'ai_status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'failed']),
        ];
    }
}
