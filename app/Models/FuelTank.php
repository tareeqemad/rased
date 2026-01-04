<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelTank extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'generator_id',
        'tank_code',
        'capacity',
        'location',
        'filtration_system_available',
        'condition',
        'material',
        'usage',
        'measurement_method',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'filtration_system_available' => 'boolean',
        ];
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(Generator::class);
    }

    /**
     * توليد كود خزان الوقود التالي بناءً على unit_code للمولد
     * الصيغة: {unit_code}-TXX (حيث XX من 01 إلى 99)
     * مثال: GU-MD-DB-001-T01, GU-MD-DB-001-T02
     */
    public static function getNextTankCode(?int $generatorId = null): ?string
    {
        if (!$generatorId) {
            return null;
        }

        $generator = Generator::with('operator')->find($generatorId);
        if (!$generator || !$generator->operator || !$generator->operator->unit_code) {
            return null;
        }

        $unitCode = $generator->operator->unit_code;
        $prefix = $unitCode . '-T';

        // البحث عن آخر رقم خزان في نفس المولد
        $lastTank = static::where('generator_id', $generatorId)
            ->whereNotNull('tank_code')
            ->where('tank_code', 'like', $prefix . '%')
            ->get()
            ->map(function ($tank) use ($prefix) {
                // استخراج الرقم من tank_code
                $numberPart = substr($tank->tank_code, strlen($prefix));
                return (int) $numberPart;
            })
            ->filter()
            ->max();

        if ($lastTank !== null) {
            $nextNumber = $lastTank + 1;
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
