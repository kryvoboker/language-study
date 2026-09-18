<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** @property array<string, mixed>|null $configuration */
class AiProviderSetting extends Model
{
    private int $default_max_input_characters;

    protected $fillable = [
        'key',
        'name',
        'prompt_instruction',
        'enabled',
        'is_default',
        'configuration',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->default_max_input_characters = integer_value(config('app.max_characters_for_input_translate', 12000));
    }

    protected static function booted(): void
    {
        static::saving(function (self $setting): void {
            if ($setting->is_default) {
                self::query()
                    ->whereKeyNot($setting->getKey())
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'is_default' => 'boolean',
            'configuration' => 'encrypted:array',
        ];
    }

    public function maxInputCharacters(): int
    {
        $limit = data_get($this->configuration, 'max_input_characters', $this->default_max_input_characters);

        return is_numeric($limit)
            ? max(1, integer_value($limit))
            : $this->default_max_input_characters;
    }
}
