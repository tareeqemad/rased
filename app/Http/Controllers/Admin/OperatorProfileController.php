<?php

namespace App\Http\Controllers\Admin;

use App\Governorate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOperatorProfileRequest;
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

        $missing = $operator->getMissingFields();

        return view('admin.operators.profile', compact('operator', 'missing'));
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

        // governorate enum
        if (isset($data['governorate'])) {
            $data['governorate'] = Governorate::fromValue((int) $data['governorate']);
        }

        // boolean
        if (isset($data['synchronization_available'])) {
            $data['synchronization_available'] = (bool) $data['synchronization_available'];
        }

        $data['profile_completed'] = true;

        $operator->update($data);

        $missing = $operator->fresh()->getMissingFields();

        $msg = empty($missing)
            ? 'تم حفظ بيانات المشغل بنجاح ✅'
            : 'تم الحفظ، لكن ما زالت هناك حقول ناقصة.';

        // AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'profile_completed' => empty($missing),
                'missing' => $missing,
            ]);
        }

        return redirect()->route('admin.dashboard')->with('success', $msg);
    }
}
