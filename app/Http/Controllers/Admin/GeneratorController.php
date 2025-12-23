<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGeneratorRequest;
use App\Http\Requests\Admin\UpdateGeneratorRequest;
use App\Models\FuelTank;
use App\Models\Generator;
use App\Models\Operator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GeneratorController extends Controller
{
    public function index(): View
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

        $generators = $query->latest()->paginate(15);

        return view('admin.generators.index', compact('generators'));
    }

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

        // التحقق من إمكانية إضافة مولدات
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();

            if (! $operator) {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'لا يوجد مشغل مرتبط بحسابك. يرجى التواصل مع مدير النظام.');
            }

            // التحقق من اكتمال بيانات المشغل
            if (! $operator->isProfileComplete()) {
                return redirect()->route('admin.operators.profile')
                    ->with('error', 'يجب إكمال بيانات المشغل أولاً قبل إضافة المولدات.');
            }

            // التحقق من عدد المولدات
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

        return view('admin.generators.create', compact('operators'));
    }

    public function store(StoreGeneratorRequest $request): RedirectResponse
    {
        $this->authorize('create', Generator::class);

        $user = auth()->user();

        // التحقق من عدد المولدات
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

        // رفع الصور
        if ($request->hasFile('engine_data_plate_image')) {
            $data['engine_data_plate_image'] = $request->file('engine_data_plate_image')->store('generators/engine-plates', 'public');
        }
        if ($request->hasFile('generator_data_plate_image')) {
            $data['generator_data_plate_image'] = $request->file('generator_data_plate_image')->store('generators/generator-plates', 'public');
        }
        if ($request->hasFile('control_panel_image')) {
            $data['control_panel_image'] = $request->file('control_panel_image')->store('generators/control-panels', 'public');
        }

        // إذا كان المستخدم CompanyOwner أو Technician، استخدم مشغله تلقائياً
        $authUser = auth()->user();
        if ($authUser->isCompanyOwner()) {
            $operator = $authUser->ownedOperators()->first();
            if ($operator) {
                $data['operator_id'] = $operator->id;
            }
        } elseif ($authUser->isTechnician()) {
            // الفني يمكنه إضافة مولدات للمشغلين المخصصين له
            $operators = $authUser->operators;
            if ($operators->count() === 1) {
                $data['operator_id'] = $operators->first()->id;
            }
        }

        // استخراج بيانات خزانات الوقود
        $fuelTanksData = $data['fuel_tanks'] ?? [];
        unset($data['fuel_tanks']);

        // إنشاء المولد
        $generator = Generator::create($data);

        // حفظ خزانات الوقود
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

        return redirect()->route('admin.generators.index')
            ->with('success', 'تم إنشاء المولد بنجاح.');
    }

    public function show(Generator $generator): View
    {
        $this->authorize('view', $generator);

        $generator->load(['operator', 'fuelTanks']);

        return view('admin.generators.show', compact('generator'));
    }

    public function edit(Generator $generator): View
    {
        $this->authorize('update', $generator);

        $user = auth()->user();
        $operators = collect();

        if ($user->isSuperAdmin()) {
            $operators = Operator::all();
        } elseif ($user->isCompanyOwner()) {
            $operators = $user->ownedOperators;
        } elseif ($user->isTechnician()) {
            $operators = $user->operators;
        }

        return view('admin.generators.edit', compact('generator', 'operators'));
    }

    public function update(UpdateGeneratorRequest $request, Generator $generator): RedirectResponse
    {
        $this->authorize('update', $generator);

        $data = $request->validated();

        // رفع الصور الجديدة
        if ($request->hasFile('engine_data_plate_image')) {
            // حذف الصورة القديمة
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

        // استخراج بيانات خزانات الوقود
        $fuelTanksData = $data['fuel_tanks'] ?? [];
        unset($data['fuel_tanks']);

        // تحديث المولد
        $generator->update($data);

        // حذف الخزانات القديمة وإعادة إنشائها (soft delete)
        $generator->fuelTanks()->delete();

        // حفظ خزانات الوقود الجديدة
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

        return redirect()->route('admin.generators.index')
            ->with('success', 'تم تحديث بيانات المولد بنجاح.');
    }

    public function destroy(Generator $generator): RedirectResponse
    {
        $this->authorize('delete', $generator);

        // حذف الصور
        if ($generator->engine_data_plate_image) {
            Storage::disk('public')->delete($generator->engine_data_plate_image);
        }
        if ($generator->generator_data_plate_image) {
            Storage::disk('public')->delete($generator->generator_data_plate_image);
        }
        if ($generator->control_panel_image) {
            Storage::disk('public')->delete($generator->control_panel_image);
        }

        $generator->delete();

        return redirect()->route('admin.generators.index')
            ->with('success', 'تم حذف المولد بنجاح.');
    }
}
