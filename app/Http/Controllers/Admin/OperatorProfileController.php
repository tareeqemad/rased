<?php

namespace App\Http\Controllers\Admin;

use App\Governorate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOperatorProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OperatorProfileController extends Controller
{
    /**
     * عرض صفحة إكمال البيانات
     */
    public function show(): View
    {
        $user = auth()->user();

        if (! $user->isCompanyOwner()) {
            abort(403);
        }

        $operator = $user->ownedOperators()->first();

        if (! $operator) {
            abort(404, 'المشغل غير موجود');
        }

        return view('admin.operators.profile', compact('operator'));
    }

    /**
     * تحديث بيانات المشغل
     */
    public function update(UpdateOperatorProfileRequest $request): RedirectResponse
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

        // تحويل رقم المحافظة إلى Enum
        if (isset($data['governorate'])) {
            $data['governorate'] = Governorate::fromValue((int) $data['governorate']);
        }

        // تحويل synchronization_available إلى boolean
        if (isset($data['synchronization_available'])) {
            $data['synchronization_available'] = (bool) $data['synchronization_available'];
        }

        // تحديد أن الملف الشخصي مكتمل
        $data['profile_completed'] = true;

        $operator->update($data);

        return redirect()->route('admin.dashboard')
            ->with('success', 'تم حفظ بيانات المشغل بنجاح.');
    }
}
