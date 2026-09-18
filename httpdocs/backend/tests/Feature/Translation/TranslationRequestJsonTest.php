<?php

declare(strict_types=1);

namespace Tests\Feature\Translation;

use App\Enums\TranslationStatus;
use App\Models\TranslationRequest;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationRequestJsonTest extends TestCase
{
    use RefreshDatabase;

    public function test_result_is_saved_as_json_without_escaping_unicode_symbols(): void
    {
        $translationRequest = TranslationRequest::query()->create([
            'user_id' => User::factory()->create()->getKey(),
            'source_text' => 'Hello',
            'source_language' => 'en',
            'target_language' => 'ru',
            'status' => TranslationStatus::Queued,
            'result' => [
                'translation' => 'Привет, мир!',
                'language' => 'Русский',
            ],
        ]);

        $rawResult = $translationRequest->fresh()->getRawOriginal('result');

        $this->assertIsString($rawResult);
        $this->assertStringContainsString('Привет, мир!', $rawResult);
        $this->assertStringNotContainsString('\u041f\u0440\u0438\u0432\u0435\u0442', $rawResult);
        $this->assertSame('Привет, мир!', $translationRequest->fresh()->result['translation']);

        $translationRequest->update([
            'result' => [
                'translation' => 'Добрый день!',
            ],
        ]);

        $updatedRawResult = $translationRequest->fresh()->getRawOriginal('result');

        $this->assertIsString($updatedRawResult);
        $this->assertStringContainsString('Добрый день!', $updatedRawResult);
        $this->assertStringNotContainsString('\u0414\u043e\u0431\u0440\u044b\u0439', $updatedRawResult);
    }
}
