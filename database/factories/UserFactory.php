<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = \App\Models\User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // نفس الباسوورد لكل المستخدمين التجريبيين
            'remember_token' => Str::random(10),
            'role' => 'student',
            'avatar' => null,
            'is_active' => true,
            'last_login_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'timezone' => 'Asia/Riyadh',
        ];
    }

    // حالة جاهزة للمدرّسين لو احتجتيها لاحقاً
    public function instructor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'instructor',
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }
}
