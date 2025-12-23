<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFuelEfficiencyRequest;
use App\Http\Requests\Admin\UpdateFuelEfficiencyRequest;
use App\Models\FuelEfficiency;
use App\Models\Generator;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FuelEfficiencyController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', FuelEfficiency::class);

        $user = auth()->user();
        $query = FuelEfficiency::with('generator.operator');

        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $query->whereHas('generator', function ($q) use ($operator) {
                    $q->where('operator_id', $operator->id);
                });
            }
        } elseif ($user->isEmployee()) {
            $operators = $user->operators;
            $query->whereHas('generator', function ($q) use ($operators) {
                $q->whereIn('operator_id', $operators->pluck('id'));
            });
        }

        $fuelEfficiencies = $query->latest('consumption_date')->paginate(15);

        return view('admin.fuel-efficiencies.index', compact('fuelEfficiencies'));
    }

    public function create(): View
    {
        $this->authorize('create', FuelEfficiency::class);

        $user = auth()->user();
        $generators = collect();

        if ($user->isSuperAdmin()) {
            $generators = Generator::all();
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $generators = $operator->generators;
            }
        } elseif ($user->isEmployee()) {
            $operators = $user->operators;
            $generators = Generator::whereIn('operator_id', $operators->pluck('id'))->get();
        }

        return view('admin.fuel-efficiencies.create', compact('generators'));
    }

    public function store(StoreFuelEfficiencyRequest $request): RedirectResponse
    {
        $this->authorize('create', FuelEfficiency::class);

        FuelEfficiency::create($request->validated());

        return redirect()->route('admin.fuel-efficiencies.index')
            ->with('success', 'تم إنشاء سجل كفاءة الوقود بنجاح.');
    }

    public function show(FuelEfficiency $fuelEfficiency): View
    {
        $this->authorize('view', $fuelEfficiency);

        $fuelEfficiency->load('generator.operator');

        return view('admin.fuel-efficiencies.show', compact('fuelEfficiency'));
    }

    public function edit(FuelEfficiency $fuelEfficiency): View
    {
        $this->authorize('update', $fuelEfficiency);

        $user = auth()->user();
        $generators = collect();

        if ($user->isSuperAdmin()) {
            $generators = Generator::all();
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $generators = $operator->generators;
            }
        } elseif ($user->isEmployee()) {
            $operators = $user->operators;
            $generators = Generator::whereIn('operator_id', $operators->pluck('id'))->get();
        }

        return view('admin.fuel-efficiencies.edit', compact('fuelEfficiency', 'generators'));
    }

    public function update(UpdateFuelEfficiencyRequest $request, FuelEfficiency $fuelEfficiency): RedirectResponse
    {
        $this->authorize('update', $fuelEfficiency);

        $fuelEfficiency->update($request->validated());

        return redirect()->route('admin.fuel-efficiencies.index')
            ->with('success', 'تم تحديث سجل كفاءة الوقود بنجاح.');
    }

    public function destroy(FuelEfficiency $fuelEfficiency): RedirectResponse
    {
        $this->authorize('delete', $fuelEfficiency);

        $fuelEfficiency->delete();

        return redirect()->route('admin.fuel-efficiencies.index')
            ->with('success', 'تم حذف سجل كفاءة الوقود بنجاح.');
    }
}
