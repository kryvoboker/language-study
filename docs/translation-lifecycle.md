[← AI Providers](ai-providers.md) · [Back to README](../README.md) · [Admin Authorization →](admin-authorization.md)

# Translation Lifecycle

## Request sequence

1. The user edits source text.
2. Nuxt cancels any active local request immediately.
3. Nuxt starts a debounce timer of 650 ms.
4. When the timer fires, Nuxt creates a new translation request.
5. Laravel validates, rate-limits, persists it, and dispatches a queue job.
6. The provider adapter creates a cancellable background AI operation.
7. Nuxt polls the local request approximately every 350 ms.
8. The backend poll job parses structured JSON and stores the result.
9. Nuxt renders the translation, an optional source-language usage object with an expression and one example, and explanations independently.
10. If the user edits again, cancellation is propagated to the backend and provider.

## Request states

| State | Meaning |
|-------|---------|
| `queued` | Persisted and waiting for provider work |
| `processing` | Provider operation is active |
| `completed` | A result is available |
| `failed` | Processing ended with an error |
| `cancelled` | The request was cancelled before completion |

## Natural variant response

For a single word or phrase, the provider may return a `natural_usage` object with the source-language expression and one source-language usage example:

```json
{
  "natural_usage": {
    "expression": "on my own",
    "example": "I learned how to build this website on my own."
  }
}
```

For complete sentences, or when no useful lexical example exists, `natural_usage` is `null`. The main `translation` remains the only translated result and is not duplicated in the usage section. This contract applies to newly created records; the project intentionally does not support legacy stored result shapes.

## Why polling?

Provider-side cancellation is a stronger requirement than token streaming. Background Responses provide an upstream operation ID that can be cancelled, while polling keeps API nodes short-lived and horizontally scalable.

A future provider can implement true streaming behind the same contract by adding a transport capability without changing the storefront domain model.

## See Also

- [AI Providers](ai-providers.md) — asynchronous provider contract
- [API Reference](api.md) — request and cancellation endpoints
- [Architecture](architecture.md) — queue and storage boundaries
