<?php
namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class Task extends Model
{
    use HasFactory, SoftDeletes;

    public const DEFAULT_WARNING_DAYS = 3;

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
        'review_requested_at',
        'review_requested_by',
    ];
    protected $casts = [
        'release_date' => 'date',
        'completed_at' => 'datetime',
        'review_requested_at' => 'datetime',
    ];

    /**
     * Accessor yang selalu ikut saat task di-serialize ke array/JSON.
     * Diperlukan agar frontend dapat mengakses SLA tanpa error saat serialize.
     * tanpa error SSR "Cannot read properties of undefined (reading 'replace')".
     */
    protected $appends = ['sla_status', 'sla_due_date', 'sla_warning_date'];
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
    public function reviewRequester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'review_requested_by');
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
    // --- SLA DEADLINE (release_date override, fallback ke config SLA kategori) ---
    public static function effectiveDeadlineExpression(): string
    {
        return 'COALESCE(tasks.release_date, DATE(DATE_ADD(tasks.created_at, INTERVAL sla_configs.max_days DAY)))';
    }

    public static function warningDeadlineExpression(int $fallbackWarningDays = self::DEFAULT_WARNING_DAYS): string
    {
        return 'DATE_SUB('.self::effectiveDeadlineExpression().', INTERVAL COALESCE(sla_configs.warning_days, '.$fallbackWarningDays.') DAY)';
    }

    public function scopeWithSlaConfig(Builder $query): Builder
    {
        return $query->leftJoin('sla_configs', 'sla_configs.category', '=', 'tasks.category');
    }

    public function scopeWhereSlaOverdue(Builder $query): Builder
    {
        return $query
            ->withSlaConfig()
            ->where('tasks.status', '!=', 'completed')
            ->whereRaw(self::effectiveDeadlineExpression().' IS NOT NULL')
            ->whereRaw(self::effectiveDeadlineExpression().' < ?', [now()->toDateString()]);
    }

    public function scopeWhereSlaDueSoon(Builder $query, int $daysAhead = 7): Builder
    {
        $today = now()->toDateString();

        return $query
            ->withSlaConfig()
            ->where('tasks.status', '!=', 'completed')
            ->whereRaw(self::effectiveDeadlineExpression().' IS NOT NULL')
            ->whereRaw(self::effectiveDeadlineExpression().' >= ?', [$today])
            ->whereRaw(self::warningDeadlineExpression($daysAhead).' <= ?', [$today]);
    }

    public function getSlaDueDateAttribute(): ?string
    {
        return $this->effectiveDeadline()?->toDateString();
    }

    public function getSlaWarningDateAttribute(): ?string
    {
        $deadline = $this->effectiveDeadline();
        $sla = $this->slaConfigForCategory();

        if (!$deadline) {
            return null;
        }

        return $deadline->copy()->subDays($sla['warning_days'] ?? self::DEFAULT_WARNING_DAYS)->toDateString();
    }

    public function getSlaStatusAttribute(): string
    {
        $dueDate = $this->effectiveDeadline();

        if (!$dueDate) {
            return 'unknown';
        }

        $sla = $this->slaConfigForCategory();
        if (!$sla && !$this->release_date) {
            return 'unknown';
        }

        $warningDate = $sla
            ? $dueDate->copy()->subDays($sla['warning_days'])->startOfDay()
            : $dueDate->copy()->subDays(self::DEFAULT_WARNING_DAYS)->startOfDay();

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

    public function effectiveDeadline(): ?CarbonInterface
    {
        if ($this->release_date) {
            return $this->release_date->copy()->endOfDay();
        }

        if (!$this->category || !$this->created_at) {
            return null;
        }

        $sla = $this->slaConfigForCategory();
        if (!$sla) {
            return null;
        }

        return $this->created_at->copy()->addDays($sla['max_days'])->endOfDay();
    }

    public function hasReviewEvidence(): bool
    {
        $hasUrl = filled($this->task_url) && $this->task_url !== '-';

        if ($hasUrl) {
            return true;
        }

        if ($this->relationLoaded('documents')) {
            return $this->documents->isNotEmpty();
        }

        return $this->documents()->exists();
    }

    protected function slaConfigForCategory(): ?array
    {
        if (!$this->category) {
            return null;
        }

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

        return $slaConfigs[$this->category] ?? null;
    }
}
