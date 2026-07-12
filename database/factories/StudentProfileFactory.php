<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StudentProfileFactory extends Factory
{
    protected $model = \App\Models\StudentProfile::class;

    public function definition(): array
    {
        return [
            // ملاحظة: user_id بتنعبى من السييدر (مش هون)
            'phone' => fake()->phoneNumber(),
            'github_url' => 'https://github.com/' . fake()->userName(),
            'country' => fake()->country(),
            'birth_date' => fake()->dateTimeBetween('-30 years', '-18 years')->format('Y-m-d'),
        ];
    }
}
