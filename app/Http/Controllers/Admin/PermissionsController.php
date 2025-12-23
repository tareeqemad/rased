<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\PermissionAuditLog;
use App\Models\User;
use App\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermissionsController extends Controller
{
    public function index(Request $request): View
    {
        // جلب جميع الصلاحيات مجمعة حسب المجموعة
        $search = $request->input('search', '');
        $query = Permission::orderBy('group')->orderBy('order');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('label', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('group_label', 'like', "%{$search}%");
            });
        }

        $permissions = $query->get()->groupBy('group');

        // جلب المستخدمين المتاحين (حسب الصلاحيات)
        $authUser = auth()->user();
        $usersQuery = User::with('permissions');

        if ($authUser->isSuperAdmin()) {
            // SuperAdmin يمكنه منح الصلاحيات لأي مستخدم
            $usersQuery->where('id', '!=', $authUser->id); // استثناء نفسه
        } elseif ($authUser->isCompanyOwner()) {
            // CompanyOwner يمكنه منح الصلاحيات لموظفيه وفنييه فقط
            $operator = $authUser->ownedOperators()->first();
            if ($operator) {
                $usersQuery->whereHas('operators', function ($q) use ($operator) {
                    $q->where('operators.id', $operator->id);
                })->whereIn('role', [Role::Employee, Role::Technician]);
            } else {
                $usersQuery->whereRaw('1 = 0');
            }
        } else {
            $usersQuery->whereRaw('1 = 0');
        }

        $users = $usersQuery->get();

        // تجميع المستخدمين حسب النوع
        $groupedUsers = [
            'company_owners' => $users->filter(fn ($user) => $user->isCompanyOwner()),
            'employees' => $users->filter(fn ($user) => $user->isEmployee()),
            'technicians' => $users->filter(fn ($user) => $user->isTechnician()),
        ];

        // تعريف الأدوار الأساسية
        $roles = [
            'super_admin' => [
                'name' => 'مدير النظام',
                'color' => 'danger',
                'icon' => 'bi-shield-check',
                'description' => 'لديه جميع الصلاحيات تلقائياً',
            ],
            'company_owner' => [
                'name' => 'صاحب المشغل',
                'color' => 'primary',
                'icon' => 'bi-building',
                'description' => 'يمكنه إدارة بيانات مشغله ومولداته وموظفيه',
            ],
            'employee' => [
                'name' => 'موظف',
                'color' => 'success',
                'icon' => 'bi-person',
                'description' => 'يمكن تخصيص صلاحياته من قبل المشغل',
            ],
            'technician' => [
                'name' => 'فني',
                'color' => 'warning',
                'icon' => 'bi-tools',
                'description' => 'يمكن تخصيص صلاحياته من قبل المشغل',
            ],
        ];

        return view('admin.permissions.index', compact('permissions', 'roles', 'users', 'groupedUsers', 'search'));
    }

    public function assignPermissions(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $authUser = auth()->user();
        $user = User::findOrFail($request->input('user_id'));

        // التحقق من الصلاحيات
        if ($authUser->isCompanyOwner()) {
            $operator = $authUser->ownedOperators()->first();
            if (! $operator || ! $user->operators->contains($operator)) {
                return redirect()->back()
                    ->with('error', 'لا يمكنك منح صلاحيات لهذا المستخدم.');
            }

            if (! $user->isEmployee() && ! $user->isTechnician()) {
                return redirect()->back()
                    ->with('error', 'يمكنك منح صلاحيات للموظفين والفنيين فقط.');
            }
        } elseif (! $authUser->isSuperAdmin()) {
            abort(403);
        }

        // SuperAdmin لا يمكن إزالة صلاحياته
        if ($user->isSuperAdmin() && ! $authUser->isSuperAdmin()) {
            return redirect()->back()
                ->with('error', 'لا يمكنك تعديل صلاحيات مدير النظام.');
        }

        // الحصول على الصلاحيات الحالية قبل التعديل
        $oldPermissions = $user->permissions->pluck('id')->toArray();

        $permissionIds = $request->input('permissions', []);
        $user->permissions()->sync($permissionIds);

        // تسجيل التغييرات في audit log
        $newPermissions = $permissionIds;
        $granted = array_diff($newPermissions, $oldPermissions); // الصلاحيات المضافة
        $revoked = array_diff($oldPermissions, $newPermissions); // الصلاحيات المحذوفة

        // تسجيل الصلاحيات المضافة
        foreach ($granted as $permissionId) {
            PermissionAuditLog::create([
                'user_id' => $user->id,
                'performed_by' => $authUser->id,
                'permission_id' => $permissionId,
                'action' => 'granted',
                'notes' => "تم منح الصلاحية من قبل {$authUser->name}",
            ]);
        }

        // تسجيل الصلاحيات المحذوفة
        foreach ($revoked as $permissionId) {
            PermissionAuditLog::create([
                'user_id' => $user->id,
                'performed_by' => $authUser->id,
                'permission_id' => $permissionId,
                'action' => 'revoked',
                'notes' => "تم إلغاء الصلاحية من قبل {$authUser->name}",
            ]);
        }

        return redirect()->route('admin.permissions.index')
            ->with('success', "تم منح الصلاحيات للمستخدم {$user->name} بنجاح.");
    }

    public function search(Request $request): JsonResponse
    {
        $search = $request->input('search', '');
        $query = Permission::orderBy('group')->orderBy('order');

        if ($search) {
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
}
