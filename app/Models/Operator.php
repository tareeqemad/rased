<?php

namespace App\Models;

use App\Governorate;
use App\Models\ConstantDetail;
use App\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Operator extends Model
{
    use SoftDeletes;

    protected $table = 'operators';

    protected $fillable = [
        'name',
        'owner_id',
        'owner_name',
        'owner_id_number',
        'operator_id_number',
        'status',
        'profile_completed',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'total_capacity' => 'decimal:2',
            'max_synchronization_capacity' => 'decimal:2',
            'profile_completed' => 'boolean',
            'synchronization_available' => 'boolean',
            'governorate' => Governorate::class,
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'operator_user')
            ->withTimestamps();
    }

    /**
     * الموظفين + الفنيين التابعين لهذا المشغل
     */
    public function staff(): BelongsToMany
    {
        return $this->users()->whereIn('role', [Role::Employee, Role::Technician]);
    }

    /**
     * وحدات التوليد التابعة لهذا المشغل
     */
    public function generationUnits(): HasMany
    {
        return $this->hasMany(GenerationUnit::class);
    }

    /**
     * المولدات التابعة لهذا المشغل (عبر وحدات التوليد)
     */
    public function generators(): HasManyThrough
    {
        return $this->hasManyThrough(Generator::class, GenerationUnit::class);
    }

    public function operationLogs(): HasMany
    {
        return $this->hasMany(OperationLog::class);
    }

    public function complianceSafeties(): HasMany
    {
        return $this->hasMany(ComplianceSafety::class);
    }

    public function electricityTariffPrices(): HasMany
    {
        return $this->hasMany(ElectricityTariffPrice::class);
    }

    public function cityDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'city_id');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function isProfileComplete(): bool
    {
        return $this->profile_completed && !empty($this->name);
    }

    public function getGovernorateDetails(): ?array
    {
        return $this->governorate?->details();
    }

    public function getGovernorateLabel(): ?string
    {
        return $this->governorate?->label();
    }

    public function getGovernorateCode(): ?string
    {
        return $this->governorate?->code();
    }

    /**
     * توليد رقم الوحدة التالي (001, 002, إلخ) حسب المحافظة والمدينة
     */
    public static function getNextUnitNumber(?Governorate $governorate, ?int $cityId = null): string
    {
        if (!$governorate || !$cityId) {
            return '001';
        }

        // البحث عن آخر رقم وحدة في نفس المحافظة والمدينة
        $lastUnit = static::where('governorate', $governorate)
            ->where('city_id', $cityId)
            ->whereNotNull('unit_number')
            ->orderByRaw('CAST(unit_number AS UNSIGNED) DESC')
            ->first();

        if ($lastUnit && $lastUnit->unit_number) {
            $lastNumber = (int) $lastUnit->unit_number;
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * توليد كود الوحدة بالصيغة GU-PP-CC-NNN
     * حيث:
     * GU: ثابت (GENERATION UNIT)
     * PP: ترميز المحافظة
     * CC: ترميز المدينة
     * NNN: رقم الوحدة (001, 002, إلخ)
     */
    public static function generateUnitCode(?Governorate $governorate, ?int $cityId, ?string $unitNumber = null): ?string
    {
        if (!$governorate || !$cityId) {
            return null;
        }

        // الحصول على ترميز المحافظة
        $governorateCode = $governorate->code();
        
        // الحصول على ترميز المدينة من الثوابت
        $cityDetail = ConstantDetail::find($cityId);
        if (!$cityDetail || !$cityDetail->code) {
            return null;
        }
        
        $cityCode = $cityDetail->code;
        
        // استخدام رقم الوحدة الموجود أو توليد واحد جديد
        if (!$unitNumber) {
            $unitNumber = self::getNextUnitNumber($governorate, $cityId);
        }

        return "GU-{$governorateCode}-{$cityCode}-{$unitNumber}";
    }

    public function getMissingFields(): array
    {
        $missing = [];

        if (empty($this->name)) $missing[] = 'اسم المشغل';

        return $missing;
    }

    /**
     * الحصول على اسم المدينة من الثوابت
     */
    public function getCityName(): ?string
    {
        return $this->cityDetail?->label ?? $this->city;
    }
}
