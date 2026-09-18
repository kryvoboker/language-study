<?php

declare(strict_types=1);

namespace App\Domain\Ai\Providers;

use App\Domain\Ai\Contracts\AiProviderContract;
use App\Domain\Ai\Data\ProviderOperationData;
use App\Domain\Ai\Data\ProviderResultData;
use App\Domain\Translation\Data\TranslationPromptData;
use App\Models\AiProviderSetting;
use Illuminate\Support\Str;
use InvalidArgumentException;
use JsonException;
use OpenAI;

final class OpenAiProvider implements AiProviderContract
{
	/**
	 * @param AiProviderSetting     $setting
	 * @param TranslationPromptData $data
	 *
	 * @return ProviderOperationData
	 * @throws JsonException
	 */
    public function start(AiProviderSetting $setting, TranslationPromptData $data): ProviderOperationData
    {
        $configuration = $this->configuration($setting);
        $client = OpenAI::client($configuration['api_key']);

        $response = $client->responses()->create([
            'model' => $configuration['model'],
            'background' => true,
            'store' => true,
            'instructions' => $this->instructions($setting),
            'input' => [[
                'role' => 'user',
                'content' => [[
                    'type' => 'input_text',
                    'text' => json_encode_throw([
                        'source_language' => $data->source_language,
                        'target_language' => $data->target_language,
                        'text' => $data->text,
                    ], JSON_UNESCAPED_UNICODE),
                ]],
            ]],
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'language_review',
                    'strict' => true,
                    'schema' => $this->schema(),
                ],
            ],
        ]);

        return new ProviderOperationData($response->id, (string) $response->status);
    }

	/**
	 * @param AiProviderSetting $setting
	 * @param string            $operation_id
	 *
	 * @return ProviderResultData
	 * @throws JsonException
	 */
    public function retrieve(AiProviderSetting $setting, string $operation_id): ProviderResultData
    {
        $client = OpenAI::client($this->configuration($setting)['api_key']);
        $response = $client->responses()->retrieve($operation_id);
        $status = $response->status;

        if ($status !== 'completed') {
            return new ProviderResultData($status, error: $response->error?->message ?? null);
        }

        $decoded = json_decode_throw($response->outputText);
        return new ProviderResultData($status, $decoded);
    }

    public function cancel(AiProviderSetting $setting, string $operation_id): void
    {
        $client = OpenAI::client($this->configuration($setting)['api_key']);
        $client->responses()->cancel($operation_id);
    }

    /** @return array{api_key: string, model: string} */
    private function configuration(AiProviderSetting $setting): array
    {
        $configuration = $setting->configuration ?? [];
        $apiKey = $configuration['api_key'] ?? null;

        if (! is_string($apiKey) || Str::trim($apiKey) === '') {
            throw new InvalidArgumentException("AI provider [$setting->key] has no API key configured.");
        }

        $model = $configuration['model'] ?? 'gpt-5.6-luna';

        return [
            'api_key' => $apiKey,
            'model' => is_string($model) && Str::trim($model) !== '' ? $model : 'gpt-5.6-luna',
        ];
    }

    private function instructions(AiProviderSetting $setting): string
    {
        if (is_string($setting->prompt_instruction) && Str::trim($setting->prompt_instruction) !== '') {
            return $setting->prompt_instruction;
        }

        return <<<'PROMPT'
You are a translation engine and language coach. Return only schema-valid JSON.
Translate faithfully into the requested target language. Independently evaluate the source text for grammar, spelling, punctuation and unnatural phrasing. Provide one corrected source version, one natural native-like target version, and concise educational issues. Do not invent errors. Preserve names, URLs, code, numbers and intended tone.
PROMPT;
    }

    private function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['translation', 'source_corrected', 'natural_version', 'issues'],
            'properties' => [
                'translation' => ['type' => 'string'],
                'source_corrected' => ['type' => 'string'],
                'natural_version' => ['type' => 'string'],
                'issues' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object', 'additionalProperties' => false,
                        'required' => ['type', 'original', 'correction', 'explanation', 'severity'],
                        'properties' => [
                            'type' => ['type' => 'string'],
                            'original' => ['type' => 'string'],
                            'correction' => ['type' => 'string'],
                            'explanation' => ['type' => 'string'],
                            'severity' => ['type' => 'string', 'enum' => ['info', 'warning', 'error']],
                        ],
                    ],
                ],
            ],
        ];
    }
}