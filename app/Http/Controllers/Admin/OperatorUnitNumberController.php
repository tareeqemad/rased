<?php

namespace App\Http\Controllers\Admin;

use App\Governorate;
use App\Http\Controllers\Controller;
use App\Models\Operator;
use Illuminate\Http\JsonResponse;

class OperatorUnitNumberController extends Controller
{
    /**
     * الحصول على رقم الوحدة التالي للمحافظة
     */
    public function getNextUnitNumber(int $governorate): JsonResponse
    {
        $governorateEnum = Governorate::fromValue($governorate);

        if (! $governorateEnum) {
            return response()->json([
                'success' => false,
                'message' => 'المحافظة غير صحيحة',
            ], 400);
        }

        $unitNumber = Operator::getNextUnitNumber($governorateEnum);

        return response()->json([
            'success' => true,
            'unit_number' => $unitNumber,
            'governorate_code' => $governorateEnum->code(),
        ]);
    }
}
