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

    protected $appends = ['sla_status'];

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

    // Many-to-many: 1 task bisa terhubung ke banyak dokumen
    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'document_task')->withTimestamps();
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

    public function sla(): BelongsTo
    {
        return $this->belongsTo(SlaConfig::class, 'category', 'category');
    }

    public function getSlaStatusAttribute(): string
    {
        if (!$this->relationLoaded('sla') && !isset($this->sla)) {
            // Jika relasi sla belum di-load, kita coba ambil config dari cache atau static collection agar tidak N+1
            // Tapi untuk amannya kita anggap unknown jika tidak ada data SLA
            return 'unknown';
        }

        if (!$this->sla) return 'unknown';

        $maxDays = $this->sla->max_days;
        $warningDays = $this->sla->warning_days;

        $startDate = $this->created_at;
        // Hitung deadline (created_at + max_days)
        $dueDate = $startDate->copy()->addDays($maxDays);
        $warningDate = $dueDate->copy()->subDays($warningDays);

        if ($this->status === 'completed') {
            if ($this->completed_at && $this->completed_at <= $dueDate) {
                return 'completed_on_time';
            } else {
                return 'completed_late';
            }
        }

        $now = now();
        if ($now > $dueDate) {
            return 'overdue';
        } elseif ($now >= $warningDate) {
            return 'warning';
        }

        return 'on_track';
    }
}
