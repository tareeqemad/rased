<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Role;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user();

        // ✅ خلّي القرار من الـ Policy (UserPolicy@create)
        return $actor ? $actor->can('create', User::class) : false;
    }

    protected function failedAuthorization()
    {
        // عشان الـ JS يطلع Toast مفهوم بدل رسالة Laravel الافتراضية
        throw new AuthorizationException('غير مصرح لك بإضافة مستخدم.');
    }

    public function rules(): array
    {
        $actor = $this->user();

        // roles المسموحة حسب نوع المستخدم
        $allowedRoles = array_map(fn (Role $r) => $r->value, Role::cases());

        if ($actor && $actor->isCompanyOwner()) {
            // ✅ المشغّل فقط موظف/فني
            $allowedRoles = [Role::Employee->value, Role::Technician->value];
        } elseif ($actor && $actor->isEnergyAuthority()) {
            // ✅ EnergyAuthority (سلطة الطاقة) يمكنه إضافة: admin, energy_authority, company_owner, employee, technician
            $allowedRoles = [
                Role::Admin->value,
                Role::EnergyAuthority->value,
                Role::CompanyOwner->value,
                Role::Employee->value,
                Role::Technician->value,
            ];
        }

        $role = (string) $this->input('role');
        $needOperator = ($actor && ($actor->isSuperAdmin() || $actor->isEnergyAuthority()))
            && in_array($role, [Role::Employee->value, Role::Technician->value], true);
        
        // إذا كان المشغل يضيف موظف، username و password اختياريين (سيتم توليدهما تلقائياً)
        $isCompanyOwnerAddingEmployee = $actor && $actor->isCompanyOwner() 
            && in_array($role, [Role::Employee->value, Role::Technician->value], true);
        
        // إذا كان SuperAdmin أو EnergyAuthority يضيف مستخدم، username و password اختياريين (سيتم توليدهما تلقائياً)
        $isSuperAdminOrEnergyAuthorityAddingUser = $actor && ($actor->isSuperAdmin() || $actor->isEnergyAuthority())
            && in_array($role, [Role::SuperAdmin->value, Role::Admin->value, Role::EnergyAuthority->value, Role::CompanyOwner->value], true);
        
        // تحديد ما إذا كانت name_en و phone مطلوبة (لأدوار رئيسية)
        $needNameEnAndPhone = $actor && ($actor->isSuperAdmin() || $actor->isEnergyAuthority())
            && in_array($role, [Role::SuperAdmin->value, Role::Admin->value, Role::EnergyAuthority->value, Role::CompanyOwner->value], true);
        
        // تحديد ما إذا كان operator_id مطلوب (لـ Employee/Technician أو CompanyOwner)
        $needOperatorForCompanyOwner = $actor && ($actor->isSuperAdmin() || $actor->isEnergyAuthority())
            && $role === Role::CompanyOwner->value;

        return [
            'name' => ['required', 'string', 'max:255'],
            'name_en' => array_merge(
                $needNameEnAndPhone ? ['required'] : ['nullable'],
                ['string', 'max:255']
            ),
            'phone' => array_merge(
                $needNameEnAndPhone ? ['required'] : ['nullable'],
                [
                    'string',
                    'max:20',
                    'regex:/^0(59|56)\d{7}$/',
                ]
            ),

            'username' => array_merge(
                ($isCompanyOwnerAddingEmployee || $isSuperAdminOrEnergyAuthorityAddingUser) ? ['nullable'] : ['required'],
                [
                    'string',
                    'max:50',
                    // ✅ تجاهل الـ soft deletes (اختياري بس مهم)
                    Rule::unique('users', 'username')->whereNull('deleted_at'),
                ]
            ),

            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],

            'role' => ['required', Rule::in($allowedRoles)],

            // ✅ SuperAdmin و EnergyAuthority: لو بدو ينشئ موظف/فني أو مشغل لازم يحدد operator_id
            // ✅ المشغّل: مش مطلوب لأنه بيرتبط تلقائيًا بمشغّله داخل الـ Controller
            'operator_id' => array_values(array_filter([
                ($needOperator || $needOperatorForCompanyOwner) ? 'required' : 'nullable',
                'integer',
                'exists:operators,id',
            ])),

            'password' => array_merge(
                ($isCompanyOwnerAddingEmployee || $isSuperAdminOrEnergyAuthorityAddingUser) ? ['nullable'] : ['required'],
                ['string', 'min:8', 'confirmed']
            ),
        ];
    }

    protected function prepareForValidation(): void
    {
        // تنظيف بسيط (اختياري)
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'name_en' => is_string($this->name_en) ? trim($this->name_en) : $this->name_en,
            'phone' => is_string($this->phone) ? preg_replace('/[^0-9]/', '', trim($this->phone)) : $this->phone,
            'username' => is_string($this->username) ? trim($this->username) : $this->username,
            'email' => is_string($this->email) ? trim($this->email) : $this->email,
        ]);
    }
}
