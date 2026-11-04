<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['user_id', 'bed_id', 'period', 'period_start', 'period_end', 'amount', 'payment_method', 'gcash_reference', 'tenant_signature',
        'receipt_image', 'status', 'verified_by', 'verified_at', 'note'];
    protected $casts = ['period' => 'date', 'period_start' => 'date', 'period_end' => 'date', 'verified_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function bed() { return $this->belongsTo(Bed::class); }
}
