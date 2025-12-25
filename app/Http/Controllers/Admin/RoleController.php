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

        $query = Role::withCount(['users', 'permissions']);

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

        $permissions = Permission::orderBy('group')->orderBy('order')->get()->groupBy('group');

        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create([
            'name' => $request->validated('name'),
            'label' => $request->validated('label'),
            'description' => $request->validated('description'),
            'is_system' => false,
            'order' => $request->validated('order', 0),
        ]);

        // ربط الصلاحيات بالدور
        if ($request->has('permissions')) {
            $role->permissions()->attach($request->validated('permissions'));
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

        $permissions = Permission::orderBy('group')->orderBy('order')->get()->groupBy('group');
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
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
        }

        $role->update($updateData);

        // تحديث الصلاحيات
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->validated('permissions'));
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
}
