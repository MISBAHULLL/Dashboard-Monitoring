<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $client_id
 * @property string $title
 * @property string $type
 * @property string|null $doc_url
 * @property string|null $file_path
 * @property string|null $file_name
 * @property string|null $mime_type
 * @property int|null $file_size
 * @property int $current_version
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'title',
        'type',
        'doc_url',
        'current_version',
        'created_by',
    ];

    protected $casts = [
        'current_version' => 'integer',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Many-to-many: 1 dokumen bisa terhubung ke banyak task, dengan kolom status di pivot
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'document_task')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class);
    }
}
