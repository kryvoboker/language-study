[← Authentication](authentication.md) · [Back to README](../README.md) · [Translation Lifecycle →](translation-lifecycle.md)

# AI Providers

## Provider contract

Every provider implements `AiProviderContract`:

```php
start(TranslationPromptData $data): ProviderOperationData
retrieve(string $operation_id): ProviderResultData
cancel(string $operation_id): void
```

The contract models an asynchronous operation rather than exposing a vendor-specific SDK response to the rest of the application.

## Provider resolution

`AiProviderResolver` selects a provider in this order:

1. the user's explicit override
2. the global default provider
3. the first enabled provider

Only administrators may change global or per-user provider selection. Storefront users do not receive provider metadata in API resources.

## OpenAI adapter

The first adapter is `OpenAiProvider`. It uses the Responses API with `background: true` and structured JSON output. Provider credentials are stored through an encrypted Laravel cast. The background response ID is persisted so queue jobs can cancel and poll it.

## Configure a provider in Filament

Administrators can add or edit provider settings at `/admin/ai-providers/ai-provider-settings`. Use the **Create** action to add a provider and enter:

- `key`: the registered adapter key, such as `openai`
- `name`: the display name
- `prompt_instruction`: the instruction sent to this AI assistant for translation requests. It replaces the default system prompt for this provider.
- `enabled`: whether the provider may receive translation jobs
- `is_default`: whether it is the global default provider
- `configuration`: provider-specific values, such as `api_key` and `model` for OpenAI

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
