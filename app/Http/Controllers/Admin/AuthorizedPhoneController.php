<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthorizedPhone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthorizedPhoneController extends Controller
{
    /**
     * عرض قائمة الأرقام المصرح بها
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', \App\Models\AuthorizedPhone::class);

        if ($request->wantsJson() || $request->boolean('ajax')) {
            return $this->ajaxIndex($request);
        }

        return view('admin.authorized-phones.index');
    }

    /**
     * AJAX endpoint للجلب
     */
    private function ajaxIndex(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = max(5, min(50, (int) $request->query('per_page', 15)));

        $query = AuthorizedPhone::with(['creator', 'updater'])
            ->orderBy('created_at', 'desc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('phone', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $phones = $query->paginate($perPage);

        return response()->json([
            'ok' => true,
            'data' => $phones->items(),
            'meta' => [
                'current_page' => $phones->currentPage(),
                'last_page' => $phones->lastPage(),
                'from' => $phones->firstItem(),
                'to' => $phones->lastItem(),
                'total' => $phones->total(),
                'per_page' => $phones->perPage(),
            ],
        ]);
    }

    /**
     * عرض نموذج إضافة رقم جديد
     */
    public function create(): View
    {
        $this->authorize('create', \App\Models\AuthorizedPhone::class);

        return view('admin.authorized-phones.create');
    }

    /**
     * حفظ رقم جديد
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', \App\Models\AuthorizedPhone::class);

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20', 'regex:/^0(59|56)\d{7}$/'],
            'name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ], [
            'phone.required' => 'رقم الجوال مطلوب',
            'phone.regex' => 'رقم الجوال غير صحيح. يجب أن يبدأ بـ 059 أو 056',
            'name.max' => 'الاسم يجب أن يكون أقل من 255 حرف',
        ]);

        // تنظيف الرقم
        $cleanPhone = preg_replace('/[^0-9]/', '', $validated['phone']);

        // التحقق من عدم التكرار - التحقق من الرقم المنظف والرقم الأصلي
        // بما أن الرقم يُحفظ منظفاً دائماً، التحقق من الرقم المنظف كافٍ
        $existingPhone = AuthorizedPhone::where('phone', $cleanPhone)
            ->orWhere('phone', $validated['phone'])
            ->first();
        
        if ($existingPhone) {
            $error = 'رقم الجوال مسجل مسبقاً';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $error], 422);
            }
            return redirect()->back()->withInput()->withErrors(['phone' => $error]);
        }

        $phone = AuthorizedPhone::create([
            'phone' => $cleanPhone,
            'name' => $validated['name'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'created_by' => Auth::id(),
        ]);

        $msg = 'تم إضافة الرقم بنجاح ✅';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'data' => $phone,
            ]);
        }

        return redirect()->route('admin.authorized-phones.index')->with('success', $msg);
    }

    /**
     * عرض نموذج تعديل
     */
    public function edit(AuthorizedPhone $authorizedPhone): View
    {
        $this->authorize('update', $authorizedPhone);

        return view('admin.authorized-phones.edit', compact('authorizedPhone'));
    }

    /**
     * تحديث رقم
     */
    public function update(Request $request, AuthorizedPhone $authorizedPhone): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $authorizedPhone);

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20', 'regex:/^0(59|56)\d{7}$/'],
            'name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ], [
            'phone.required' => 'رقم الجوال مطلوب',
            'phone.regex' => 'رقم الجوال غير صحيح. يجب أن يبدأ بـ 059 أو 056',
        ]);

        $cleanPhone = preg_replace('/[^0-9]/', '', $validated['phone']);

        // التحقق من عدم التكرار (باستثناء الرقم الحالي)
        // التحقق من الرقم المنظف والرقم الأصلي
        // بما أن الرقم يُحفظ منظفاً دائماً، التحقق من الرقم المنظف كافٍ
        $existingPhone = AuthorizedPhone::where('id', '!=', $authorizedPhone->id)
            ->where(function ($query) use ($cleanPhone, $validated) {
                $query->where('phone', $cleanPhone)
                    ->orWhere('phone', $validated['phone']);
            })
            ->first();
        
        if ($existingPhone) {
            $error = 'رقم الجوال مسجل مسبقاً';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $error], 422);
            }
            return redirect()->back()->withInput()->withErrors(['phone' => $error]);
        }

        $authorizedPhone->update([
            'phone' => $cleanPhone,
            'name' => $validated['name'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'updated_by' => Auth::id(),
        ]);

        $msg = 'تم تحديث الرقم بنجاح ✅';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'data' => $authorizedPhone->fresh(),
            ]);
        }

        return redirect()->route('admin.authorized-phones.index')->with('success', $msg);
    }

    /**
     * حذف رقم
     */
    public function destroy(AuthorizedPhone $authorizedPhone): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $authorizedPhone);

        $authorizedPhone->delete();

        $msg = 'تم حذف الرقم بنجاح ✅';

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return redirect()->route('admin.authorized-phones.index')->with('success', $msg);
    }
}
