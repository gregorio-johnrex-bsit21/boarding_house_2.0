<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['mesh_room', 'number', 'capacity', 'monthly_rate'];
    public function beds() { return $this->hasMany(Bed::class); }
}
