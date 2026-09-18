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
     * @throws JsonException
     * @return ProviderOperationData
     */
    public function start(AiProviderSetting $setting, TranslationPromptData $data): ProviderOperationData
    {
        $configuration = $this->configuration($setting);
        $client = OpenAI::client($configuration['api_key']);

        $response = $client->responses()->create([
            'model' => $configuration['model'],
            ...$configuration['request'],
            'instructions' => $this->instructions($setting, $data->locale),
            'input' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => json_encode_throw([
                                'source_language' => $data->source_language,
                                'target_language' => $data->target_language,
                                'text' => $data->text,
                            ], JSON_UNESCAPED_UNICODE),
                        ],
                    ],
                ],
            ],
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'language_review',
                    'strict' => true,
                    'schema' => $this->schema(),
                ],
            ],
        ]);

        return new ProviderOperationData($response->id, (string)$response->status);
    }

    /**
     * @param AiProviderSetting $setting
     * @param string            $operation_id
     *
     * @throws JsonException
     * @return ProviderResultData
     */
    public function retrieve(AiProviderSetting $setting, string $operation_id): ProviderResultData
    {
        $client = OpenAI::client($this->configuration($setting)['api_key']);
        $response = $client->responses()->retrieve($operation_id);
        $status = $response->status;

        if ($status !== 'completed') {
            return new ProviderResultData($status, error: $response->error->message ?? null);
        }

        if (! is_string($response->outputText)) {
            return new ProviderResultData($status, error: 'The AI provider returned an empty response.');
        }

        $decoded = json_decode_throw($response->outputText);
        /** @var array<string, mixed>|null $result */
        $result = is_array($decoded) ? $decoded : null;

        return new ProviderResultData($status, $result);
    }

    public function cancel(AiProviderSetting $setting, string $operation_id): void
    {
        $client = OpenAI::client($this->configuration($setting)['api_key']);
        $client->responses()->cancel($operation_id);
    }

    /**
     * @return array{api_key: string, model: string, request: array<string, mixed>}
     */
    private function configuration(AiProviderSetting $setting): array
    {
        $configuration = is_array($setting->configuration) ? $setting->configuration : [];
        $apiKey = $configuration['api_key'] ?? null;

        if (!is_string($apiKey) || blank($apiKey)) {
            throw new InvalidArgumentException("AI provider [$setting->key] has no API key configured.");
        }

        $configured_model = $configuration['model'] ?? null;
        $model = is_string($configured_model) && Str::trim($configured_model) !== ''
            ? Str::trim($configured_model)
            : Str::trim(string_value(config('ai.providers.openai.default_model', 'gpt-5-nano')));

        $request = [
            'background' => true,
            'store' => true,
        ];

        foreach (['background', 'store'] as $key) {
            if (array_key_exists($key, $configuration) && is_bool($configuration[$key])) {
                $request[$key] = $configuration[$key];
            }
        }

        $key = 'max_output_tokens';

        if (array_key_exists($key, $configuration) && is_numeric($configuration[$key])) {
            $value = (int)$configuration[$key];

            if ($value > 0) {
                $request[$key] = $value;
            }
        }

        $key = 'temperature';

        if (array_key_exists($key, $configuration) && is_numeric($configuration[$key])) {
            $request[$key] = (float)$configuration[$key];
        }

        $key = 'service_tier';

        if (is_string($configuration[$key] ?? null) && Str::trim($configuration[$key]) !== '') {
            $request[$key] = $configuration[$key];
        }

        $reasoning = [];

        foreach (['effort', 'summary', 'mode'] as $key) {
            $configurationKey = "reasoning_$key";

            if (is_string($configuration[$configurationKey] ?? null) && Str::trim($configuration[$configurationKey]) !== '') {
                $reasoning[$key] = $configuration[$configurationKey];
            }
        }

        if ($reasoning !== []) {
            $request['reasoning'] = $reasoning;
        }

        return [
            'api_key' => $apiKey,
            'model' => $model,
            'request' => $request,
        ];
    }

    private function instructions(AiProviderSetting $setting, string $locale): string
    {
        $instructions = is_string($setting->prompt_instruction) && Str::trim($setting->prompt_instruction) !== ''
            ? $setting->prompt_instruction
            : <<<'PROMPT'
You are a translation engine and language coach. Return only schema-valid JSON.
Translate faithfully into the requested target language. Independently evaluate the source text for grammar, spelling, punctuation and unnatural phrasing. Provide one corrected source version, one natural native-like target version, and concise educational issues. Do not invent errors. Preserve names, URLs, code, numbers and intended tone.
PROMPT;

        return Str::finish($instructions, "\n") . "Provide error messages to the user only in $locale.";
    }

    /** @return array<string, mixed> */
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
