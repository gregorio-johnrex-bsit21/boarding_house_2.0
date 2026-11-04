<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'phone', 'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_shared', 'notification_preferences', 'bed_id', 'move_in_date', 'due_day'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'move_in_date' => 'date',
            'emergency_contact_shared' => 'boolean',
            'notification_preferences' => 'array',
        ];
    }

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function bed(): BelongsTo { return $this->belongsTo(Bed::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function maintenanceRequests(): HasMany { return $this->hasMany(MaintenanceRequest::class, 'tenant_id'); }
    public function deviceDeclarations(): HasMany { return $this->hasMany(TenantDeviceDeclaration::class); }
    public function reviewedDeviceDeclarations(): HasMany { return $this->hasMany(DeviceDeclaration::class); }
    public function reviewedDeclarations(): HasMany { return $this->hasMany(DeviceDeclaration::class, 'reviewed_by'); }
    public function createdUtilityBills(): HasMany { return $this->hasMany(UtilityBill::class, 'created_by'); }
}
