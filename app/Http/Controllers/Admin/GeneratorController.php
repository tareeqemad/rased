<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGeneratorRequest;
use App\Http\Requests\Admin\UpdateGeneratorRequest;
use App\Models\Generator;
use App\Models\Notification;
use App\Models\Operator;
use App\Helpers\ConstantsHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GeneratorController extends Controller
{
    /**
     * Display paginated generators list with search and status filters.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Generator::class);

        $user = auth()->user();
        $query = Generator::with([
            'operator',
            'statusDetail',
            'engineTypeDetail',
            'injectionSystemDetail',
            'measurementIndicatorDetail',
            'technicalConditionDetail',
            'controlPanelTypeDetail',
            'controlPanelStatusDetail'
        ]);

        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $query->where('operator_id', $operator->id);
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operators = $user->operators;
            $query->whereIn('operator_id', $operators->pluck('id'));
        }

        $q = trim((string) $request->input('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('generator_number', 'like', "%{$q}%")
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

        if ($user->isSuperAdmin()) {
            $operatorId = (int) $request->input('operator_id', 0);
            if ($operatorId > 0) {
                $query->where('operator_id', $operatorId);
            }
        }

        $generators = $query->latest()->paginate(15);

        if ($request->ajax() || $request->wantsJson()) {
            $html = view('admin.generators.partials.list', compact('generators'))->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $generators->total(),
            ]);
        }

        $operators = collect();
        if ($user->isSuperAdmin()) {
            $operators = Operator::select('id', 'name', 'unit_number')
                ->orderBy('name')
                ->get();
        }

        // جلب ثوابت الحالة للفلترة
        $statusConstants = ConstantsHelper::get(3); // حالة المولد

        return view('admin.generators.index', compact('generators', 'operators', 'statusConstants'));
    }

    /**
     * Show form for creating a new generator with validation checks.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $this->authorize('create', Generator::class);

        $user = auth()->user();
        $operators = collect();

        if ($user->isSuperAdmin()) {
            $operators = Operator::all();
        } elseif ($user->isCompanyOwner()) {
            $operators = $user->ownedOperators;
        }

        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();

            if (! $operator) {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'لا يوجد مشغل مرتبط بحسابك. يرجى التواصل مع مدير النظام.');
            }

            if (! $operator->isProfileComplete()) {
                return redirect()->route('admin.operators.profile')
                    ->with('error', 'يجب إكمال بيانات المشغل أولاً قبل إضافة المولدات.');
            }

            // التحقق من وجود وحدات التوليد
            $generationUnits = $operator->generationUnits;
            if ($generationUnits->isEmpty()) {
                return redirect()->route('admin.operators.profile')
                    ->with('error', 'يجب إضافة وحدة توليد على الأقل قبل إضافة المولدات.');
            }

            // إذا تم تحديد generation_unit_id في الطلب، التحقق من أن الوحدة موجودة ومتاحة
            $generationUnitId = $request->input('generation_unit_id');
            if ($generationUnitId) {
                $generationUnit = $generationUnits->find($generationUnitId);
                if (!$generationUnit) {
                    return redirect()->route('admin.operators.profile')
                        ->with('error', 'وحدة التوليد المحددة غير موجودة أو غير متاحة.');
                }

                // التحقق من أن عدد المولدات لم يتجاوز العدد المطلوب
                $currentCount = $generationUnit->generators()->count();
                $maxCount = $generationUnit->generators_count;

                if ($currentCount >= $maxCount) {
                    return redirect()->route('admin.operators.profile')
                        ->with('error', "لقد وصلت إلى الحد الأقصى لعدد المولدات في هذه الوحدة ({$maxCount}). يمكنك إضافة مولدات جديدة بعد تحديث عدد المولدات في وحدة التوليد.");
                }
            }
        }

        $constants = [
            'status' => ConstantsHelper::get(3), // حالة المولد
            'engine_type' => ConstantsHelper::get(4), // نوع المحرك
            'injection_system' => ConstantsHelper::get(5), // نظام الحقن
            'measurement_indicator' => ConstantsHelper::get(6), // مؤشر القياس
            'technical_condition' => ConstantsHelper::get(7), // الحالة الفنية
            'control_panel_type' => ConstantsHelper::get(8), // نوع لوحة التحكم
            'control_panel_status' => ConstantsHelper::get(9), // حالة لوحة التحكم
            'material' => ConstantsHelper::get(10), // مادة التصنيع
            'usage' => ConstantsHelper::get(11), // الاستخدام
            'measurement_method' => ConstantsHelper::get(19), // طريقة القياس
        ];

        return view('admin.generators.create', compact('operators', 'constants'));
    }

    /**
     * Store newly created generator with images and fuel tanks data.
     */
    public function store(StoreGeneratorRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', Generator::class);

        $user = auth()->user();

        $data = $request->validated();

        if ($request->hasFile('engine_data_plate_image')) {
            $data['engine_data_plate_image'] = $request->file('engine_data_plate_image')->store('generators/engine-plates', 'public');
        }
        if ($request->hasFile('generator_data_plate_image')) {
            $data['generator_data_plate_image'] = $request->file('generator_data_plate_image')->store('generators/generator-plates', 'public');
        }
        if ($request->hasFile('control_panel_image')) {
            $data['control_panel_image'] = $request->file('control_panel_image')->store('generators/control-panels', 'public');
        }

        $authUser = auth()->user();
        if ($authUser->isCompanyOwner()) {
            $operator = $authUser->ownedOperators()->first();
            if ($operator) {
                $data['operator_id'] = $operator->id;
            }
        } elseif ($authUser->isTechnician()) {
            $operators = $authUser->operators;
            if ($operators->count() === 1) {
                $data['operator_id'] = $operators->first()->id;
            }
        }

        // التحقق من وجود generation_unit_id
        if (empty($data['generation_unit_id'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'يجب اختيار وحدة التوليد.');
        }

        // التحقق من أن عدد المولدات لم يتجاوز العدد المطلوب
        $generationUnit = \App\Models\GenerationUnit::find($data['generation_unit_id']);
        if (!$generationUnit) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'وحدة التوليد المحددة غير موجودة.');
        }

        $currentCount = $generationUnit->generators()->count();
        $maxCount = $generationUnit->generators_count;
        if ($currentCount >= $maxCount) {
            return redirect()->back()
                ->withInput()
                ->with('error', "لقد وصلت إلى الحد الأقصى لعدد المولدات في هذه الوحدة ({$maxCount}).");
        }

        // تعيين operator_id من generation_unit
        if (empty($data['operator_id'])) {
            $data['operator_id'] = $generationUnit->operator_id;
        }

        // توليد رقم المولد تلقائياً إذا لم يكن محدداً
        if (empty($data['generator_number'])) {
            $data['generator_number'] = Generator::getNextGeneratorNumber($data['generation_unit_id']);
            if (!$data['generator_number']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'تعذر توليد رقم المولد. تأكد من أن وحدة التوليد لديها unit_code وأن عدد المولدات لم يتجاوز 99.');
            }
        }

        $generator = Generator::create($data);

        $generator->load('operator');
        if ($generator->operator) {
            Notification::notifyOperatorUsers(
                $generator->operator,
                'generator_added',
                'تم إضافة مولد جديد',
                "تم إضافة المولد: {$generator->name}",
                route('admin.generators.show', $generator)
            );
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء المولد بنجاح.',
            ]);
        }

        return redirect()->route('admin.generators.index')
            ->with('success', 'تم إنشاء المولد بنجاح.');
    }

    /**
     * Display detailed information about the specified generator.
     */
    /**
     * Display QR Code for generator.
     */
    public function qrCode(Generator $generator): View
    {
        $this->authorize('view', $generator);

        $generator->load(['operator', 'generationUnit']);

        // إنشاء بيانات QR Code - استخدام URL يفتح معلومات المولد
        $qrData = route('qr.generator', ['code' => $generator->generator_number ?? 'GEN-' . $generator->id]);
        
        // مسار حفظ QR Code
        $qrCodePath = 'qr-codes/generators/' . $generator->id . '.svg';
        $fullPath = storage_path('app/public/' . $qrCodePath);

        // التحقق من وجود QR Code محفوظ
        if (!file_exists($fullPath) || !$generator->qr_code_generated_at) {
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
            $generator->update(['qr_code_generated_at' => now()]);
        } else {
            // قراءة QR Code المحفوظ
            $qrCodeSvg = file_get_contents($fullPath);
        }

        // بيانات إضافية للعرض في الصفحة
        $qrInfo = [
            'type' => 'generator',
            'id' => $generator->id,
            'generator_number' => $generator->generator_number,
            'name' => $generator->name,
            'operator_id' => $generator->operator_id,
            'operator_name' => $generator->operator?->name,
            'generation_unit_id' => $generator->generation_unit_id,
            'generation_unit_code' => $generator->generationUnit?->unit_code,
        ];

        return view('admin.generators.qr-code', compact('generator', 'qrCodeSvg', 'qrInfo'));
    }

    public function show(Generator $generator): View
    {
        $this->authorize('view', $generator);

        $generator->load([
            'operator', 
            'generationUnit',
            'statusDetail',
            'engineTypeDetail',
            'injectionSystemDetail',
            'measurementIndicatorDetail',
            'technicalConditionDetail',
            'controlPanelTypeDetail',
            'controlPanelStatusDetail'
        ]);

        return view('admin.generators.show', compact('generator'));
    }

    /**
     * Show form for editing the specified generator record.
     */
    public function edit(Generator $generator): View
    {
        $this->authorize('update', $generator);

        $generator->load('generationUnit');

        $user = auth()->user();
        $operators = collect();

        if ($user->isSuperAdmin()) {
            $operators = Operator::all();
        } elseif ($user->isCompanyOwner()) {
            $operators = $user->ownedOperators;
        } elseif ($user->isTechnician()) {
            $operators = $user->operators;
        }

        $constants = [
            'status' => ConstantsHelper::get(3), // حالة المولد
            'engine_type' => ConstantsHelper::get(4), // نوع المحرك
            'injection_system' => ConstantsHelper::get(5), // نظام الحقن
            'measurement_indicator' => ConstantsHelper::get(6), // مؤشر القياس
            'technical_condition' => ConstantsHelper::get(7), // الحالة الفنية
            'control_panel_type' => ConstantsHelper::get(8), // نوع لوحة التحكم
            'control_panel_status' => ConstantsHelper::get(9), // حالة لوحة التحكم
            'material' => ConstantsHelper::get(10), // مادة التصنيع
            'usage' => ConstantsHelper::get(11), // الاستخدام
            'measurement_method' => ConstantsHelper::get(19), // طريقة القياس
        ];

        return view('admin.generators.edit', compact('generator', 'operators', 'constants'));
    }

    /**
     * Update generator data including images and fuel tanks information.
     */
    public function update(UpdateGeneratorRequest $request, Generator $generator): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $generator);

        $data = $request->validated();

        if ($request->hasFile('engine_data_plate_image')) {
            if ($generator->engine_data_plate_image) {
                Storage::disk('public')->delete($generator->engine_data_plate_image);
            }
            $data['engine_data_plate_image'] = $request->file('engine_data_plate_image')->store('generators/engine-plates', 'public');
        }
        if ($request->hasFile('generator_data_plate_image')) {
            if ($generator->generator_data_plate_image) {
                Storage::disk('public')->delete($generator->generator_data_plate_image);
            }
            $data['generator_data_plate_image'] = $request->file('generator_data_plate_image')->store('generators/generator-plates', 'public');
        }
        if ($request->hasFile('control_panel_image')) {
            if ($generator->control_panel_image) {
                Storage::disk('public')->delete($generator->control_panel_image);
            }
            $data['control_panel_image'] = $request->file('control_panel_image')->store('generators/control-panels', 'public');
        }

        $generator->update($data);

        $generator->load('operator');
        if ($generator->operator) {
            Notification::notifyOperatorUsers(
                $generator->operator,
                'generator_updated',
                'تم تحديث مولد',
                "تم تحديث بيانات المولد: {$generator->name}",
                route('admin.generators.show', $generator)
            );
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث بيانات المولد بنجاح.',
            ]);
        }

        return redirect()->route('admin.generators.index')
            ->with('success', 'تم تحديث بيانات المولد بنجاح.');
    }

    /**
     * Delete generator record and associated image files from storage.
     */
    public function destroy(Request $request, Generator $generator): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $generator);

        if ($generator->engine_data_plate_image) {
            Storage::disk('public')->delete($generator->engine_data_plate_image);
        }
        if ($generator->generator_data_plate_image) {
            Storage::disk('public')->delete($generator->generator_data_plate_image);
        }
        if ($generator->control_panel_image) {
            Storage::disk('public')->delete($generator->control_panel_image);
        }

        $generatorName = $generator->name;
        $generator->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف المولد بنجاح.',
            ]);
        }

        return redirect()->route('admin.generators.index')
            ->with('success', 'تم حذف المولد بنجاح.');
    }

    /**
     * Get generation units for a specific operator (AJAX).
     */
    public function getGenerationUnits(Operator $operator): JsonResponse
    {
        $this->authorize('view', $operator);

        $generationUnits = $operator->generationUnits()
            ->select('id', 'name', 'unit_code', 'generators_count')
            ->get()
            ->map(function ($unit) {
                $currentCount = $unit->generators()->count();
                $maxCount = $unit->generators_count;
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'unit_code' => $unit->unit_code,
                    'label' => "{$unit->name} ({$unit->unit_code}) - {$currentCount}/{$maxCount} مولد",
                    'current_count' => $currentCount,
                    'max_count' => $maxCount,
                    'available' => $currentCount < $maxCount,
                ];
            });

        return response()->json([
            'success' => true,
            'generation_units' => $generationUnits,
        ]);
    }

    /**
     * Generate generator number for a specific generation unit (AJAX).
     */
    public function generateGeneratorNumber(\App\Models\GenerationUnit $generationUnit): JsonResponse
    {
        $this->authorize('view', $generationUnit);

        // التحقق من أن عدد المولدات لم يتجاوز العدد المطلوب
        $currentCount = $generationUnit->generators()->count();
        $maxCount = $generationUnit->generators_count;
        
        if ($currentCount >= $maxCount) {
            return response()->json([
                'success' => false,
                'message' => "لقد وصلت إلى الحد الأقصى لعدد المولدات في هذه الوحدة ({$maxCount}).",
            ], 400);
        }

        $generatorNumber = Generator::getNextGeneratorNumber($generationUnit->id);
        
        if (!$generatorNumber) {
            return response()->json([
                'success' => false,
                'message' => 'تعذر توليد رقم المولد. تأكد من أن وحدة التوليد لديها unit_code وأن عدد المولدات لم يتجاوز 99.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'generator_number' => $generatorNumber,
        ]);
    }
}
