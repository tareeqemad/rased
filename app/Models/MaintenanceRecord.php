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
        'start_time',
        'end_time',
        'technician_name',
        'work_performed',
        'downtime_hours',
        'parts_cost',
        'labor_hours',
        'labor_rate_per_hour',
        'maintenance_cost',
    ];

    protected function casts(): array
    {
        return [
            'maintenance_date' => 'date',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'downtime_hours' => 'decimal:2',
            'parts_cost' => 'decimal:2',
            'labor_hours' => 'decimal:2',
            'labor_rate_per_hour' => 'decimal:2',
            'maintenance_cost' => 'decimal:2',
        ];
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(Generator::class);
    }
}
