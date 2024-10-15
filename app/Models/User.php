<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @OA\Schema(
 *     schema="User",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="username", type="string"),
 *     @OA\Property(property="email", type="string"),
 *     @OA\Property(property="phone", type="string"),
 *     @OA\Property(property="address", type="string", nullable=true),
 *     @OA\Property(property="role", type="string", enum={"user", "admin"}),
 *     @OA\Property(property="image", type="string", format="uri", nullable=true)
 * )
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    use HasApiTokens, Notifiable;
    protected $fillable = [
            'username',
            'email',
            'password',
            'address',
            'phone',
            'image',
            'role'
            // 'google_id',
            // 'email_verified_at'
    ];

    public function bookings():HasMany {
        return $this->hasMany(related: Booking::class);
    }

    public function payments(): HasMany {
        return $this->hasMany(related: Payment::class);
    }

  public function notifications():HasMany
  {
return $this->hasMany(related: Notification::class);
  }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'role' => 'string',
    ];
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}
