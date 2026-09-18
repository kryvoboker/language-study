<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Users\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_blocked_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create(['is_blocked' => true]);

        $this->assertFalse($user->canAccessPanel(Mockery::mock(Panel::class)));
    }

    public function test_inactive_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->assertFalse($user->canAccessPanel(Mockery::mock(Panel::class)));
    }

    public function test_active_unblocked_admin_can_access_admin_panel(): void
    {
        $this->seed();

        $user = User::factory()->create(['is_active' => true, 'is_blocked' => false]);
        $user->assignRole('admin');

        $this->assertTrue($user->canAccessPanel(Mockery::mock(Panel::class)));
    }
}
