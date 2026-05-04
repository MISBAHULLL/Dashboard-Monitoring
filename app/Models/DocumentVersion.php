<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $document_id
 * @property int $version_number
 * @property string|null $file_path
 * @property string|null $doc_url
 * @property int|null $file_size
 * @property string|null $notes
 * @property int|null $uploaded_by
 * @property Carbon|null $created_at
 */
class DocumentVersion extends Model
{
    use HasFactory;

    // Matikan updated_at karena versi dokumen bersifat read-only (tidak pernah diupdate)
    public const UPDATED_AT = null;

    protected $fillable = [
        'document_id',
        'version_number',
        'file_path',
        'doc_url',
        'file_size',
        'notes',
        'uploaded_by',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'file_size' => 'integer',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
