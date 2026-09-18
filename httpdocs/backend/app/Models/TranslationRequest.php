<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TranslationStatus;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TranslationRequest extends Model
{
	use HasUuids;

	protected $fillable = [
		'user_id',
		'source_text',
		'source_language',
		'target_language',
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
			'status'       => TranslationStatus::class,
			'result'       => 'array',
			'cancelled_at' => 'datetime',
			'completed_at' => 'datetime',
		];
	}

	/**
	 * @return BelongsTo<User>
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	public function result(): Attribute
	{
		return Attribute::make(
			set: fn ($value) => to_json($value),
		);
	}
}