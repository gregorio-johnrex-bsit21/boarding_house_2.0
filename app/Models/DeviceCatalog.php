<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DeviceCatalog extends Model
{
    protected $table = 'device_catalog';

    protected $fillable = ['name', 'note', 'available', 'reason', 'sort_order', 'total_units'];

    protected function casts(): array
    {
        return ['available' => 'boolean', 'total_units' => 'integer'];
    }

    public function declarations(): BelongsToMany
    {
        return $this->belongsToMany(
            DeviceDeclaration::class,
            'device_declaration_catalog',
            'device_catalog_id',
            'device_declaration_id'
        )->withTimestamps();
    }

    public function unitsInUse(): int
    {
        if (array_key_exists('active_declarations_count', $this->attributes)) {
            return (int) $this->active_declarations_count;
        }

        return $this->declarations()
            ->whereIn('device_declarations.status', ['pending', 'approved'])
            ->count();
    }

    public function unitsLeft(): ?int
    {
        if (! $this->available) {
            return 0;
        }

        if ($this->total_units === null) {
            return null;
        }

        return max(0, $this->total_units - $this->unitsInUse());
    }
}