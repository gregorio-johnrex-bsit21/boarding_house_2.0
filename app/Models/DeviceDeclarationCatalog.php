<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class DeviceDeclarationCatalog extends Pivot
{
    protected $table = 'device_declaration_catalog';

    protected $fillable = ['device_declaration_id', 'device_catalog_id'];
}
