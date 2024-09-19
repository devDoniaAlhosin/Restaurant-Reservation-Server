<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'phone',
        'date_time',
        'total_person',
        'status',
        'notes',
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
