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
            TextInput::make('lastname')->maxLength(120),
            TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            TextInput::make('telephone')
                ->tel()
                ->maxLength(20)
                ->unique(ignoreRecord: true),
            TextInput::make('avatar')
                ->maxLength(600)
                ->helperText('An optional URL or storage path for the user avatar.'),
            TextInput::make('password')
                ->password()
                ->revealable()
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->helperText('Set a password when creating a user or leave it blank to keep the current password.'),
            Toggle::make('is_blocked')->label('Blocked'),
            Toggle::make('is_active')->label('Active'),
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
