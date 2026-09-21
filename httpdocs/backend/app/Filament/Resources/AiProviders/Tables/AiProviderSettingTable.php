<?php

declare(strict_types=1);

namespace App\Filament\Resources\AiProviders\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class AiProviderSettingTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name'),
            TextColumn::make('key')->badge(),
            IconColumn::make('enabled')->boolean(),
            IconColumn::make('is_default')->boolean(),
        ])->recordActions([EditAction::make()]);
    }
}
