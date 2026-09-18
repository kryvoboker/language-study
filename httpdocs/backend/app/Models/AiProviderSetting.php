<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class AiProviderSetting extends Model
{
	protected $fillable = [
		'key',
		'name',
		'prompt_instruction',
		'enabled',
		'is_default',
		'configuration',
	];

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
			'enabled'       => 'boolean',
			'is_default'    => 'boolean',
			'configuration' => 'encrypted:array',
		];
	}
}