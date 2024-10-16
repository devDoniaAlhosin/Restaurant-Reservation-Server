<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="Booking",
 *     type="object",
 *     title="Booking",
 *     description="Booking model",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="Booking ID"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         description="User ID associated with the booking"
 *     ),
 *     @OA\Property(
 *         property="username",
 *         type="string",
 *         description="Username of the user"
 *     ),
 *     @OA\Property(
 *         property="phone",
 *         type="string",
 *         description="Phone number of the user"
 *     ),
 *     @OA\Property(
 *         property="date_time",
 *         type="string",
 *         format="date-time",
 *         description="Booking date and time"
 *     ),
 *     @OA\Property(
 *         property="total_person",
 *         type="integer",
 *         description="Total number of people for the booking"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Booking status"
 *     ),
 *     @OA\Property(
 *         property="notes",
 *         type="string",
 *         description="Additional notes"
 *     )
 * )
 */

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'username',
        'phone',
        'date_time',
        'total_person',
        'status',
        'notes',
        'email_sent',
    ];
    protected $casts = [
        'date_time' => 'datetime',
        'status' => 'string',  // Enum as string
    ];
    public function user():BelongsTo{
        return $this->belongsTo(related: User::class);
    }
    public function payment():BelongsTo{
        return $this->belongsTo(related: Payment::class);
    }
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date_time', [$startDate, $endDate]);
    }
}
