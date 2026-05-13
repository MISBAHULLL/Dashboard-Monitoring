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

    /**
     * Accessor yang selalu ikut saat task di-serialize ke array/JSON.
     * Diperlukan agar frontend (Tasks/Index.vue) dapat mengakses task.sla_status
     * tanpa error SSR "Cannot read properties of undefined (reading 'replace')".
     */
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
    // --- SLA STATUS (cached lookup, bukan relasi) ---
    public function getSlaStatusAttribute(): string
    {
        if (!$this->category || !$this->created_at) {
            return 'unknown';
        }

        // Cache SLA configs selama 1 jam sebagai array primitive (hindari menyimpan
        // Eloquent Collection di cache -> bisa berujung "incomplete object" saat unserialize).
        $slaConfigs = cache()->remember('sla_configs', 3600, function () {
            return SlaConfig::query()
                ->get(['category', 'max_days', 'warning_days'])
                ->keyBy('category')
                ->map(fn ($sla) => [
                    'max_days' => (int) $sla->max_days,
                    'warning_days' => (int) $sla->warning_days,
                ])
                ->toArray();
        });

        $sla = $slaConfigs[$this->category] ?? null;
        if (!$sla) {
            return 'unknown';
        }

        $dueDate = $this->created_at->copy()->addDays($sla['max_days']);
        $warningDate = $dueDate->copy()->subDays($sla['warning_days']);

        if ($this->status === 'completed') {
            return ($this->completed_at && $this->completed_at <= $dueDate)
                ? 'completed_on_time'
                : 'completed_late';
        }

        $now = now();
        if ($now > $dueDate) {
            return 'overdue';
        }
        if ($now >= $warningDate) {
            return 'warning';
        }

        return 'on_track';
    }
}
