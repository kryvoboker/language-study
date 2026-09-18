<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Users\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class UserTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('email')->searchable(),
            TextColumn::make('roles.name')->badge(),
            IconColumn::make('is_blocked')->boolean(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}