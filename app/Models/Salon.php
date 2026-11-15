<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salon extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'edition',
        'city',
        'venue',
        'start_date',
        'end_date',
        'description',
        'status',
        'registration_open',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'registration_open' => 'boolean',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'En cours',
            'completed' => 'Terminé',
            'cancelled' => 'Annulé',
            default => 'Planifié',
        };
    }
}
