<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string|null $navigationIcon = 'heroicon-o-users';
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(120),
            TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            Toggle::make('is_blocked')->label('Blocked'),
            Select::make('roles')->relationship('roles', 'name')->multiple()->preload()->searchable()->required()
                ->helperText('The user role is storefront-only. Admin roles require access_admin_panel.'),
            Select::make('ai_provider_id')->relationship('aiProvider', 'name')->searchable()->preload()->placeholder('Use global provider'),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(), TextColumn::make('email')->searchable(),
            TextColumn::make('roles.name')->badge(), IconColumn::make('is_blocked')->boolean(), TextColumn::make('created_at')->dateTime()->sortable(),
        ])->recordActions([EditAction::make(), DeleteAction::make()]);
    }
    public static function getPages(): array
    {
        return ['index' => ListUsers::route('/'), 'create' => CreateUser::route('/create'), 'edit' => EditUser::route('/{record}/edit')];
    }
}
