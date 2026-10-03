<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Setting;

class Bed extends Model
{
    public const STATUSES = ['available', 'occupied', 'reserved', 'ready'];
    protected $fillable = ['room_id', 'bed_no', 'bunk', 'label', 'mesh_id', 'status', 'monthly_rate'];

    public function room() { return $this->belongsTo(Room::class); }
    public function tenant() { return $this->hasOne(User::class); }

    /** Effective monthly rate: bed override, bunk-specific setting, then default rent. */
    public function getRateAttribute(): float
    {
        if ($this->monthly_rate !== null) return (float) $this->monthly_rate;
        $key = $this->bunk === 'A' ? 'rent.top_bunk_rate' : 'rent.bottom_bunk_rate';
        $bunkRate = Setting::get($key);
        if ($bunkRate !== null && $bunkRate !== '') return (float) $bunkRate;
        return (float) Setting::get('rent.default_monthly_rate', 1500);
    }
    public function getFullNameAttribute(): string
    {
        return "Room {$this->room->number} · {$this->label}";
    }
}
