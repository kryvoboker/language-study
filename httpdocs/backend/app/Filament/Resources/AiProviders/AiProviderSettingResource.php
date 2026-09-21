<?php

declare(strict_types=1);

namespace App\Filament\Resources\AiProviders;

use App\Filament\Navigation\AdminNavigationGroupEnum;
use App\Filament\Resources\AiProviders\Pages\CreateAiProviderSetting;
use App\Filament\Resources\AiProviders\Pages\EditAiProviderSetting;
use App\Filament\Resources\AiProviders\Pages\ListAiProviderSettings;
use App\Filament\Resources\AiProviders\Schemas\AiProviderSettingForm;
use App\Filament\Resources\AiProviders\Tables\AiProviderSettingTable;
use App\Models\AiProviderSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AiProviderSettingResource extends Resource
{
    protected static ?string                $model = AiProviderSetting::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;
    protected static string|null|UnitEnum   $navigationGroup = AdminNavigationGroupEnum::AiProviders;

    public static function form(Schema $schema): Schema
    {
        return AiProviderSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AiProviderSettingTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAiProviderSettings::route('/'),
            'create' => CreateAiProviderSetting::route('/create'),
            'edit' => EditAiProviderSetting::route('/{record}/edit'),
        ];
    }

    /**
     * Signature in the navigation menu (left panel)
     */
    public static function getNavigationLabel(): string
    {
        return __('admin/ai-providers/provider-settings.navigation_label');
    }

    /**
     * A single model name (e.g. in headings, "Create X" button)
     */
    public static function getModelLabel(): string
    {
        return __('admin/ai-providers/provider-settings.labels.model');
    }

    /**
     * Plural model name (e.g. in lists, section headings)
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin/ai-providers/provider-settings.labels.plural_model');
    }
}
