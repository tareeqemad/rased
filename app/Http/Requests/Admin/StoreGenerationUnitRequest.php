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
        return [
            'operator_id' => ['nullable', 'exists:operators,id'],
            'name' => ['required', 'string', 'max:255'],
            'generators_count' => ['required', 'integer', 'min:1', 'max:99'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],

            // الملكية والتشغيل
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_id_number' => ['nullable', 'string', 'max:255'],
            'operation_entity' => ['required', 'string', Rule::in(['same_owner', 'other_party'])],
            'operator_id_number' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'phone_alt' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],

            // الموقع
            'governorate' => ['required', 'string'],
            'city_id' => ['required', 'exists:constant_details,id'],
            'detailed_address' => ['required', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],

            // القدرات الفنية
            'total_capacity' => ['nullable', 'numeric', 'min:0'],
            'synchronization_available' => ['nullable', 'boolean'],
            'max_synchronization_capacity' => ['nullable', 'numeric', 'min:0'],

            // المستفيدون والبيئة
            'beneficiaries_count' => ['nullable', 'integer', 'min:0'],
            'beneficiaries_description' => ['nullable', 'string'],
            'environmental_compliance_status' => ['nullable', 'string', Rule::in(['compliant', 'under_monitoring', 'under_evaluation', 'non_compliant'])],
            
            // خزانات الوقود
            'external_fuel_tank' => ['nullable', 'boolean'],
            'fuel_tanks_count' => ['nullable', 'integer', 'min:0', 'max:10'],
            'fuel_tanks' => ['nullable', 'array'],
            'fuel_tanks.*.capacity' => ['required_with:fuel_tanks', 'numeric', 'min:0', 'max:10000'],
            'fuel_tanks.*.location' => ['required_with:fuel_tanks'],
            'fuel_tanks.*.filtration_system_available' => ['nullable', 'boolean'],
            'fuel_tanks.*.condition' => ['nullable', 'string'],
            'fuel_tanks.*.material' => ['nullable'],
            'fuel_tanks.*.usage' => ['nullable'],
            'fuel_tanks.*.measurement_method' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم وحدة التوليد مطلوب.',
            'generators_count.required' => 'عدد المولدات المطلوب.',
            'generators_count.min' => 'يجب أن يكون عدد المولدات على الأقل 1.',
            'generators_count.max' => 'يجب ألا يتجاوز عدد المولدات 99.',
            'status.required' => 'حالة الوحدة مطلوبة.',
            'owner_name.required' => 'اسم المالك مطلوب.',
            'operation_entity.required' => 'جهة التشغيل مطلوبة.',
            'operator_id_number.required' => 'رقم هوية المشغل مطلوب.',
            'governorate.required' => 'المحافظة مطلوبة.',
            'city_id.required' => 'المدينة مطلوبة.',
            'city_id.exists' => 'المدينة المحددة غير صحيحة.',
            'detailed_address.required' => 'العنوان التفصيلي مطلوب.',
            'latitude.required' => 'خط العرض مطلوب.',
            'longitude.required' => 'خط الطول مطلوب.',
        ];
    }
}

