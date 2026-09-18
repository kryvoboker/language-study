<?php

declare(strict_types=1);

namespace App\Filament\Resources\AiProviders\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class AiProviderSettingForm
{
	public static function configure(Schema $schema): Schema
	{
		return $schema->components([
			Section::make()
				->columnSpanFull()
				->schema([
					Group::make([
						TextInput::make('name')
							->required(),
						TextInput::make('key')
							->required()
							->alphaDash()
							->unique(ignoreRecord: true)
							->disabledOn('edit')
							->dehydrated(),
					])
						->columns(),
					Textarea::make('prompt_instruction')
						->label('Prompt instruction')
						->helperText('Instructions sent to the AI assistant for this provider. This replaces the default system prompt.')
						->required()
						->rows(8)
						->columnSpanFull(),
					Group::make([
						Toggle::make('enabled'),
						Toggle::make('is_default'),
					]),
					KeyValue::make('configuration')
						->keyLabel('Setting')
						->valueLabel('Value')
						->helperText('Encrypted at rest. Provider-specific typed pages can replace this generic editor as integrations are added.'),
				])
		]);
	}
}