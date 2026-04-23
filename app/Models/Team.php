<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'phone',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Local Scope untuk mengambil tim yang aktif saja.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}