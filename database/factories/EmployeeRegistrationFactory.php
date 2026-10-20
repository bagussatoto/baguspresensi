<?php

namespace Database\Factories;

use App\Models\EmployeeRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeRegistration>
 */
class EmployeeRegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'username' => fake()->unique()->userName(),
            'password' => bcrypt('password123'),
            'pin' => bcrypt('123456'),
            'status' => 'pending',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
        ];
    }
}
