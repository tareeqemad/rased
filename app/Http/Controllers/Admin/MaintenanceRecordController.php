<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMaintenanceRecordRequest;
use App\Http\Requests\Admin\UpdateMaintenanceRecordRequest;
use App\Models\Generator;
use App\Models\MaintenanceRecord;
use App\Models\Notification;
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
    public function create(Request $request): View
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
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operators = $user->operators;
            $generators = Generator::whereIn('operator_id', $operators->pluck('id'))->get();
        }

        $selectedGeneratorId = $request->input('generator_id');

        return view('admin.maintenance-records.create', compact('generators', 'selectedGeneratorId'));
    }

    /**
     * Store newly created maintenance record in database.
     */
    public function store(StoreMaintenanceRecordRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', MaintenanceRecord::class);

        $data = $request->validated();
        
        // Always calculate downtime hours from start and end time (ignore user input for security)
        if (isset($data['start_time']) && isset($data['end_time']) && !empty($data['start_time']) && !empty($data['end_time'])) {
            try {
                $startTime = \Carbon\Carbon::createFromFormat('H:i', $data['start_time']);
                $endTime = \Carbon\Carbon::createFromFormat('H:i', $data['end_time']);
                
                if ($endTime < $startTime) {
                    $endTime->addDay();
                }
                
                $diffInMinutes = $startTime->diffInMinutes($endTime);
                $diffInHours = $diffInMinutes / 60;
                $data['downtime_hours'] = round($diffInHours, 2);
            } catch (\Exception $e) {
                $data['downtime_hours'] = null;
            }
        } else {
            $data['downtime_hours'] = null;
        }
        
        // Always calculate maintenance cost from parts cost, labor hours and rate (ignore user input for security)
        if (isset($data['parts_cost']) && isset($data['labor_hours']) && isset($data['labor_rate_per_hour'])) {
            $partsCost = $data['parts_cost'] ?? 0;
            $laborHours = $data['labor_hours'] ?? 0;
            $laborRate = $data['labor_rate_per_hour'] ?? 0;
            $data['maintenance_cost'] = round($partsCost + ($laborHours * $laborRate), 2);
        } else {
            $data['maintenance_cost'] = null;
        }

        $maintenanceRecord = MaintenanceRecord::create($data);

        // Update generator's last maintenance date if it's a periodic or major maintenance
        $generator = Generator::find($maintenanceRecord->generator_id);
        if ($generator && in_array($maintenanceRecord->maintenance_type, ['periodic', 'major'])) {
            $generator->update([
                'last_major_maintenance_date' => $maintenanceRecord->maintenance_date
            ]);
        }

        $generator->load('operator');
        if ($generator && $generator->operator) {
            Notification::notifyOperatorUsers(
                $generator->operator,
                'maintenance_added',
                'تم إضافة سجل صيانة',
                "تم إضافة سجل صيانة للمولد: {$generator->name}",
                route('admin.maintenance-records.show', $maintenanceRecord)
            );
        }

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

        $data = $request->validated();
        
        // Always calculate downtime hours from start and end time (ignore user input for security)
        if (isset($data['start_time']) && isset($data['end_time']) && !empty($data['start_time']) && !empty($data['end_time'])) {
            try {
                $startTime = \Carbon\Carbon::createFromFormat('H:i', $data['start_time']);
                $endTime = \Carbon\Carbon::createFromFormat('H:i', $data['end_time']);
                
                if ($endTime < $startTime) {
                    $endTime->addDay();
                }
                
                $diffInMinutes = $startTime->diffInMinutes($endTime);
                $diffInHours = $diffInMinutes / 60;
                $data['downtime_hours'] = round($diffInHours, 2);
            } catch (\Exception $e) {
                $data['downtime_hours'] = null;
            }
        } else {
            $data['downtime_hours'] = null;
        }
        
        // Always calculate maintenance cost from parts cost, labor hours and rate (ignore user input for security)
        $partsCost = isset($data['parts_cost']) && $data['parts_cost'] !== null ? (float)$data['parts_cost'] : 0;
        $laborHours = isset($data['labor_hours']) && $data['labor_hours'] !== null ? (float)$data['labor_hours'] : 0;
        $laborRate = isset($data['labor_rate_per_hour']) && $data['labor_rate_per_hour'] !== null ? (float)$data['labor_rate_per_hour'] : 0;
        
        if ($partsCost > 0 || $laborHours > 0) {
            $data['maintenance_cost'] = round($partsCost + ($laborHours * $laborRate), 2);
        } else {
            $data['maintenance_cost'] = null;
        }

        $maintenanceRecord->update($data);

        // Update generator's last maintenance date if it's a periodic or major maintenance
        $maintenanceRecord->load('generator');
        if ($maintenanceRecord->generator && in_array($maintenanceRecord->maintenance_type, ['periodic', 'major'])) {
            $maintenanceRecord->generator->update([
                'last_major_maintenance_date' => $maintenanceRecord->maintenance_date
            ]);
        }

        $maintenanceRecord->load('generator.operator');
        if ($maintenanceRecord->generator && $maintenanceRecord->generator->operator) {
            Notification::notifyOperatorUsers(
                $maintenanceRecord->generator->operator,
                'maintenance_updated',
                'تم تحديث سجل صيانة',
                "تم تحديث سجل الصيانة للمولد: {$maintenanceRecord->generator->name}",
                route('admin.maintenance-records.show', $maintenanceRecord)
            );
        }

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
