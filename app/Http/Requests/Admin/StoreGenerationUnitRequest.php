<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGenerationUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\GenerationUnit::class);
    }

    public function rules(): array
    {
        $user = $this->user();
        
        // Super Admin يجب أن يختار مشغل
        $operatorIdRule = ['nullable', 'exists:operators,id'];
        if ($user && $user->isSuperAdmin()) {
            $operatorIdRule = ['required', 'exists:operators,id'];
        }
        
        return [
            'operator_id' => $operatorIdRule,
            
            // الحقول الأساسية المطلوبة فقط
            'name' => ['required', 'string', 'max:255'],
            'governorate_id' => ['required', 'exists:constant_details,id'],
            'city_id' => ['required', 'exists:constant_details,id'],
            'detailed_address' => ['required', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],

            // باقي الحقول اختيارية (يمكن ملؤها لاحقاً)
            'generators_count' => ['nullable', 'integer', 'min:1', 'max:99'],
            'status_id' => ['nullable', 'exists:constant_details,id'],

            // الملكية والتشغيل
            'owner_name' => ['nullable', 'string', 'max:255'],
            'owner_id_number' => ['nullable', 'string', 'max:255'],
            'operation_entity_id' => ['nullable', 'exists:constant_details,id'],
            'operator_id_number' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'phone_alt' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],

            // القدرات الفنية
            'total_capacity' => ['nullable', 'numeric', 'min:0'],
            'synchronization_available_id' => ['nullable', 'exists:constant_details,id'],
            'max_synchronization_capacity' => ['nullable', 'numeric', 'min:0'],

            // المستفيدون والبيئة
            'beneficiaries_count' => ['nullable', 'integer', 'min:0'],
            'beneficiaries_description' => ['nullable', 'string'],
            'environmental_compliance_status_id' => ['nullable', 'exists:constant_details,id'],
            
            // خزانات الوقود
            'external_fuel_tank' => ['nullable', 'boolean'],
            'fuel_tanks_count' => ['nullable', 'integer', 'min:0', 'max:10'],
            'fuel_tanks' => ['nullable', 'array'],
            'fuel_tanks.*.capacity' => ['required_with:fuel_tanks', 'numeric', 'min:0', 'max:10000'],
            'fuel_tanks.*.location_id' => ['required_with:fuel_tanks', 'exists:constant_details,id'],
            'fuel_tanks.*.filtration_system_available' => ['nullable', 'boolean'],
            'fuel_tanks.*.condition' => ['nullable', 'string'],
            'fuel_tanks.*.material_id' => ['nullable', 'exists:constant_details,id'],
            'fuel_tanks.*.usage_id' => ['nullable', 'exists:constant_details,id'],
            'fuel_tanks.*.measurement_method_id' => ['nullable', 'exists:constant_details,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'operator_id.required' => 'يجب اختيار المشغل.',
            'operator_id.exists' => 'المشغل المحدد غير موجود.',
            'name.required' => 'اسم وحدة التوليد مطلوب.',
            'governorate_id.required' => 'المحافظة مطلوبة.',
            'governorate_id.exists' => 'المحافظة المحددة غير صحيحة.',
            'city_id.required' => 'المدينة مطلوبة.',
            'city_id.exists' => 'المدينة المحددة غير صحيحة.',
            'detailed_address.required' => 'العنوان التفصيلي مطلوب.',
            'latitude.required' => 'خط العرض مطلوب.',
            'longitude.required' => 'خط الطول مطلوب.',
            'generators_count.min' => 'يجب أن يكون عدد المولدات على الأقل 1.',
            'generators_count.max' => 'يجب ألا يتجاوز عدد المولدات 99.',
            'status_id.exists' => 'حالة الوحدة المحددة غير صحيحة.',
            'operation_entity_id.exists' => 'جهة التشغيل المحددة غير صحيحة.',
        ];
    }
}

