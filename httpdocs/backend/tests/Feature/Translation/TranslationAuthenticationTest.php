<?php

declare(strict_types=1);

namespace Tests\Feature\Translation;

use App\Jobs\StartTranslationJob;
use App\Models\AiProviderSetting;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Passport\Passport;
use Tests\TestCase;

class TranslationAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_passport_user_can_create_a_translation_request(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $this->enableProvider();
        Passport::actingAs($user, ['translate']);

        $this->postJson('/api/v1/translation-requests', $this->payload())
            ->assertAccepted();

        Queue::assertPushed(StartTranslationJob::class);
    }

    public function test_verified_filament_session_user_can_create_a_translation_request_with_csrf(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $this->enableProvider();

        $this->actingAs($user, 'web')
            ->withSession(['_token' => 'test-token'])
            ->withHeader('X-CSRF-TOKEN', 'test-token')
            ->postJson('/api/v1/translation-requests', $this->payload())
            ->assertAccepted();

        Queue::assertPushed(StartTranslationJob::class);
    }

    public function test_anonymous_user_cannot_create_a_translation_request(): void
    {
        $this->postJson('/api/v1/translation-requests', $this->payload())
            ->assertUnauthorized();
    }

    private function enableProvider(): void
    {
        AiProviderSetting::query()->create([
            'key' => 'openai',
            'name' => 'OpenAI',
            'enabled' => true,
            'is_default' => true,
            'configuration' => ['api_key' => 'test-key', 'model' => 'gpt-5.6'],
        ]);
    }

    /** @return array{source_text: string, source_language: string, target_language: string} */
    private function payload(): array
    {
        return [
            'source_text' => 'Hello',
            'source_language' => 'en',
            'target_language' => 'uk',
        ];
    }
}
