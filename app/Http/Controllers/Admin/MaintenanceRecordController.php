<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMaintenanceRecordRequest;
use App\Http\Requests\Admin\UpdateMaintenanceRecordRequest;
use App\Models\Generator;
use App\Models\MaintenanceRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MaintenanceRecordController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', MaintenanceRecord::class);

        $user = auth()->user();
        $query = MaintenanceRecord::with('generator.operator');

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

        $maintenanceRecords = $query->latest('maintenance_date')->paginate(15);

        return view('admin.maintenance-records.index', compact('maintenanceRecords'));
    }

    public function create(): View
    {
        $this->authorize('create', MaintenanceRecord::class);

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

        return view('admin.maintenance-records.create', compact('generators'));
    }

    public function store(StoreMaintenanceRecordRequest $request): RedirectResponse
    {
        $this->authorize('create', MaintenanceRecord::class);

        MaintenanceRecord::create($request->validated());

        return redirect()->route('admin.maintenance-records.index')
            ->with('success', 'تم إنشاء سجل الصيانة بنجاح.');
    }

    public function show(MaintenanceRecord $maintenanceRecord): View
    {
        $this->authorize('view', $maintenanceRecord);

        $maintenanceRecord->load('generator.operator');

        return view('admin.maintenance-records.show', compact('maintenanceRecord'));
    }

    public function edit(MaintenanceRecord $maintenanceRecord): View
    {
        $this->authorize('update', $maintenanceRecord);

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

        return view('admin.maintenance-records.edit', compact('maintenanceRecord', 'generators'));
    }

    public function update(UpdateMaintenanceRecordRequest $request, MaintenanceRecord $maintenanceRecord): RedirectResponse
    {
        $this->authorize('update', $maintenanceRecord);

        $maintenanceRecord->update($request->validated());

        return redirect()->route('admin.maintenance-records.index')
            ->with('success', 'تم تحديث سجل الصيانة بنجاح.');
    }

    public function destroy(MaintenanceRecord $maintenanceRecord): RedirectResponse
    {
        $this->authorize('delete', $maintenanceRecord);

        $maintenanceRecord->delete();

        return redirect()->route('admin.maintenance-records.index')
            ->with('success', 'تم حذف سجل الصيانة بنجاح.');
    }
}
