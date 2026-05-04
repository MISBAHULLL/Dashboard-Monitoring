<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $task_id
 * @property int|null $changed_by
 * @property Carbon|null $old_date
 * @property Carbon|null $new_date
 * @property string|null $reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ReleaseDateLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'changed_by',
        'old_date',
        'new_date',
        'reason',
    ];

    protected $casts = [
        'old_date' => 'date',
        'new_date' => 'date',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
