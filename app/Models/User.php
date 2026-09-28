<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'phone', 'bed_id', 'move_in_date', 'due_day'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed', 'move_in_date' => 'date'];
    }

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function bed(): BelongsTo { return $this->belongsTo(Bed::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function maintenanceRequests(): HasMany { return $this->hasMany(MaintenanceRequest::class, 'tenant_id'); }
}
