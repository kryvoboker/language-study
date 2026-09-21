<?php

declare(strict_types=1);

namespace Tests\Feature\Ai;

use App\Domain\Ai\Providers\OpenAiProvider;
use App\Models\AiProviderSetting;
use ReflectionMethod;
use Tests\TestCase;

class OpenAiProviderSchemaTest extends TestCase
{
    public function test_natural_usage_is_a_nullable_structured_object(): void
    {
        $schema_method = new ReflectionMethod(OpenAiProvider::class, 'schema');
        $schema_method->setAccessible(true);

        /** @var array{properties: array{natural_usage: array<string, mixed>}} $schema */
        $schema = $schema_method->invoke(new OpenAiProvider());
        $natural_usage = $schema['properties']['natural_usage'];

        self::assertSame(['object', 'null'], $natural_usage['type']);
        self::assertSame(['expression', 'pronunciation', 'example', 'example_translation'], $natural_usage['required']);
        self::assertSame(['type' => 'string'], $natural_usage['properties']['expression']);
        self::assertSame(['type' => 'string'], $natural_usage['properties']['pronunciation']);
        self::assertSame(['type' => 'string'], $natural_usage['properties']['example']);
        self::assertSame(['type' => 'string'], $natural_usage['properties']['example_translation']);
    }

    public function test_instructions_bind_pronunciation_and_example_translation_to_the_requested_locale(): void
    {
        $instructions_method = new ReflectionMethod(OpenAiProvider::class, 'instructions');
        $instructions_method->setAccessible(true);

        $instructions = $instructions_method->invoke(
            new OpenAiProvider(),
            new AiProviderSetting(['prompt_instruction' => 'Follow the assistant instructions.']),
            'ru',
        );

        self::assertStringContainsString('natural_usage.pronunciation', $instructions);
        self::assertStringContainsString('natural_usage.example_translation', $instructions);
        self::assertStringContainsString('RU only', $instructions);
        self::assertStringContainsString('Never translate or define the expression in this field.', $instructions);
        self::assertStringContainsString('[э кэт]', $instructions);
        self::assertStringContainsString('[это кошка]', $instructions);
    }
}
