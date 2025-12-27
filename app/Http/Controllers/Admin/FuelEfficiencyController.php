<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFuelEfficiencyRequest;
use App\Http\Requests\Admin\UpdateFuelEfficiencyRequest;
use App\Models\FuelEfficiency;
use App\Models\Generator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FuelEfficiencyController extends Controller
{
    /**
     * Display paginated list of fuel efficiency records filtered by user role.
     */
    public function index(Request $request): View|JsonResponse
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
            });
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
            $query->whereDate('consumption_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('consumption_date', '<=', $request->input('date_to'));
        }

        // Group by generator if requested
        $groupByGenerator = $request->boolean('group_by_generator', false);
        $groupedLogs = null;
        
        if ($groupByGenerator) {
            // Paginate first, then group the current page's logs
            $fuelEfficiencies = $query->latest('consumption_date')->paginate(15);
            // Group the current page's logs by generator
            $groupedLogs = $fuelEfficiencies->groupBy('generator_id');
        } else {
            $fuelEfficiencies = $query->latest('consumption_date')->paginate(15);
        }

        if ($request->ajax() || $request->wantsJson()) {
            if ($groupByGenerator && $groupedLogs) {
                $html = view('admin.fuel-efficiencies.partials.grouped-list', [
                    'groupedLogs' => $groupedLogs,
                    'fuelEfficiencies' => $fuelEfficiencies
                ])->render();
            } else {
                $html = view('admin.fuel-efficiencies.partials.list', compact('fuelEfficiencies'))->render();
            }
            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $fuelEfficiencies->total(),
            ]);
        }

        $operators = collect();
        $generators = collect();
        
        if ($user->isSuperAdmin()) {
            $operators = \App\Models\Operator::select('id', 'name', 'unit_number')
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

        return view('admin.fuel-efficiencies.index', compact('fuelEfficiencies', 'operators', 'generators', 'groupedLogs'));
    }

    /**
     * Show form for creating a new fuel efficiency record entry.
     */
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

    /**
     * Store newly created fuel efficiency record in database.
     */
    public function store(StoreFuelEfficiencyRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', FuelEfficiency::class);

        FuelEfficiency::create($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء سجل كفاءة الوقود بنجاح.',
            ]);
        }

        return redirect()->route('admin.fuel-efficiencies.index')
            ->with('success', 'تم إنشاء سجل كفاءة الوقود بنجاح.');
    }

    /**
     * Display detailed information about the specified fuel efficiency record.
     */
    public function show(FuelEfficiency $fuelEfficiency): View
    {
        $this->authorize('view', $fuelEfficiency);

        $fuelEfficiency->load('generator.operator');

        return view('admin.fuel-efficiencies.show', compact('fuelEfficiency'));
    }

    /**
     * Show form for editing the specified fuel efficiency record.
     */
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

    /**
     * Update the specified fuel efficiency record data in database.
     */
    public function update(UpdateFuelEfficiencyRequest $request, FuelEfficiency $fuelEfficiency): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $fuelEfficiency);

        $fuelEfficiency->update($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث سجل كفاءة الوقود بنجاح.',
            ]);
        }

        return redirect()->route('admin.fuel-efficiencies.index')
            ->with('success', 'تم تحديث سجل كفاءة الوقود بنجاح.');
    }

    /**
     * Remove the specified fuel efficiency record from database.
     */
    public function destroy(Request $request, FuelEfficiency $fuelEfficiency): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $fuelEfficiency);

        $fuelEfficiency->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف سجل كفاءة الوقود بنجاح.',
            ]);
        }

        return redirect()->route('admin.fuel-efficiencies.index')
            ->with('success', 'تم حذف سجل كفاءة الوقود بنجاح.');
    }
}
