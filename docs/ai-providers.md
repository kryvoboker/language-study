[← Authentication](authentication.md) · [Back to README](../README.md) · [Translation Lifecycle →](translation-lifecycle.md)

# AI Providers

## Provider contract

Every provider implements `AiProviderContract`:

```php
start(AiProviderSetting $setting, TranslationPromptData $data): ProviderOperationData
retrieve(AiProviderSetting $setting, string $operation_id): ProviderResultData
cancel(AiProviderSetting $setting, string $operation_id): void
```

The contract models an asynchronous operation rather than exposing a vendor-specific SDK response to the rest of the application.

## Provider resolution

`AiProviderResolver` selects a provider in this order:

1. the user's explicit override
2. the global default provider
3. the first enabled provider

Only administrators may change global or per-user provider selection. Storefront users do not receive provider metadata in API resources.

## OpenAI adapter

The first adapter is `OpenAiProvider`. It uses the OpenAI Responses API with background processing and structured JSON output. Provider credentials are stored through an encrypted Laravel cast. The background response ID is persisted so queue jobs can poll or cancel it.

The request sends the following fixed values:

| Request field | Application behavior |
|---------------|----------------------|
| `model` | Uses the configured model, or `gpt-5.6-luna` when no model is configured |
| `background` | Enabled by default so translation can be processed asynchronously |
| `store` | Enabled by default so the queued polling job can retrieve the response |
| `instructions` | Uses the provider's `prompt_instruction`, with a built-in fallback for older settings |
| `text.format` | Requires strict JSON matching the translation result schema |

Optional request settings are included only when configured:

| Configuration key | Responses API field | Purpose |
|-------------------|---------------------|---------|
| `reasoning_effort` | `reasoning.effort` | Controls the reasoning effort used by the model |
| `reasoning_summary` | `reasoning.summary` | Controls the amount of reasoning summary returned |
| `reasoning_mode` | `reasoning.mode` | Selects the supported standard or pro execution mode |
| `max_output_tokens` | `max_output_tokens` | Limits generated output, including reasoning tokens |
| `temperature` | `temperature` | Controls response randomness |
| `service_tier` | `service_tier` | Selects the processing service level |

`Top P` and `Truncation` are intentionally not exposed in the admin form and are not sent by `OpenAiProvider`. OpenAI recommends configuring either temperature or Top P rather than both; this application uses `temperature` only. Check the [Responses API reference](https://developers.openai.com/api/reference/cli/resources/responses/methods/create) for model-specific availability and supported values.

## Configure a provider in Filament

Administrators can add or edit provider settings at `/admin/ai-providers/ai-provider-settings`. Use the **Create** action to add a provider and enter:

- `key`: the registered adapter key, such as `openai`
- `name`: the display name
- `prompt_instruction`: the instruction sent to this AI assistant for translation requests. It replaces the default system prompt for this provider.
- `enabled`: whether the provider may receive translation jobs
- `is_default`: whether it is the global default provider
- `configuration`: encrypted OpenAI settings described below

For the OpenAI adapter, configure these fields in the **OpenAI Responses API** section:

| Field | Required | Purpose |
|-------|----------|---------|
| `api_key` | On create | Authorizes requests to the OpenAI API |
| `model` | Yes | Selects the model used for translation and language analysis |
| `reasoning_effort` | No | Sets the model's reasoning effort |
| `reasoning_summary` | No | Controls the returned reasoning summary |
| `reasoning_mode` | No | Selects standard or pro reasoning when supported |
| `max_output_tokens` | No | Limits the generated response size |
| `temperature` | No | Controls response randomness; leave Top P out because it is not supported by this integration |
| `service_tier` | No | Selects the request processing tier |
| `background` | No | Runs the request asynchronously; enabled by default |
| `store` | No | Keeps the response available for polling; enabled by default |

The `configuration` value is encrypted at rest. API keys must be entered only in Filament and must not be put into frontend environment variables. Only one provider is kept as the global default; a user's provider override takes precedence when it is enabled.

Provider keys are matched case-insensitively and with surrounding whitespace ignored. Use the canonical key `openai` for the OpenAI adapter.

The prompt instruction is stored per provider setting, so different assistants can use different translation and language-coaching behavior. Existing settings without an instruction continue to use the built-in compatibility instruction until they are edited.

## Adding a provider

1. Add a provider key to `AiProviderType`.
2. Implement `AiProviderContract`.
3. Register the adapter in `AiProviderManager`.
4. Add provider-specific configuration fields to Filament.
5. Add tests for normalization, cancellation, and error mapping.

## See Also

- [Translation Lifecycle](translation-lifecycle.md) — how jobs use the contract
- [Architecture](architecture.md) — domain boundaries and data isolation
- [Configuration](configuration.md) — runtime credentials and services
