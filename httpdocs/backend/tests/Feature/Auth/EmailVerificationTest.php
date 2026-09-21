<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_verification_is_idempotent(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::signedRoute('verification.verify', ['id' => $user->getKey(), 'hash' => sha1($user->getEmailForVerification())]);

        $this->get($url)->assertRedirect();
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->get($url)->assertRedirect();
    }

    public function test_wrong_email_hash_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::signedRoute('verification.verify', ['id' => $user->getKey(), 'hash' => sha1('wrong@example.com')]);

        $this->get($url)->assertForbidden();
        $this->assertNull($user->fresh()->email_verified_at);
    }
}
