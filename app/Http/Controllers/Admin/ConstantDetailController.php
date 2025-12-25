<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreConstantDetailRequest;
use App\Http\Requests\Admin\UpdateConstantDetailRequest;
use App\Models\ConstantDetail;
use App\Models\ConstantMaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ConstantDetailController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConstantDetailRequest $request): RedirectResponse
    {
        ConstantDetail::create($request->validated());

        return redirect()->back()
            ->with('success', 'تم إضافة التفصيل بنجاح.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConstantDetailRequest $request, ConstantDetail $constantDetail): RedirectResponse|JsonResponse
    {
        $constantDetail->update($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث التفصيل بنجاح.',
            ]);
        }

        return redirect()->back()
            ->with('success', 'تم تحديث التفصيل بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ConstantDetail $constantDetail): RedirectResponse|JsonResponse
    {
        $constantDetail->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف التفصيل بنجاح.',
            ]);
        }

        return redirect()->back()
            ->with('success', 'تم حذف التفصيل بنجاح.');
    }

    /**
     * Get details by constant master ID (AJAX)
     */
    public function getByMaster(ConstantMaster $constantMaster): JsonResponse
    {
        $details = $constantMaster->details;

        return response()->json([
            'success' => true,
            'data' => $details->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'label' => $detail->label,
                    'code' => $detail->code,
                    'value' => $detail->value,
                ];
            }),
        ]);
    }
}
