<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $category
 * @property int $max_days
 * @property int $warning_days
 */
class SlaConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'max_days',
        'warning_days',
    ];

    protected $casts = [
        'max_days' => 'integer',
        'warning_days' => 'integer',
    ];
}
