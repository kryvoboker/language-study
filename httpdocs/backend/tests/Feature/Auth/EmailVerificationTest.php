<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Users\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_endpoints_are_not_available(): void
    {
        $this->get('/email/verify/1/hash')->assertNotFound();
        $this->postJson('/api/v1/auth/email/verification-notification')->assertNotFound();
    }

    public function test_active_admin_can_access_panel_without_email_verification(): void
    {
        $this->seed();

        $user = User::factory()->create([
            'email_verified_at' => null,
            'is_active' => true,
            'is_blocked' => false,
        ]);
        $user->assignRole('admin');

        $this->assertTrue($user->canAccessPanel(Mockery::mock(Panel::class)));
    }

    public function test_user_can_sign_in_without_email_verification(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password-password',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user, 'web');
    }
}
