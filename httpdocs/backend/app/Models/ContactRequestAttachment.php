<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactRequestAttachment extends Model
{
    use HasUuids;

    protected $fillable = [
        'contact_request_id',
        'disk',
        'path',
        'mime_type',
        'size_bytes',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'position' => 'integer',
        ];
    }

    /** @return BelongsTo<ContactRequest, $this> */
    public function contactRequest(): BelongsTo
    {
        return $this->belongsTo(ContactRequest::class);
    }
}
