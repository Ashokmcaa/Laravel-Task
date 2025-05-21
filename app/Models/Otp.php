<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OTP extends Model
{
    protected $table = 'otps';

    protected $fillable = ['user_id', 'code', 'type', 'expires_at'];

    protected $casts = [
        'type' => 'string',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generateForUser(User $user, string $type = 'email'): self
    {
        self::where('user_id', $user->id)
            ->where('expires_at', '<', now())
            ->delete();

        $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(15);

        return self::create([
            'user_id' => $user->id,
            'code' => $code,
            'type' => $type,
            'expires_at' => $expiresAt,
        ]);
    }

    public function isValid(string $code, User $user): bool
    {
        return $this->code === $code &&
               $this->user_id === $user->id &&
               is_null($this->verified_at) &&
               $this->expires_at->isFuture();
    }

    public function verify(): void
    {
        $this->update(['verified_at' => now()]);
    }

    public static function cleanupExpired(): void
    {
        self::where('expires_at', '<', now())->delete();
    }
}