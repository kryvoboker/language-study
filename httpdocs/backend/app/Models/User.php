<?php

namespace httpdocs\backend\app\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use httpdocs\backend\app\Models\AiProviderSetting;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail, OAuthenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'is_blocked', 'ai_provider_id'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed', 'is_blocked' => 'boolean'];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return ! $this->is_blocked
            && $this->hasVerifiedEmail()
            && ! $this->hasRole('user')
            && $this->can('access_admin_panel');
    }

    public function aiProvider()
    {
        return $this->belongsTo(AiProviderSetting::class, 'ai_provider_id');
    }
}