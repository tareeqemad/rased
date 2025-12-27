<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\GeneralHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOperatorRequest;
use App\Http\Requests\Admin\UpdateOperatorRequest;
use App\Mail\OperatorCredentialsMail;
use App\Models\Operator;
use App\Models\Role as RoleModel;
use App\Models\User;
use App\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class OperatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Operator::class);

        $authUser = $request->user();

        $q = trim((string) $request->get('q', ''));
        $status = trim((string) $request->get('status', ''));

        $operatorsQuery = Operator::query()
            ->with('owner')
            ->withCount([
                'generators',
                'users as employees_count' => function ($q) {
                    // غالبًا جدول pivot operator_user فيه فقط موظفين/فنيين
                    // ومع ذلك نخليه فلترة احتياطية حسب enum القديم
                    $q->whereIn('role', [Role::Employee, Role::Technician]);
                },
            ]);

        // Scope حسب الدور
        if ($authUser->isCompanyOwner()) {
            $operatorsQuery->where('owner_id', $authUser->id);
        } elseif ($authUser->isEmployee() || $authUser->isTechnician()) {
            $operatorIds = $authUser->operators()->pluck('operators.id');
            $operatorsQuery->whereIn('id', $operatorIds);
        }

        // Search
        if ($q !== '') {
            $operatorsQuery->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('unit_name', 'like', "%{$q}%")
                    ->orWhere('unit_number', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhereHas('owner', function ($oq) use ($q) {
                        $oq->where('name', 'like', "%{$q}%")
                           ->orWhere('username', 'like', "%{$q}%")
                           ->orWhere('email', 'like', "%{$q}%");
                    });
            });
        }

        // Status filter (active/inactive)
        if ($status !== '' && in_array($status, ['active', 'inactive'], true)) {
            $operatorsQuery->where('status', $status);
        }

        $operators = $operatorsQuery
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // AJAX: رجّع partial list فقط
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('admin.operators.partials.list', compact('operators'))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
            ]);
        }

        // للي مش SuperAdmin: غالبًا رح يكون عنده مشغل واحد
        $myOperator = (! $authUser->isSuperAdmin()) ? $operators->first() : null;

        return view('admin.operators.index', [
            'operators' => $operators,
            'q' => $q,
            'status' => $status,
            'myOperator' => $myOperator,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Operator::class);

        // للـ AJAX مودال: رجع form partial فقط
        if ($request->ajax()) {
            return view('admin.operators.partials.form', [
                'mode' => 'create',
                'operator' => new Operator(),
            ]);
        }

        return view('admin.operators.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOperatorRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', Operator::class);

        $companyOwnerRole = RoleModel::where('name', 'company_owner')->first();
        if (! $companyOwnerRole) {
            $msg = 'لم يتم العثور على دور المشغل. يرجى تشغيل RoleSeeder أولاً.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        // إنشاء User للمشغل
        $user = User::create([
            'name' => $request->validated('username'),
            'username' => $request->validated('username'),
            'email' => $request->validated('email') ?? ($request->validated('username') . '@rased.ps'),
            'password' => Hash::make($request->validated('password')),
            'role' => Role::CompanyOwner,
            'role_id' => $companyOwnerRole->id,
        ]);

        // إنشاء Operator
        $operator = Operator::create([
            'name' => $request->validated('username'),
            'email' => $user->email,
            'owner_id' => $user->id,
            'profile_completed' => false,
        ]);

        // إرسال إيميل (اختياري)
        if ($request->boolean('send_email') && $user->email) {
            try {
                $loginUrl = URL::route('login');
                Mail::to($user->email)->send(new OperatorCredentialsMail(
                    $user->username,
                    $request->validated('password'),
                    $loginUrl
                ));
            } catch (\Throwable $e) {
                \Log::error('فشل إرسال الإيميل للمشغل: ' . $e->getMessage());
            }
        }

        $message = 'تم إنشاء المشغل بنجاح. اسم المستخدم: ' . $user->username;

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'operator' => [
                    'id' => $operator->id,
                    'name' => $operator->name,
                ],
            ], 201);
        }

        return redirect()->route('admin.operators.index')->with('success', $message);
    }

    /**
     * Display detailed information about the specified operator.
     */
    public function show(Operator $operator): View
    {
        $this->authorize('view', $operator);

        $operator->load([
            'owner',
            'generators' => function ($q) {
                $q->latest()->take(10);
            },
            'users',
            'operationLogs' => function ($q) {
                $q->latest()->take(5);
            },
        ]);

        $operator->loadCount([
            'generators',
            'users',
            'operationLogs',
        ]);

        return view('admin.operators.show', compact('operator'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Operator $operator): View
    {
        $this->authorize('update', $operator);

        $operator->load('owner');

        // للـ AJAX مودال: رجع form partial فقط
        if ($request->ajax()) {
            return view('admin.operators.partials.form', [
                'mode' => 'edit',
                'operator' => $operator,
            ]);
        }

        return view('admin.operators.edit', compact('operator'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOperatorRequest $request, Operator $operator): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $operator);

        $operator->update([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'address' => $request->validated('address'),
        ]);

        // تحديث بيانات المستخدم المالك
        if ($operator->owner) {
            $auth = auth()->user();
            $userData = [];

            // SuperAdmin فقط يغير username
            if ($auth->isSuperAdmin() && $request->filled('username')) {
                $userData['username'] = $request->validated('username');
            }

            if ($request->filled('user_email')) {
                $userData['email'] = $request->validated('user_email');
            }

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->validated('password'));
            }

            if (!empty($userData)) {
                $operator->owner->update($userData);
            }
        }

        $msg = 'تم تحديث بيانات المشغل بنجاح.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->route('admin.operators.index')->with('success', $msg);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Operator $operator): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $operator);

        try {
            if ($operator->owner) {
                $operator->owner->delete();
            }
            $operator->delete();
        } catch (\Throwable $e) {
            $msg = 'تعذر حذف المشغل بسبب بيانات مرتبطة.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->route('admin.operators.index')->with('error', $msg);
        }

        $msg = 'تم حذف المشغل بنجاح.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->route('admin.operators.index')->with('success', $msg);
    }

    /**
     * الحصول على المشغلين حسب المحافظة
     */
    public function getByGovernorate(Request $request, int $governorate): JsonResponse
    {
        $activeOnly = $request->boolean('active_only', true);
        
        $operators = GeneralHelper::getOperatorsByGovernorateSimple($governorate, $activeOnly);

        return response()->json([
            'success' => true,
            'data' => $operators->map(function ($operator) {
                return [
                    'id' => $operator->id,
                    'name' => $operator->name,
                    'city' => $operator->city,
                    'unit_number' => $operator->unit_number,
                    'status' => $operator->status,
                ];
            }),
        ]);
    }
}
