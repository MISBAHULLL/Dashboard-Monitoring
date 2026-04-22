<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',    // JSON otomatis di-decode jadi PHP array
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendapatkan model yang terkait dengan log ini.
     * morphTo = relasi polimorfik (bisa ke Task, Team, Client, dll)
     */
    public function subject()
    {
        return $this->morphTo(null, 'model_type', 'model_id');
    }
}
