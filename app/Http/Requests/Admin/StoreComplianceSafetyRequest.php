<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreComplianceSafetyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSuperAdmin() || $this->user()->isCompanyOwner();
    }

    public function rules(): array
    {
        return [
            'operator_id' => ['required', 'exists:operators,id'],
            'safety_certificate_status' => ['required', 'string', Rule::in(['available', 'expired', 'not_available'])],
            'last_inspection_date' => ['nullable', 'date'],
            'inspection_authority' => ['nullable', 'string', 'max:255'],
            'inspection_result' => ['nullable', 'string'],
            'violations' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'operator_id.required' => 'المشغل مطلوب.',
            'operator_id.exists' => 'المشغل المحدد غير موجود.',
            'safety_certificate_status.required' => 'حالة شهادة السلامة مطلوبة.',
            'safety_certificate_status.in' => 'حالة شهادة السلامة غير صحيحة.',
        ];
    }
}
