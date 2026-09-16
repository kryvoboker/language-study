<?php
namespace httpdocs\backend\app\Domain\Translation\Data;
final readonly class TranslationPromptData
{
    public function __construct(public string $text, public string $source_language, public string $target_language) {}
}