<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'client_id',
        'engineer_id',
        'document_id',
        'template_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'modul',
        'task_url',
        'category',
        'priority',
        'status',
        'release_date',
        'completed_at',
    ];

    protected $casts = [
        'release_date' => 'date',
        'completed_at' => 'datetime',
    ];

    // --- RELATIONS ---

    public function product(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'product_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function engineer(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'engineer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(TaskTemplate::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'task_tags');
    }
}
