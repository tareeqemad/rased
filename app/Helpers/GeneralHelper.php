<?php

namespace App\Helpers;

use App\Models\Operator;
use Illuminate\Support\Collection;

class GeneralHelper
{
    /**
     * الحصول على المشغلين التابعين لمحافظة معينة
     *
     * @param int $governorateValue رقم المحافظة (10, 20, 30, 40)
     * @param bool $activeOnly إذا كان true، يرجع فقط المشغلين النشطين
     * @return Collection
     */
    public static function getOperatorsByGovernorate(int $governorateValue, bool $activeOnly = true): Collection
    {
        $query = Operator::where('governorate', $governorateValue);

        if ($activeOnly) {
            $query->where('status', 'active');
        }

        return $query->orderBy('name')->get();
    }

    /**
     * الحصول على المشغلين التابعين لمحافظة معينة مع معلومات أساسية فقط
     *
     * @param int $governorateValue رقم المحافظة
     * @param bool $activeOnly إذا كان true، يرجع فقط المشغلين النشطين
     * @return Collection
     */
    public static function getOperatorsByGovernorateSimple(int $governorateValue, bool $activeOnly = true): Collection
    {
        $query = Operator::select('id', 'name', 'governorate', 'city', 'unit_number', 'status')
            ->where('governorate', $governorateValue);

        if ($activeOnly) {
            $query->where('status', 'active');
        }

        return $query->orderBy('name')->get();
    }
}






