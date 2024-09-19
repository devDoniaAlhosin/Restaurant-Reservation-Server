<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'message',
        'is_read',
    ];
    protected $casts = [
        'is_read' => 'boolean',
    ];
    public function user(): BelongsTo {
        return $this->belongsTo(related: User::class);
    }
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
}
