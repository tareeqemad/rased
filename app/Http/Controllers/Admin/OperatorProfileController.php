<?php

namespace App\Http\Controllers\Admin;

use App\Governorate;
use App\Helpers\ConstantsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOperatorProfileRequest;
use App\Models\GenerationUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperatorProfileController extends Controller
{
    public function show(Request $request, ?\App\Models\Operator $operator = null): View
    {
        $user = auth()->user();

        // السوبر أدمن يمكنه رؤية ملف أي مشغل
        if ($user->isSuperAdmin() || $user->isEnergyAuthority()) {
            // إذا تم تمرير operator_id في الـ request
            $operatorId = $request->query('operator_id');
            if ($operatorId) {
                $operator = \App\Models\Operator::findOrFail($operatorId);
            } elseif ($operator) {
                // إذا تم تمرير operator كـ route parameter
                $operator = $operator;
            } else {
                // إذا لم يتم تحديد مشغل، نعرض رسالة خطأ
                abort(404, 'يرجى تحديد المشغل');
            }
        } elseif ($user->isCompanyOwner()) {
            // المشغل يشوف ملفه فقط
            $operator = $user->ownedOperators()->first();
            if (! $operator) {
                abort(404, 'المشغل غير موجود');
            }
        } else {
            abort(403, 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }

        // جلب وحدات التوليد للمشغل
        $generationUnits = $operator->generationUnits()->with('statusDetail')->withCount('generators')->get();

        return view('admin.operators.profile', compact('operator', 'generationUnits'));
    }

    public function update(UpdateOperatorProfileRequest $request): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        if (! $user->isCompanyOwner()) {
            abort(403);
        }

        $operator = $user->ownedOperators()->first();
        if (! $operator) {
            abort(404, 'المشغل غير موجود');
        }

        $data = $request->validated();

        $operator->update($data);

        $msg = 'تم حفظ اسم المشغل بنجاح ✅';

        // AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'operator' => [
                    'name' => $operator->name,
                    'owner_name' => $operator->owner_name,
                    'owner_id_number' => $operator->owner_id_number,
                    'operator_id_number' => $operator->operator_id_number,
                ],
            ]);
        }

        return redirect()->route('admin.operators.profile')->with('success', $msg);
    }
}
