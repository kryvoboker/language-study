<?php

declare(strict_types=1);

namespace App\Filament\Resources\AiProviders\Pages;

use App\Filament\Resources\AiProviders\AiProviderSettingResource;
use Filament\Resources\Pages\EditRecord;

class EditAiProviderSetting extends EditRecord
{
    protected static string $resource = AiProviderSettingResource::class;
}