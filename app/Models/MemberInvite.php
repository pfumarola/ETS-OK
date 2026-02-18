<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MemberInvite extends Model
{
    protected $table = 'member_invites';

    protected $fillable = [
        'email',
        'token',
        'expires_at',
        'invited_by',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function scopeValid(Builder $query): Builder
    {
        return $query->whereNull('used_at')
            ->where('expires_at', '>', now());
    }

    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }

    public function isValid(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }

    /**
     * Genera un token univoco (64 caratteri).
     */
    public static function generateUniqueToken(): string
    {
        $maxAttempts = 10;
        for ($i = 0; $i < $maxAttempts; $i++) {
            $token = Str::random(64);
            if (! static::where('token', $token)->exists()) {
                return $token;
            }
        }
        return Str::random(64) . bin2hex(random_bytes(8));
    }
}
