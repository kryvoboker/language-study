<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(120),
            TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            Toggle::make('is_blocked')->label('Blocked'),
            Select::make('roles')
                ->relationship('roles', 'name')
                ->multiple()
                ->preload()
                ->searchable()
                ->required()
                ->helperText('The user role is storefront-only. Admin roles require access_admin_panel.'),
            Select::make('ai_provider_id')
                ->relationship('aiProvider', 'name')
                ->searchable()
                ->preload()
                ->placeholder('Use global provider'),
        ]);
    }
}