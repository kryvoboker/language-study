<?php

namespace httpdocs\backend\app\Providers;

use httpdocs\backend\app\Domain\Ai\AiProviderManager;
use httpdocs\backend\app\Domain\Ai\Providers\OpenAiProvider;
use Illuminate\Support\ServiceProvider;

class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AiProviderManager::class, function ($app): AiProviderManager {
            return new AiProviderManager([
                'openai' => $app->make(OpenAiProvider::class),
            ]);
        });
    }
}