<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'max_days',
        'warning_days',
        'description',
        'is_active',
    ];

    protected $casts = [
        'max_days' => 'integer',
        'warning_days' => 'integer',
        'is_active' => 'boolean',
    ];
}
