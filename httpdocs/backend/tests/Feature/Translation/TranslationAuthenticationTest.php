<?php

declare(strict_types=1);

namespace Tests\Feature\Translation;

use App\Enums\TranslationStatus;
use App\Jobs\StartTranslationJob;
use App\Models\AiProviderSetting;
use App\Models\TranslationRequest;
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
        $this->assertDatabaseHas('translation_requests', ['locale' => 'ru']);
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

    public function test_source_text_cannot_exceed_the_configured_provider_limit(): void
    {
        $user = User::factory()->create();
        $this->enableProvider(['max_input_characters' => 5]);
        Passport::actingAs($user, ['translate']);

        $this->postJson('/api/v1/translation-requests', [
            ...$this->payload(),
            'source_text' => '123456',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['source_text']);
    }

    public function test_authenticated_user_receives_the_configured_provider_limit(): void
    {
        $user = User::factory()->create();
        $this->enableProvider(['max_input_characters' => 5]);
        Passport::actingAs($user, ['profile']);

        $this->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('max_input_characters', 5);
    }

    public function test_translation_locale_must_be_supported(): void
    {
        $user = User::factory()->create();
        $this->enableProvider();
        Passport::actingAs($user, ['translate']);

        $this->postJson('/api/v1/translation-requests', [
            ...$this->payload(),
            'locale' => 'de',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['locale']);
    }

    public function test_completed_translation_is_reused_for_normalized_request_data(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $this->enableProvider();
        TranslationRequest::query()->create([
            'user_id' => User::factory()->create()->id,
            'source_text' => 'Hello',
            'source_language' => 'en',
            'target_language' => 'uk',
            'request_hash' => TranslationRequest::generateRequestHash(' Hello ', 'EN', 'uk'),
            'locale' => 'en',
            'status' => TranslationStatus::Completed,
            'result' => ['translation' => 'Привіт'],
            'completed_at' => now(),
        ]);
        Passport::actingAs($user, ['translate']);

        $this->postJson('/api/v1/translation-requests', [
            ...$this->payload(),
            'source_text' => '  HELLO  ',
            'source_language' => 'EN',
        ])
            ->assertOk()
            ->assertJsonPath('status', TranslationStatus::Completed->value)
            ->assertJsonPath('translation', 'Привіт');

        Queue::assertNothingPushed();
        $this->assertDatabaseHas('translation_requests', [
            'user_id' => $user->id,
            'request_hash' => TranslationRequest::generateRequestHash('Hello', 'en', 'uk'),
            'status' => TranslationStatus::Completed->value,
        ]);
    }

    /** @param array<string, int> $configuration */
    private function enableProvider(array $configuration = []): void
    {
        AiProviderSetting::query()->create([
            'key' => 'openai',
            'name' => 'OpenAI',
            'enabled' => true,
            'is_default' => true,
            'configuration' => ['api_key' => 'test-key', 'model' => 'gpt-5.6', ...$configuration],
        ]);
    }

    /** @return array{source_text: string, source_language: string, target_language: string} */
    private function payload(): array
    {
        return [
            'source_text' => 'Hello',
            'source_language' => 'en',
            'target_language' => 'uk',
            'locale' => 'ru',
        ];
    }
}
