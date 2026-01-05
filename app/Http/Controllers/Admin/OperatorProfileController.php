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
    public function show(Request $request): View
    {
        $user = auth()->user();

        if (! $user->isCompanyOwner()) {
            abort(403);
        }

        $operator = $user->ownedOperators()->first();
        if (! $operator) {
            abort(404, 'المشغل غير موجود');
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
