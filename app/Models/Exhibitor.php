<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Notifications\NewExhibitorNotification;
use Illuminate\Support\Facades\Notification;

class Exhibitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name', 'contact_name', 'email', 'phone', 'stand_number',
        'area_sqm', 'authorized_badges',
    ];

    protected function casts(): array
    {
        return ['area_sqm' => 'decimal:2', 'authorized_badges' => 'integer'];
    }

    public function commercial(): BelongsTo
    {
        return $this->belongsTo(User::class, 'commercial_id');
    }

    protected static function booted(): void
   {
    static::created(function (Exhibitor $exhibitor): void {
        $admins = User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->get();

        Notification::send(
            $admins,
            new NewExhibitorNotification($exhibitor)
        );
    });
   }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function badges(): HasMany { return $this->hasMany(Badge::class); }
}
