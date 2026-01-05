<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ConstantDetail;

class Generator extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'generator_number',
        'operator_id',
        'generation_unit_id',
        'description',
        'status_id', // ID من constant_details - ثابت Master رقم 3 (حالة المولد)
        // المواصفات الفنية
        'capacity_kva',
        'power_factor',
        'voltage',
        'frequency',
        'engine_type_id', // ID من constant_details - ثابت Master رقم 4 (نوع المحرك)
        // التشغيل والوقود
        'manufacturing_year',
        'injection_system_id', // ID من constant_details - ثابت Master رقم 5 (نظام الحقن)
        'fuel_consumption_rate',
        'ideal_fuel_efficiency',
        'internal_tank_capacity',
        'measurement_indicator_id', // ID من constant_details - ثابت Master رقم 6 (مؤشر القياس)
        // الحالة الفنية والتوثيق
        'technical_condition_id', // ID من constant_details - ثابت Master رقم 7 (الحالة الفنية)
        'last_major_maintenance_date',
        'engine_data_plate_image',
        'generator_data_plate_image',
        // نظام التحكم
        'control_panel_available',
        'control_panel_type_id', // ID من constant_details - ثابت Master رقم 8 (نوع لوحة التحكم)
        'control_panel_status_id', // ID من constant_details - ثابت Master رقم 9 (حالة لوحة التحكم)
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

    public function generationUnit(): BelongsTo
    {
        return $this->belongsTo(GenerationUnit::class);
    }

    // Relationships للثوابت - ثابت Master رقم 3 (حالة المولد)
    public function statusDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'status_id');
    }

    // Relationships للثوابت - ثابت Master رقم 4 (نوع المحرك)
    public function engineTypeDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'engine_type_id');
    }

    // Relationships للثوابت - ثابت Master رقم 5 (نظام الحقن)
    public function injectionSystemDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'injection_system_id');
    }

    // Relationships للثوابت - ثابت Master رقم 6 (مؤشر القياس)
    public function measurementIndicatorDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'measurement_indicator_id');
    }

    // Relationships للثوابت - ثابت Master رقم 7 (الحالة الفنية)
    public function technicalConditionDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'technical_condition_id');
    }

    // Relationships للثوابت - ثابت Master رقم 8 (نوع لوحة التحكم)
    public function controlPanelTypeDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'control_panel_type_id');
    }

    // Relationships للثوابت - ثابت Master رقم 9 (حالة لوحة التحكم)
    public function controlPanelStatusDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'control_panel_status_id');
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
     * توليد كود المولد التالي بناءً على unit_code لوحدة التوليد
     * الصيغة الكاملة: GU-PP-CC-NNN-GXX
     * حيث:
     * - GU-PP-CC-NNN: كود وحدة التوليد (Generation Unit Code)
     * - GXX: رقم المولد داخل الوحدة (G01 - G99)
     * 
     * مثال: GU-MD-DR-001-G01, GU-MD-DR-001-G02
     * 
     * @param int|null $generationUnitId معرف وحدة التوليد
     * @return string|null كود المولد أو null في حالة الفشل
     */
    public static function getNextGeneratorNumber(?int $generationUnitId = null): ?string
    {
        if (!$generationUnitId) {
            return null;
        }

        $generationUnit = \App\Models\GenerationUnit::find($generationUnitId);
        if (!$generationUnit || !$generationUnit->unit_code) {
            return null;
        }

        $unitCode = $generationUnit->unit_code;
        $prefix = $unitCode . '-G';

        // البحث عن آخر رقم مولد في نفس وحدة التوليد
        $lastGenerator = static::where('generation_unit_id', $generationUnitId)
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
