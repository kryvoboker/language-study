<?php

declare(strict_types=1);

namespace Tests\Feature\Ai;

use App\Domain\Ai\AiProviderManager;
use App\Domain\Ai\AiProviderResolver;
use App\Domain\Ai\Providers\OpenAiProvider;
use App\Models\AiProviderSetting;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiProviderResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_provider_override_has_priority_over_global_default(): void
    {
        $default = AiProviderSetting::query()->create($this->configuration('default', true));
        $override = AiProviderSetting::query()->create($this->configuration('override', false));
        $user = User::factory()->create(['ai_provider_id' => $override->id]);

        $this->assertSame($override->id, app(AiProviderResolver::class)->resolveFor($user)->id);
        $this->assertTrue($default->fresh()->is_default);
    }

    public function test_resolver_uses_enabled_default_and_manager_registers_openai(): void
    {
        $default = AiProviderSetting::query()->create($this->configuration('default', true));

        $this->assertSame($default->id, app(AiProviderResolver::class)->resolveFor(User::factory()->create())->id);
        $this->assertInstanceOf(OpenAiProvider::class, app(AiProviderManager::class)->driver('openai'));
    }

    /** @return array{key: string, name: string, enabled: bool, is_default: bool, configuration: array{api_key: string, model: string}} */
    private function configuration(string $key, bool $isDefault): array
    {
        return [
            'key' => $key,
            'name' => ucfirst($key),
            'enabled' => true,
            'is_default' => $isDefault,
            'configuration' => ['api_key' => 'test-key', 'model' => 'test-model'],
        ];
    }
}
