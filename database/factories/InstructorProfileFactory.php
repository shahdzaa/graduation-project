<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class InstructorProfileFactory extends Factory
{
    protected $model = \App\Models\InstructorProfile::class;

    public function definition(): array
    {
        return [
            // ملاحظة: user_id بينعبى من السييدر
            'bio' => fake()->paragraph(3),
            'specialization' => fake()->randomElement([
                'Machine Learning',
                'Web Development',
                'Data Science',
                'Cybersecurity',
                'Cloud Computing',
                'Mobile Development',
                'Artificial Intelligence',
                'Database Systems',
            ]),
            'linkedin_url' => 'https://linkedin.com/in/' . fake()->userName(),
            'years_experience' => fake()->numberBetween(1, 25),
            'website_url' => fake()->optional()->url(),
            'average_rating' => fake()->randomFloat(2, 3, 5),
        ];
    }
}
