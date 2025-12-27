<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGeneratorRequest;
use App\Http\Requests\Admin\UpdateGeneratorRequest;
use App\Models\FuelTank;
use App\Models\Generator;
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
        $query = Generator::with('operator');

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

        $status = trim((string) $request->input('status', ''));
        if ($status !== '' && in_array($status, ['active', 'inactive'], true)) {
            $query->where('status', $status);
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

        return view('admin.generators.index', compact('generators', 'operators'));
    }

    /**
     * Show form for creating a new generator with validation checks.
     */
    public function create(): View|RedirectResponse
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

            $currentCount = $operator->generators()->count();
            $maxCount = $operator->generators_count ?? 0;

            if ($maxCount == 0) {
                return redirect()->route('admin.operators.profile')
                    ->with('error', 'يجب تحديد عدد المولدات في بيانات المشغل أولاً قبل إضافة المولدات.');
            }

            if ($currentCount >= $maxCount) {
                return redirect()->route('admin.generators.index')
                    ->with('error', "لقد وصلت إلى الحد الأقصى لعدد المولدات ({$maxCount}). يمكنك إضافة مولدات جديدة بعد تحديث عدد المولدات في بيانات المشغل.");
            }
        }

        $constants = [
            'status' => ConstantsHelper::getByName('حالة المولد'),
            'engine_type' => ConstantsHelper::getByName('نوع المحرك'),
            'injection_system' => ConstantsHelper::getByName('نظام الحقن'),
            'measurement_indicator' => ConstantsHelper::getByName('مؤشر القياس'),
            'technical_condition' => ConstantsHelper::getByName('الحالة الفنية'),
            'control_panel_type' => ConstantsHelper::getByName('نوع لوحة التحكم'),
            'control_panel_status' => ConstantsHelper::getByName('حالة لوحة التحكم'),
            'material' => ConstantsHelper::getByName('مادة التصنيع'),
            'usage' => ConstantsHelper::getByName('الاستخدام'),
            'measurement_method' => ConstantsHelper::getByName('طريقة القياس'),
            'location' => ConstantsHelper::getByName('موقع الخزان'),
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

        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $currentCount = $operator->generators()->count();
                $maxCount = $operator->generators_count ?? 0;

                if ($currentCount >= $maxCount) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "لقد وصلت إلى الحد الأقصى لعدد المولدات ({$maxCount}).");
                }
            }
        }

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

        $fuelTanksData = $data['fuel_tanks'] ?? [];
        unset($data['fuel_tanks']);

        if (!isset($data['fuel_tanks_count']) || $data['fuel_tanks_count'] === null || $data['fuel_tanks_count'] === '') {
            $data['fuel_tanks_count'] = 0;
        }

        if (!isset($data['external_fuel_tank']) || $data['external_fuel_tank'] === null || $data['external_fuel_tank'] === '') {
            $data['external_fuel_tank'] = false;
        } else {
            $data['external_fuel_tank'] = (bool) $data['external_fuel_tank'];
        }

        $generator = Generator::create($data);

        if (! empty($fuelTanksData)) {
            foreach ($fuelTanksData as $index => $tankData) {
                FuelTank::create([
                    'generator_id' => $generator->id,
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
                'message' => 'تم إنشاء المولد بنجاح.',
            ]);
        }

        return redirect()->route('admin.generators.index')
            ->with('success', 'تم إنشاء المولد بنجاح.');
    }

    /**
     * Display detailed information about the specified generator.
     */
    public function show(Generator $generator): View
    {
        $this->authorize('view', $generator);

        $generator->load(['operator', 'fuelTanks']);

        return view('admin.generators.show', compact('generator'));
    }

    /**
     * Show form for editing the specified generator record.
     */
    public function edit(Generator $generator): View
    {
        $this->authorize('update', $generator);

        $generator->load('fuelTanks');

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
            'status' => ConstantsHelper::getByName('حالة المولد'),
            'engine_type' => ConstantsHelper::getByName('نوع المحرك'),
            'injection_system' => ConstantsHelper::getByName('نظام الحقن'),
            'measurement_indicator' => ConstantsHelper::getByName('مؤشر القياس'),
            'technical_condition' => ConstantsHelper::getByName('الحالة الفنية'),
            'control_panel_type' => ConstantsHelper::getByName('نوع لوحة التحكم'),
            'control_panel_status' => ConstantsHelper::getByName('حالة لوحة التحكم'),
            'material' => ConstantsHelper::getByName('مادة التصنيع'),
            'usage' => ConstantsHelper::getByName('الاستخدام'),
            'measurement_method' => ConstantsHelper::getByName('طريقة القياس'),
            'location' => ConstantsHelper::getByName('موقع الخزان'),
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

        $fuelTanksData = $data['fuel_tanks'] ?? [];
        unset($data['fuel_tanks']);

        if (!isset($data['fuel_tanks_count']) || $data['fuel_tanks_count'] === null || $data['fuel_tanks_count'] === '') {
            $data['fuel_tanks_count'] = 0;
        }

        if (!isset($data['external_fuel_tank']) || $data['external_fuel_tank'] === null || $data['external_fuel_tank'] === '') {
            $data['external_fuel_tank'] = false;
        } else {
            $data['external_fuel_tank'] = (bool) $data['external_fuel_tank'];
        }

        $generator->update($data);
        $generator->fuelTanks()->delete();

        if (! empty($fuelTanksData)) {
            foreach ($fuelTanksData as $index => $tankData) {
                FuelTank::create([
                    'generator_id' => $generator->id,
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
}
