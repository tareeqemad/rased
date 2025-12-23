<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\PermissionAuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserPermissionController extends Controller
{
    public function show(User $user): View
    {
        // SuperAdmin يمكنه إدارة صلاحيات أي مستخدم
        // CompanyOwner يمكنه إدارة صلاحيات موظفيه وفنييه فقط
        $authUser = auth()->user();

        if ($authUser->isCompanyOwner()) {
            $operator = $authUser->ownedOperators()->first();
            if (! $operator || ! $user->operators->contains($operator)) {
                abort(403, 'لا يمكنك إدارة صلاحيات هذا المستخدم.');
            }

            if (! $user->isEmployee() && ! $user->isTechnician()) {
                abort(403, 'يمكنك إدارة صلاحيات الموظفين والفنيين فقط.');
            }
        } elseif (! $authUser->isSuperAdmin()) {
            abort(403);
        }

        $permissions = Permission::orderBy('group')->orderBy('order')->get()->groupBy('group');
        $userPermissions = $user->permissions->pluck('id')->toArray();

        return view('admin.users.permissions', compact('user', 'permissions', 'userPermissions'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $authUser = auth()->user();

        if ($authUser->isCompanyOwner()) {
            $operator = $authUser->ownedOperators()->first();
            if (! $operator || ! $user->operators->contains($operator)) {
                abort(403, 'لا يمكنك إدارة صلاحيات هذا المستخدم.');
            }

            if (! $user->isEmployee() && ! $user->isTechnician()) {
                abort(403, 'يمكنك إدارة صلاحيات الموظفين والفنيين فقط.');
            }
        } elseif (! $authUser->isSuperAdmin()) {
            abort(403);
        }

        $permissionIds = $request->input('permissions', []);

        // SuperAdmin لا يمكن إزالة صلاحياته
        if ($user->isSuperAdmin() && ! $authUser->isSuperAdmin()) {
            return redirect()->back()
                ->with('error', 'لا يمكنك تعديل صلاحيات مدير النظام.');
        }

        // الحصول على الصلاحيات الحالية قبل التعديل
        $oldPermissions = $user->permissions->pluck('id')->toArray();

        // تحديث الصلاحيات
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

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'تم تحديث صلاحيات المستخدم بنجاح.');
    }
}
