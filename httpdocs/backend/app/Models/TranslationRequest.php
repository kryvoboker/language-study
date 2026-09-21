<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TranslationStatus;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property TranslationStatus $status
 * @property User              $user
 */
class TranslationRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'source_text',
        'source_language',
        'target_language',
        'request_hash',
        'locale',
        'status',
        'provider_key',
        'provider_operation_id',
        'result',
        'error_message',
        'cancelled_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TranslationStatus::class,
            'result' => 'json:unicode',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /** @return Attribute<array<string, mixed>|null, array<string, mixed>|string|null> */
    public function result(): Attribute
    {
        return Attribute::make(
            set: static fn (mixed $value): mixed => is_array($value)
                ? to_json($value)
                : $value,
        );
    }

    /**
     * @param string $source_text
     * @param string $source_language
     * @param string $target_language
     * @param string $current_locale
     *
     * @return string
     */
    public static function generateRequestHash(
        string $source_text,
        string $source_language,
        string $target_language,
        string $current_locale,
    ): string {
        $normalized_values = array_map(
            static fn (string $value): string => Str::lower(sanitize_str($value)),
            [$source_text, $source_language, $target_language, $current_locale],
        );

        return hash('sha256', implode("\0", $normalized_values));
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
