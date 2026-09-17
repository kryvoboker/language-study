<?php

declare(strict_types=1);

namespace App\Filament\Resources\AiProviders;

use App\Filament\Navigation\AdminNavigationGroupEnum;
use App\Filament\Resources\AiProviders\Pages\CreateAiProviderSetting;
use App\Filament\Resources\AiProviders\Pages\EditAiProviderSetting;
use App\Filament\Resources\AiProviders\Pages\ListAiProviderSettings;
use App\Models\AiProviderSetting;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class AiProviderSettingResource extends Resource
{
    protected static ?string                $model = AiProviderSetting::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;
    protected static string|null|UnitEnum   $navigationGroup = AdminNavigationGroupEnum::AiProviders;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required(),
            Textarea::make('prompt_instruction')
                ->label('Prompt instruction')
                ->helperText('Instructions sent to the AI assistant for this provider. This replaces the default system prompt.')
                ->required()
                ->rows(8)
                ->columnSpanFull(),
            TextInput::make('key')
                ->required()
                ->alphaDash()
                ->unique(ignoreRecord: true)
                ->disabledOn('edit')
                ->dehydrated(),
            Toggle::make('enabled'),
            Toggle::make('is_default'),
            KeyValue::make('configuration')
                ->keyLabel('Setting')
                ->valueLabel('Value')
                ->helperText('Encrypted at rest. Provider-specific typed pages can replace this generic editor as integrations are added.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns(
            [
                TextColumn::make('name'),
                TextColumn::make('key')->badge(),
                IconColumn::make('enabled')->boolean(),
                IconColumn::make('is_default')->boolean(),
            ],
        )
            ->recordActions([EditAction::make()]);
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
