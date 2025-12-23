<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreComplianceSafetyRequest;
use App\Http\Requests\Admin\UpdateComplianceSafetyRequest;
use App\Models\ComplianceSafety;
use App\Models\Operator;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ComplianceSafetyController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', ComplianceSafety::class);

        $user = auth()->user();
        $query = ComplianceSafety::with('operator');

        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $query->where('operator_id', $operator->id);
            }
        } elseif ($user->isEmployee()) {
            $operators = $user->operators;
            $query->whereIn('operator_id', $operators->pluck('id'));
        }

        $complianceSafeties = $query->latest('last_inspection_date')->paginate(15);

        return view('admin.compliance-safeties.index', compact('complianceSafeties'));
    }

    public function create(): View
    {
        $this->authorize('create', ComplianceSafety::class);

        $user = auth()->user();
        $operators = collect();

        if ($user->isSuperAdmin()) {
            $operators = Operator::all();
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $operators = collect([$operator]);
            }
        } elseif ($user->isEmployee()) {
            $operators = $user->operators;
        }

        return view('admin.compliance-safeties.create', compact('operators'));
    }

    public function store(StoreComplianceSafetyRequest $request): RedirectResponse
    {
        $this->authorize('create', ComplianceSafety::class);

        $data = $request->validated();

        // إذا كان المستخدم CompanyOwner، استخدم مشغله تلقائياً
        if (auth()->user()->isCompanyOwner()) {
            $operator = auth()->user()->ownedOperators()->first();
            if ($operator) {
                $data['operator_id'] = $operator->id;
            }
        }

        ComplianceSafety::create($data);

        return redirect()->route('admin.compliance-safeties.index')
            ->with('success', 'تم إنشاء سجل الامتثال والسلامة بنجاح.');
    }

    public function show(ComplianceSafety $complianceSafety): View
    {
        $this->authorize('view', $complianceSafety);

        $complianceSafety->load('operator');

        return view('admin.compliance-safeties.show', compact('complianceSafety'));
    }

    public function edit(ComplianceSafety $complianceSafety): View
    {
        $this->authorize('update', $complianceSafety);

        $user = auth()->user();
        $operators = collect();

        if ($user->isSuperAdmin()) {
            $operators = Operator::all();
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $operators = collect([$operator]);
            }
        } elseif ($user->isEmployee()) {
            $operators = $user->operators;
        }

        return view('admin.compliance-safeties.edit', compact('complianceSafety', 'operators'));
    }

    public function update(UpdateComplianceSafetyRequest $request, ComplianceSafety $complianceSafety): RedirectResponse
    {
        $this->authorize('update', $complianceSafety);

        $data = $request->validated();

        // إذا كان المستخدم CompanyOwner، استخدم مشغله تلقائياً
        if (auth()->user()->isCompanyOwner()) {
            $operator = auth()->user()->ownedOperators()->first();
            if ($operator) {
                $data['operator_id'] = $operator->id;
            }
        }

        $complianceSafety->update($data);

        return redirect()->route('admin.compliance-safeties.index')
            ->with('success', 'تم تحديث سجل الامتثال والسلامة بنجاح.');
    }

    public function destroy(ComplianceSafety $complianceSafety): RedirectResponse
    {
        $this->authorize('delete', $complianceSafety);

        $complianceSafety->delete();

        return redirect()->route('admin.compliance-safeties.index')
            ->with('success', 'تم حذف سجل الامتثال والسلامة بنجاح.');
    }
}
