<?php

declare(strict_types=1);

namespace App\Filament\Resources\AiProviders\Pages;

use App\Filament\Resources\AiProviders\AiProviderSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAiProviderSettings extends ListRecords
{
    protected static string $resource = AiProviderSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}