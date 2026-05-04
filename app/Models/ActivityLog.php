<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $module
 * @property int|null $target_id
 * @property string|null $target_title
 * @property string $action
 * @property string|null $description
 * @property array|null $old_values
 * @property array|null $new_values
 * @property string|null $ip_address
 * @property Carbon|null $created_at
 */
class ActivityLog extends Model
{
    use HasFactory;

    // Matikan updated_at karena log sifatnya read-only
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'module',
        'target_id',
        'target_title',
        'action',
        'description',
        'old_values',
        'new_values',
        'ip_address',
    ];

    protected $casts = [
        'old_values' => 'array', // Supaya JSON otomatis jadi array di PHP
        'new_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
