<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'title',
        'type',
        'doc_url',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'current_version',
        'created_by',
    ];

    protected $casts = [
        'current_version' => 'integer',
        'file_size' => 'integer',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

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
