<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreConstantMasterRequest;
use App\Http\Requests\Admin\UpdateConstantMasterRequest;
use App\Models\ConstantMaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConstantMasterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', ConstantMaster::class);

        $query = ConstantMaster::withCount('allDetails');

        // البحث
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('constant_number', 'like', "%{$search}%")
                    ->orWhere('constant_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $constants = $query->orderBy('order')
            ->orderBy('constant_number')
            ->paginate(15);

        // إذا كان الطلب AJAX، إرجاع JSON
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.constants.partials.table', compact('constants'))->render(),
                'pagination' => view('admin.constants.partials.pagination', compact('constants'))->render(),
                'modals' => view('admin.constants.partials.modals', compact('constants'))->render(),
            ]);
        }

        return view('admin.constants.index', compact('constants'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', ConstantMaster::class);

        return view('admin.constants.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConstantMasterRequest $request): RedirectResponse
    {
        ConstantMaster::create([
            'constant_number' => $request->validated('constant_number'),
            'constant_name' => $request->validated('constant_name'),
            'description' => $request->validated('description'),
            'is_active' => $request->validated('is_active', true),
            'order' => $request->validated('order', 0),
        ]);

        return redirect()->route('admin.constants.index')
            ->with('success', 'تم إنشاء الثابت بنجاح.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ConstantMaster $constant): View
    {
        $this->authorize('view', $constant);

        $constant->load('allDetails');

        return view('admin.constants.show', compact('constant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ConstantMaster $constant): View
    {
        $this->authorize('update', $constant);

        $constant->load('allDetails');

        return view('admin.constants.edit', compact('constant'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConstantMasterRequest $request, ConstantMaster $constant): RedirectResponse
    {
        $constant->update([
            'constant_number' => $request->validated('constant_number'),
            'constant_name' => $request->validated('constant_name'),
            'description' => $request->validated('description'),
            'is_active' => $request->validated('is_active', true),
            'order' => $request->validated('order', $constant->order),
        ]);

        return redirect()->route('admin.constants.index')
            ->with('success', 'تم تحديث الثابت بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ConstantMaster $constant): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $constant);

        // التحقق من وجود تفاصيل مرتبطة
        if ($constant->allDetails()->count() > 0) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن حذف الثابت لأنه يحتوي على تفاصيل مرتبطة.',
                ], 403);
            }

            return redirect()->route('admin.constants.index')
                ->with('error', 'لا يمكن حذف الثابت لأنه يحتوي على تفاصيل مرتبطة.');
        }

        $constant->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف الثابت بنجاح.',
            ]);
        }

        return redirect()->route('admin.constants.index')
            ->with('success', 'تم حذف الثابت بنجاح.');
    }
}
