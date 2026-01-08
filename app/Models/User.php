<?php

namespace App\Models;

use App\Models\Role as RoleModel;
use App\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'password_plain',
        'phone',
        'role',
        'role_id',
        'status',
    ];

    protected $hidden = [
        'password',
        'password_plain',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }

    protected $attributes = [
        'status' => 'active',
    ];

    public function ownedOperators(): HasMany
    {
        return $this->hasMany(Operator::class, 'owner_id');
    }

    public function operators(): BelongsToMany
    {
        return $this->belongsToMany(Operator::class, 'operator_user')
            ->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permission')
            ->withTimestamps();
    }

    public function revokedPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permission_revoked')
            ->withTimestamps();
    }

    public function permissionAuditLogs(): HasMany
    {
        return $this->hasMany(PermissionAuditLog::class, 'user_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function roleModel(): BelongsTo
    {
        return $this->belongsTo(RoleModel::class, 'role_id');
    }

    public function isSuperAdmin(): bool
    {
        if ($this->role_id) {
            return $this->roleModel?->name === 'super_admin';
        }

        return $this->role === Role::SuperAdmin;
    }

    public function isCompanyOwner(): bool
    {
        if ($this->role_id) {
            return $this->roleModel?->name === 'company_owner';
        }

        return $this->role === Role::CompanyOwner;
    }

    public function isEmployee(): bool
    {
        if ($this->role_id) {
            return $this->roleModel?->name === 'employee';
        }

        return $this->role === Role::Employee;
    }

    public function isTechnician(): bool
    {
        if ($this->role_id) {
            return $this->roleModel?->name === 'technician';
        }

        return $this->role === Role::Technician;
    }

    public function isAdmin(): bool
    {
        if ($this->role_id) {
            return $this->roleModel?->name === 'admin';
        }

        return $this->role === Role::Admin;
    }

    public function ownsOperator(Operator $operator): bool
    {
        return $this->isSuperAdmin() || $this->ownedOperators()->where('id', $operator->id)->exists();
    }

    public function belongsToOperator(Operator $operator): bool
    {
        return $this->isSuperAdmin()
            || $this->ownsOperator($operator)
            || $this->operators()->where('operators.id', $operator->id)->exists();
    }

    public function hasPermission(string $permissionName): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        // إذا كان لديه role_id، استخدم صلاحيات الدور أولاً
        if ($this->roleModel) {
            if ($this->roleModel->hasPermission($permissionName)) {
                // تحقق من أن الصلاحية لم يتم إلغاؤها
                if (!$this->revokedPermissions()->where('name', $permissionName)->exists()) {
                    return true;
                }
            }
        }

        // Fallback للـ Admin (إذا لم يكن لديه role_id)
        if ($this->isAdmin() && !$this->roleModel) {
            return in_array($permissionName, [
                'operators.view',
                'generators.view',
                'generation_units.view',
                'operation_logs.view',
                'fuel_efficiencies.view',
                'maintenance_records.view',
                'compliance_safeties.view',
                'electricity_tariff_prices.view', // الأدمن يمكنهم الاستعلام فقط
            ]);
        }

        if ($this->revokedPermissions()->where('name', $permissionName)->exists()) {
            return false;
        }

        if ($this->permissions()->where('name', $permissionName)->exists()) {
            return true;
        }

        return false;
    }

    public function hasAnyPermission(array $permissionNames): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        // إذا كان لديه role_id، استخدم صلاحيات الدور أولاً
        if ($this->roleModel) {
            foreach ($permissionNames as $permissionName) {
                if ($this->roleModel->hasPermission($permissionName)) {
                    if (!$this->revokedPermissions()->where('name', $permissionName)->exists()) {
                        return true;
                    }
                }
            }
        }

        // Fallback للـ Admin (إذا لم يكن لديه role_id)
        if ($this->isAdmin() && !$this->roleModel) {
            $adminPermissions = [
                'operators.view',
                'generators.view',
                'generation_units.view',
                'operation_logs.view',
                'fuel_efficiencies.view',
                'maintenance_records.view',
                'compliance_safeties.view',
                'electricity_tariff_prices.view', // الأدمن يمكنهم الاستعلام فقط
            ];

            return !empty(array_intersect($permissionNames, $adminPermissions));
        }

        $revokedPermissionNames = $this->revokedPermissions()
            ->whereIn('name', $permissionNames)
            ->pluck('name')
            ->toArray();

        $availablePermissionNames = array_diff($permissionNames, $revokedPermissionNames);

        if (empty($availablePermissionNames)) {
            return false;
        }

        if ($this->permissions()->whereIn('name', $availablePermissionNames)->exists()) {
            return true;
        }

        if ($this->roleModel) {
            foreach ($availablePermissionNames as $permissionName) {
                if ($this->roleModel->hasPermission($permissionName)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function hasAllPermissions(array $permissionNames): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        // إذا كان لديه role_id، استخدم صلاحيات الدور أولاً
        if ($this->roleModel) {
            $rolePermissions = [];
            foreach ($permissionNames as $permissionName) {
                if ($this->roleModel->hasPermission($permissionName)) {
                    if (!$this->revokedPermissions()->where('name', $permissionName)->exists()) {
                        $rolePermissions[] = $permissionName;
                    }
                }
            }
            return count($permissionNames) === count($rolePermissions);
        }

        // Fallback للـ Admin (إذا لم يكن لديه role_id)
        if ($this->isAdmin() && !$this->roleModel) {
            $adminPermissions = [
                'operators.view',
                'generators.view',
                'generation_units.view',
                'operation_logs.view',
                'fuel_efficiencies.view',
                'maintenance_records.view',
                'compliance_safeties.view',
                'electricity_tariff_prices.view',
            ];

            return count($permissionNames) === count(array_intersect($permissionNames, $adminPermissions));
        }

        $revokedPermissionNames = $this->revokedPermissions()
            ->whereIn('name', $permissionNames)
            ->pluck('name')
            ->toArray();

        $availablePermissionNames = array_diff($permissionNames, $revokedPermissionNames);

        if (count($availablePermissionNames) !== count($permissionNames)) {
            return false;
        }

        $userPermissions = $this->permissions()
            ->whereIn('name', $availablePermissionNames)
            ->pluck('name')
            ->toArray();

        if ($this->roleModel) {
            $rolePermissions = $this->roleModel->permissions()
                ->whereIn('name', $availablePermissionNames)
                ->pluck('name')
                ->toArray();

            $userPermissions = array_unique(array_merge($userPermissions, $rolePermissions));
        }

        return count($availablePermissionNames) === count($userPermissions);
    }

    public function getAvatarUrlAttribute(): string
    {
        if (isset($this->attributes['avatar']) && $this->attributes['avatar']) {
            return asset('storage/'.$this->attributes['avatar']);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=0066cc&color=fff&size=128';
    }

    public function getRoleNameAttribute(): string
    {
        if ($this->roleModel) {
            return $this->roleModel->label;
        }

        return match ($this->role ?? null) {
            Role::SuperAdmin => 'مدير النظام',
            Role::Admin => 'مدير سلطة الطاقة',
            Role::CompanyOwner => 'صاحب مشغل',
            Role::Employee => 'موظف',
            Role::Technician => 'فني',
            default => 'بدون صلاحية',
        };
    }

    /**
     * إنشاء 3 رسائل افتراضية للمستخدم الجديد
     */
    public function createDefaultMessages(): void
    {
        // الحصول على Super Admin لإرسال الرسائل منه
        $superAdmin = User::where('role', Role::SuperAdmin)->first();
        
        if (!$superAdmin) {
            return;
        }

        // الحصول على المشغل المرتبط بالمستخدم (إن وجد)
        $operator = null;
        if ($this->isCompanyOwner()) {
            $operator = $this->ownedOperators()->first();
        } elseif ($this->isEmployee() || $this->isTechnician()) {
            $operator = $this->operators()->first();
        }

        $messages = [
            [
                'sender_id' => $superAdmin->id,
                'receiver_id' => $this->id,
                'operator_id' => $operator?->id,
                'subject' => 'مرحباً بك في منصة راصد',
                'body' => "عزيزي/عزيزتي {$this->name}،\n\nنرحب بك في منصة راصد لإدارة وحدات التوليد. نتمنى أن تجد في النظام كل ما تحتاجه لإدارة عملك بكفاءة وفعالية.\n\nنتمنى لك تجربة ممتعة!",
                'type' => 'admin_to_operator',
                'is_read' => false,
                'read_at' => null,
            ],
            [
                'sender_id' => $superAdmin->id,
                'receiver_id' => $this->id,
                'operator_id' => $operator?->id,
                'subject' => 'دليل الاستخدام السريع',
                'body' => "عزيزي/عزيزتي {$this->name}،\n\nيمكنك من خلال النظام:\n- إدارة بيانات المولدات ووحدات التوليد\n- متابعة سجلات التشغيل والوقود\n- إدارة أعمال الصيانة\n- التواصل مع الفريق من خلال نظام الرسائل\n\nللمزيد من المعلومات، يرجى مراجعة الدليل الإرشادي.",
                'type' => 'admin_to_operator',
                'is_read' => false,
                'read_at' => null,
            ],
            [
                'sender_id' => $superAdmin->id,
                'receiver_id' => $this->id,
                'operator_id' => $operator?->id,
                'subject' => 'معلومات مهمة',
                'body' => "عزيزي/عزيزتي {$this->name}،\n\nنود تذكيرك بأن:\n- يرجى إكمال بيانات المشغل في أقرب وقت ممكن\n- يمكنك التواصل معنا في أي وقت من خلال نظام الرسائل\n- ننصح بتغيير كلمة المرور بعد تسجيل الدخول لأول مرة\n\nنتمنى لك تجربة ناجحة!",
                'type' => 'admin_to_operator',
                'is_read' => false,
                'read_at' => null,
            ],
        ];

        foreach ($messages as $messageData) {
            Message::create($messageData);
        }
    }
}
