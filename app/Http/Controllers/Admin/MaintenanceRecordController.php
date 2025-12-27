<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMaintenanceRecordRequest;
use App\Http\Requests\Admin\UpdateMaintenanceRecordRequest;
use App\Models\Generator;
use App\Models\MaintenanceRecord;
use App\Models\Operator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceRecordController extends Controller
{
    /**
     * Display paginated list of maintenance records filtered by user role.
     */
    public function index(Request $request): View|JsonResponse
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
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operators = $user->operators;
            $query->whereHas('generator', function ($q) use ($operators) {
                $q->whereIn('operator_id', $operators->pluck('id'));
            });
        }

        // Search
        $q = trim((string) $request->input('q', ''));
        if ($q !== '') {
            $query->whereHas('generator', function ($gq) use ($q) {
                $gq->where('name', 'like', "%{$q}%")
                   ->orWhere('generator_number', 'like', "%{$q}%");
            })
            ->orWhere('technician_name', 'like', "%{$q}%")
            ->orWhere('work_performed', 'like', "%{$q}%");
        }

        if ($user->isSuperAdmin()) {
            $operatorId = (int) $request->input('operator_id', 0);
            if ($operatorId > 0) {
                $query->whereHas('generator', function ($q) use ($operatorId) {
                    $q->where('operator_id', $operatorId);
                });
            }
        }

        // Filter by generator
        $generatorId = (int) $request->input('generator_id', 0);
        if ($generatorId > 0) {
            $query->where('generator_id', $generatorId);
        }

        // Date filters
        if ($request->filled('date_from')) {
            $query->whereDate('maintenance_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('maintenance_date', '<=', $request->input('date_to'));
        }

        // Group by generator if requested
        $groupByGenerator = $request->boolean('group_by_generator', false);
        $groupedLogs = null;
        
        if ($groupByGenerator) {
            // Paginate first, then group the current page's logs
            $maintenanceRecords = $query->latest('maintenance_date')->paginate(15);
            // Group the current page's logs by generator
            $groupedLogs = $maintenanceRecords->groupBy('generator_id');
        } else {
            $maintenanceRecords = $query->latest('maintenance_date')->paginate(15);
        }

        if ($request->ajax() || $request->wantsJson()) {
            if ($groupByGenerator && $groupedLogs) {
                $html = view('admin.maintenance-records.partials.grouped-list', [
                    'groupedLogs' => $groupedLogs,
                    'maintenanceRecords' => $maintenanceRecords
                ])->render();
            } else {
                $html = view('admin.maintenance-records.partials.list', compact('maintenanceRecords'))->render();
            }
            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $maintenanceRecords->total(),
            ]);
        }

        $operators = collect();
        $generators = collect();
        
        if ($user->isSuperAdmin()) {
            $operators = Operator::select('id', 'name', 'unit_number')
                ->orderBy('name')
                ->get();
            
            // Get generators based on selected operator or all
            $selectedOperatorId = (int) $request->input('operator_id', 0);
            if ($selectedOperatorId > 0) {
                $generators = Generator::where('operator_id', $selectedOperatorId)
                    ->select('id', 'name', 'generator_number', 'operator_id')
                    ->orderBy('generator_number')
                    ->get();
            } else {
                $generators = Generator::select('id', 'name', 'generator_number', 'operator_id')
                    ->orderBy('generator_number')
                    ->get();
            }
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $generators = $operator->generators()->select('id', 'name', 'generator_number', 'operator_id')
                    ->orderBy('generator_number')
                    ->get();
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $userOperators = $user->operators;
            $generators = Generator::whereIn('operator_id', $userOperators->pluck('id'))
                ->select('id', 'name', 'generator_number', 'operator_id')
                ->orderBy('generator_number')
                ->get();
        }

        return view('admin.maintenance-records.index', compact('maintenanceRecords', 'operators', 'generators', 'groupedLogs'));
    }

    /**
     * Show form for creating a new maintenance record entry.
     */
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

    /**
     * Store newly created maintenance record in database.
     */
    public function store(StoreMaintenanceRecordRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', MaintenanceRecord::class);

        MaintenanceRecord::create($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء سجل الصيانة بنجاح.',
            ]);
        }

        return redirect()->route('admin.maintenance-records.index')
            ->with('success', 'تم إنشاء سجل الصيانة بنجاح.');
    }

    /**
     * Display detailed information about the specified maintenance record.
     */
    public function show(MaintenanceRecord $maintenanceRecord): View
    {
        $this->authorize('view', $maintenanceRecord);

        $maintenanceRecord->load('generator.operator');

        return view('admin.maintenance-records.show', compact('maintenanceRecord'));
    }

    /**
     * Show form for editing the specified maintenance record.
     */
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

    /**
     * Update the specified maintenance record data in database.
     */
    public function update(UpdateMaintenanceRecordRequest $request, MaintenanceRecord $maintenanceRecord): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $maintenanceRecord);

        $maintenanceRecord->update($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث سجل الصيانة بنجاح.',
            ]);
        }

        return redirect()->route('admin.maintenance-records.index')
            ->with('success', 'تم تحديث سجل الصيانة بنجاح.');
    }

    /**
     * Remove the specified maintenance record from database.
     */
    public function destroy(Request $request, MaintenanceRecord $maintenanceRecord): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $maintenanceRecord);

        $maintenanceRecord->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف سجل الصيانة بنجاح.',
            ]);
        }

        return redirect()->route('admin.maintenance-records.index')
            ->with('success', 'تم حذف سجل الصيانة بنجاح.');
    }
}
