<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Ai\AiProviderManager;
use App\Domain\Ai\Providers\OpenAiProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AiProviderManager::class, function (Application $app): AiProviderManager {
            return new AiProviderManager([
                'openai' => $app->make(OpenAiProvider::class),
            ]);
        });
    }
}
