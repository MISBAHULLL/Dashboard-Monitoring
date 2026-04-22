<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'engineer_id',
        'client_id',
        'assigned_to',
        'created_by',
        'feature',
        'description',
        'task_url',
        'type',
        'priority',
        'status',
        'start_date',
        'due_date',
        'release_date',
        'progress',
    ];

    protected function casts(): array
    {
        return [
            'start_date'   => 'date',
            'due_date'     => 'date',
            'release_date' => 'date',
            'progress'     => 'integer',
        ];
    }

    // ==========================================
    // RELATIONSHIPS (BelongsTo = "milik siapa")
    // ==========================================

    /** Tim product yang memiliki task ini */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'product_id');
    }

    /** Tim engineer yang mengerjakan task ini */
    public function engineer(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'engineer_id');
    }

    /** Client/faskes yang request task ini */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** User yang di-assign task ini */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** User yang membuat task ini */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==========================================
    // RELATIONSHIPS (HasMany = "punya banyak")
    // ==========================================

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function releaseDateLogs(): HasMany
    {
        return $this->hasMany(ReleaseDateLog::class);
    }

    // ==========================================
    // RELATIONSHIPS (BelongsToMany = Many-to-Many)
    // ==========================================

    /** Tags yang dipasang di task ini */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'task_tags')->withTimestamps();
    }

    // ==========================================
    // SCOPES (Filter query yang sering dipakai)
    // ==========================================

    /** Task yang overdue (lewat deadline) */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                     ->whereNotIn('status', ['done', 'released']);
    }

    /** Task yang deadline-nya dalam 7 hari ke depan */
    public function scopeDueSoon($query)
    {
        return $query->whereBetween('due_date', [now(), now()->addDays(7)])
                     ->whereNotIn('status', ['done', 'released']);
    }

    /** Filter by status */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /** Filter by priority */
    public function scopePriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /** Cek apakah task sudah lewat deadline */
    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && !in_array($this->status, ['done', 'released']);
    }
}
