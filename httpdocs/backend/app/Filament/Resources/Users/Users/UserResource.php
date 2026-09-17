<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Users;

use App\Filament\Navigation\AdminNavigationGroupEnum;
use App\Filament\Resources\Trait\TotalModelItemsResourceTrait;
use App\Filament\Resources\Users\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Users\Pages\EditUser;
use App\Filament\Resources\Users\Users\Pages\ListUsers;
use App\Models\Users\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class UserResource extends Resource
{
	use TotalModelItemsResourceTrait;

	protected static ?string $model = User::class;

	protected static string|BackedEnum|null $navigationIcon = Heroicon::User;

	protected static ?string $recordTitleAttribute = 'name';

	protected static string|null|UnitEnum $navigationGroup = AdminNavigationGroupEnum::Users;

    public static function form(Schema $schema): Schema
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
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
			TextColumn::make('email')->searchable(),
            TextColumn::make('roles.name')->badge(),
			IconColumn::make('is_blocked')->boolean(),
			TextColumn::make('created_at')->dateTime()->sortable(),
        ])->recordActions([EditAction::make(), DeleteAction::make()]);
    }
    public static function getPages(): array
    {
        return ['index' => ListUsers::route('/'), 'create' => CreateUser::route('/create'), 'edit' => EditUser::route('/{record}/edit')];
    }

	/**
	 * Signature in the navigation menu (left panel)
	 */
	public static function getNavigationLabel(): string
	{
		return __('admin/users/users.navigation_label');
	}

	/**
	 * A single model name (e.g. in headings, "Create X" button)
	 */
	public static function getModelLabel(): string
	{
		return __('admin/users/users.labels.model');
	}

	/**
	 * Plural model name (e.g. in lists, section headings)
	 */
	public static function getPluralModelLabel(): string
	{
		return __('admin/users/users.labels.plural_model');
	}

	public static function getGloballySearchableAttributes(): array
	{
		return [
			'name',
			'lastname',
			'email',
			'telephone',
		];
	}

	public static function getGlobalSearchResultTitle(Model $record): string
	{
		return trim($record->getAttribute('name') . ' ' . $record->getAttribute('lastname'));
	}
}