<?php

namespace App\Http\Controllers\Admin;

use App\Governorate;
use App\Helpers\ConstantsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGenerationUnitRequest;
use App\Http\Requests\Admin\UpdateGenerationUnitRequest;
use App\Models\FuelTank;
use App\Models\GenerationUnit;
use App\Models\Operator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GenerationUnitController extends Controller
{
    /**
     * Display a listing of generation units.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', GenerationUnit::class);

        $user = auth()->user();
        $query = GenerationUnit::with(['operator', 'generators'])->withCount('generators');

        // فلترة حسب نوع المستخدم
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $query->where('operator_id', $operator->id);
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operatorIds = $user->operators()->pluck('operators.id');
            $query->whereIn('operator_id', $operatorIds);
        }

        // البحث
        $q = trim((string) $request->input('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('unit_code', 'like', "%{$q}%")
                    ->orWhereHas('operator', function ($oq) use ($q) {
                        $oq->where('name', 'like', "%{$q}%");
                    });
            });
        }

        // فلترة حسب الحالة
        $status = trim((string) $request->input('status', ''));
        if ($status !== '' && in_array($status, ['active', 'inactive'], true)) {
            $query->where('status', $status);
        }

        // فلترة حسب المشغل (للسوبر أدمن فقط)
        if ($user->isSuperAdmin()) {
            $operatorId = (int) $request->input('operator_id', 0);
            if ($operatorId > 0) {
                $query->where('operator_id', $operatorId);
            }
        }

        $generationUnits = $query->latest()->paginate(15);

        if ($request->ajax() || $request->wantsJson()) {
            $html = view('admin.generation-units.partials.list', compact('generationUnits'))->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $generationUnits->total(),
            ]);
        }

        $operators = collect();
        if ($user->isSuperAdmin()) {
            $operators = Operator::select('id', 'name')
                ->orderBy('name')
                ->get();
        }

        return view('admin.generation-units.index', compact('generationUnits', 'operators'));
    }

    /**
     * Show the form for creating a new generation unit.
     */
    public function create(): View|RedirectResponse
    {
        $this->authorize('create', GenerationUnit::class);

        $user = auth()->user();
        $operator = null;

        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if (!$operator) {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'لا يوجد مشغل مرتبط بحسابك.');
            }
        }

        // جلب المحافظات والمدن
        $governorates = ConstantsHelper::get(1);
        $cities = collect();
        $selectedGovernorateCode = null;

        if ($operator && $operator->governorate) {
            $selectedGovernorateCode = $operator->governorate->code();
            $governorateDetail = ConstantsHelper::findByCode(1, $selectedGovernorateCode);
            if ($governorateDetail) {
                $cities = ConstantsHelper::getCitiesByGovernorate($governorateDetail->id);
            }
        }

        $constants = [
            'status' => ConstantsHelper::get(3),
            'location' => ConstantsHelper::get(18), // موقع الخزان
            'material' => ConstantsHelper::get(10), // مادة التصنيع
            'usage' => ConstantsHelper::get(11), // الاستخدام
            'measurement_method' => ConstantsHelper::get(19), // طريقة القياس
        ];

        // جلب جميع المشغلين للسوبر أدمن
        $allOperators = collect();
        if ($user->isSuperAdmin()) {
            $allOperators = Operator::select('id', 'name')->orderBy('name')->get();
        }

        return view('admin.generation-units.create', compact('operator', 'governorates', 'cities', 'selectedGovernorateCode', 'constants', 'allOperators'));
    }

    /**
     * Store a newly created generation unit.
     */
    public function store(StoreGenerationUnitRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', GenerationUnit::class);

        $user = auth()->user();
        $data = $request->validated();

        // تحديد المشغل
        $operator = null;
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if (!$operator) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'لا يوجد مشغل مرتبط بحسابك.');
            }
            $data['operator_id'] = $operator->id;
        } elseif ($user->isSuperAdmin() && isset($data['operator_id'])) {
            $operator = Operator::find($data['operator_id']);
        }

        // إذا كان "نفس المالك"، جلب البيانات من المشغل
        if (isset($data['operation_entity']) && $data['operation_entity'] === 'same_owner' && $operator) {
            $data['owner_name'] = $operator->owner_name;
            $data['owner_id_number'] = $operator->owner_id_number;
            $data['operator_id_number'] = $operator->operator_id_number;
        }

        // تحويل governorate من code إلى enum value أولاً (قبل توليد unit_code)
        $governorateCode = null;
        $cityCode = null;
        if (isset($data['governorate'])) {
            $governorateDetail = \App\Models\ConstantDetail::whereHas('master', function($q) {
                $q->where('constant_number', 1);
            })->where('code', $data['governorate'])->first();
            
            if ($governorateDetail && $governorateDetail->value) {
                $governorateEnum = Governorate::fromValue((int) $governorateDetail->value);
                $governorateCode = $governorateEnum->code();
                $data['governorate'] = $governorateCode;
            }
        }

        // الحصول على city code
        if (isset($data['city_id'])) {
            $cityDetail = \App\Models\ConstantDetail::find($data['city_id']);
            if ($cityDetail && $cityDetail->code) {
                $cityCode = $cityDetail->code;
            }
        }

        // توليد رقم الوحدة وكود الوحدة تلقائياً
        if (!isset($data['unit_number']) || empty($data['unit_number'])) {
            // إذا كان governorate و city_id موجودان في الـ form، استخدمهما لتوليد unit_number
            if ($governorateCode && $cityCode) {
                $data['unit_number'] = GenerationUnit::getNextUnitNumberByLocation($governorateCode, $cityCode);
            } else {
                $data['unit_number'] = GenerationUnit::getNextUnitNumber($data['operator_id']);
            }
        }

        if (!isset($data['unit_code']) || empty($data['unit_code'])) {
            // استخدام governorateCode و cityCode من الـ form إذا كانا موجودين
            if ($governorateCode && $cityCode) {
                $data['unit_code'] = GenerationUnit::generateUnitCodeByLocation($governorateCode, $cityCode, $data['unit_number'] ?? null);
            } else {
                // في هذه الحالة، يجب أن يكون governorateCode و cityCode موجودين
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'فشل في توليد كود الوحدة. يرجى التأكد من إدخال المحافظة والمدينة بشكل صحيح.');
            }
        }

        // التأكد من أن unit_code ليس null
        if (empty($data['unit_code'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'فشل في توليد كود الوحدة. يرجى التأكد من إدخال المحافظة والمدينة بشكل صحيح.');
        }

        // boolean fields
        if (isset($data['synchronization_available'])) {
            $data['synchronization_available'] = (bool) $data['synchronization_available'];
        }

        // تتبع المستخدمين
        $data['created_by'] = $user->id;
        $data['last_updated_by'] = $user->id;

        // معالجة خزانات الوقود
        $fuelTanksData = $data['fuel_tanks'] ?? [];
        unset($data['fuel_tanks']);
        unset($data['external_fuel_tank']);
        unset($data['fuel_tanks_count']);

        $generationUnit = GenerationUnit::create($data);

        // إضافة خزانات الوقود
        if (!empty($fuelTanksData)) {
            foreach ($fuelTanksData as $index => $tankData) {
                $tankCode = FuelTank::getNextTankCode($generationUnit->id);
                
                FuelTank::create([
                    'generation_unit_id' => $generationUnit->id,
                    'tank_code' => $tankCode,
                    'capacity' => $tankData['capacity'] ?? null,
                    'location' => $tankData['location'] ?? null,
                    'filtration_system_available' => $tankData['filtration_system_available'] ?? false,
                    'condition' => $tankData['condition'] ?? null,
                    'material' => $tankData['material'] ?? null,
                    'usage' => $tankData['usage'] ?? null,
                    'measurement_method' => $tankData['measurement_method'] ?? null,
                    'order' => $index + 1,
                ]);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء وحدة التوليد بنجاح.',
                'generation_unit' => $generationUnit,
            ]);
        }

        return redirect()->route('admin.generation-units.index')
            ->with('success', 'تم إنشاء وحدة التوليد بنجاح.');
    }

    /**
     * Display the specified generation unit.
     */
    public function show(GenerationUnit $generationUnit): View
    {
        $this->authorize('view', $generationUnit);

        $generationUnit->load(['operator', 'generators', 'fuelTanks']);

        return view('admin.generation-units.show', compact('generationUnit'));
    }

    /**
     * Show the form for editing the specified generation unit.
     */
    public function edit(GenerationUnit $generationUnit): View
    {
        $this->authorize('update', $generationUnit);

        $operator = $generationUnit->operator;

        // جلب المحافظات والمدن
        $governorates = ConstantsHelper::get(1);
        $cities = collect();
        $selectedGovernorateCode = null;

        if ($generationUnit->governorate) {
            $selectedGovernorateCode = $generationUnit->governorate;
            $governorateDetail = ConstantsHelper::findByCode(1, $selectedGovernorateCode);
            if ($governorateDetail) {
                $cities = ConstantsHelper::getCitiesByGovernorate($governorateDetail->id);
            }
        }

        $constants = [
            'status' => ConstantsHelper::get(3),
            'location' => ConstantsHelper::get(18), // موقع الخزان
            'material' => ConstantsHelper::get(10), // مادة التصنيع
            'usage' => ConstantsHelper::get(11), // الاستخدام
            'measurement_method' => ConstantsHelper::get(19), // طريقة القياس
        ];

        $generationUnit->load('fuelTanks');

        return view('admin.generation-units.edit', compact('generationUnit', 'operator', 'governorates', 'cities', 'selectedGovernorateCode', 'constants'));
    }

    /**
     * Update the specified generation unit.
     */
    public function update(UpdateGenerationUnitRequest $request, GenerationUnit $generationUnit): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $generationUnit);

        $user = auth()->user();
        $data = $request->validated();

        // إذا كان "نفس المالك"، جلب البيانات من المشغل
        $operator = $generationUnit->operator;
        if (isset($data['operation_entity']) && $data['operation_entity'] === 'same_owner' && $operator) {
            $data['owner_name'] = $operator->owner_name;
            $data['owner_id_number'] = $operator->owner_id_number;
            $data['operator_id_number'] = $operator->operator_id_number;
        }

        // تحويل governorate من code إلى enum value
        if (isset($data['governorate'])) {
            $governorateDetail = \App\Models\ConstantDetail::whereHas('master', function($q) {
                $q->where('constant_number', 1);
            })->where('code', $data['governorate'])->first();
            
            if ($governorateDetail && $governorateDetail->value) {
                $governorateEnum = Governorate::fromValue((int) $governorateDetail->value);
                $data['governorate'] = $governorateEnum->code();
            }
        }

        // boolean fields
        if (isset($data['synchronization_available'])) {
            $data['synchronization_available'] = (bool) $data['synchronization_available'];
        }

        // تتبع المستخدمين
        $data['last_updated_by'] = $user->id;

        // معالجة خزانات الوقود
        $fuelTanksData = $data['fuel_tanks'] ?? [];
        unset($data['fuel_tanks']);
        unset($data['external_fuel_tank']);
        unset($data['fuel_tanks_count']);

        $generationUnit->update($data);

        // حذف خزانات الوقود القديمة
        $generationUnit->fuelTanks()->delete();

        // إضافة خزانات الوقود الجديدة
        if (!empty($fuelTanksData)) {
            foreach ($fuelTanksData as $index => $tankData) {
                $tankCode = FuelTank::getNextTankCode($generationUnit->id);
                
                FuelTank::create([
                    'generation_unit_id' => $generationUnit->id,
                    'tank_code' => $tankCode,
                    'capacity' => $tankData['capacity'] ?? null,
                    'location' => $tankData['location'] ?? null,
                    'filtration_system_available' => $tankData['filtration_system_available'] ?? false,
                    'condition' => $tankData['condition'] ?? null,
                    'material' => $tankData['material'] ?? null,
                    'usage' => $tankData['usage'] ?? null,
                    'measurement_method' => $tankData['measurement_method'] ?? null,
                    'order' => $index + 1,
                ]);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث وحدة التوليد بنجاح.',
                'generation_unit' => $generationUnit->fresh(),
            ]);
        }

        return redirect()->route('admin.generation-units.index')
            ->with('success', 'تم تحديث وحدة التوليد بنجاح.');
    }

    /**
     * Remove the specified generation unit.
     */
    public function destroy(Request $request, GenerationUnit $generationUnit): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $generationUnit);

        $generationUnit->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف وحدة التوليد بنجاح.',
            ]);
        }

        return redirect()->route('admin.generation-units.index')
            ->with('success', 'تم حذف وحدة التوليد بنجاح.');
    }

    /**
     * Get operator data for auto-filling generation unit form.
     */
    public function getOperatorData(Operator $operator): JsonResponse
    {
        $this->authorize('view', $operator);

        return response()->json([
            'success' => true,
            'operator' => [
                'owner_name' => $operator->owner_name,
                'owner_id_number' => $operator->owner_id_number,
                'operator_id_number' => $operator->operator_id_number,
            ],
        ]);
    }
}

