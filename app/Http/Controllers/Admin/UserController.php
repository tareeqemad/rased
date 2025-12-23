<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Operator;
use App\Models\User;
use App\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $user = auth()->user();
        $query = User::with(['ownedOperators', 'operators']);

        if ($user->isCompanyOwner()) {
            // المشغل يرى فقط موظفيه
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $query->whereHas('operators', function ($q) use ($operator) {
                    $q->where('operators.id', $operator->id);
                })->whereIn('role', [Role::Employee, Role::Technician]);
            } else {
                $query->whereRaw('1 = 0'); // لا يوجد مستخدمين
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            // الموظف أو الفني لا يمكنهما رؤية المستخدمين
            $query->whereRaw('1 = 0');
        }

        $users = $query->latest()->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', User::class);

        $user = auth()->user();
        $roles = collect(Role::cases());

        // CompanyOwner يمكنه إنشاء موظفين وفنيين فقط
        if ($user->isCompanyOwner()) {
            $roles = $roles->filter(fn ($role) => $role === Role::Employee || $role === Role::Technician);
        }

        $operators = collect();
        if ($user->isSuperAdmin()) {
            $operators = Operator::all();
        } elseif ($user->isCompanyOwner()) {
            $operators = $user->ownedOperators;
        }

        return view('admin.users.create', compact('roles', 'operators'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $authUser = auth()->user();
        $role = Role::from($request->validated('role'));

        // CompanyOwner يمكنه إنشاء موظفين وفنيين فقط
        if ($authUser->isCompanyOwner() && $role !== Role::Employee && $role !== Role::Technician) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'يمكنك إنشاء موظفين وفنيين فقط.');
        }

        $user = User::create([
            'name' => $request->validated('name'),
            'username' => $request->validated('username'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'role' => $role,
        ]);

        // ربط الموظف أو الفني بالمشغل
        if ($user->isEmployee() || $user->isTechnician()) {
            if ($authUser->isCompanyOwner()) {
                // CompanyOwner يربط الموظف أو الفني بمشغله تلقائياً
                $operator = $authUser->ownedOperators()->first();
                if ($operator) {
                    $user->operators()->attach($operator->id);
                }
            } elseif ($request->filled('operator_id')) {
                $user->operators()->attach($request->input('operator_id'));
            }
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'تم إنشاء المستخدم بنجاح.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): View
    {
        $this->authorize('view', $user);

        $user->load(['ownedOperators', 'operators', 'permissions']);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $authUser = auth()->user();
        $roles = collect(Role::cases());

        // CompanyOwner يمكنه تحديث موظفيه وفنييه فقط
        if ($authUser->isCompanyOwner()) {
            $roles = $roles->filter(fn ($role) => $role === Role::Employee || $role === Role::Technician);
        }

        $operators = collect();
        if ($authUser->isSuperAdmin()) {
            $operators = Operator::all();
        } elseif ($authUser->isCompanyOwner()) {
            $operators = $authUser->ownedOperators;
        }

        $userOperators = $user->operators->pluck('id')->toArray();

        return view('admin.users.edit', compact('user', 'roles', 'operators', 'userOperators'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = [
            'name' => $request->validated('name'),
            'username' => $request->validated('username'),
            'email' => $request->validated('email'),
            'role' => Role::from($request->validated('role')),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validated('password'));
        }

        $user->update($data);

        // تحديث المشغلين للموظف أو الفني
        if ($user->isEmployee() || $user->isTechnician()) {
            $authUser = auth()->user();
            if ($authUser->isCompanyOwner()) {
                // CompanyOwner يربط الموظف أو الفني بمشغله فقط
                $operator = $authUser->ownedOperators()->first();
                if ($operator) {
                    $user->operators()->sync([$operator->id]);
                }
            } else {
                $operatorIds = $request->input('operator_id', []);
                $user->operators()->sync($operatorIds);
            }
        } else {
            $user->operators()->detach();
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'تم تحديث المستخدم بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'لا يمكنك حذف حسابك الخاص.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'تم حذف المستخدم بنجاح.');
    }
}
