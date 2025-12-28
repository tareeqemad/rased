<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOperationLogRequest;
use App\Http\Requests\Admin\UpdateOperationLogRequest;
use App\Models\Generator;
use App\Models\Notification;
use App\Models\OperationLog;
use App\Models\Operator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperationLogController extends Controller
{
    /**
     * Display paginated operation logs with search and filters.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', OperationLog::class);

        $user = auth()->user();
        $query = OperationLog::with(['generator', 'operator']);

        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $query->where('operator_id', $operator->id);
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operators = $user->operators;
            $query->whereIn('operator_id', $operators->pluck('id'));
        }

        // Search
        $q = trim((string) $request->input('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->whereHas('generator', function ($gq) use ($q) {
                    $gq->where('name', 'like', "%{$q}%")
                       ->orWhere('generator_number', 'like', "%{$q}%");
                })
                ->orWhereHas('operator', function ($oq) use ($q) {
                    $oq->where('name', 'like', "%{$q}%");
                })
                ->orWhere('operational_notes', 'like', "%{$q}%");
            });
        }

        if ($user->isSuperAdmin()) {
            $operatorId = (int) $request->input('operator_id', 0);
            if ($operatorId > 0) {
                $query->where('operator_id', $operatorId);
            }
        }

        // Filter by generator
        $generatorId = (int) $request->input('generator_id', 0);
        if ($generatorId > 0) {
            $query->where('generator_id', $generatorId);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('operation_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('operation_date', '<=', $request->input('date_to'));
        }

        // Group by generator if requested
        $groupByGenerator = $request->boolean('group_by_generator', false);
        $groupedLogs = null;
        
        if ($groupByGenerator) {
            // Paginate first, then group the current page's logs
            $operationLogs = $query->latest('operation_date')->latest('start_time')->paginate(15);
            // Group the current page's logs by generator
            $groupedLogs = $operationLogs->groupBy('generator_id');
        } else {
            $operationLogs = $query->latest('operation_date')->latest('start_time')->paginate(15);
        }

        if ($request->ajax() || $request->wantsJson()) {
            if ($groupByGenerator && $groupedLogs) {
                $html = view('admin.operation-logs.partials.grouped-list', [
                    'groupedLogs' => $groupedLogs,
                    'operationLogs' => $operationLogs
                ])->render();
            } else {
                $html = view('admin.operation-logs.partials.list', compact('operationLogs'))->render();
            }
            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $operationLogs->total(),
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

        return view('admin.operation-logs.index', compact('operationLogs', 'operators', 'generators', 'groupedLogs'));
    }

    /**
     * Show form for creating a new operation log entry.
     */
    public function create(): View
    {
        $this->authorize('create', OperationLog::class);

        $user = auth()->user();
        $generators = collect();
        $operators = collect();

        if ($user->isSuperAdmin()) {
            $generators = Generator::all();
            $operators = Operator::all();
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $generators = $operator->generators;
                $operators = collect([$operator]);
            }
        } elseif ($user->isEmployee()) {
            $operators = $user->operators;
            $generators = Generator::whereIn('operator_id', $operators->pluck('id'))->get();
        }

        return view('admin.operation-logs.create', compact('generators', 'operators'));
    }

    /**
     * Store a newly created operation log in database.
     */
    public function store(StoreOperationLogRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', OperationLog::class);

        $data = $request->validated();

        if (auth()->user()->isCompanyOwner()) {
            $operator = auth()->user()->ownedOperators()->first();
            if ($operator) {
                $data['operator_id'] = $operator->id;
            }
        }

        $lastSequence = OperationLog::where('generator_id', $data['generator_id'])
            ->max('sequence') ?? 0;
        $data['sequence'] = $lastSequence + 1;

        // Always calculate fuel consumed from start and end (ignore user input for security)
        if (isset($data['fuel_meter_start']) && isset($data['fuel_meter_end']) 
            && $data['fuel_meter_start'] > 0 && $data['fuel_meter_end'] > 0 
            && $data['fuel_meter_end'] >= $data['fuel_meter_start']) {
            $data['fuel_consumed'] = round($data['fuel_meter_end'] - $data['fuel_meter_start'], 2);
        } else {
            $data['fuel_consumed'] = null;
        }

        // Always calculate energy produced from start and end (ignore user input for security)
        if (isset($data['energy_meter_start']) && isset($data['energy_meter_end']) 
            && $data['energy_meter_start'] > 0 && $data['energy_meter_end'] > 0 
            && $data['energy_meter_end'] >= $data['energy_meter_start']) {
            $data['energy_produced'] = round($data['energy_meter_end'] - $data['energy_meter_start'], 2);
        } else {
            $data['energy_produced'] = null;
        }

        $operationLog = OperationLog::create($data);

        $generator = Generator::with('operator')->find($data['generator_id']);
        if ($generator && $generator->operator) {
            Notification::notifyOperatorUsers(
                $generator->operator,
                'operation_log_added',
                'تم إضافة سجل تشغيل',
                "تم إضافة سجل تشغيل جديد للمولد: {$generator->name}",
                route('admin.operation-logs.show', $operationLog)
            );
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء سجل التشغيل بنجاح.',
            ]);
        }

        return redirect()->route('admin.operation-logs.index')
            ->with('success', 'تم إنشاء سجل التشغيل بنجاح.');
    }

    /**
     * Display details of the specified operation log record.
     */
    public function show(OperationLog $operationLog): View
    {
        $this->authorize('view', $operationLog);

        $operationLog->load(['generator', 'operator']);

        return view('admin.operation-logs.show', compact('operationLog'));
    }

    /**
     * Show form for editing the specified operation log entry.
     */
    public function edit(OperationLog $operationLog): View
    {
        $this->authorize('update', $operationLog);

        $user = auth()->user();
        $generators = collect();
        $operators = collect();

        if ($user->isSuperAdmin()) {
            $generators = Generator::all();
            $operators = Operator::all();
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $generators = $operator->generators;
                $operators = collect([$operator]);
            }
        } elseif ($user->isEmployee()) {
            $operators = $user->operators;
            $generators = Generator::whereIn('operator_id', $operators->pluck('id'))->get();
        }

        return view('admin.operation-logs.edit', compact('operationLog', 'generators', 'operators'));
    }

    /**
     * Update the specified operation log record in database.
     */
    public function update(UpdateOperationLogRequest $request, OperationLog $operationLog): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $operationLog);

        $data = $request->validated();

        if (auth()->user()->isCompanyOwner()) {
            $operator = auth()->user()->ownedOperators()->first();
            if ($operator) {
                $data['operator_id'] = $operator->id;
            }
        }

        // Always calculate fuel consumed from start and end (ignore user input for security)
        if (isset($data['fuel_meter_start']) && isset($data['fuel_meter_end']) 
            && $data['fuel_meter_start'] > 0 && $data['fuel_meter_end'] > 0 
            && $data['fuel_meter_end'] >= $data['fuel_meter_start']) {
            $data['fuel_consumed'] = round($data['fuel_meter_end'] - $data['fuel_meter_start'], 2);
        } else {
            $data['fuel_consumed'] = null;
        }

        // Always calculate energy produced from start and end (ignore user input for security)
        if (isset($data['energy_meter_start']) && isset($data['energy_meter_end']) 
            && $data['energy_meter_start'] > 0 && $data['energy_meter_end'] > 0 
            && $data['energy_meter_end'] >= $data['energy_meter_start']) {
            $data['energy_produced'] = round($data['energy_meter_end'] - $data['energy_meter_start'], 2);
        } else {
            $data['energy_produced'] = null;
        }

        $operationLog->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث سجل التشغيل بنجاح.',
            ]);
        }

        return redirect()->route('admin.operation-logs.index')
            ->with('success', 'تم تحديث سجل التشغيل بنجاح.');
    }

    /**
     * Remove the specified operation log record from database.
     */
    public function destroy(Request $request, OperationLog $operationLog): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $operationLog);

        $operationLog->delete();

        $msg = 'تم حذف سجل التشغيل بنجاح.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->route('admin.operation-logs.index')->with('success', $msg);
    }
}
