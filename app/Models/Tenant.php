<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
<<<<<<< HEAD
        'user_id', // 🌟 Make sure user_id is mass-fillable to link accounts
=======
>>>>>>> 0c223bd492001434a0baf1bdce431350fcbb7e5b
        'name',
        'phone',
        'room_id',
        'status',
    ];
<<<<<<< HEAD

    /**
     * 🌟 CONNECTION TO PAYMENTS: A tenant can submit multiple payment logs
     */
    public function payments()
    {
        // Links the tenant to their payment history data rows
        return $this->hasMany(Payment::class, 'tenant_id');
    }

    /**
     * 🌟 CONNECTION TO USER: Tell Tenant it belongs to a User account
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 🌟 CONNECTION TO ROOM: Tell Tenant it belongs to a specific Room
     */
    public function room()
    {
        // Links room_id column on this table to the Room model's primary key
        return $this->belongsTo(Room::class, 'room_id');
    }
=======
>>>>>>> 0c223bd492001434a0baf1bdce431350fcbb7e5b
}