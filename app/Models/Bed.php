<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    public const STATUSES = ['available', 'occupied', 'reserved', 'ready'];
    protected $fillable = ['room_id', 'bed_no', 'bunk', 'label', 'mesh_id', 'status', 'monthly_rate'];

    public function room() { return $this->belongsTo(Room::class); }
    public function tenant() { return $this->hasOne(User::class); }

    /** Effective monthly rate: bed override, else the room rate. */
    public function getRateAttribute(): float
    {
        return (float) ($this->monthly_rate ?? $this->room->monthly_rate);
    }
    public function getFullNameAttribute(): string
    {
        return "Room {$this->room->number} · {$this->label}";
    }
}
