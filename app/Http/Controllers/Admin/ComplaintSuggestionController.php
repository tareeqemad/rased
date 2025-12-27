<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComplaintSuggestion;
use App\Models\Generator;
use App\Models\Operator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ComplaintSuggestionController extends Controller
{
    /**
     * عرض قائمة الشكاوى والمقترحات
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = ComplaintSuggestion::with(['generator.operator', 'responder']);

        // تصفية حسب نوع المستخدم
        if ($user->isSuperAdmin()) {
            // السوبر ادمن يشوف كل الشكاوى
        } elseif ($user->isCompanyOwner()) {
            // المشغل يشوف الشكاوى المرتبطة بمولداته فقط
            $operatorIds = $user->ownedOperators()->pluck('id');
            $generatorIds = Generator::whereIn('operator_id', $operatorIds)->pluck('id');
            $query->whereIn('generator_id', $generatorIds);
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            // الموظف/الفني يشوف الشكاوى المرتبطة بمولدات المشغل الذي ينتمي إليه
            $operatorIds = $user->operators()->pluck('operators.id');
            $generatorIds = Generator::whereIn('operator_id', $operatorIds)->pluck('id');
            $query->whereIn('generator_id', $generatorIds);
        } else {
            // غير مصرح له
            abort(403);
        }

        // تصفية حسب النوع
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // تصفية حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // البحث
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('tracking_code', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $complaintsSuggestions = $query->orderBy('created_at', 'desc')->paginate(20);

        // إحصائيات
        $stats = [
            'total' => (clone $query)->count(),
            'complaints' => (clone $query)->where('type', 'complaint')->count(),
            'suggestions' => (clone $query)->where('type', 'suggestion')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'resolved' => (clone $query)->where('status', 'resolved')->count(),
        ];

        return view('admin.complaints-suggestions.index', compact('complaintsSuggestions', 'stats'));
    }

    /**
     * عرض تفاصيل شكوى/مقترح
     */
    public function show(ComplaintSuggestion $complaintSuggestion)
    {
        $user = Auth::user();
        
        // التحقق من الصلاحيات
        if (!$this->canAccess($user, $complaintSuggestion)) {
            abort(403);
        }

        $complaintSuggestion->load(['generator.operator', 'responder']);

        return view('admin.complaints-suggestions.show', compact('complaintSuggestion'));
    }

    /**
     * عرض صفحة تعديل شكوى/مقترح
     */
    public function edit(ComplaintSuggestion $complaintSuggestion)
    {
        $user = Auth::user();
        
        // فقط السوبر ادمن يمكنه التعديل
        if (!$user->isSuperAdmin()) {
            abort(403);
        }

        $complaintSuggestion->load(['generator.operator', 'responder']);

        return view('admin.complaints-suggestions.edit', compact('complaintSuggestion'));
    }

    /**
     * تحديث شكوى/مقترح
     */
    public function update(Request $request, ComplaintSuggestion $complaintSuggestion)
    {
        $user = Auth::user();
        
        // فقط السوبر ادمن يمكنه التعديل
        if (!$user->isSuperAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,resolved,rejected',
            'response' => 'nullable|string|max:5000',
        ]);

        $complaintSuggestion->update([
            'status' => $validated['status'],
            'response' => $validated['response'] ?? null,
            'responded_by' => $user->id,
            'responded_at' => now(),
        ]);

        return redirect()->route('admin.complaints-suggestions.show', $complaintSuggestion)
            ->with('success', 'تم تحديث الطلب بنجاح');
    }

    /**
     * الرد على شكوى/مقترح
     */
    public function respond(Request $request, ComplaintSuggestion $complaintSuggestion)
    {
        $user = Auth::user();
        
        // التحقق من الصلاحيات
        if (!$this->canAccess($user, $complaintSuggestion)) {
            abort(403);
        }

        $validated = $request->validate([
            'response' => 'required|string|max:5000',
            'status' => 'required|in:pending,in_progress,resolved,rejected',
        ]);

        $complaintSuggestion->update([
            'response' => $validated['response'],
            'status' => $validated['status'],
            'responded_by' => $user->id,
            'responded_at' => now(),
        ]);

        return redirect()->route('admin.complaints-suggestions.show', $complaintSuggestion)
            ->with('success', 'تم إرسال الرد بنجاح');
    }

    /**
     * حذف شكوى/مقترح
     */
    public function destroy(ComplaintSuggestion $complaintSuggestion)
    {
        $user = Auth::user();
        
        // فقط السوبر ادمن يمكنه الحذف
        if (!$user->isSuperAdmin()) {
            abort(403);
        }

        $complaintSuggestion->delete();

        return redirect()->route('admin.complaints-suggestions.index')
            ->with('success', 'تم حذف الطلب بنجاح');
    }

    /**
     * التحقق من إمكانية الوصول للشكوى
     */
    private function canAccess($user, ComplaintSuggestion $complaintSuggestion): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (!$complaintSuggestion->generator_id) {
            return false;
        }

        $generator = $complaintSuggestion->generator;
        if (!$generator || !$generator->operator_id) {
            return false;
        }

        $operator = $generator->operator;

        if ($user->isCompanyOwner()) {
            return $user->ownsOperator($operator);
        }

        if ($user->isEmployee() || $user->isTechnician()) {
            return $user->belongsToOperator($operator);
        }

        return false;
    }
}




