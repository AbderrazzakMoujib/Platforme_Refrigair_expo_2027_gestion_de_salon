<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipment';

    protected $fillable = [
        'name',
        'category',
        'description',
        'price',
        'quantity_available',
        'image_path',
        'is_active',
        'commercial_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'quantity_available' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function commercial(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'commercial_id'
        );
    }
}
