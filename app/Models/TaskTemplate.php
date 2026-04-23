<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'product_id',
        'client_id',
        'engineer_id',
        'category',
        'priority',
        'description',
        'created_by',
    ];

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
}
