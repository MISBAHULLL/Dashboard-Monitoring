<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'color',
    ];

    /** Task yang memiliki tag ini (Many-to-Many) */
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_tags')->withTimestamps();
    }
}
