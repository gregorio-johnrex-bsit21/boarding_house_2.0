<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['user_id', 'bed_id', 'period', 'amount', 'payment_method', 'gcash_reference',
        'receipt_image', 'status', 'verified_by', 'verified_at', 'note'];
    protected $casts = ['period' => 'date', 'verified_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function bed() { return $this->belongsTo(Bed::class); }
}
