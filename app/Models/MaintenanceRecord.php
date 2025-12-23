<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'generator_id',
        'maintenance_type',
        'maintenance_date',
        'technician_name',
        'work_performed',
        'downtime_hours',
        'maintenance_cost',
    ];

    protected function casts(): array
    {
        return [
            'maintenance_date' => 'date',
            'downtime_hours' => 'decimal:2',
            'maintenance_cost' => 'decimal:2',
        ];
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(Generator::class);
    }
}
