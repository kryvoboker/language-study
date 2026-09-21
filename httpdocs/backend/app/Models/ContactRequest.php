<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContactRequestStatus;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactRequest extends Model
{
    use HasUuids;

    /** @use HasFactory<\Database\Factories\ContactRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sender_first_name',
        'sender_last_name',
        'sender_email',
        'sender_is_blocked',
        'message',
        'status',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sender_is_blocked' => 'boolean',
            'status' => ContactRequestStatus::class,
            'sent_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<ContactRequestAttachment, $this> */
    public function attachments(): HasMany
    {
        return $this->hasMany(ContactRequestAttachment::class)->orderBy('position');
    }
}
