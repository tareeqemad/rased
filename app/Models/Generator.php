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
        'ideal_fuel_efficiency',
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
            'ideal_fuel_efficiency' => 'decimal:3',
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

    /**
     * توليد رقم المولد التالي بناءً على unit_code للمشغل
     * الصيغة: {unit_code}-GXX (حيث XX من 01 إلى 99)
     * مثال: GU-MD-DB-001-G01, GU-MD-DB-001-G02
     */
    public static function getNextGeneratorNumber(?int $operatorId = null): ?string
    {
        if (!$operatorId) {
            return null;
        }

        $operator = \App\Models\Operator::find($operatorId);
        if (!$operator || !$operator->unit_code) {
            return null;
        }

        $unitCode = $operator->unit_code;
        $prefix = $unitCode . '-G';

        // البحث عن آخر رقم مولد في نفس المشغل
        $lastGenerator = static::where('operator_id', $operatorId)
            ->whereNotNull('generator_number')
            ->where('generator_number', 'like', $prefix . '%')
            ->get()
            ->map(function ($gen) use ($prefix) {
                // استخراج الرقم من generator_number
                $numberPart = substr($gen->generator_number, strlen($prefix));
                return (int) $numberPart;
            })
            ->filter()
            ->max();

        if ($lastGenerator !== null) {
            $nextNumber = $lastGenerator + 1;
        } else {
            $nextNumber = 1;
        }

        // التأكد من أن الرقم لا يتجاوز 99
        if ($nextNumber > 99) {
            return null; // تم الوصول إلى الحد الأقصى
        }

        return $prefix . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
    }
}
