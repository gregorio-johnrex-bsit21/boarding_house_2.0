<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UtilityBill extends Model
{
    protected $fillable = ['period', 'type', 'total_amount', 'created_by'];

    protected function casts(): array
    {
        return [
            'period' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
