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
        }

        $role = (string) $this->input('role');
        $needOperator = ($actor && $actor->isSuperAdmin())
            && in_array($role, [Role::Employee->value, Role::Technician->value], true);

        return [
            'name' => ['required', 'string', 'max:255'],

            'username' => [
                'required',
                'string',
                'max:50',
                // ✅ تجاهل الـ soft deletes (اختياري بس مهم)
                Rule::unique('users', 'username')->whereNull('deleted_at'),
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],

            'role' => ['required', Rule::in($allowedRoles)],

            // ✅ SuperAdmin فقط: لو بدو ينشئ موظف/فني لازم يحدد operator_id
            // ✅ المشغّل: مش مطلوب لأنه بيرتبط تلقائيًا بمشغّله داخل الـ Controller
            'operator_id' => array_values(array_filter([
                $needOperator ? 'required' : 'nullable',
                'integer',
                'exists:operators,id',
            ])),

            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // تنظيف بسيط (اختياري)
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'username' => is_string($this->username) ? trim($this->username) : $this->username,
            'email' => is_string($this->email) ? trim($this->email) : $this->email,
        ]);
    }
}
