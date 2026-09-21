<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Users\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Event::fake([Registered::class]);
    }

    public function test_user_can_register_without_an_avatar_and_sign_in_without_email_verification(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Study User',
            'email' => 'study-user@example.test',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
        ]);

        $response->assertCreated()->assertJsonPath('message', 'Account created. You can now sign in.');

        $user = User::query()->where('email', 'study-user@example.test')->firstOrFail();
        $this->assertSame('Study User', $user->name);
        $this->assertNull($user->avatar);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue($user->hasRole('user'));
        Event::assertDispatched(Registered::class);
    }

    public function test_user_can_register_with_an_avatar_stored_under_the_resolved_configured_path(): void
    {
        Storage::fake('public');
        config(['app.user_dir' => 'storage/app/public/user/email/{year}/{month}']);

        $response = $this->post('/api/v1/auth/register', [
            'name' => 'Avatar User',
            'email' => 'avatar-user@example.test',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
            'avatar' => UploadedFile::fake()->image('avatar.png'),
        ], ['Accept' => 'application/json']);

        $response->assertCreated();

        $user = User::query()->where('email', 'avatar-user@example.test')->firstOrFail();
        $expected_directory = 'user/email/' . now()->format('Y/m') . '/images/avatar/';

        $this->assertIsString($user->avatar);
        $this->assertStringStartsWith($expected_directory, $user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
    }

    public function test_registration_requires_name_email_password_and_matching_confirmation(): void
    {
        $this->postJson('/api/v1/auth/register', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password', 'password_confirmation']);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Study User',
            'email' => 'study-user@example.test',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Study User',
            'email' => 'study-user@example.test',
            'password' => 'secure-password',
            'password_confirmation' => 'different-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password_confirmation']);
    }

    public function test_registration_rejects_duplicate_email_and_non_image_avatar(): void
    {
        User::factory()->create(['email' => 'existing@example.test']);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Duplicate User',
            'email' => 'existing@example.test',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->post('/api/v1/auth/register', [
            'name' => 'Invalid Avatar User',
            'email' => 'invalid-avatar@example.test',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
            'avatar' => UploadedFile::fake()->create('not-an-image.txt', 1, 'text/plain'),
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_registration_accepts_six_to_thirty_two_character_passwords_with_special_characters(): void
    {
        foreach ([
            'a!@#$_',
            str_repeat('!', 31) . 'a',
        ] as $index => $password) {
            $email = 'password-boundary-' . $index . '@example.test';

            $this->postJson('/api/v1/auth/register', [
                'name' => 'Password Boundary User',
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
            ])->assertCreated();

            $this->assertDatabaseHas('users', ['email' => $email]);
        }
    }

    public function test_registration_rejects_passwords_longer_than_thirty_two_characters(): void
    {
        $password = str_repeat('a', 33);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Long Password User',
            'email' => 'long-password@example.test',
            'password' => $password,
            'password_confirmation' => $password,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_rejects_webp_avatars(): void
    {
        $this->post('/api/v1/auth/register', [
            'name' => 'WebP Avatar User',
            'email' => 'webp-avatar@example.test',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
            'avatar' => UploadedFile::fake()->create('avatar.webp', 10, 'image/webp'),
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['avatar']);
    }
}
