<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'icon',
        'data',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data'    => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Cek apakah notifikasi sudah dibaca */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /** Tandai sudah dibaca */
    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /** Hanya notifikasi yang belum dibaca */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
