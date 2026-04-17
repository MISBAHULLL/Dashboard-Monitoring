<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskComment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'task_id',
        'user_id',
        'content',
        'parent_id',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Komentar parent (jika ini adalah reply) */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(TaskComment::class, 'parent_id');
    }

    /** Reply/balasan dari komentar ini */
    public function replies(): HasMany
    {
        return $this->hasMany(TaskComment::class, 'parent_id');
    }
}
