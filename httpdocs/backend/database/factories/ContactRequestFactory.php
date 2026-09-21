<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContactRequestStatus;
use App\Models\ContactRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactRequest>
 */
class ContactRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'sender_first_name' => fake()->firstName(),
            'sender_last_name' => fake()->lastName(),
            'sender_email' => fake()->safeEmail(),
            'sender_is_blocked' => false,
            'message' => fake()->paragraph(),
            'status' => ContactRequestStatus::Pending,
        ];
    }
}
