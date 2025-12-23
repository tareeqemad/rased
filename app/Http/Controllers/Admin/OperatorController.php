<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOperatorRequest;
use App\Http\Requests\Admin\UpdateOperatorRequest;
use App\Models\Operator;
use App\Models\User;
use App\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class OperatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('viewAny', Operator::class);

        $user = auth()->user();

        $operators = Operator::with('owner')
            ->when($user->isCompanyOwner(), function ($query) use ($user) {
                // المشغل يرى فقط مشغله
                $query->where('owner_id', $user->id);
            })
            ->when($user->isEmployee(), function ($query) use ($user) {
                // الموظف يرى فقط المشغل الذي يعمل عنده
                $query->whereIn('id', $user->operators->pluck('id'));
            })
            ->latest()
            ->paginate(15);

        return view('admin.operators.index', compact('operators'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Operator::class);

        return view('admin.operators.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOperatorRequest $request): RedirectResponse
    {
        // إنشاء المستخدم للمشغل
        $user = User::create([
            'name' => $request->validated('name'),
            'username' => $request->validated('username'),
            'email' => $request->validated('user_email') ?? $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'role' => Role::CompanyOwner,
        ]);

        // إنشاء المشغل وربطه بالمستخدم
        $operator = Operator::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'address' => $request->validated('address'),
            'owner_id' => $user->id,
        ]);

        return redirect()->route('admin.operators.index')
            ->with('success', 'تم إنشاء المشغل بنجاح. يمكن للمشغل تسجيل الدخول باستخدام: '.$request->validated('username'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Operator $operator): View
    {
        $this->authorize('view', $operator);

        $operator->load(['owner', 'generators', 'users']);

        return view('admin.operators.show', compact('operator'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Operator $operator): View
    {
        $this->authorize('update', $operator);

        $operator->load('owner');

        return view('admin.operators.edit', compact('operator'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOperatorRequest $request, Operator $operator): RedirectResponse
    {
        $operator->update([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'address' => $request->validated('address'),
        ]);

        // تحديث بيانات المستخدم إذا تم توفيرها (فقط SuperAdmin يمكنه تغيير username)
        if ($operator->owner && auth()->user()->isSuperAdmin()) {
            $userData = [];

            // فقط SuperAdmin يمكنه تغيير username
            if ($request->filled('username')) {
                $userData['username'] = $request->validated('username');
            }

            if ($request->filled('user_email')) {
                $userData['email'] = $request->validated('user_email');
            }

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->validated('password'));
            }

            if (! empty($userData)) {
                $operator->owner->update($userData);
            }
        } elseif ($operator->owner && ! auth()->user()->isSuperAdmin()) {
            // المشغل يمكنه تغيير email و password فقط، لكن ليس username
            $userData = [];

            if ($request->filled('user_email')) {
                $userData['email'] = $request->validated('user_email');
            }

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->validated('password'));
            }

            if (! empty($userData)) {
                $operator->owner->update($userData);
            }
        }

        return redirect()->route('admin.operators.index')
            ->with('success', 'تم تحديث بيانات المشغل بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Operator $operator): RedirectResponse
    {
        $this->authorize('delete', $operator);

        // حذف المستخدم المرتبط
        if ($operator->owner) {
            $operator->owner->delete();
        }

        $operator->delete();

        return redirect()->route('admin.operators.index')
            ->with('success', 'تم حذف المشغل بنجاح.');
    }
}
