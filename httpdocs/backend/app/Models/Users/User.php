<?php

declare(strict_types=1);

namespace App\Models\Users;

use App\Models\AiProviderSetting;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int|null $ai_provider_id
 */
// TODO: Implement MustVerifyEmail again when email verification is re-enabled.
class User extends Authenticatable implements FilamentUser, OAuthenticatable
{
    use HasApiTokens;
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasRoles;
    use Notifiable;

    protected $fillable = [
        'name',
        'lastname',
        'email',
        'password',
        'is_blocked',
        'ai_provider_id',
        'telephone',
        'avatar',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_blocked' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active
            && !$this->is_blocked
            && !$this->hasRole('user')
            && $this->can('access_admin_panel');
    }

    /** @return BelongsTo<AiProviderSetting, $this> */
    public function aiProvider(): BelongsTo
    {
        return $this->belongsTo(AiProviderSetting::class, 'ai_provider_id');
    }
}