<?php

declare(strict_types=1);

namespace App\Filament\Resources\AiProviders\Schemas;

use Filament\Forms\Components\Select;
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
		$max_input_characters     = (int)config('app.max_characters_for_input_translate', 12000);
		$default_input_characters = (int)config('app.default_characters_for_input_translate', 500);
		$default_model            = (string)config('ai.providers.openai.default_model', 'gpt-5-nano');

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

						TextInput::make('configuration.max_input_characters')
							->label('Maximum input characters')
							->numeric()
							->integer()
							->minValue(1)
							->maxValue($max_input_characters)
							->default($default_input_characters)
							->helperText('Limits how many characters a user can submit for one translation request.'),
					])
						->columns(),
					Section::make('OpenAI Responses API')
						->description('These values are sent to the Responses API when they are configured. Availability depends on the selected model.')
						->schema([
							TextInput::make('configuration.api_key')
								->label('API key')
								->password()
								->revealable()
								->helperText('Connects this provider to your OpenAI account and authorizes its API requests.')
								->required(fn(string $operation): bool => $operation === 'create')
								->dehydrated(fn(?string $state): bool => filled($state)),
							TextInput::make('configuration.model')
								->helperText('Chooses which OpenAI model performs translation and language analysis.')
								->required()
								->default($default_model),
							Select::make('configuration.reasoning_effort')
								->label('Reasoning effort')
								->options([
									'none'    => 'None',
									'minimal' => 'Minimal',
									'low'     => 'Low',
									'medium'  => 'Medium',
									'high'    => 'High',
									'xhigh'   => 'Extra high',
									'max'     => 'Maximum',
								])
								->helperText('Sets how much time and token budget the model can use to think before answering.'),
							Select::make('configuration.reasoning_summary')
								->label('Reasoning summary')
								->options([
									'auto'     => 'Automatic',
									'concise'  => 'Concise',
									'detailed' => 'Detailed',
								])
								->helperText('Controls how much of the model\'s reasoning summary is returned with the response.'),
							Select::make('configuration.reasoning_mode')
								->label('Reasoning mode')
								->options([
									'standard' => 'Standard',
									'pro'      => 'Pro',
								])
								->helperText('Chooses the execution mode for models that provide standard and pro reasoning.'),
							TextInput::make('configuration.max_output_tokens')
								->label('Max output tokens')
								->numeric()
								->integer()
								->minValue(1)
								->helperText('Limits the size of the generated translation and language feedback response.'),

							TextInput::make('configuration.temperature')
								->numeric()
								->minValue(0)
								->maxValue(2)
								->helperText('Controls response randomness: lower values make translations more consistent, higher values more varied.'),
							Select::make('configuration.service_tier')
								->options([
									'auto'     => 'Automatic',
									'default'  => 'Default',
									'flex'     => 'Flex',
									'priority' => 'Priority',
								])
								->helperText('Chooses the processing service level used for this provider\'s requests.'),
							Toggle::make('configuration.background')
								->default(true)
								->helperText('Allows long translation requests to run in the background while the application checks their status.'),
							Toggle::make('configuration.store')
								->default(true)
								->helperText('Keeps the response available on OpenAI so the application can retrieve it while processing.'),
						])
						->columns()
						->columnSpanFull(),
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
				]),
		]);
	}
}