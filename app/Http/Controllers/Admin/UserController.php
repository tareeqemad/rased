<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Notification;
use App\Models\Operator;
use App\Models\User;
use App\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * ============================================
 * UserController - User Management Controller
 * ============================================
 *
 * This controller is responsible for managing all users in the system.
 *
 * Note: Roles are not fixed. Energy Authority and Company Owners can define custom roles
 * and assign permissions as needed. The system supports both system roles (super_admin,
 * admin, energy_authority, company_owner) and custom roles defined by operators or energy authority.
 *
 * Main Roles:
 * ------------------
 * 1. Super Admin (SuperAdmin):
 *    - Can create: SuperAdmin, Admin, CompanyOwner, and custom roles
 *    - Has full control over all users
 *
 * 2. Energy Authority (EnergyAuthority) - Main role in the system:
 *    - Can create: Admin, EnergyAuthority, CompanyOwner, and custom roles
 *    - Can define custom roles with specific permissions
 *    - Can add operators through authorized phone numbers
 *    - Has access to roles and permissions definition
 *    - Can define users under their authority
 *    - Has control over users and operators
 *
 * 3. Company Owner (CompanyOwner):
 *    - Can create: Users with custom roles defined by Energy Authority or their own custom roles
 *    - Can manage their own users
 *
 * SMS Notification:
 * ------------------
 * When a user is created, if a phone number is provided, SMS is automatically sent with:
 * - Welcome message
 * - Role name (from database - supports custom roles)
 * - Username
 * - Password
 * - Login link
 *
 * ============================================
 */
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

        $allowedRoles = array_map(fn (\App\Role $r) => $r->value, \App\Role::cases());
        if ($role !== '' && ! in_array($role, $allowedRoles, true)) {
            $role = '';
        }

        // -----------------------------
        // Base scope (filter by user permissions)
        // -----------------------------
        $base = User::query()
            ->where('username', '!=', 'platform_rased'); // Exclude system user (منصة راصد) from lists

        if ($actor->isCompanyOwner()) {
            $operator = $actor->ownedOperators()->select('id')->first();

            if (! $operator) {
                return response()->json([
                    'ok' => true,
                    'data' => [],
                    'meta' => ['current_page' => 1, 'last_page' => 1, 'from' => 0, 'to' => 0, 'total' => 0],
                    'stats' => ['total' => 0, 'company_owners' => 0, 'admins' => 0, 'custom_roles' => 0],
                    'message' => 'لا يوجد Operator مرتبط بهذا المشغل.',
                ]);
            }

            // Company Owner can only see users with custom roles linked to their operator
            // Custom roles are defined dynamically by Energy Authority or Company Owner
            $customRoleIds = \App\Models\Role::getCustomRolesForOperator($operator->id)->pluck('id')->toArray();

            if (empty($customRoleIds)) {
                // No custom roles defined yet, show only operator owner
                $base->where('id', $operator->owner_id);
            } else {
                // Show users with custom roles linked to this operator
                $base->where(function (Builder $q) use ($operator, $customRoleIds) {
                    $q->where('id', $operator->owner_id) // The operator owner
                        ->orWhere(function (Builder $sub) use ($customRoleIds) {
                            // Users with custom roles linked to this operator
                            $sub->whereIn('role_id', $customRoleIds)
                                ->whereHas('operators', fn (Builder $op) => $op->where('operators.id', $operator->id));
                        });
                });
            }

            // Ignore operator filter from UI (Company Owner only sees their own operator)
            $operatorId = 0;

            // Company Owner can only filter by custom roles (not system roles)
            $systemRoles = [\App\Role::SuperAdmin->value, \App\Role::Admin->value, \App\Role::EnergyAuthority->value, \App\Role::CompanyOwner->value];
            if (in_array($role, $systemRoles, true)) {
                $role = '';
            }
        } elseif (! ($actor->isSuperAdmin() || $actor->isEnergyAuthority())) {
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
        // Operator filter (for SuperAdmin/EnergyAuthority only)
        // -----------------------------
        // If a specific operator is selected: show operator + their users (with custom roles)
        // Note: When operator is selected, we ignore role filter to show operator + all their users
        if (($actor->isSuperAdmin() || $actor->isEnergyAuthority()) && $operatorId > 0) {
            $operator = Operator::find($operatorId);
            if ($operator) {
                // The operator (parent/owner)
                $operatorOwnerId = $operator->owner_id;

                // Get custom roles linked to this operator
                $customRoleIds = \App\Models\Role::getCustomRolesForOperator($operatorId)->pluck('id')->toArray();

                // Build query to include: operator + users with custom roles linked to this operator
                $base->where(function (Builder $q) use ($operatorId, $operatorOwnerId, $customRoleIds) {
                    // The operator owner
                    $q->where('id', $operatorOwnerId);

                    // Users with custom roles linked to this operator
                    if (! empty($customRoleIds)) {
                        $q->orWhere(function (Builder $sub) use ($operatorId, $customRoleIds) {
                            $sub->whereIn('role_id', $customRoleIds)
                                ->whereHas('operators', fn (Builder $op) => $op->where('operators.id', $operatorId));
                        });
                    }
                });

                // Ignore role filter when operator is selected
                $role = '';
            }
        }

        // -----------------------------
        // Stats (without role filter)
        // -----------------------------
        $counts = (clone $base)->toBase()
            ->select('role', DB::raw('COUNT(*) as c'))
            ->groupBy('role')
            ->pluck('c', 'role')
            ->all();

        // Count custom roles (non-system roles)
        $customRolesCount = (clone $base)->whereHas('roleModel', function ($q) {
            $q->where('is_system', false);
        })->count();

        $stats = [
            'total' => array_sum($counts),
            'company_owners' => (int) ($counts[\App\Role::CompanyOwner->value] ?? 0),
            'admins' => (int) ($counts[\App\Role::Admin->value] ?? 0) + (int) ($counts[\App\Role::SuperAdmin->value] ?? 0),
            'custom_roles' => $customRolesCount,
        ];

        // -----------------------------
        // List query (with eager loading)
        // -----------------------------
        $list = (clone $base)->with([
            'operators:id,name',
            'roleModel:id,name,label',
            'ownedOperators' => function ($q) {
                $q->select('id', 'owner_id', 'name')
                    ->withCount([
                        // Count users with custom roles (non-system roles) linked to this operator
                        'users as custom_users_count' => function ($uq) {
                            $uq->whereHas('roleModel', function ($roleQ) {
                                $roleQ->where('is_system', false);
                            });
                        },
                    ]);
            },
        ]);

        // Apply role filter only if operator is not selected
        // (because selecting operator means showing operator + all their users regardless of role)
        if ($role !== '' && $operatorId === 0) {
            $list->where('role', $role);
        }

        $p = $list->orderByDesc('created_at')->paginate($perPage);

        $data = $p->getCollection()->map(function (User $u) use ($actor) {
            $operatorName = null;
            $employeesCount = null;

            if ($u->isCompanyOwner()) {
                $op = $u->ownedOperators->first();
                $operatorName = $op?->name;
                $employeesCount = $op ? (int) ($op->custom_users_count ?? 0) : 0;
            }

            // For users with custom roles: get operator from roleModel or operators relationship
            if ($u->hasCustomRole() && ! $operatorName) {
                if ($u->roleModel && $u->roleModel->operator_id) {
                    $operatorName = $u->roleModel->operator?->name;
                } else {
                    $operatorName = $u->operators->first()?->name;
                }
            }

            // Get operator_id
            $operatorId = null;
            if ($u->isCompanyOwner()) {
                $op = $u->ownedOperators->first();
                $operatorId = $op?->id;
            } elseif ($u->hasCustomRole()) {
                // User with custom role: get operator from roleModel or operators relationship
                if ($u->roleModel && $u->roleModel->operator_id) {
                    $operatorId = $u->roleModel->operator_id;
                } else {
                    $operatorId = $u->operators->first()?->id;
                }
            }

            return [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $u->username,
                'email' => $u->email,
                'phone' => $u->phone ?? null,
                'role' => $u->role?->value ?? (string) $u->getRawOriginal('role'),
                'status' => $u->status ?? 'active',
                'operator' => $operatorName,
                'operator_id' => $operatorId,
                'employees_count' => $employeesCount,
                'created_at' => optional($u->created_at)->format('Y-m-d'),
                'can_edit' => $actor->can('update', $u),
                'can_delete' => $actor->can('delete', $u),
                'urls' => [
                    'show' => route('admin.users.show', $u),
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
        $defaultRole = trim((string) $request->query('role', ''));

        // Get available roles based on user authority
        // Company Owner can ONLY see and use custom roles they created (no system roles at all)
        // SuperAdmin and Energy Authority can see system roles and all custom roles
        $roles = collect(Role::cases());
        $customRoles = collect();

        if ($authUser->isCompanyOwner()) {
            // Company Owner can ONLY use custom roles they created (no system roles, no general roles, no roles from others)
            $customRoles = \App\Models\Role::getAvailableCustomRoles($authUser);
            // Remove all system roles - Company Owner should not see any system roles
            $roles = collect(); // Empty - Company Owner only uses custom roles
        } elseif ($authUser->isSuperAdmin() || $authUser->isEnergyAuthority()) {
            // SuperAdmin and Energy Authority can see all system roles and custom roles
            $customRoles = \App\Models\Role::getAvailableCustomRoles($authUser);
        }

        // operators
        $operatorLocked = null;
        $operators = collect();

        if ($authUser->isCompanyOwner()) {
            $operatorLocked = $authUser->ownedOperators()->first();
        } elseif ($authUser->isSuperAdmin() || $authUser->isEnergyAuthority()) {
            $operators = Operator::select('id', 'name')->orderBy('name')->get();
        }

        // ✅ Ajax Modal
        if ($request->ajax() || $request->boolean('modal')) {
            return view('admin.users.partials.modal-form', [
                'mode' => 'create',
                'user' => null,
                'roles' => $roles,
                'customRoles' => $customRoles,
                'defaultRole' => $defaultRole,
                'operatorLocked' => $operatorLocked,
                'operators' => $operators,
            ]);
        }

        return view('admin.users.create', compact('roles', 'customRoles', 'operators', 'operatorLocked', 'defaultRole'));
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorize('update', $user);

        $authUser = auth()->user();

        // Get available roles based on user authority
        // Company Owner can ONLY see and use custom roles they created (no system roles at all)
        $roles = collect(Role::cases());
        $customRoles = collect();

        if ($authUser->isCompanyOwner()) {
            // Company Owner can ONLY use custom roles they created (no system roles, no general roles, no roles from others)
            $customRoles = \App\Models\Role::getAvailableCustomRoles($authUser);
            // Remove all system roles - Company Owner should not see any system roles
            $roles = collect(); // Empty - Company Owner only uses custom roles
        } elseif ($authUser->isSuperAdmin() || $authUser->isEnergyAuthority()) {
            // SuperAdmin and Energy Authority can see all system roles and custom roles
            $customRoles = \App\Models\Role::getAvailableCustomRoles($authUser);
        }

        $operatorLocked = null;
        $operators = collect();
        if ($authUser->isCompanyOwner()) {
            $operatorLocked = $authUser->ownedOperators()->first();
        } elseif ($authUser->isSuperAdmin() || $authUser->isEnergyAuthority()) {
            $operators = Operator::select('id', 'name')->orderBy('name')->get();
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
        // Prevent viewing system user (منصة راصد)
        if ($user->isSystemUser()) {
            abort(404, 'User not found');
        }

        $this->authorize('view', $user);

        // Load required relationships
        $user->load([
            'ownedOperators',
            'operators',
            'permissions',
            'roleModel',
        ]);

        // تحميل بيانات المشغل إذا كان المستخدم مشغل أو إذا كان السوبر أدمن يشوف ملف مشغل
        $operator = null;
        $authUser = auth()->user();

        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->with([
                'cityDetail',
                'generationUnits' => function ($q) {
                    $q->withCount('generators');
                },
            ])->first();
        } elseif (($authUser->isSuperAdmin() || $authUser->isEnergyAuthority()) && $user->isCompanyOwner()) {
            // السوبر أدمن يمكنه رؤية ملف المشغل من صفحة المستخدم
            $operator = $user->ownedOperators()->with([
                'cityDetail',
                'generationUnits' => function ($q) {
                    $q->withCount('generators');
                },
            ])->first();
        }

        return view('admin.users.show', compact('user', 'operator'));
    }

    /**
     * Create a new user in the system
     *
     * ============================================
     * User Creation Policy by Role:
     * ============================================
     *
     * Note: Roles are not fixed. Energy Authority and Company Owners can define custom roles
     * and assign permissions as needed. The system supports both system roles (super_admin,
     * admin, energy_authority, company_owner) and custom roles defined by operators or energy authority.
     *
     * 1. Super Admin (SuperAdmin):
     *    - Can create: SuperAdmin, Admin, CompanyOwner, and users with custom roles (defined by Energy Authority)
     *    - When creating SuperAdmin, Admin, or CompanyOwner:
     *      * Input: Name, Name (English), Phone, Email, Role
     *      * Username auto-generated: sp_ + first_char + last_name (for SuperAdmin)
     *      * Password auto-generated (8 random characters)
     *      * SMS sent automatically with credentials and login link (if phone provided)
     *
     * 2. Energy Authority (EnergyAuthority):
     *    - Can create: Admin, EnergyAuthority, CompanyOwner, and custom roles
     *    - Can define custom roles with specific permissions
     *    - When creating Admin or CompanyOwner:
     *      * Input: Name, Name (English), Phone, Email, Role
     *      * Username auto-generated: ad_ + first_char + last_name (for Admin)
     *      * Password auto-generated (8 random characters)
     *      * SMS sent automatically with credentials and login link (if phone provided)
     *    - When creating user for a specific operator (custom role):
     *      * Username auto-generated: operator_username_user_name (to show operator affiliation)
     *      * Example: co_ababa_ahmad_mohammed (clear that user belongs to operator co_ababa)
     *      * Password auto-generated (8 random characters)
     *      * SMS sent automatically with credentials and login link (if phone provided)
     *    - Can add operators through authorized phone numbers
     *    - Has full control over roles and permissions definition
     *
     * 3. Company Owner (CompanyOwner):
     *    - Can create: Users with custom roles defined by Energy Authority or their own custom roles
     *    - When creating user for their operator:
     *      * Input: Name only (phone optional)
     *      * Username auto-generated: operator_username_user_name
     *      *   Example: co_ababa_ahmad_mohammed (clear that user belongs to operator co_ababa)
     *      * Password auto-generated (8 random characters)
     *      * SMS sent automatically with credentials and login link (if phone provided)
     *
     * ============================================
     */
    public function store(StoreUserRequest $request)
    {
        $authUser = auth()->user();
        $role = Role::from($request->validated('role'));

        // ============================================
        // Step 1: Determine if role is custom role or system role
        // ============================================
        $systemRoles = [Role::SuperAdmin->value, Role::Admin->value, Role::EnergyAuthority->value, Role::CompanyOwner->value];
        $isSystemRole = in_array($role->value, $systemRoles, true);
        $isCustomRole = ! $isSystemRole;

        // Check permissions: Company Owner can ONLY create users with custom roles they created
        // Company Owner cannot create users with any system roles (including CompanyOwner itself)
        if ($authUser->isCompanyOwner() && $isSystemRole) {
            return $this->jsonOrRedirect($request, false, 'يمكنك إنشاء مستخدمين بأدوار مخصصة أنشأتها أنت فقط.');
        }

        // ============================================
        // Step 2: Get role_id from roles table (for system roles) or from request (for custom roles)
        // ============================================
        $roleModel = null;
        if ($isSystemRole) {
            $roleModel = \App\Models\Role::findByName($role->value);
        } else {
            // Custom role: get role_id from request
            $roleId = (int) $request->input('role_id');
            if (! $roleId) {
                return $this->jsonOrRedirect($request, false, 'يجب تحديد الدور المخصص.');
            }
            $roleModel = \App\Models\Role::find($roleId);
            if (! $roleModel || $roleModel->is_system) {
                return $this->jsonOrRedirect($request, false, 'الدور المحدد غير موجود أو غير صالح.');
            }

            // Check if Company Owner is trying to use a role that doesn't belong to them
            if ($authUser->isCompanyOwner()) {
                $operator = $authUser->ownedOperators()->first();
                if ($operator && $roleModel->operator_id && $roleModel->operator_id !== $operator->id) {
                    return $this->jsonOrRedirect($request, false, 'لا يمكنك استخدام دور يخص مشغل آخر.');
                }
            }
        }

        // ============================================
        // Step 3: Link user to operator (if applicable)
        // Custom roles may need to be linked to an operator
        // ============================================
        $operator = null;
        if ($isCustomRole) {
            // Custom role: check if it's linked to an operator or needs operator selection
            if ($roleModel && $roleModel->operator_id) {
                // Role is already linked to an operator
                $operator = $roleModel->operator;
            } elseif ($authUser->isCompanyOwner()) {
                // Company Owner creating user: use their own operator
                $operator = $authUser->ownedOperators()->first();
                if (! $operator) {
                    return $this->jsonOrRedirect($request, false, 'لا يوجد مشغل مرتبط بحسابك. أكمل ملف المشغل أولاً.');
                }
            } else {
                // Energy Authority or SuperAdmin creating user with custom role: need to select operator
                $operatorId = (int) $request->input('operator_id');
                if (! $operatorId) {
                    return $this->jsonOrRedirect($request, false, 'اختر المشغل لربط المستخدم.');
                }
                $operator = Operator::find($operatorId);
                if (! $operator) {
                    return $this->jsonOrRedirect($request, false, 'المشغل المحدد غير موجود.');
                }
            }
        }

        // ============================================
        // Step 3: Auto-generate username and password based on role
        // ============================================
        $username = $request->validated('username');
        $password = $request->validated('password');

        // Case 1: Company Owner or Energy Authority adds user with custom role for a specific operator
        // username = operator_username_user_name (to show operator affiliation)
        if ($operator && $operator->owner && $isCustomRole) {
            $operatorUsername = $operator->owner->username;
            $userName = trim($request->validated('name'));

            // Clean user name (remove spaces and special characters)
            $cleanUserName = preg_replace('/[^a-zA-Z0-9\u0600-\u06FF\s]/', '', $userName);
            $cleanUserName = preg_replace('/\s+/', '_', trim($cleanUserName));
            $cleanUserName = mb_strtolower($cleanUserName);

            // username = operator_username_user_name
            // Example: co_ababa_ahmad_mohammed (clear that user belongs to operator co_ababa)
            $usernameBase = $operatorUsername.'_'.$cleanUserName;

            // Ensure username is unique
            $counter = 1;
            $username = $usernameBase;
            while (User::where('username', $username)->whereNull('deleted_at')->exists()) {
                $username = $usernameBase.$counter;
                $counter++;
            }

            // Auto-generate password (8 random characters)
            $password = \Illuminate\Support\Str::random(8);
            $password = preg_replace('/[^a-zA-Z0-9]/', '', $password);
            if (strlen($password) < 6) {
                $password = \Illuminate\Support\Str::random(8);
                $password = preg_replace('/[^a-zA-Z0-9]/', '', $password);
            }
            if (strlen($password) < 8) {
                $password = str_pad($password, 8, \Illuminate\Support\Str::random(1), STR_PAD_RIGHT);
            }
        } // Case 2: SuperAdmin or EnergyAuthority adds system role user (SuperAdmin, Admin, EnergyAuthority, CompanyOwner)
        elseif (($authUser->isSuperAdmin() || $authUser->isEnergyAuthority()) && in_array($role, [Role::SuperAdmin, Role::Admin, Role::EnergyAuthority, Role::CompanyOwner], true)) {
            // Auto-generate username:
            // - SuperAdmin: sp_ + first_char + last_name (example: sp_telbawab)
            // - Admin: ad_ + first_char + last_name (example: ad_gadmin)
            // - EnergyAuthority: ea_ + first_char + last_name (example: ea_amanager)
            // - CompanyOwner: co_ + first_char + last_name (example: co_ababa)

            $name = trim($request->validated('name'));
            $nameEn = trim($request->validated('name_en', ''));

            // Use name_en if available, otherwise use name
            $nameToUse = $nameEn ?: $name;

            // Split name into words
            $nameParts = preg_split('/[\s\-_]+/', $nameToUse);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[count($nameParts) - 1] ?? $firstName;

            // Get first character of first name
            $firstChar = mb_substr($firstName, 0, 1, 'UTF-8');
            $firstChar = mb_strtolower($firstChar, 'UTF-8');

            // Clean last name
            $cleanLastName = preg_replace('/[^a-zA-Z0-9\u0600-\u06FF]/', '', $lastName);
            $cleanLastName = mb_strtolower($cleanLastName, 'UTF-8');

            // Determine prefix based on role
            $prefix = match ($role) {
                Role::SuperAdmin => 'sp_',
                Role::EnergyAuthority => 'ea_',
                Role::CompanyOwner => 'co_',
                default => 'ad_',
            };

            // username = prefix + first_char + last_name
            $usernameBase = $prefix.$firstChar.$cleanLastName;

            // Ensure username is unique
            $counter = 1;
            $username = $usernameBase;
            while (User::where('username', $username)->whereNull('deleted_at')->exists()) {
                $username = $usernameBase.$counter;
                $counter++;
            }

            // Auto-generate password (8 random characters)
            $password = \Illuminate\Support\Str::random(8);
            $password = preg_replace('/[^a-zA-Z0-9]/', '', $password);
            if (strlen($password) < 6) {
                $password = \Illuminate\Support\Str::random(8);
                $password = preg_replace('/[^a-zA-Z0-9]/', '', $password);
            }
            if (strlen($password) < 8) {
                $password = str_pad($password, 8, \Illuminate\Support\Str::random(1), STR_PAD_RIGHT);
            }
        }

        $plainPassword = $password;

        // ============================================
        // Step 4: Generate unique email if not provided
        // ============================================
        $email = $request->validated('email');
        if (! $email) {
            $email = $username.'@gazarased.com';
            $counter = 1;
            while (User::where('email', $email)->whereNull('deleted_at')->exists()) {
                $email = $username.'_'.$counter.'@gazarased.com';
                $counter++;
            }
        }

        // ============================================
        // Step 5: Create user in database
        // ============================================
        $user = User::create([
            'name' => $request->validated('name'),
            'name_en' => $request->validated('name_en'),
            'phone' => $request->validated('phone'),
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($plainPassword),
            'password_plain' => $plainPassword,
            'role' => $role,
            'role_id' => $roleModel?->id,
        ]);

        // ============================================
        // Step 6: Link user to operator (if applicable)
        // ============================================
        if ($isCustomRole && $operator) {
            // User with custom role: link to operator
            $user->operators()->sync([$operator->id]);
        } elseif ($user->isCompanyOwner() && ($authUser->isSuperAdmin() || $authUser->isEnergyAuthority())) {
            // If SuperAdmin/EnergyAuthority adds CompanyOwner, link to operator
            $operatorId = (int) $request->input('operator_id');
            if ($operatorId) {
                $operator = Operator::find($operatorId);
                if (! $operator) {
                    return $this->jsonOrRedirect($request, false, 'المشغل المحدد غير موجود.');
                }

                // Verify that operator phone exists in authorized phones
                $operatorPhone = $operator->phone;
                if ($operatorPhone) {
                    $authorizedPhone = \App\Models\AuthorizedPhone::where('phone', $operatorPhone)
                        ->where('status', 'active')
                        ->first();

                    if (! $authorizedPhone) {
                        return $this->jsonOrRedirect($request, false, 'لا يمكن إضافة مشغل: رقم المشغل غير موجود في الأرقام المصرح بها. يرجى إضافة الرقم أولاً.');
                    }
                }

                // Link user to operator
                $user->operators()->sync([$operator->id]);

                // Note: If this is a new operator (created via storeJoinRequest), Operator::boot() will send notification
                // But if this is an existing operator that was just linked to a new owner, we don't send notification here
                // because the operator already exists and notifications were sent when it was first created
                // Only send notification if operator owner was just updated and operator is not approved
                // (This handles the case where SuperAdmin/EnergyAuthority creates a CompanyOwner and links to an existing unapproved operator)
                if (! $operator->is_approved && $operator->owner_id === $user->id) {
                    // Check if operator was just created (within last 5 seconds) - if so, boot() already sent notification
                    $recentlyCreated = $operator->created_at && $operator->created_at->gt(now()->subSeconds(5));
                    if (! $recentlyCreated) {
                        // Operator exists but not approved and owner just linked - send notification
                        \App\Models\Notification::notifyOperatorApprovers(
                            'operator_pending_approval',
                            'مشغل يحتاج للاعتماد',
                            "تم ربط مشغل ({$operator->name}) بمشغل جديد ({$user->name}) - يحتاج للاعتماد والتفعيل",
                            route('admin.operators.show', $operator)
                        );
                    }
                }
            } else {
                return $this->jsonOrRedirect($request, false, 'يجب تحديد المشغل عند إنشاء مشغل جديد.');
            }
        }

        // ============================================
        // Step 7: Send login credentials via SMS
        // SMS is automatically sent when user is created (if phone is provided)
        // Contains: Welcome message, Role name, Username, Password, Login link
        // ============================================
        if ($user->phone) {
            try {
                $this->sendUserCredentialsSMS($user->phone, $user->name, $username, $plainPassword, $role, $roleModel);
            } catch (\Exception $e) {
                \Log::error('Failed to send SMS to user', [
                    'phone' => $user->phone,
                    'username' => $username,
                    'role' => $role->value,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // ============================================
        // Step 8: Create 3 default welcome messages for new user
        // ============================================
        try {
            $user->createDefaultMessages();
        } catch (\Exception $e) {
            \Log::error('Failed to create default messages for user: '.$e->getMessage());
        }

        // Notify super admins when a new user is created (except if creator is SuperAdmin)
        if (! auth()->user()->isSuperAdmin()) {
            Notification::notifySuperAdmins(
                'user_added',
                'تم إضافة مستخدم جديد',
                "تم إضافة المستخدم: {$user->name} ({$user->role_name})",
                route('admin.users.show', $user)
            );
        }

        // Return message with login credentials if they were auto-generated
        if (($authUser->isCompanyOwner() && $isCustomRole && $operator) ||
            (($authUser->isSuperAdmin() || $authUser->isEnergyAuthority()) && in_array($role, [Role::SuperAdmin, Role::Admin, Role::EnergyAuthority, Role::CompanyOwner], true))) {
            $message = "تم إنشاء المستخدم بنجاح ✅\n";
            $message .= "اسم المستخدم: {$username}\n";
            $message .= "كلمة المرور: {$plainPassword}";
            if ($user->phone) {
                $message .= "\n(تم إرسالها على رقم الجوال)";
            }

            return $this->jsonOrRedirect($request, true, $message, [
                'username' => $username,
                'password' => $plainPassword,
                'phone' => $user->phone,
            ]);
        }

        return $this->jsonOrRedirect($request, true, 'تم إنشاء المستخدم بنجاح.');
    }

    /**
     * Send login credentials to user via SMS
     * SMS is automatically sent when user is created with phone number
     * Contains: Welcome message, Role name (from database), Username, Password, Login link
     *
     * @param  string  $phone  User phone number
     * @param  string  $name  User name
     * @param  string  $username  Generated username
     * @param  string  $password  Generated password
     * @param  Role  $role  User role enum
     * @param  \App\Models\Role|null  $roleModel  Role model from database (contains custom role label)
     */
    private function sendUserCredentialsSMS(string $phone, string $name, string $username, string $password, Role $role, ?\App\Models\Role $roleModel = null): void
    {
        $loginUrl = route('login');

        // Use role label from database if available (for custom roles defined by Energy Authority or Company Owner)
        // Otherwise, use default labels for system roles
        if ($roleModel && $roleModel->label) {
            $roleName = $roleModel->label;
        } else {
            // Fallback to default labels for system roles only
            // Custom roles should always use roleModel->label (should not reach here)
            $roleName = match ($role) {
                Role::SuperAdmin => 'مدير النظام',
                Role::Admin => 'مدير',
                Role::EnergyAuthority => 'سلطة الطاقة',
                Role::CompanyOwner => 'مشغل',
                default => $role->value, // Fallback for custom roles (should not happen if roleModel exists)
            };
        }

        // Get SMS template from database
        $smsTemplate = \App\Models\SmsTemplate::getByKey('user_credentials');

        if ($smsTemplate) {
            // Use template from database
            $message = $smsTemplate->render([
                'name' => $name,
                'username' => $username,
                'password' => $password,
                'role' => $roleName,
                'login_url' => $loginUrl,
            ]);
        } else {
            // Fallback to default template if no template exists in database (max 160 characters)
            $message = "مرحباً {$name}،\nتم تسجيلك على منصة راصد.\nالدور: {$roleName}\nاسم المستخدم: {$username}\nكلمة المرور: {$password}\nرابط الدخول: {$loginUrl}";

            // Ensure message does not exceed 160 characters
            if (mb_strlen($message) > 160) {
                $message = mb_substr($message, 0, 157).'...';
            }
        }

        try {
            $smsService = new \App\Services\HotSMSService;
            $result = $smsService->sendSMS($phone, $message, 2);

            if ($result['success']) {
                \Log::info('SMS sent to user successfully', [
                    'phone' => $phone,
                    'name' => $name,
                    'username' => $username,
                    'role' => $role->value,
                    'role_label' => $roleName,
                    'message_id' => $result['message_id'] ?? null,
                ]);
            } else {
                \Log::error('Failed to send SMS to user', [
                    'phone' => $phone,
                    'name' => $name,
                    'username' => $username,
                    'role' => $role->value,
                    'error_code' => $result['code'],
                    'error_message' => $result['message'],
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('SMS service exception for user', [
                'phone' => $phone,
                'name' => $name,
                'username' => $username,
                'role' => $role->value,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $authUser = auth()->user();

        // Get role_id from roles table (system role or custom role)
        $roleValue = $request->validated('role');
        $isSystemRole = in_array($roleValue, [Role::SuperAdmin->value, Role::Admin->value, Role::EnergyAuthority->value, Role::CompanyOwner->value], true);

        $newRole = null;
        $roleModel = null;

        if ($isSystemRole) {
            $newRole = Role::from($roleValue);
            $roleModel = \App\Models\Role::findByName($newRole->value);
        } else {
            // Custom role: get role_id from request
            $roleId = (int) $request->input('role_id');
            if ($roleId) {
                $roleModel = \App\Models\Role::find($roleId);
                if (! $roleModel || $roleModel->is_system) {
                    return $this->jsonOrRedirect($request, false, 'الدور المحدد غير موجود أو غير صالح.');
                }
                // For custom roles, we still need a Role enum value for backward compatibility
                // Use the role name as the enum value
                try {
                    $newRole = Role::from($roleModel->name);
                } catch (\ValueError $e) {
                    // Custom role name doesn't match enum, use a fallback
                    $newRole = Role::CompanyOwner; // Fallback
                }
            } else {
                return $this->jsonOrRedirect($request, false, 'يجب تحديد الدور.');
            }
        }

        $data = [
            'name' => $request->validated('name'),
            'username' => $request->validated('username'),
            'email' => $request->validated('email'),
            'role' => $newRole,
            'role_id' => $roleModel?->id,
        ];

        if ($request->filled('password')) {
            $plainPassword = $request->validated('password');
            $data['password'] = Hash::make($plainPassword);
            $data['password_plain'] = $plainPassword;
        }

        $user->update($data);

        // Update operator relationship for users with custom roles
        if ($user->hasCustomRole()) {
            if ($authUser->isCompanyOwner()) {
                $operator = $authUser->ownedOperators()->first();
                if (! $operator) {
                    return $this->jsonOrRedirect($request, false, 'لا يوجد مشغل مرتبط بحسابك.');
                }
                $user->operators()->sync([$operator->id]);
            } else {
                $operatorId = (int) $request->validated('operator_id', 0);
                if ($operatorId) {
                    $operator = Operator::find($operatorId);
                    if (! $operator) {
                        return $this->jsonOrRedirect($request, false, 'المشغل المحدد غير موجود.');
                    }
                    $user->operators()->sync([$operatorId]);
                } elseif ($user->roleModel && $user->roleModel->operator_id) {
                    // If role is linked to operator, keep the relationship
                    $user->operators()->sync([$user->roleModel->operator_id]);
                }
            }
        } elseif (! $user->isCompanyOwner()) {
            // System roles (SuperAdmin, Admin, EnergyAuthority) don't need operator relationship
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
    /**
     * Toggle user status (active/inactive) - not for suspension/banning
     * For suspension/banning, use suspend/unsuspend methods
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $authUser = auth()->user();

        // Check authorization (update policy)
        $this->authorize('update', $user);

        // Super Admin or Company Owner can change status
        // (Policy checks relationship for Company Owner)
        if (! $authUser->isSuperAdmin() && ! $authUser->isCompanyOwner()) {
            abort(403, 'لا تملك صلاحية لتغيير حالة المستخدم');
        }

        // Prevent deactivating yourself
        if ($user->id === $authUser->id) {
            return $this->jsonOrRedirect($request, false, 'لا يمكنك إيقاف حسابك الخاص.');
        }

        // If user is suspended, they can only be unsuspended via suspend/unsuspend method
        if ($user->isSuspended()) {
            return $this->jsonOrRedirect($request, false, 'المستخدم محظور/معطل. يجب رفع الحظر أولاً.');
        }

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        $statusLabel = $user->status === 'active' ? 'تفعيل' : 'إيقاف';
        $message = "تم {$statusLabel} المستخدم بنجاح";

        return $this->jsonOrRedirect($request, true, $message);
    }

    /**
     * Suspend/ban a user who causes problems
     */
    public function suspend(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $authUser = auth()->user();

        if (! $authUser->hasPermission('users.suspend')) {
            abort(403, 'لا تملك صلاحية لتعطيل/حظر المستخدمين');
        }

        if ($user->id === $authUser->id) {
            return $this->jsonOrRedirect($request, false, 'لا يمكنك حظر حسابك الخاص.');
        }

        if ($user->isSuperAdmin() && ! $authUser->isSuperAdmin()) {
            return $this->jsonOrRedirect($request, false, 'لا يمكنك حظر السوبر أدمن.');
        }

        $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ], [
            'reason.required' => 'يجب إدخال سبب التعطيل/الحظر.',
            'reason.max' => 'سبب التعطيل/الحظر يجب أن لا يتجاوز 1000 حرف.',
        ]);

        $reason = $request->input('reason');

        // Suspend the user
        $user->update([
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspended_reason' => $reason,
            'suspended_by' => $authUser->id,
        ]);

        $operator = null;
        $staffCount = 0;

        // If Company Owner, suspend operator and staff
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $operator->update(['status' => 'inactive']);

                // Suspend staff users
                $staffUsers = $operator->users()
                    ->whereHas('roleModel', function ($q) use ($operator) {
                        $q->where('is_system', false)->where('operator_id', $operator->id);
                    })
                    ->where('id', '!=', $user->id)
                    ->get();

                $staffCount = $staffUsers->count();

                foreach ($staffUsers as $staffUser) {
                    $staffUser->update([
                        'status' => 'suspended',
                        'suspended_at' => now(),
                        'suspended_reason' => "تم تعطيل حسابك بسبب تعطيل المشغل ({$user->name})",
                        'suspended_by' => $authUser->id,
                    ]);

                    Notification::createNotification(
                        $staffUser->id,
                        'user_suspended',
                        'تم حظر/تعطيل حسابك',
                        "تم حظر/تعطيل حسابك بسبب تعطيل المشغل ({$user->name}). يرجى التواصل مع الإدارة.",
                        null
                    );
                }

                Notification::createNotification(
                    $user->id,
                    'operator_suspended',
                    'تم تعطيل المشغل',
                    "تم تعطيل/حظر حسابك ({$user->name}) وبالتالي تم تعطيل المشغل ({$operator->name}) و{$staffCount} من الموظفين. السبب: {$reason}",
                    null
                );
            } else {
                // Company Owner but no operator found
                Notification::createNotification(
                    $user->id,
                    'user_suspended',
                    'تم حظر/تعطيل حسابك',
                    "تم حظر/تعطيل حسابك. السبب: {$reason}. يرجى التواصل مع الإدارة.",
                    null
                );
            }
        } else {
            // Regular user (not Company Owner)
            Notification::createNotification(
                $user->id,
                'user_suspended',
                'تم حظر/تعطيل حسابك',
                "تم حظر/تعطيل حسابك. السبب: {$reason}. يرجى التواصل مع الإدارة.",
                null
            );
        }

        $message = "تم حظر/تعطيل المستخدم بنجاح. السبب: {$reason}";
        if ($operator) {
            $message .= " (تم أيضاً تعطيل المشغل ({$operator->name}) و{$staffCount} من الموظفين)";
        }

        return $this->jsonOrRedirect($request, true, $message);
    }

    /**
     * Unsuspend/unban a user
     */
    public function unsuspend(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $authUser = auth()->user();

        if (! $authUser->hasPermission('users.suspend')) {
            abort(403, 'لا تملك صلاحية لرفع الحظر عن المستخدمين');
        }

        if (! $user->isSuspended()) {
            return $this->jsonOrRedirect($request, false, 'المستخدم غير محظور/معطل.');
        }

        // Unsuspend the user
        $user->update([
            'status' => 'active',
            'suspended_at' => null,
            'suspended_reason' => null,
            'suspended_by' => null,
        ]);

        $operator = null;
        $unsuspendedStaffCount = 0;

        // If Company Owner, reactivate operator and staff
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $operator->update(['status' => 'active']);

                // Reactivate staff users that were suspended due to operator suspension
                $staffUsers = $operator->users()
                    ->whereHas('roleModel', function ($q) use ($operator) {
                        $q->where('is_system', false)->where('operator_id', $operator->id);
                    })
                    ->where('id', '!=', $user->id)
                    ->where('status', 'suspended')
                    ->whereNotNull('suspended_by')
                    ->get();

                foreach ($staffUsers as $staffUser) {
                    if (str_contains($staffUser->suspended_reason ?? '', 'تعطيل المشغل')) {
                        $staffUser->update([
                            'status' => 'active',
                            'suspended_at' => null,
                            'suspended_reason' => null,
                            'suspended_by' => null,
                        ]);

                        $unsuspendedStaffCount++;

                        Notification::createNotification(
                            $staffUser->id,
                            'user_unsuspended',
                            'تم رفع الحظر عن حسابك',
                            "تم رفع الحظر عن حسابك بسبب إعادة تفعيل المشغل ({$user->name}). يمكنك الآن تسجيل الدخول مرة أخرى.",
                            route('login')
                        );
                    }
                }

                Notification::createNotification(
                    $user->id,
                    'operator_reactivated',
                    'تم إعادة تفعيل المشغل',
                    "تم رفع الحظر عن حسابك ({$user->name}) وبالتالي تم إعادة تفعيل المشغل ({$operator->name}) و{$unsuspendedStaffCount} من الموظفين.",
                    route('admin.operators.profile')
                );
            } else {
                // Company Owner but no operator found
                Notification::createNotification(
                    $user->id,
                    'user_unsuspended',
                    'تم رفع الحظر عن حسابك',
                    'تم رفع الحظر عن حسابك. يمكنك الآن تسجيل الدخول مرة أخرى.',
                    route('login')
                );
            }
        } else {
            // Regular user (not Company Owner)
            Notification::createNotification(
                $user->id,
                'user_unsuspended',
                'تم رفع الحظر عن حسابك',
                'تم رفع الحظر عن حسابك. يمكنك الآن تسجيل الدخول مرة أخرى.',
                route('login')
            );
        }

        $message = 'تم رفع الحظر عن المستخدم بنجاح.';
        if ($operator) {
            $message .= " (تم أيضاً إعادة تفعيل المشغل ({$operator->name}) و{$unsuspendedStaffCount} من الموظفين)";
        }

        return $this->jsonOrRedirect($request, true, $message);
    }

    /**
     * Select2 operators (server-side)
     */
    public function ajaxOperators(Request $request)
    {
        $authUser = auth()->user();
        if (! $authUser || ! $authUser->isSuperAdmin()) {
            abort(403);
        }

        $term = trim((string) $request->query('q', $request->query('term', '')));
        $page = max(1, (int) $request->query('page', 1));
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
        if (! $currentUser->isSuperAdmin()) {
            return redirect()->back()->with('error', 'غير مصرح لك بالدخول بحساب مستخدم آخر.');
        }

        // منع الدخول بحساب نفسه
        if ($currentUser->id === $user->id) {
            return redirect()->back()->with('error', 'لا يمكنك الدخول بحسابك الخاص.');
        }

        // منع الدخول بحساب system user (منصة راصد)
        if ($user->isSystemUser()) {
            return redirect()->back()->with('error', 'لا يمكن الدخول بحساب منصة راصد.');
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

        if (! $impersonatorId) {
            return redirect()->route('admin.dashboard')->with('error', 'لا يوجد حساب أصلي للعودة إليه.');
        }

        $impersonator = User::find($impersonatorId);

        if (! $impersonator) {
            session()->forget(['impersonator_id', 'impersonator_name']);

            return redirect()->route('login')->with('error', 'الحساب الأصلي غير موجود.');
        }

        // حذف معلومات الـ impersonation من Session
        session()->forget(['impersonator_id', 'impersonator_name']);

        // تسجيل الدخول بالحساب الأصلي
        Auth::login($impersonator);

        return redirect()->route('admin.users.index')->with('success', 'تم العودة لحسابك الأصلي بنجاح.');
    }

    private function jsonOrRedirect(Request $request, bool $ok, string $message, array $extraData = [])
    {
        if ($request->wantsJson() || $request->ajax()) {
            $response = [
                'ok' => $ok,
                'message' => $message,
            ];
            if (! empty($extraData)) {
                $response = array_merge($response, $extraData);
            }

            return response()->json($response, $ok ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $ok
            ? redirect()->route('admin.users.index')->with('success', $message)->with($extraData)
            : redirect()->back()->withInput()->with('error', $message);
    }
}
