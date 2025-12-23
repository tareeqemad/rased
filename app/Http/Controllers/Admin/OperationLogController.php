<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOperationLogRequest;
use App\Http\Requests\Admin\UpdateOperationLogRequest;
use App\Models\Generator;
use App\Models\OperationLog;
use App\Models\Operator;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OperationLogController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', OperationLog::class);

        $user = auth()->user();
        $query = OperationLog::with(['generator', 'operator']);

        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $query->where('operator_id', $operator->id);
            }
        } elseif ($user->isEmployee()) {
            $operators = $user->operators;
            $query->whereIn('operator_id', $operators->pluck('id'));
        }

        $operationLogs = $query->latest('operation_date')->latest('start_time')->paginate(15);

        return view('admin.operation-logs.index', compact('operationLogs'));
    }

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

    public function store(StoreOperationLogRequest $request): RedirectResponse
    {
        $this->authorize('create', OperationLog::class);

        $data = $request->validated();

        // إذا كان المستخدم CompanyOwner، استخدم مشغله تلقائياً
        if (auth()->user()->isCompanyOwner()) {
            $operator = auth()->user()->ownedOperators()->first();
            if ($operator) {
                $data['operator_id'] = $operator->id;
            }
        }

        OperationLog::create($data);

        return redirect()->route('admin.operation-logs.index')
            ->with('success', 'تم إنشاء سجل التشغيل بنجاح.');
    }

    public function show(OperationLog $operationLog): View
    {
        $this->authorize('view', $operationLog);

        $operationLog->load(['generator', 'operator']);

        return view('admin.operation-logs.show', compact('operationLog'));
    }

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

    public function update(UpdateOperationLogRequest $request, OperationLog $operationLog): RedirectResponse
    {
        $this->authorize('update', $operationLog);

        $data = $request->validated();

        // إذا كان المستخدم CompanyOwner، استخدم مشغله تلقائياً
        if (auth()->user()->isCompanyOwner()) {
            $operator = auth()->user()->ownedOperators()->first();
            if ($operator) {
                $data['operator_id'] = $operator->id;
            }
        }

        $operationLog->update($data);

        return redirect()->route('admin.operation-logs.index')
            ->with('success', 'تم تحديث سجل التشغيل بنجاح.');
    }

    public function destroy(OperationLog $operationLog): RedirectResponse
    {
        $this->authorize('delete', $operationLog);

        $operationLog->delete();

        return redirect()->route('admin.operation-logs.index')
            ->with('success', 'تم حذف سجل التشغيل بنجاح.');
    }
}
