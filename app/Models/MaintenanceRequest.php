<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    public const CATEGORIES = ['pipes' => 'Pipes / Plumbing', 'light' => 'Light / Electrical', 'lock' => 'Lock / Door',
        'furniture' => 'Furniture', 'wifi' => 'Wi-Fi', 'other' => 'Other'];
    public const STATUSES = ['pending', 'in_progress', 'resolved'];

    protected $fillable = ['tenant_id', 'bed_id', 'issue_category', 'urgency', 'description', 'image', 'status', 'resolved_at', 'cost'];
    protected $casts = ['resolved_at' => 'datetime'];

    public function tenant() { return $this->belongsTo(User::class, 'tenant_id'); }
    public function bed() { return $this->belongsTo(Bed::class); }
}
