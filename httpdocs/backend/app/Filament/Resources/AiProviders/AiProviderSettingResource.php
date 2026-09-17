<?php

declare(strict_types=1);

namespace App\Filament\Resources\AiProviders;

use App\Filament\Resources\AiProviders\Pages\EditAiProviderSetting;
use App\Filament\Resources\AiProviders\Pages\ListAiProviderSettings;
use App\Models\AiProviderSetting;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AiProviderSettingResource extends Resource
{
    protected static ?string $model = AiProviderSetting::class;
    protected static string|\UnitEnum|null $navigationGroup = 'AI Providers';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $modelLabel = 'Provider setting';
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required(), TextInput::make('key')->disabled()->dehydrated(), Toggle::make('enabled'), Toggle::make('is_default'),
            KeyValue::make('configuration')->keyLabel('Setting')->valueLabel('Value')->helperText('Encrypted at rest. Provider-specific typed pages can replace this generic editor as integrations are added.'),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name'), TextColumn::make('key')->badge(), IconColumn::make('enabled')->boolean(), IconColumn::make('is_default')->boolean()])->recordActions([EditAction::make()]);
    }
    public static function getPages(): array
    {
        return ['index' => ListAiProviderSettings::route('/'), 'edit' => EditAiProviderSetting::route('/{record}/edit')];
    }
}
