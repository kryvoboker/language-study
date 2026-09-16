<?php

namespace httpdocs\backend\app\Models;

use httpdocs\backend\app\Enums\TranslationStatus;
use httpdocs\backend\app\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TranslationRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id', 'source_text', 'source_language', 'target_language', 'status', 'provider_key',
        'provider_operation_id', 'result', 'error_message', 'cancelled_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TranslationStatus::class,
            'result' => 'array',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}