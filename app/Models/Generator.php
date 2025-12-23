<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Generator extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'generator_number',
        'operator_id',
        'description',
        'status',
        // المواصفات الفنية
        'capacity_kva',
        'power_factor',
        'voltage',
        'frequency',
        'engine_type',
        // التشغيل والوقود
        'manufacturing_year',
        'injection_system',
        'fuel_consumption_rate',
        'internal_tank_capacity',
        'measurement_indicator',
        // الحالة الفنية والتوثيق
        'technical_condition',
        'last_major_maintenance_date',
        'engine_data_plate_image',
        'generator_data_plate_image',
        // نظام التحكم
        'control_panel_available',
        'control_panel_type',
        'control_panel_status',
        'control_panel_image',
        'operating_hours',
        // خزانات الوقود
        'external_fuel_tank',
        'fuel_tanks_count',
    ];

    protected function casts(): array
    {
        return [
            'capacity_kva' => 'decimal:2',
            'power_factor' => 'decimal:2',
            'fuel_consumption_rate' => 'decimal:2',
            'control_panel_available' => 'boolean',
            'external_fuel_tank' => 'boolean',
            'last_major_maintenance_date' => 'date',
        ];
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    public function fuelTanks(): HasMany
    {
        return $this->hasMany(FuelTank::class)->orderBy('order');
    }

    public function operationLogs(): HasMany
    {
        return $this->hasMany(OperationLog::class);
    }

    public function fuelEfficiencies(): HasMany
    {
        return $this->hasMany(FuelEfficiency::class);
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class);
    }
}
