<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOperatorProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->isCompanyOwner();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // بيانات الوحدة
            'unit_number' => ['required', 'string', 'max:255'],
            'unit_code' => ['nullable', 'string', 'max:255'],
            'unit_name' => ['required', 'string', 'max:255'],

            // الموقع
            'governorate' => ['required', 'integer', 'in:10,20,30,40'],
            'city' => ['required', 'string', 'max:255'],
            'detailed_address' => ['required', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],

            // الملكية والتشغيل
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_id_number' => ['nullable', 'string', 'max:255'],
            'operation_entity' => ['required', 'string', 'in:same_owner,other_party'],
            'operator_id_number' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'phone_alt' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],

            // القدرة والقدرات الفنية
            'total_capacity' => ['nullable', 'numeric', 'min:0'],
            'generators_count' => ['nullable', 'integer', 'min:0'],
            'synchronization_available' => ['nullable', 'boolean'],
            'max_synchronization_capacity' => ['nullable', 'numeric', 'min:0'],

            // المستفيدون والبيئة
            'beneficiaries_count' => ['nullable', 'integer', 'min:0'],
            'beneficiaries_description' => ['nullable', 'string'],
            'environmental_compliance_status' => ['nullable', 'string', 'in:compliant,under_monitoring,under_evaluation,non_compliant'],

            // الحالة العامة
            'status' => ['required', 'string', 'in:active,inactive'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'unit_number.required' => 'رقم الوحدة مطلوب.',
            'unit_name.required' => 'اسم الوحدة مطلوب.',
            'governorate.required' => 'المحافظة مطلوبة.',
            'governorate.in' => 'المحافظة المحددة غير صحيحة.',
            'city.required' => 'المدينة مطلوبة.',
            'detailed_address.required' => 'العنوان التفصيلي مطلوب.',
            'latitude.required' => 'خط العرض مطلوب.',
            'latitude.numeric' => 'خط العرض يجب أن يكون رقماً.',
            'latitude.between' => 'خط العرض يجب أن يكون بين -90 و 90.',
            'longitude.required' => 'خط الطول مطلوب.',
            'longitude.numeric' => 'خط الطول يجب أن يكون رقماً.',
            'longitude.between' => 'خط الطول يجب أن يكون بين -180 و 180.',
            'owner_name.required' => 'اسم المالك مطلوب.',
            'operation_entity.required' => 'جهة التشغيل مطلوبة.',
            'operation_entity.in' => 'جهة التشغيل المحددة غير صحيحة.',
            'operator_id_number.required' => 'رقم هوية المشغل مطلوب.',
            'email.email' => 'البريد الإلكتروني غير صحيح.',
            'status.required' => 'حالة الوحدة مطلوبة.',
            'status.in' => 'حالة الوحدة المحددة غير صحيحة.',
            'environmental_compliance_status.in' => 'حالة الامتثال البيئي المحددة غير صحيحة.',
        ];
    }
}
