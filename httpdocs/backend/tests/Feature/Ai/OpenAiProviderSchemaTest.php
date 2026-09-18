<?php

declare(strict_types=1);

namespace Tests\Feature\Ai;

use App\Domain\Ai\Providers\OpenAiProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

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
        self::assertSame(['expression', 'example'], $natural_usage['required']);
        self::assertSame(['type' => 'string'], $natural_usage['properties']['expression']);
        self::assertSame(['type' => 'string'], $natural_usage['properties']['example']);
    }
}
