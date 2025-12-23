<?php

namespace App\Models;

use App\Governorate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Operator extends Model
{
    use SoftDeletes;

    protected $table = 'operators';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'phone_alt',
        'address',
        'owner_id',
        'unit_number',
        'unit_code',
        'unit_name',
        'governorate',
        'city',
        'detailed_address',
        'latitude',
        'longitude',
        'total_capacity',
        'generators_count',
        'synchronization_available',
        'max_synchronization_capacity',
        'owner_name',
        'owner_id_number',
        'operation_entity',
        'operator_id_number',
        'beneficiaries_count',
        'beneficiaries_description',
        'environmental_compliance_status',
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
        return $this->belongsToMany(User::class, 'operator_user');
    }

    public function generators(): HasMany
    {
        return $this->hasMany(Generator::class);
    }

    public function operationLogs(): HasMany
    {
        return $this->hasMany(OperationLog::class);
    }

    public function complianceSafeties(): HasMany
    {
        return $this->hasMany(ComplianceSafety::class);
    }

    /**
     * التحقق من اكتمال بيانات الملف الشخصي
     */
    public function isProfileComplete(): bool
    {
        return $this->profile_completed &&
            ! empty($this->unit_number) &&
            ! empty($this->unit_name) &&
            ! is_null($this->governorate) &&
            ! empty($this->city) &&
            ! empty($this->detailed_address) &&
            ! is_null($this->latitude) &&
            ! is_null($this->longitude) &&
            ! empty($this->owner_name) &&
            ! empty($this->operator_id_number) &&
            ! empty($this->operation_entity) &&
            ! is_null($this->status);
    }

    /**
     * الحصول على تفاصيل المحافظة
     */
    public function getGovernorateDetails(): ?array
    {
        return $this->governorate?->details();
    }

    /**
     * الحصول على اسم المحافظة
     */
    public function getGovernorateLabel(): ?string
    {
        return $this->governorate?->label();
    }

    /**
     * الحصول على ترميز المحافظة
     */
    public function getGovernorateCode(): ?string
    {
        return $this->governorate?->code();
    }

    /**
     * الحصول على آخر رقم وحدة للمحافظة
     */
    public static function getNextUnitNumber(?Governorate $governorate): string
    {
        if (! $governorate) {
            return 'OP-001';
        }

        $code = $governorate->code();

        // البحث عن آخر رقم وحدة لهذه المحافظة
        $lastUnit = static::where('governorate', $governorate)
            ->whereNotNull('unit_number')
            ->where('unit_number', 'like', $code.'-%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(unit_number, "-", -1) AS UNSIGNED) DESC')
            ->first();

        if ($lastUnit && $lastUnit->unit_number) {
            // استخراج الرقم من آخر وحدة
            $parts = explode('-', $lastUnit->unit_number);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $code.'-'.str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * الحصول على قائمة الحقول المفقودة
     */
    public function getMissingFields(): array
    {
        $missing = [];

        if (empty($this->unit_number)) {
            $missing[] = 'رقم الوحدة';
        }
        if (empty($this->unit_name)) {
            $missing[] = 'اسم الوحدة';
        }
        if (is_null($this->governorate)) {
            $missing[] = 'المحافظة';
        }
        if (empty($this->city)) {
            $missing[] = 'المدينة';
        }
        if (empty($this->detailed_address)) {
            $missing[] = 'العنوان التفصيلي';
        }
        if (is_null($this->latitude) || is_null($this->longitude)) {
            $missing[] = 'إحداثيات الموقع';
        }
        if (empty($this->owner_name)) {
            $missing[] = 'اسم المالك';
        }
        if (empty($this->operator_id_number)) {
            $missing[] = 'رقم هوية المشغل';
        }
        if (empty($this->operation_entity)) {
            $missing[] = 'جهة التشغيل';
        }
        if (is_null($this->status)) {
            $missing[] = 'حالة الوحدة';
        }

        return $missing;
    }
}
