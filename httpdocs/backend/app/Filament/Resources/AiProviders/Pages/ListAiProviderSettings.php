<?php
namespace httpdocs\backend\app\Filament\Resources\AiProviders\Pages;
use httpdocs\backend\app\Filament\Resources\AiProviders\AiProviderSettingResource;
use Filament\Resources\Pages\ListRecords;
class ListAiProviderSettings extends ListRecords { protected static string $resource = AiProviderSettingResource::class; }