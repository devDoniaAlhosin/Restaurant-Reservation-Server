<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;
    protected $fillable=[
    'booking_id',
    'user_id',
    'amount',
    'payment_method',
    'payment_status',
    'payment_date',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount' => 'decimal:2',
        'payment_method' => 'string',  // Enum for payment method
        'payment_status' => 'string',  // Enum for payment status
    ];
    public function booking(): BelongsTo {
        return $this->belongsTo(related: Booking::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(related: User::class);
    }

    // scopeStatus(): Allows you to filter payments by their
    //  payment_status (e.g., pending, completed, failed).
    public function scopeStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

   
// scopeMethod(): Allows filtering by payment_method (e.g., credit card, PayPal, cash).
    public function scopeMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }
}
