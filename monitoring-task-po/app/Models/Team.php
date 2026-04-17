<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',         // 'product' atau 'engineer'
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /** Task di mana tim ini sebagai Product Owner */
    public function productTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'product_id');
    }

    /** Task di mana tim ini sebagai Engineer */
    public function engineerTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'engineer_id');
    }

    // ==========================================
    // SCOPES (Query filter yang bisa di-reuse)
    // ==========================================

    /** Hanya tim bertipe product: Team::product()->get() */
    public function scopeProduct($query)
    {
        return $query->where('type', 'product');
    }

    /** Hanya tim bertipe engineer: Team::engineer()->get() */
    public function scopeEngineer($query)
    {
        return $query->where('type', 'engineer');
    }

    /** Hanya tim aktif: Team::active()->get() */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
