<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $address
 * @property string|null $city
 * @property string|null $type
 * @property string|null $pic_name
 * @property string|null $pic_phone
 * @property bool $is_active
 */
class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'city',
        'type',
        'pic_name',
        'pic_phone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the tasks associated with the client.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
