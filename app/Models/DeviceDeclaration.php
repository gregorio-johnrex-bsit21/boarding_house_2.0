<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class DeviceDeclaration extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'note',
        'status',
        'monthly_fee',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'monthly_fee' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function catalogDevice(): HasOneThrough
    {
        return $this->hasOneThrough(
            DeviceCatalog::class,
            DeviceDeclarationCatalog::class,
            'device_declaration_id',
            'id',
            'id',
            'device_catalog_id'
        );
    }
}
