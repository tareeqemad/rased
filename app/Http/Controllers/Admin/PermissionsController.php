<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Operator;
use App\Models\Permission;
use App\Models\PermissionAuditLog;
use App\Models\User;
use App\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PermissionsController extends Controller
{
    private ?array $cachedTenantAssignablePermissionIds = null;

    /**
     * صلاحيات Tenant اللي مسموح للمشغل يوزعها (حتى لو عنده غيرها بالغلط).
     * عدّل القائمة حسب Modules اللي بدك المشغل يتحكم فيها فقط.
     */
    private function tenantAssignablePermissionIds(): array
    {
        if ($this->cachedTenantAssignablePermissionIds !== null) {
            return $this->cachedTenantAssignablePermissionIds;
        }

        $ids = Permission::query()
            ->where(function ($q) {
                // Operators: نخليها View فقط (لو بدك)
                $q->where('name', '=', 'operators.view')

                    // Tenant Modules
                    ->orWhere('name', 'like', 'generators.%')
                    ->orWhere('name', 'like', 'generation_units.%')
                    ->orWhere('name', 'like', 'operation_logs.%')
                    ->orWhere('name', 'like', 'fuel_efficiencies.%')
                    ->orWhere('name', 'like', 'maintenance_records.%')
                    ->orWhere('name', 'like', 'compliance_safeties.%')
                    ->orWhere('name', 'like', 'electricity_tariff_prices.%');
            })
            ->pluck('id')
            ->toArray();

        $this->cachedTenantAssignablePermissionIds = $ids;

        return $ids;
    }

    /**
     * الصلاحيات الفعلية للمستخدم (Role + Direct - Revoked)
     */
    private function effectivePermissionIdsFor(User $actor): array
    {
        if ($actor->isSuperAdmin()) {
            return Permission::pluck('id')->toArray();
        }

        $actor->loadMissing(['permissions', 'revokedPermissions', 'roleModel.permissions']);

        $rolePermissionIds = $actor->roleModel?->permissions->pluck('id')->toArray() ?? [];
        $directPermissionIds = $actor->permissions->pluck('id')->toArray();
        $revokedPermissionIds = $actor->revokedPermissions->pluck('id')->toArray();

        $effective = array_values(array_unique(array_merge($rolePermissionIds, $directPermissionIds)));

        return array_values(array_diff($effective, $revokedPermissionIds));
    }

    /**
     * سقف المشغل: (Effective للمشغل) ∩ (TenantAssignable)
     * هذا هو "القفل الحديدي" اللي يمنع constants/users/roles/permissions… إلخ.
     */
    private function companyOwnerCeilingPermissionIds(User $actor): array
    {
        $effective = $this->effectivePermissionIdsFor($actor);
        $assignable = $this->tenantAssignablePermissionIds();

        return array_values(array_intersect($effective, $assignable));
    }

    private function assertActorCanOpenTree(User $actor): void
    {
        if (! $actor->isSuperAdmin() && ! $actor->isCompanyOwner()) {
            abort(403);
        }
    }

    private function assertActorCanManageTarget(User $actor, User $target): void
    {
        // SuperAdmin يمسك الكل
        if ($actor->isSuperAdmin()) {
            return;
        }

        // CompanyOwner: فقط موظفين/فنيين تابعين لمشغله
        if ($actor->isCompanyOwner()) {
            $operator = $actor->ownedOperators()->first();
            if (! $operator) {
                abort(403, 'لا يوجد مشغل مرتبط بك.');
            }

            if (! $target->isEmployee() && ! $target->isTechnician()) {
                abort(403, 'يمكنك منح صلاحيات للموظفين والفنيين فقط.');
            }

            // لازم يكون تابع لنفس المشغل
            $isUnderSameOperator = $target->operators()
                ->where('operators.id', $operator->id)
                ->exists();

            if (! $isUnderSameOperator) {
                abort(403, 'لا يمكنك إدارة صلاحيات مستخدم خارج مشغلك.');
            }

            // ممنوع العبث بالسوبر أدمن
            if ($target->isSuperAdmin()) {
                abort(403, 'لا يمكنك تعديل صلاحيات مدير النظام.');
            }

            return;
        }

        abort(403);
    }

    public function index(Request $request): View
    {
        $authUser = auth()->user();
        $this->assertActorCanOpenTree($authUser);

        $search = trim((string) $request->input('search', ''));

        // ✅ لازم يتعرّف دائماً
        $operator = null;
        if ($authUser->isCompanyOwner()) {
            $operator = $authUser->ownedOperators()->first();
        }

        // Permissions query
        $query = Permission::query()->orderBy('group')->orderBy('order');

        // CompanyOwner: فلترة بالسقف
        if ($authUser->isCompanyOwner()) {
            $ceilingIds = $this->companyOwnerCeilingPermissionIds($authUser);
            $query->whereIn('id', $ceilingIds);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('label', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('group_label', 'like', "%{$search}%");
            });
        }

        $permissions = $query->get()->groupBy('group');

        // Users list (Legacy blade)
        $users = collect();
        $groupedUsers = [
            'company_owners' => collect(),
            'employees' => collect(),
            'technicians' => collect(),
        ];

        if ($authUser->isSuperAdmin()) {
            $users = User::query()
                ->where('id', '!=', $authUser->id)
                ->where('role', Role::CompanyOwner)
                ->latest()
                ->get();

            $groupedUsers['company_owners'] = $users;

        } elseif ($authUser->isCompanyOwner()) {

            if ($operator) {
                $users = User::query()
                    ->whereHas('operators', function ($q) use ($operator) {
                        $q->where('operators.id', $operator->id);
                    })
                    ->whereIn('role', [Role::Employee, Role::Technician])
                    ->orderBy('name')
                    ->get();

                $groupedUsers['employees'] = $users->filter(fn ($u) => $u->isEmployee())->values();
                $groupedUsers['technicians'] = $users->filter(fn ($u) => $u->isTechnician())->values();
            }
        }

        // (قد يكون مستخدم بالـ blade القديم)
        $roles = [
            'company_owner' => [
                'name' => 'مشغل',
                'color' => 'primary',
                'icon' => 'bi-building',
                'description' => 'مشغل (Tenant Admin)',
            ],
            'employee' => [
                'name' => 'موظف',
                'color' => 'success',
                'icon' => 'bi-person',
                'description' => 'تابع للمشغل',
            ],
            'technician' => [
                'name' => 'فني',
                'color' => 'warning',
                'icon' => 'bi-tools',
                'description' => 'تابع للمشغل',
            ],
        ];

        // ✅ هذا اللي ناقصك (للـ JS / badges)
        $rolesMeta = [
            'super_admin' => ['label' => 'مدير النظام', 'color' => 'danger', 'icon' => 'bi-shield-check'],
            'admin'       => ['label' => 'مدير', 'color' => 'info', 'icon' => 'bi-person-badge'],
            'energy_authority' => ['label' => 'سلطة الطاقة', 'color' => 'info', 'icon' => 'bi-bank2'],
            'company_owner' => ['label' => 'مشغل', 'color' => 'primary', 'icon' => 'bi-building'],
            'employee'      => ['label' => 'موظف', 'color' => 'success', 'icon' => 'bi-person'],
            'technician'    => ['label' => 'فني', 'color' => 'warning', 'icon' => 'bi-tools'],
        ];

        return view('admin.permissions.index', compact(
            'permissions',
            'roles',
            'rolesMeta',     // ✅ مهم
            'users',
            'groupedUsers',
            'search',
            'operator'       // ✅ مهم
        ));
    }



    public function search(Request $request): JsonResponse
    {
        $authUser = auth()->user();
        $this->assertActorCanOpenTree($authUser);

        $search = trim((string) $request->input('search', ''));

        $query = Permission::query()->orderBy('group')->orderBy('order');

        if ($authUser->isCompanyOwner()) {
            $ceilingIds = $this->companyOwnerCeilingPermissionIds($authUser);
            $query->whereIn('id', $ceilingIds);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('label', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('group_label', 'like', "%{$search}%");
            });
        }

        $permissions = $query->get()->groupBy('group');

        $html = view('admin.permissions.partials.permissions-tree', [
            'permissions' => $permissions,
            'search' => $search,
        ])->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'count' => $permissions->flatten()->count(),
        ]);
    }

    public function getUserPermissions(User $user): JsonResponse
    {
        $authUser = auth()->user();
        $this->assertActorCanOpenTree($authUser);
        $this->assertActorCanManageTarget($authUser, $user);

        $user->load(['permissions', 'revokedPermissions', 'roleModel.permissions']);

        $direct = $user->permissions->pluck('id')->toArray();
        $role = $user->roleModel?->permissions->pluck('id')->toArray() ?? [];
        $revoked = $user->revokedPermissions->pluck('id')->toArray();

        // CompanyOwner: رجّع فقط اللي ضمن السقف
        if ($authUser->isCompanyOwner()) {
            $ceiling = $this->companyOwnerCeilingPermissionIds($authUser);

            $direct = array_values(array_intersect($direct, $ceiling));
            $role = array_values(array_intersect($role, $ceiling));
            $revoked = array_values(array_intersect($revoked, $ceiling));
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'direct_permissions' => $direct,
                'role_permissions' => $role,
                'revoked_permissions' => $revoked,
            ],
        ]);
    }

    public function assignPermissions(Request $request): RedirectResponse|JsonResponse
    {
        $authUser = auth()->user();
        $this->assertActorCanOpenTree($authUser);

        try {
            $request->validate([
                'user_id' => ['required', 'exists:users,id'],
                'permissions' => ['nullable', 'array'],
                'permissions.*' => ['integer', 'exists:permissions,id'],
                'revoked_permissions' => ['nullable', 'array'],
                'revoked_permissions.*' => ['integer', 'exists:permissions,id'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'خطأ في التحقق من البيانات',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }

        $target = User::with(['permissions', 'revokedPermissions', 'roleModel.permissions'])
            ->findOrFail((int) $request->input('user_id'));

        $this->assertActorCanManageTarget($authUser, $target);

        $newDirect = array_map('intval', (array) $request->input('permissions', []));
        $newRevoked = array_map('intval', (array) $request->input('revoked_permissions', []));

        // CompanyOwner: قفل السقف + Partial Sync (ما يلمس خارج السقف)
        if ($authUser->isCompanyOwner()) {
            $ceiling = $this->companyOwnerCeilingPermissionIds($authUser);

            $invalidDirect = array_diff($newDirect, $ceiling);
            $invalidRevoked = array_diff($newRevoked, $ceiling);

            if (! empty($invalidDirect) || ! empty($invalidRevoked)) {
                return response()->json([
                    'success' => false,
                    'message' => 'تم رفض الطلب: محاولة منح صلاحيات خارج صلاحيات المشغل المسموحة.',
                ], 403);
            }

            $keepDirectOutsideCeiling = array_diff($target->permissions->pluck('id')->toArray(), $ceiling);
            $keepRevokedOutsideCeiling = array_diff($target->revokedPermissions->pluck('id')->toArray(), $ceiling);

            $finalDirect = array_values(array_unique(array_merge($keepDirectOutsideCeiling, $newDirect)));
            $finalRevoked = array_values(array_unique(array_merge($keepRevokedOutsideCeiling, $newRevoked)));

            $newDirect = $finalDirect;
            $newRevoked = $finalRevoked;
        }

        // Audit: snapshot قبل
        $oldDirect = $target->permissions->pluck('id')->toArray();
        $oldRevoked = $target->revokedPermissions->pluck('id')->toArray();

        DB::transaction(function () use ($target, $newDirect, $newRevoked, $authUser, $oldDirect, $oldRevoked) {
            // Direct
            $target->permissions()->sync($newDirect);
            // Revoked (منع)
            $target->revokedPermissions()->sync($newRevoked);

            // Audit direct changes
            $grantedDirect = array_diff($newDirect, $oldDirect);
            $revokedDirect = array_diff($oldDirect, $newDirect);

            foreach ($grantedDirect as $pid) {
                PermissionAuditLog::create([
                    'user_id' => $target->id,
                    'performed_by' => $authUser->id,
                    'permission_id' => $pid,
                    'action' => 'granted',
                    'notes' => "تم منح صلاحية مباشرة بواسطة {$authUser->name}",
                ]);
            }

            foreach ($revokedDirect as $pid) {
                PermissionAuditLog::create([
                    'user_id' => $target->id,
                    'performed_by' => $authUser->id,
                    'permission_id' => $pid,
                    'action' => 'revoked',
                    'notes' => "تم إلغاء صلاحية مباشرة بواسطة {$authUser->name}",
                ]);
            }

            // Audit revoked-table changes (deny)
            $addedToRevoked = array_diff($newRevoked, $oldRevoked);
            $removedFromRevoked = array_diff($oldRevoked, $newRevoked);

            foreach ($addedToRevoked as $pid) {
                PermissionAuditLog::create([
                    'user_id' => $target->id,
                    'performed_by' => $authUser->id,
                    'permission_id' => $pid,
                    'action' => 'revoked',
                    'notes' => "تم منع الصلاحية (Override) بواسطة {$authUser->name}",
                ]);
            }

            foreach ($removedFromRevoked as $pid) {
                PermissionAuditLog::create([
                    'user_id' => $target->id,
                    'performed_by' => $authUser->id,
                    'permission_id' => $pid,
                    'action' => 'granted',
                    'notes' => "تم رفع المنع عن الصلاحية (Override) بواسطة {$authUser->name}",
                ]);
            }
        });

        // Response
        if ($request->ajax()) {
            $target->refresh()->load(['permissions', 'revokedPermissions', 'roleModel.permissions']);

            $direct = $target->permissions->pluck('id')->toArray();
            $role = $target->roleModel?->permissions->pluck('id')->toArray() ?? [];
            $revoked = $target->revokedPermissions->pluck('id')->toArray();

            // CompanyOwner: رجّع بس السقف
            if ($authUser->isCompanyOwner()) {
                $ceiling = $this->companyOwnerCeilingPermissionIds($authUser);

                $direct = array_values(array_intersect($direct, $ceiling));
                $role = array_values(array_intersect($role, $ceiling));
                $revoked = array_values(array_intersect($revoked, $ceiling));
            }

            return response()->json([
                'success' => true,
                'message' => "تم تحديث صلاحيات المستخدم {$target->name} بنجاح.",
                'user' => [
                    'id' => $target->id,
                    'name' => $target->name,
                    'direct_permissions' => $direct,
                    'role_permissions' => $role,
                    'revoked_permissions' => $revoked,
                ],
            ]);
        }

        return redirect()->route('admin.permissions.index')
            ->with('success', "تم تحديث صلاحيات المستخدم {$target->name} بنجاح.");
    }

    /**
     * Select2: Operators (SuperAdmin/Admin) + CompanyOwner (يرجع مشغله فقط)
     */
    public function select2Operators(Request $request): JsonResponse
    {
        $authUser = auth()->user();

        if (! $authUser->isSuperAdmin() && ! $authUser->isAdmin() && ! $authUser->isCompanyOwner()) {
            abort(403);
        }

        $term = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;

        $query = Operator::query()->select(['id', 'name', 'unit_number']);

        if ($authUser->isCompanyOwner()) {
            $query->where('owner_id', $authUser->id);
        }

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('unit_number', 'like', "%{$term}%");
            });
        }

        $p = $query->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);

        $results = $p->getCollection()->map(function ($op) {
            $text = $op->unit_number ? "{$op->unit_number} - {$op->name}" : $op->name;
            return ['id' => $op->id, 'text' => $text];
        })->values();

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $p->hasMorePages()],
        ]);
    }

    /**
     * Select2: Users by operator
     * - SuperAdmin/Admin: لازم operator_id
     * - CompanyOwner: يتجاهل operator_id ويرجع موظفينه/فنييه فقط
     */
    public function select2Users(Request $request): JsonResponse
    {
        $authUser = auth()->user();

        if (! $authUser->isSuperAdmin() && ! $authUser->isAdmin() && ! $authUser->isCompanyOwner()) {
            abort(403);
        }

        $term = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;

        $operatorId = (int) $request->input('operator_id', 0);

        if ($authUser->isCompanyOwner()) {
            $operator = $authUser->ownedOperators()->first();
            if (! $operator) {
                return response()->json(['results' => [], 'pagination' => ['more' => false]]);
            }
        } else {
            if ($operatorId <= 0) {
                return response()->json(['results' => [], 'pagination' => ['more' => false]]);
            }
            $operator = Operator::findOrFail($operatorId);
        }

        $query = User::query()
            ->select(['id', 'name', 'username', 'role'])
            ->where(function ($q) use ($operator) {
                // employees/technicians from pivot
                $q->whereHas('operators', function ($qq) use ($operator) {
                    $qq->where('operators.id', $operator->id);
                });

                // NOTE: owner (company owner) مش داخل pivot غالبًا
                // بنضيفه كـ OR على id
                $q->orWhere('id', $operator->owner_id);
            });

        // CompanyOwner: فقط موظفين/فنيين (بدون نفسه)
        if ($authUser->isCompanyOwner()) {
            $query->whereIn('role', [Role::Employee, Role::Technician]);
        }

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('username', 'like', "%{$term}%");
            });
        }

        $p = $query->orderBy('name')->paginate($perPage, ['*'], 'page', $page);

        $results = $p->getCollection()->map(function ($u) {
            $extra = $u->username ? " ({$u->username})" : '';
            return ['id' => $u->id, 'text' => $u->name . $extra];
        })->values();

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $p->hasMorePages()],
        ]);
    }
}
