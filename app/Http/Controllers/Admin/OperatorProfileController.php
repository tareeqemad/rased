<?php

namespace App\Http\Controllers\Admin;

use App\Governorate;
use App\Helpers\ConstantsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOperatorProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperatorProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = auth()->user();

        if (! $user->isCompanyOwner()) {
            abort(403);
        }

        $operator = $user->ownedOperators()->first();
        if (! $operator) {
            abort(404, 'المشغل غير موجود');
        }

        $missing = $operator->getMissingFields();

        // جلب المحافظات من الثوابت (رقم ثابت المحافظات = 1)
        $governorates = ConstantsHelper::get(1);
        
        // جلب المدن إذا كانت المحافظة محددة
        $cities = collect();
        $selectedGovernorateCode = null;
        
        if ($operator->governorate) {
            $selectedGovernorateCode = $operator->governorate->code();
            // البحث عن المحافظة في الثوابت
            $governorateDetail = ConstantsHelper::findByCode(1, $selectedGovernorateCode);
            if ($governorateDetail) {
                $cities = ConstantsHelper::getCitiesByGovernorate($governorateDetail->id);
            }
        }

        return view('admin.operators.profile', compact('operator', 'missing', 'governorates', 'cities', 'selectedGovernorateCode'));
    }

    public function update(UpdateOperatorProfileRequest $request): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        if (! $user->isCompanyOwner()) {
            abort(403);
        }

        $operator = $user->ownedOperators()->first();
        if (! $operator) {
            abort(404, 'المشغل غير موجود');
        }

        $data = $request->validated();

        // تحويل governorate من code إلى enum value
        $governorateEnum = null;
        if (isset($data['governorate'])) {
            $governorateDetail = \App\Models\ConstantDetail::whereHas('master', function($q) {
                $q->where('constant_number', 1);
            })->where('code', $data['governorate'])->first();
            
            if ($governorateDetail && $governorateDetail->value) {
                $governorateEnum = Governorate::fromValue((int) $governorateDetail->value);
                $data['governorate'] = $governorateEnum;
            }
        }

        // توليد رقم الوحدة وكود الوحدة تلقائياً إذا تم تحديد المحافظة والمدينة
        if ($governorateEnum && isset($data['city_id'])) {
            // توليد رقم الوحدة إذا لم يكن موجوداً
            if (empty($data['unit_number']) || $operator->governorate != $governorateEnum || $operator->city_id != $data['city_id']) {
                $data['unit_number'] = \App\Models\Operator::getNextUnitNumber($governorateEnum, $data['city_id']);
            }
            
            // توليد كود الوحدة
            $data['unit_code'] = \App\Models\Operator::generateUnitCode($governorateEnum, $data['city_id'], $data['unit_number']);
        }

        // boolean
        if (isset($data['synchronization_available'])) {
            $data['synchronization_available'] = (bool) $data['synchronization_available'];
        }

        $data['profile_completed'] = true;

        $operator->update($data);

        $missing = $operator->fresh()->getMissingFields();

        $msg = empty($missing)
            ? 'تم حفظ بيانات المشغل بنجاح ✅'
            : 'تم الحفظ، لكن ما زالت هناك حقول ناقصة.';

        // AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'profile_completed' => empty($missing),
                'missing' => $missing,
                'operator' => [
                    'unit_name' => $operator->unit_name,
                    'unit_number' => $operator->unit_number,
                    'unit_code' => $operator->unit_code,
                    'owner_name' => $operator->owner_name,
                    'governorate_code' => $operator->governorate?->code(),
                    'city_id' => $operator->city_id,
                    'phone' => $operator->phone,
                    'phone_alt' => $operator->phone_alt,
                    'email' => $operator->email,
                    'total_capacity' => $operator->total_capacity,
                    'generators_count' => $operator->generators_count,
                    'synchronization_available' => $operator->synchronization_available,
                    'max_synchronization_capacity' => $operator->max_synchronization_capacity,
                    'beneficiaries_count' => $operator->beneficiaries_count,
                    'beneficiaries_description' => $operator->beneficiaries_description,
                    'environmental_compliance_status' => $operator->environmental_compliance_status,
                    'status' => $operator->status,
                ],
            ]);
        }

        return redirect()->route('admin.dashboard')->with('success', $msg);
    }
}
