<?php

declare(strict_types=1);

namespace App\Filament\Navigation;

use Filament\Support\Contracts\HasLabel;

enum AdminNavigationGroupEnum: string implements HasLabel
{
    case Users = 'users';
    case AiProviders = 'ai_providers';

    public function getLabel(): string
    {
        return match ($this) {
            self::Users => __('admin/default.menu.item_users'),
            self::AiProviders => __('admin/default.menu.item_ai_providers'),
        };
    }
}
