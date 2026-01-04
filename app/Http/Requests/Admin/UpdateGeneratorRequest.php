<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGeneratorRequest extends FormRequest
{
    public function authorize(): bool
    {
        $generator = $this->route('generator');

        return $this->user()->isSuperAdmin() || $this->user()->belongsToOperator($generator->operator);
    }

    public function rules(): array
    {
        $generator = $this->route('generator');

        return [
            'name' => ['required', 'string', 'max:255'],
            'generator_number' => ['required', 'string', 'max:255', Rule::unique('generators')->ignore($generator->id)],
            'operator_id' => ['required', 'exists:operators,id'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            // المواصفات الفنية
            'capacity_kva' => ['nullable', 'numeric', 'min:0'],
            'power_factor' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'voltage' => ['nullable', 'integer', 'min:0'],
            'frequency' => ['nullable', 'integer', 'min:0'],
            'engine_type' => ['nullable', 'string', Rule::in(['Perkins', 'Volvo', 'Caterpillar', 'DAF', 'MAN', 'SCAINA'])],
            // التشغيل والوقود
            'manufacturing_year' => ['nullable', 'integer', 'min:1900', 'max:'.date('Y')],
            'injection_system' => ['nullable', 'string', Rule::in(['عادي', 'كهربائي', 'هجين'])],
            'fuel_consumption_rate' => ['nullable', 'numeric', 'min:0'],
            'ideal_fuel_efficiency' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'internal_tank_capacity' => ['nullable', 'integer', 'min:0'],
            'measurement_indicator' => ['nullable', 'string', Rule::in(['غير متوفر', 'متوفر ويعمل', 'متوفر ولا يعمل'])],
            // الحالة الفنية والتوثيق
            'technical_condition' => ['nullable', 'string', Rule::in(['ممتازة', 'جيدة جدا', 'جيدة', 'متوسطة', 'سيئة'])],
            'last_major_maintenance_date' => ['nullable', 'date'],
            'engine_data_plate_image' => ['nullable', 'image', 'max:2048'],
            'generator_data_plate_image' => ['nullable', 'image', 'max:2048'],
            // نظام التحكم
            'control_panel_available' => ['nullable', 'boolean'],
            'control_panel_type' => ['nullable', 'string', Rule::in(['Deep Sea', 'ComAp', 'Datakom', 'Analog'])],
            'control_panel_status' => ['nullable', 'string', Rule::in(['تعمل', 'لا تعمل'])],
            'control_panel_image' => ['nullable', 'image', 'max:2048'],
            'operating_hours' => ['nullable', 'integer', 'min:0'],
            // خزانات الوقود
            'external_fuel_tank' => ['nullable', 'boolean'],
            'fuel_tanks_count' => ['nullable', 'integer', 'min:0', 'max:10'],
            'fuel_tanks' => ['nullable', 'array'],
            'fuel_tanks.*.capacity' => ['required_with:fuel_tanks', 'integer', 'min:0', 'max:10000'],
            'fuel_tanks.*.location' => ['required_with:fuel_tanks', 'string', Rule::in(['ارضي', 'علوي', 'تحت الارض'])],
            'fuel_tanks.*.filtration_system_available' => ['nullable', 'boolean'],
            'fuel_tanks.*.condition' => ['nullable', 'string'],
            'fuel_tanks.*.material' => ['nullable', 'string', Rule::in(['حديد', 'بلاستيك', 'بلاستيك مقوي', 'فايبر'])],
            'fuel_tanks.*.usage' => ['nullable', 'string', Rule::in(['مركزي', 'احتياطي'])],
            'fuel_tanks.*.measurement_method' => ['nullable', 'string', Rule::in(['سيخ مدرج', 'ساعه ميكانيكية', 'حساس الكتروني', 'خرطوم شفاف'])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم المولد مطلوب.',
            'generator_number.required' => 'رقم المولد مطلوب.',
            'generator_number.unique' => 'رقم المولد هذا مستخدم بالفعل.',
            'operator_id.required' => 'المشغل مطلوب.',
            'operator_id.exists' => 'المشغل المحدد غير موجود.',
            'status.required' => 'حالة المولد مطلوبة.',
            'status.in' => 'حالة المولد المحددة غير صحيحة.',
            // رسائل خزانات الوقود
            'fuel_tanks.*.capacity.required_with' => 'سعة الخزان مطلوبة.',
            'fuel_tanks.*.capacity.integer' => 'سعة الخزان يجب أن تكون رقماً صحيحاً.',
            'fuel_tanks.*.capacity.min' => 'سعة الخزان يجب أن تكون أكبر من أو تساوي 0.',
            'fuel_tanks.*.capacity.max' => 'سعة الخزان يجب أن تكون أقل من أو تساوي 10000 لتر.',
            'fuel_tanks.*.location.required_with' => 'موقع الخزان مطلوب.',
            'fuel_tanks.*.location.in' => 'موقع الخزان المحدد غير صحيح.',
            'fuel_tanks.*.material.in' => 'مادة التصنيع المحددة غير صحيحة.',
            'fuel_tanks.*.usage.in' => 'نوع الاستخدام المحدد غير صحيح.',
            'fuel_tanks.*.measurement_method.in' => 'طريقة القياس المحددة غير صحيحة.',
        ];
    }
}
