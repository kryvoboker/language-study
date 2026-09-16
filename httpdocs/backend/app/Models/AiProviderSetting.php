<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiProviderSetting extends Model
{
    protected $fillable = ['key', 'name', 'enabled', 'is_default', 'configuration'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean', 'is_default' => 'boolean', 'configuration' => 'encrypted:array'];
    }
}
