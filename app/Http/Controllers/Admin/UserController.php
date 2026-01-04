<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Notification;
use App\Models\Operator;
use App\Models\User;
use App\Role;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
   public function index(Request $request)
{
    $this->authorize('viewAny', User::class);

    $actor = $request->user();

    // ✅ New UI expects JSON on ajax=1 / wantsJson
    if ($request->wantsJson() || $request->boolean('ajax')) {
        return $this->ajaxIndex($request, $actor);
    }

    // ✅ Normal page load
    return view('admin.users.index');
}

private function ajaxIndex(Request $request, User $actor): JsonResponse
{
    $name = trim((string) $request->query('name', ''));
    $username = trim((string) $request->query('username', ''));
    $email = trim((string) $request->query('email', ''));
    $role = trim((string) $request->query('role', ''));
    $operatorId = (int) $request->query('operator_id', 0);

    $perPage = (int) $request->query('per_page', 15);
    $perPage = max(5, min(50, $perPage));

    $allowedRoles = array_map(fn(\App\Role $r) => $r->value, \App\Role::cases());
    if ($role !== '' && !in_array($role, $allowedRoles, true)) {
        $role = '';
    }

    // -----------------------------
    // Base scope (حسب صلاحية المستخدم)
    // -----------------------------
    $base = User::query();

    if ($actor->isCompanyOwner()) {
        $operator = $actor->ownedOperators()->select('id')->first();

        if (!$operator) {
            return response()->json([
                'ok' => true,
                'data' => [],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'from' => 0, 'to' => 0, 'total' => 0],
                'stats' => ['total' => 0, 'company_owners' => 0, 'admins' => 0, 'employees' => 0, 'technicians' => 0],
                'message' => 'لا يوجد Operator مرتبط بهذا المشغل.',
            ]);
        }

        // ✅ المشغل يشوف فقط موظفينه وفنيينه
        $base->whereIn('role', [\App\Role::Employee->value, \App\Role::Technician->value])
            ->whereHas('operators', fn(Builder $q) => $q->where('operators.id', $operator->id));

        // ✅ تجاهل فلترة "المشغل" القادمة من الواجهة
        $operatorId = 0;

        // ✅ المشغل لا يفلتر لأدوار غير (employee/technician)
        if (!in_array($role, [\App\Role::Employee->value, \App\Role::Technician->value], true)) {
            $role = '';
        }
    } elseif (!($actor->isSuperAdmin() || $actor->isAdmin())) {
        return response()->json(['ok' => false, 'message' => 'غير مصرح.'], 403);
    }

    // -----------------------------
    // Search
    // -----------------------------
    if ($name !== '' || $username !== '' || $email !== '') {
        $base->where(function (Builder $qb) use ($name, $username, $email) {
            if ($name !== '') {
                $qb->where('users.name', 'like', "%{$name}%");
            }
            if ($username !== '') {
                $qb->where('users.username', 'like', "%{$username}%");
            }
            if ($email !== '') {
                $qb->where('users.email', 'like', "%{$email}%");
            }
        });
    }

    // -----------------------------
    // Operator filter (للسوبر/الأدمن فقط)
    // -----------------------------
    if (($actor->isSuperAdmin() || $actor->isAdmin()) && $operatorId > 0) {
        $base->whereIn('role', [\App\Role::Employee->value, \App\Role::Technician->value])
            ->whereHas('operators', fn(Builder $op) => $op->where('operators.id', $operatorId));
    }

    // -----------------------------
    // Stats (بدون role filter)
    // -----------------------------
    $counts = (clone $base)->toBase()
        ->select('role', DB::raw('COUNT(*) as c'))
        ->groupBy('role')
        ->pluck('c', 'role')
        ->all();

    $stats = [
        'total' => array_sum($counts),
        'company_owners' => (int)($counts[\App\Role::CompanyOwner->value] ?? 0),
        'admins' => (int)($counts[\App\Role::Admin->value] ?? 0) + (int)($counts[\App\Role::SuperAdmin->value] ?? 0),
        'employees' => (int)($counts[\App\Role::Employee->value] ?? 0),
        'technicians' => (int)($counts[\App\Role::Technician->value] ?? 0),
    ];

    // -----------------------------
    // List query (مع eager load)
    // -----------------------------
    $list = (clone $base)->with([
        'operators:id,name',
        'ownedOperators' => function ($q) {
            $q->select('id', 'owner_id', 'name')
              ->withCount([
                  'users as employees_count' => function ($uq) {
                      $uq->whereIn('role', [\App\Role::Employee->value, \App\Role::Technician->value]);
                  },
              ]);
        },
    ]);

    if ($role !== '') {
        $list->where('role', $role);
    }

    $p = $list->orderByDesc('created_at')->paginate($perPage);

    $data = $p->getCollection()->map(function (User $u) use ($actor) {
        $operatorName = null;
        $employeesCount = null;

        if ($u->isCompanyOwner()) {
            $op = $u->ownedOperators->first();
            $operatorName = $op?->name;
            $employeesCount = $op ? (int)($op->employees_count ?? 0) : 0;
        }
        
        // للموظفين والفنيين: أخذ المشغل من علاقة operators
        if (($u->isEmployee() || $u->isTechnician()) && !$operatorName) {
            $operatorName = $u->operators->first()?->name;
        }

            return [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $u->username,
                'email' => $u->email,
                'role' => $u->role?->value ?? (string)$u->getRawOriginal('role'),
                'status' => $u->status ?? 'active',
                'operator' => $operatorName,
                'employees_count' => $employeesCount,
                'created_at' => optional($u->created_at)->format('Y-m-d'),
                'can_edit' => $actor->can('update', $u),
                'can_delete' => $actor->can('delete', $u),
                'urls' => [
                    'edit' => route('admin.users.edit', $u),
                    'permissions' => route('admin.permissions.index', ['user_id' => $u->id]),
                ],
            ];
    })->values();

        return response()->json([
            'ok' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $p->currentPage(),
                'last_page' => $p->lastPage(),
                'from' => $p->firstItem() ?? 0,
                'to' => $p->lastItem() ?? 0,
                'total' => $p->total(),
            ],
            'stats' => $stats,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', User::class);

        $authUser = auth()->user();
        $defaultRole = trim((string)$request->query('role', ''));

        // roles
        $roles = collect(Role::cases());
        if ($authUser->isCompanyOwner()) {
            $roles = $roles->filter(fn ($r) => in_array($r, [Role::Employee, Role::Technician], true));
        }

        // operators
        $operatorLocked = null;
        $operators = collect();

        if ($authUser->isCompanyOwner()) {
            $operatorLocked = $authUser->ownedOperators()->first();
        }

        // ✅ Ajax Modal
        if ($request->ajax() || $request->boolean('modal')) {
            return view('admin.users.partials.modal-form', [
                'mode' => 'create',
                'user' => null,
                'roles' => $roles,
                'defaultRole' => $defaultRole,
                'operatorLocked' => $operatorLocked,
                'operators' => $operators,
            ]);
        }

        return view('admin.users.create', compact('roles', 'operators', 'operatorLocked', 'defaultRole'));
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorize('update', $user);

        $authUser = auth()->user();

        $roles = collect(Role::cases());
        if ($authUser->isCompanyOwner()) {
            $roles = $roles->filter(fn ($r) => in_array($r, [Role::Employee, Role::Technician], true));
        }

        $operatorLocked = null;
        $operators = collect();
        if ($authUser->isCompanyOwner()) {
            $operatorLocked = $authUser->ownedOperators()->first();
        }

        $userOperators = $user->operators->pluck('id')->toArray();
        $selectedOperator = $user->operators->first();

        if ($request->ajax() || $request->boolean('modal')) {
            return view('admin.users.partials.modal-form', [
                'mode' => 'edit',
                'user' => $user,
                'roles' => $roles,
                'defaultRole' => '',
                'operatorLocked' => $operatorLocked,
                'operators' => $operators,
                'userOperators' => $userOperators,
                'selectedOperator' => $selectedOperator,
            ]);
        }

        return view('admin.users.edit', compact('user', 'roles', 'operators', 'operatorLocked', 'userOperators'));
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);

        // تحميل العلاقات المطلوبة
        $user->load(['ownedOperators', 'operators', 'permissions']);

        return view('admin.users.show', compact('user'));
    }

    public function store(StoreUserRequest $request)
    {
        $authUser = auth()->user();
        $role = Role::from($request->validated('role'));

        if ($authUser->isCompanyOwner() && !in_array($role, [Role::Employee, Role::Technician], true)) {
            return $this->jsonOrRedirect($request, false, 'يمكنك إنشاء موظفين وفنيين فقط.');
        }

        // الحصول على role_id من جدول roles
        $roleModel = \App\Models\Role::findByName($role->value);
        
        $user = User::create([
            'name' => $request->validated('name'),
            'username' => $request->validated('username'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'role' => $role,
            'role_id' => $roleModel?->id,
        ]);

        // ربط الموظف/الفني بمشغل واحد فقط
        if ($user->isEmployee() || $user->isTechnician()) {
            if ($authUser->isCompanyOwner()) {
                $operator = $authUser->ownedOperators()->first();
                if (!$operator) {
                    $user->delete();
                    return $this->jsonOrRedirect($request, false, 'لا يوجد مشغل مرتبط بحسابك. أكمل ملف المشغل أولاً.');
                }
                $user->operators()->sync([$operator->id]);
            } else {
                $operatorId = (int)$request->input('operator_id');
                if (!$operatorId) {
                    $user->delete();
                    return $this->jsonOrRedirect($request, false, 'اختر المشغل لربط الموظف/الفني.');
                }
                $user->operators()->sync([$operatorId]);
            }
        }

        if (!auth()->user()->isSuperAdmin()) {
            Notification::notifySuperAdmins(
                'user_added',
                'تم إضافة مستخدم جديد',
                "تم إضافة المستخدم: {$user->name} ({$user->role_name})",
                route('admin.users.show', $user)
            );
        }

        return $this->jsonOrRedirect($request, true, 'تم إنشاء المستخدم بنجاح.');
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $authUser = auth()->user();

        // الحصول على role_id من جدول roles
        $newRole = Role::from($request->validated('role'));
        $roleModel = \App\Models\Role::findByName($newRole->value);

        $data = [
            'name' => $request->validated('name'),
            'username' => $request->validated('username'),
            'email' => $request->validated('email'),
            'role' => $newRole,
            'role_id' => $roleModel?->id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validated('password'));
        }

        $user->update($data);

        if ($user->isEmployee() || $user->isTechnician()) {
            if ($authUser->isCompanyOwner()) {
                $operator = $authUser->ownedOperators()->first();
                if (!$operator) {
                    return $this->jsonOrRedirect($request, false, 'لا يوجد مشغل مرتبط بحسابك.');
                }
                $user->operators()->sync([$operator->id]);
            } else {
                $operatorId = (int) $request->validated('operator_id', 0);
                if (!$operatorId) {
                    return $this->jsonOrRedirect($request, false, 'اختر المشغل لربط الموظف/الفني.');
                }
                $user->operators()->sync([$operatorId]);
            }
        } else {
            $user->operators()->detach();
        }

        return $this->jsonOrRedirect($request, true, 'تم تحديث المستخدم بنجاح.');
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorize('delete', $user);

        if ($user->id === auth()->id()) {
            return $this->jsonOrRedirect($request, false, 'لا يمكنك حذف حسابك الخاص.');
        }

        $user->delete();

        return $this->jsonOrRedirect($request, true, 'تم حذف المستخدم بنجاح.');
    }

    /**
     * Toggle user status (active/inactive)
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $authUser = auth()->user();
        
        // التحقق من الصلاحية (update policy)
        $this->authorize('update', $user);

        // السوبر أدمن أو المشغل يمكنهما تغيير الحالة
        // (Policy يتحقق من العلاقة للمشغل)
        if (!$authUser->isSuperAdmin() && !$authUser->isCompanyOwner()) {
            abort(403, 'لا تملك صلاحية لتغيير حالة المستخدم');
        }

        // منع إيقاف نفسه
        if ($user->id === $authUser->id) {
            return $this->jsonOrRedirect($request, false, 'لا يمكنك إيقاف حسابك الخاص.');
        }

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        $statusLabel = $user->status === 'active' ? 'تفعيل' : 'إيقاف';
        $message = "تم {$statusLabel} المستخدم بنجاح";

        return $this->jsonOrRedirect($request, true, $message);
    }

    /**
     * Select2 operators (server-side)
     */
    public function ajaxOperators(Request $request)
    {
        $authUser = auth()->user();
        if (!$authUser || !$authUser->isSuperAdmin()) {
            abort(403);
        }

       $term = trim((string) $request->query('q', $request->query('term', '')));
        $page = max(1, (int)$request->query('page', 1));
        $perPage = 10;

        $query = Operator::query()->orderBy('name');

        if ($term !== '') {
            $query->where(function ($x) use ($term) {
                $x->where('name', 'like', "%{$term}%")
                    ->orWhereHas('owner', function ($q) use ($term) {
                        $q->where('name', 'like', "%{$term}%")
                          ->orWhere('username', 'like', "%{$term}%")
                          ->orWhere('email', 'like', "%{$term}%");
                    });
            });
        }

        $p = $query->paginate($perPage, ['id', 'name'], 'page', $page);

        $results = $p->getCollection()->map(function ($op) {
            return ['id' => $op->id, 'text' => $op->name];
        })->values();

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $p->hasMorePages()],
        ]);
    }

    /**
     * الدخول بحساب مستخدم آخر (للسوبر أدمن فقط)
     */
    public function impersonate(Request $request, User $user): RedirectResponse
    {
        $currentUser = auth()->user();

        // التحقق من أن المستخدم الحالي هو سوبر أدمن
        if (!$currentUser->isSuperAdmin()) {
            return redirect()->back()->with('error', 'غير مصرح لك بالدخول بحساب مستخدم آخر.');
        }

        // منع الدخول بحساب نفسه
        if ($currentUser->id === $user->id) {
            return redirect()->back()->with('error', 'لا يمكنك الدخول بحسابك الخاص.');
        }

        // حفظ معلومات المستخدم الأصلي في Session
        session()->put('impersonator_id', $currentUser->id);
        session()->put('impersonator_name', $currentUser->name);

        // تسجيل الدخول بحساب المستخدم المطلوب
        Auth::login($user);

        return redirect()->route('admin.dashboard')->with('success', "تم الدخول بحساب {$user->name} بنجاح.");
    }

    /**
     * الخروج من حساب المستخدم والعودة للحساب الأصلي
     */
    public function stopImpersonating(Request $request): RedirectResponse
    {
        $impersonatorId = session()->get('impersonator_id');

        if (!$impersonatorId) {
            return redirect()->route('admin.dashboard')->with('error', 'لا يوجد حساب أصلي للعودة إليه.');
        }

        $impersonator = User::find($impersonatorId);

        if (!$impersonator) {
            session()->forget(['impersonator_id', 'impersonator_name']);
            return redirect()->route('login')->with('error', 'الحساب الأصلي غير موجود.');
        }

        // حذف معلومات الـ impersonation من Session
        session()->forget(['impersonator_id', 'impersonator_name']);

        // تسجيل الدخول بالحساب الأصلي
        Auth::login($impersonator);

        return redirect()->route('admin.users.index')->with('success', 'تم العودة لحسابك الأصلي بنجاح.');
    }

    private function jsonOrRedirect(Request $request, bool $ok, string $message)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok' => $ok,
                'message' => $message,
            ], $ok ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $ok
            ? redirect()->route('admin.users.index')->with('success', $message)
            : redirect()->back()->withInput()->with('error', $message);
    }
}
