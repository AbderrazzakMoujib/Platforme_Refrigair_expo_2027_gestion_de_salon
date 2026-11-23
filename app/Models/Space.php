<?php

namespace App\Models;

use App\Enums\SpaceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Space extends Model
{
    protected $fillable = [
        'name',
        'type',
        'capacity',
    ];

    protected function casts(): array
    {
        return [
            'type' => SpaceType::class,
            'capacity' => 'integer',
        ];
    }

    public function slots(): HasMany
    {
        return $this->hasMany(Slot::class);
    }
}
