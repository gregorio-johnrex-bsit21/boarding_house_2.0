<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceTicket extends Model
{
    use HasFactory;

    // Directs the model to link up with your migrated table
    protected $table = 'maintenance_tickets';

    // Mass fillable fields required for form submissions
    protected $fillable = [
        'tenant_id',
        'category',
        'description',
        'status',
    ];

    /**
     * Get the tenant that submitted the maintenance ticket.
     */
    public function tenant()
    {
        // This links the 'tenant_id' in your tickets table to the Tenant model
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}