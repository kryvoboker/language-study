<?php

declare(strict_types=1);

namespace App\Domain\Ai;

use App\Models\AiProviderSetting;
use App\Models\Users\User;
use RuntimeException;

final class AiProviderResolver
{
    public function resolveFor(User $user): AiProviderSetting
    {
        if ($user->ai_provider_id !== null) {
            $override = AiProviderSetting::query()->whereKey($user->ai_provider_id)->where('enabled', true)->first();
            if ($override !== null) {
                return $override;
            }
        }

        return AiProviderSetting::query()->where('enabled', true)->orderByDesc('is_default')->first()
            ?? throw new RuntimeException('No enabled AI provider is configured.');
    }
}