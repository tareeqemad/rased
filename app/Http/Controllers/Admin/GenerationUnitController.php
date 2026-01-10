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
        $query = GenerationUnit::with(['operator', 'generators', 'statusDetail', 'operationEntityDetail', 'synchronizationAvailableDetail', 'environmentalComplianceStatusDetail'])->withCount('generators');

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

        // فلترة حسب الحالة (استخدام status_id)
        $statusId = (int) $request->input('status_id', 0);
        if ($statusId > 0) {
            $query->where('status_id', $statusId);
        }

        // فلترة حسب المشغل (للسوبر أدمن و Admin)
        if ($user->isSuperAdmin() || $user->isAdmin()) {
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
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            $operators = Operator::select('id', 'name')
                ->orderBy('name')
                ->get();
        }

        return view('admin.generation-units.index', compact('generationUnits', 'operators'));
    }

    /**
     * Show the form for creating a new generation unit.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $this->authorize('create', GenerationUnit::class);

        $user = auth()->user();
        $operator = null;

        // Check if operator_id is provided in query parameter (for Super Admin)
        $operatorId = $request->query('operator_id');
        
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if (!$operator) {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'لا يوجد مشغل مرتبط بحسابك.');
            }
        } elseif ($user->isSuperAdmin() && $operatorId) {
            // Super Admin can specify operator_id via query parameter
            $operator = Operator::find($operatorId);
            if (!$operator) {
                return redirect()->back()
                    ->with('error', 'المشغل المحدد غير موجود.');
            }
        }

        // جلب المحافظات والمدن
        $governorates = ConstantsHelper::get(1);
        $cities = collect();
        $selectedGovernorateId = null;

        if ($operator && $operator->governorate) {
            $selectedGovernorateCode = $operator->governorate->code();
            $governorateDetail = ConstantsHelper::findByCode(1, $selectedGovernorateCode);
            if ($governorateDetail) {
                $selectedGovernorateId = $governorateDetail->id;
                $cities = ConstantsHelper::getCitiesByGovernorate($governorateDetail->id);
            }
        }

        $constants = [
            'status' => ConstantsHelper::get(15), // حالة الوحدة
            'operation_entity' => ConstantsHelper::get(2), // جهة التشغيل
            'synchronization_available' => ConstantsHelper::get(16), // إمكانية المزامنة
            'environmental_compliance_status' => ConstantsHelper::get(14), // حالة الامتثال البيئي
            'location' => ConstantsHelper::get(21), // موقع الخزان (تم تحديثه من 18 إلى 21)
            'material' => ConstantsHelper::get(10), // مادة التصنيع
            'usage' => ConstantsHelper::get(11), // الاستخدام
            'measurement_method' => ConstantsHelper::get(19), // طريقة القياس
        ];

        // جلب جميع المشغلين للسوبر أدمن
        $allOperators = collect();
        if ($user->isSuperAdmin()) {
            $allOperators = Operator::select('id', 'name')->orderBy('name')->get();
            
            // If operator is selected via query parameter, set it as selected in the dropdown
            if ($operator && $operatorId) {
                $allOperators = $allOperators->map(function($op) use ($operatorId) {
                    $op->is_selected = ($op->id == $operatorId);
                    return $op;
                });
            }
        }

        return view('admin.generation-units.create', compact('operator', 'governorates', 'cities', 'selectedGovernorateId', 'constants', 'allOperators'));
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
        } elseif ($user->isSuperAdmin()) {
            // Super Admin يجب أن يختار مشغل
            if (!isset($data['operator_id']) || empty($data['operator_id'])) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'يجب اختيار المشغل.');
            }
            $operator = Operator::find($data['operator_id']);
            if (!$operator) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'المشغل المحدد غير موجود.');
            }
        }

        // إذا كان "نفس المالك"، جلب البيانات من المشغل
        if (isset($data['operation_entity_id']) && $operator) {
            // الحصول على ID ثابت "نفس المالك" من constant_master رقم 2
            $sameOwnerConstant = ConstantsHelper::findByCode(2, 'SAME_OWNER');
            if ($sameOwnerConstant && (int)$data['operation_entity_id'] === $sameOwnerConstant->id) {
                $data['owner_name'] = $operator->owner_name;
                $data['owner_id_number'] = $operator->owner_id_number;
                $data['operator_id_number'] = $operator->operator_id_number;
            }
        }

        // Get governorate code and city code to generate unit_code
        $governorateCode = null;
        $cityCode = null;
        
        if (isset($data['governorate_id']) && !empty($data['governorate_id'])) {
            $governorateDetail = \App\Models\ConstantDetail::find($data['governorate_id']);
            if ($governorateDetail && $governorateDetail->value) {
                try {
                    $governorateEnum = Governorate::fromValue((int) $governorateDetail->value);
                    $governorateCode = $governorateEnum->code();
                } catch (\Exception $e) {
                    \Log::error('Failed to get governorate code', [
                        'governorate_id' => $data['governorate_id'],
                        'value' => $governorateDetail->value,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
        
        // If governorate code not found and operator exists, try to get from operator
        if (!$governorateCode && $operator && $operator->governorate) {
            try {
                $governorateCode = $operator->governorate->code();
            } catch (\Exception $e) {
                \Log::error('Failed to get governorate code from operator', [
                    'operator_id' => $operator->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Get city code
        if (isset($data['city_id']) && !empty($data['city_id'])) {
            $cityDetail = \App\Models\ConstantDetail::find($data['city_id']);
            if ($cityDetail && $cityDetail->code) {
                $cityCode = $cityDetail->code;
            }
        }
        
        // If city code not found and operator exists, try to get from operator
        if (!$cityCode && $operator && $operator->city_id) {
            $cityDetail = $operator->cityDetail;
            if ($cityDetail && $cityDetail->code) {
                $cityCode = $cityDetail->code;
            }
        }

        // تعيين قيم افتراضية للحقول الاختيارية
        if (!isset($data['generators_count']) || empty($data['generators_count'])) {
            $data['generators_count'] = 1; // افتراضي: مولد واحد
        }
        
        if (!isset($data['status_id']) || empty($data['status_id'])) {
            // جلب حالة "فعال" من الثوابت
            $activeStatus = \App\Models\ConstantDetail::whereHas('master', function($q) {
                $q->where('constant_number', 15);
            })->where('code', 'ACTIVE')->first();
            
            if ($activeStatus) {
                $data['status_id'] = $activeStatus->id;
            }
        }

        // توليد رقم الوحدة وكود الوحدة تلقائياً
        if (!isset($data['unit_number']) || empty($data['unit_number'])) {
            // إذا كان governorate و city_id موجودان في الـ form، استخدمهما لتوليد unit_number
            if ($governorateCode && $cityCode) {
                $data['unit_number'] = GenerationUnit::getNextUnitNumberByLocation($governorateCode, $cityCode);
            } else {
                // إذا لم يكن هناك operator_id، استخدم رقم افتراضي
                $data['unit_number'] = GenerationUnit::getNextUnitNumber($data['operator_id'] ?? null);
            }
        }

        if (!isset($data['unit_code']) || empty($data['unit_code'])) {
            // Use governorateCode and cityCode from form if available
            if ($governorateCode && $cityCode) {
                try {
                    $data['unit_code'] = GenerationUnit::generateUnitCodeByLocation($governorateCode, $cityCode, $data['unit_number'] ?? null);
                } catch (\Exception $e) {
                    \Log::error('Failed to generate unit code', [
                        'governorate_code' => $governorateCode,
                        'city_code' => $cityCode,
                        'unit_number' => $data['unit_number'] ?? null,
                        'error' => $e->getMessage(),
                    ]);
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'فشل في توليد كود الوحدة. يرجى التأكد من إدخال المحافظة والمدينة بشكل صحيح.');
                }
            } else {
                // Cannot generate unit code without governorate and city
                $missingFields = [];
                if (!$governorateCode) {
                    $missingFields[] = 'المحافظة';
                }
                if (!$cityCode) {
                    $missingFields[] = 'المدينة';
                }
                
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'فشل في توليد كود الوحدة. يرجى التأكد من إدخال: ' . implode(' و ', $missingFields) . '.');
            }
        }

        // التأكد من أن unit_code ليس null
        if (empty($data['unit_code'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'فشل في توليد كود الوحدة. يرجى التأكد من إدخال المحافظة والمدينة بشكل صحيح.');
        }

        // Ensure operator_id is set
        if (!isset($data['operator_id']) || empty($data['operator_id'])) {
            if ($operator) {
                $data['operator_id'] = $operator->id;
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'يجب تحديد المشغل.');
            }
        }
        
        // Track users
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
                    'location_id' => $tankData['location_id'] ?? null,
                    'filtration_system_available' => $tankData['filtration_system_available'] ?? false,
                    'condition' => $tankData['condition'] ?? null,
                    'material_id' => $tankData['material_id'] ?? null,
                    'usage_id' => $tankData['usage_id'] ?? null,
                    'measurement_method_id' => $tankData['measurement_method_id'] ?? null,
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

        $generationUnit->load(['operator', 'generators', 'fuelTanks', 'statusDetail', 'operationEntityDetail', 'synchronizationAvailableDetail', 'environmentalComplianceStatusDetail', 'city']);

        return view('admin.generation-units.show', compact('generationUnit'));
    }

    /**
     * Display QR Code for generation unit.
     */
    public function qrCode(GenerationUnit $generationUnit): View
    {
        $this->authorize('view', $generationUnit);

        $generationUnit->load(['operator']);

        // إنشاء بيانات QR Code - استخدام URL يفتح معلومات الوحدة
        $qrData = route('qr.generation-unit', ['code' => $generationUnit->unit_code ?? 'GU-' . $generationUnit->id]);
        
        // مسار حفظ QR Code
        $qrCodePath = 'qr-codes/generation-units/' . $generationUnit->id . '.svg';
        $fullPath = storage_path('app/public/' . $qrCodePath);

        // التحقق من وجود QR Code محفوظ
        if (!file_exists($fullPath) || !$generationUnit->qr_code_generated_at) {
            // إنشاء مجلد إذا لم يكن موجوداً
            $directory = dirname($fullPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // إنشاء QR Code
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(400),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            );
            $writer = new \BaconQrCode\Writer($renderer);
            $qrCodeSvg = $writer->writeString($qrData);

            // حفظ QR Code
            file_put_contents($fullPath, $qrCodeSvg);

            // تسجيل تاريخ توليد QR Code
            $generationUnit->update(['qr_code_generated_at' => now()]);
        } else {
            // قراءة QR Code المحفوظ
            $qrCodeSvg = file_get_contents($fullPath);
        }

        // بيانات إضافية للعرض في الصفحة
        $qrInfo = [
            'type' => 'generation_unit',
            'id' => $generationUnit->id,
            'unit_code' => $generationUnit->unit_code,
            'name' => $generationUnit->name,
            'operator_id' => $generationUnit->operator_id,
            'operator_name' => $generationUnit->operator?->name,
        ];

        return view('admin.generation-units.qr-code', compact('generationUnit', 'qrCodeSvg', 'qrInfo'));
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
        $selectedGovernorateId = null;

        if ($generationUnit->governorate_id) {
            $selectedGovernorateId = $generationUnit->governorate_id;
            $governorateDetail = \App\Models\ConstantDetail::find($generationUnit->governorate_id);
            if ($governorateDetail) {
                $cities = ConstantsHelper::getCitiesByGovernorate($governorateDetail->id);
            }
        }

        $constants = [
            'status' => ConstantsHelper::get(15), // حالة الوحدة
            'operation_entity' => ConstantsHelper::get(2), // جهة التشغيل
            'synchronization_available' => ConstantsHelper::get(16), // إمكانية المزامنة
            'environmental_compliance_status' => ConstantsHelper::get(14), // حالة الامتثال البيئي
            'location' => ConstantsHelper::get(21), // موقع الخزان (تم تحديثه من 18 إلى 21)
            'material' => ConstantsHelper::get(10), // مادة التصنيع
            'usage' => ConstantsHelper::get(11), // الاستخدام
            'measurement_method' => ConstantsHelper::get(19), // طريقة القياس
        ];

        $generationUnit->load(['fuelTanks', 'statusDetail', 'operationEntityDetail', 'synchronizationAvailableDetail', 'environmentalComplianceStatusDetail', 'governorateDetail', 'city']);

        return view('admin.generation-units.edit', compact('generationUnit', 'operator', 'governorates', 'cities', 'selectedGovernorateId', 'constants'));
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
        if (isset($data['operation_entity_id']) && $operator) {
            // الحصول على ID ثابت "نفس المالك" من constant_master رقم 2
            $sameOwnerConstant = ConstantsHelper::findByCode(2, 'SAME_OWNER');
            if ($sameOwnerConstant && (int)$data['operation_entity_id'] === $sameOwnerConstant->id) {
                $data['owner_name'] = $operator->owner_name;
                $data['owner_id_number'] = $operator->owner_id_number;
                $data['operator_id_number'] = $operator->operator_id_number;
            }
        }

        // الحصول على governorate code و city code لتوليد unit_code (إذا تم تغييرهما)
        $governorateCode = null;
        $cityCode = null;
        if (isset($data['governorate_id'])) {
            $governorateDetail = \App\Models\ConstantDetail::find($data['governorate_id']);
            if ($governorateDetail && $governorateDetail->value) {
                $governorateEnum = Governorate::fromValue((int) $governorateDetail->value);
                $governorateCode = $governorateEnum->code();
            }
        } elseif ($generationUnit->governorate_id) {
            // إذا لم يتم تغيير المحافظة، استخدم القيمة الحالية
            $governorateDetail = \App\Models\ConstantDetail::find($generationUnit->governorate_id);
            if ($governorateDetail && $governorateDetail->value) {
                $governorateEnum = Governorate::fromValue((int) $governorateDetail->value);
                $governorateCode = $governorateEnum->code();
            }
        }

        if (isset($data['city_id'])) {
            $cityDetail = \App\Models\ConstantDetail::find($data['city_id']);
            if ($cityDetail && $cityDetail->code) {
                $cityCode = $cityDetail->code;
            }
        } elseif ($generationUnit->city_id) {
            $cityDetail = \App\Models\ConstantDetail::find($generationUnit->city_id);
            if ($cityDetail && $cityDetail->code) {
                $cityCode = $cityDetail->code;
            }
        }

        // إذا تم تغيير المحافظة أو المدينة، إعادة توليد unit_code
        if ($governorateCode && $cityCode && 
            ($data['governorate_id'] != $generationUnit->governorate_id || 
             (isset($data['city_id']) && $data['city_id'] != $generationUnit->city_id))) {
            $data['unit_number'] = GenerationUnit::getNextUnitNumberByLocation($governorateCode, $cityCode);
            $data['unit_code'] = GenerationUnit::generateUnitCodeByLocation($governorateCode, $cityCode, $data['unit_number']);
        }

        // لا حاجة لتحويل synchronization_available لأنه أصبح ID الآن

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
                    'location_id' => $tankData['location_id'] ?? null,
                    'filtration_system_available' => $tankData['filtration_system_available'] ?? false,
                    'condition' => $tankData['condition'] ?? null,
                    'material_id' => $tankData['material_id'] ?? null,
                    'usage_id' => $tankData['usage_id'] ?? null,
                    'measurement_method_id' => $tankData['measurement_method_id'] ?? null,
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

