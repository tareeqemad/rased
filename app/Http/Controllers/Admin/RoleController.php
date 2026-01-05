<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Role::class);

        $user = auth()->user();
        $query = Role::withCount(['users', 'permissions'])->with('operator');

        // فلترة الأدوار حسب المستخدم
        if ($user->isAdmin()) {
            // Admin يشوف جميع الأدوار النظامية والأدوار العامة (operator_id = null)
            // ولا يشوف الأدوار الخاصة بمشغلين محددين
            $query->where(function ($q) {
                $q->where('is_system', true)
                  ->orWhereNull('operator_id');
            });
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                // المشغل يشوف أدواره فقط + الأدوار النظامية (لا يشوف الأدوار العامة أو أدوار مشغلين آخرين)
                $query->where(function ($q) use ($operator) {
                    $q->where('is_system', true)
                      ->orWhere('operator_id', $operator->id);
                });
            } else {
                // إذا لم يكن لديه مشغل، يشوف الأدوار النظامية فقط
                $query->where('is_system', true);
            }
        } elseif (!$user->isSuperAdmin()) {
            // غير مصرح
            abort(403);
        }

        // البحث
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('label', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $roles = $query->orderBy('is_system', 'desc')
            ->orderBy('order')
            ->orderBy('label')
            ->paginate(15);

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Role::class);

        $user = auth()->user();
        
        // الحصول على الصلاحيات المتاحة
        $permissions = Permission::orderBy('group')->orderBy('order')->get();
        
        if ($user->isAdmin()) {
            // Admin: لا يمكنه منح صلاحيات إعدادات النظام (users, operators, permissions)
            $systemPermissions = $this->getSystemPermissions();
            $permissions = $permissions->reject(function ($permission) use ($systemPermissions) {
                return in_array($permission->name, $systemPermissions);
            });
        } elseif ($user->isCompanyOwner()) {
            // فلترة الصلاحيات - إزالة صلاحيات النظام
            $systemPermissions = $this->getSystemPermissions();
            $permissions = $permissions->reject(function ($permission) use ($systemPermissions) {
                return in_array($permission->name, $systemPermissions);
            });
        }
        
        $permissions = $permissions->groupBy('group');

        // الحصول على المشغلين (للسوبر أدمن فقط - Admin لا يحتاج لأنه ينشئ أدوار عامة)
        $operators = collect();
        if ($user->isSuperAdmin()) {
            $operators = \App\Models\Operator::orderBy('name')->get(['id', 'name', 'unit_number']);
        }

        return view('admin.roles.create', compact('permissions', 'operators'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $operatorId = null;
        
        // Admin يمكنه إنشاء أدوار عامة (operator_id = null) فقط
        if ($user->isAdmin()) {
            $operatorId = null; // أدوار عامة فقط
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if (!$operator) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'لا يوجد مشغل مرتبط بحسابك. أكمل ملف المشغل أولاً.');
            }
            $operatorId = $operator->id;
        } elseif ($user->isSuperAdmin()) {
            // السوبر أدمن يمكنه اختيار المشغل أو تركه null (دور عام)
            $operatorId = $request->validated('operator_id');
            if ($operatorId === '' || $operatorId === null) {
                $operatorId = null;
            }
        }

        // التحقق من الصلاحيات - منع منح صلاحيات النظام
        $permissionIds = $request->validated('permissions', []);
        if (($user->isAdmin() || $user->isCompanyOwner()) && !empty($permissionIds)) {
            $systemPermissions = $this->getSystemPermissions();
            $systemPermissionIds = Permission::whereIn('name', $systemPermissions)->pluck('id')->toArray();
            $permissionIds = array_diff($permissionIds, $systemPermissionIds);
        }

        $role = Role::create([
            'name' => $request->validated('name'),
            'label' => $request->validated('label'),
            'description' => $request->validated('description'),
            'is_system' => false,
            'order' => $request->validated('order', 0),
            'operator_id' => $operatorId,
        ]);

        // ربط الصلاحيات بالدور
        if (!empty($permissionIds)) {
            $role->permissions()->attach($permissionIds);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'تم إنشاء الدور بنجاح.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role): View
    {
        $this->authorize('view', $role);

        $role->load(['permissions', 'users']);

        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role): View
    {
        $this->authorize('update', $role);

        $user = auth()->user();
        
        // الحصول على الصلاحيات المتاحة
        $permissions = Permission::orderBy('group')->orderBy('order')->get();
        
        if ($user->isCompanyOwner()) {
            // فلترة الصلاحيات - إزالة صلاحيات النظام
            $systemPermissions = $this->getSystemPermissions();
            $permissions = $permissions->reject(function ($permission) use ($systemPermissions) {
                return in_array($permission->name, $systemPermissions);
            });
        }
        
        $permissions = $permissions->groupBy('group');
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        // الحصول على المشغلين (للسوبر أدمن فقط)
        $operators = collect();
        if ($user->isSuperAdmin()) {
            $operators = \App\Models\Operator::orderBy('name')->get(['id', 'name', 'unit_number']);
        }

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions', 'operators'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $user = auth()->user();
        
        // منع تحديث الأدوار النظامية (name و is_system)
        if ($role->is_system) {
            $updateData = [
                'label' => $request->validated('label'),
                'description' => $request->validated('description'),
                'order' => $request->validated('order', $role->order),
            ];
        } else {
            $updateData = [
                'name' => $request->validated('name'),
                'label' => $request->validated('label'),
                'description' => $request->validated('description'),
                'order' => $request->validated('order', $role->order),
            ];
            
            // السوبر أدمن يمكنه تغيير المشغل
            if ($user->isSuperAdmin() && $request->has('operator_id')) {
                $operatorId = $request->validated('operator_id');
                $updateData['operator_id'] = $operatorId === '' || $operatorId === null ? null : $operatorId;
            }
        }

        $role->update($updateData);

        // تحديث الصلاحيات
        if ($request->has('permissions')) {
            $permissionIds = $request->validated('permissions');
            
            // التحقق من الصلاحيات - منع منح صلاحيات النظام
            if (($user->isAdmin() || $user->isCompanyOwner()) && !empty($permissionIds)) {
                $systemPermissions = $this->getSystemPermissions();
                $systemPermissionIds = Permission::whereIn('name', $systemPermissions)->pluck('id')->toArray();
                $permissionIds = array_diff($permissionIds, $systemPermissionIds);
            }
            
            // Admin لا يمكنه تحديث الأدوار النظامية
            if ($user->isAdmin() && $role->is_system) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'لا يمكنك تحديث الأدوار النظامية.');
            }
            
            $role->permissions()->sync($permissionIds);
        } else {
            $role->permissions()->detach();
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'تم تحديث الدور بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $role);

        // منع حذف الأدوار النظامية
        if ($role->is_system) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن حذف الدور النظامي.',
                ], 403);
            }

            return redirect()->route('admin.roles.index')
                ->with('error', 'لا يمكن حذف الدور النظامي.');
        }

        // التحقق من وجود مستخدمين بهذا الدور
        if ($role->users()->count() > 0) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن حذف الدور لأنه مرتبط بمستخدمين.',
                ], 403);
            }

            return redirect()->route('admin.roles.index')
                ->with('error', 'لا يمكن حذف الدور لأنه مرتبط بمستخدمين.');
        }

        $role->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف الدور بنجاح.',
            ]);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'تم حذف الدور بنجاح.');
    }

    /**
     * الحصول على قائمة الصلاحيات النظامية التي لا يمكن للمشغل منحها
     */
    private function getSystemPermissions(): array
    {
        return [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'operators.view',
            'operators.create',
            'operators.update',
            'operators.delete',
            'permissions.manage',
        ];
    }
}
