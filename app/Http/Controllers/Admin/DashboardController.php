<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Generator;
use App\Models\Operator;
use App\Models\User;
use App\Role;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // التحقق من اكتمال بيانات المشغل
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator && ! $operator->isProfileComplete()) {
                return redirect()->route('admin.operators.profile')
                    ->with('warning', 'يرجى إكمال بيانات المشغل أولاً.');
            }
        }

        // إحصائيات عامة (Super Admin فقط)
        if ($user->isSuperAdmin()) {
            $stats = [
                'users' => [
                    'total' => User::count(),
                    'super_admins' => User::where('role', Role::SuperAdmin)->count(),
                    'company_owners' => User::where('role', Role::CompanyOwner)->count(),
                    'employees' => User::where('role', Role::Employee)->count(),
                ],
                'operators' => [
                    'total' => Operator::count(),
                    'active' => Operator::count(),
                ],
                'generators' => [
                    'total' => Generator::count(),
                    'active' => Generator::where('status', 'active')->count(),
                ],
            ];
        } elseif ($user->isCompanyOwner()) {
            // إحصائيات لصاحب المشغل
            $ownedOperators = $user->ownedOperators;
            $stats = [
                'operators' => [
                    'total' => $ownedOperators->count(),
                    'active' => $ownedOperators->count(),
                ],
                'generators' => [
                    'total' => Generator::whereIn('operator_id', $ownedOperators->pluck('id'))->count(),
                    'active' => Generator::whereIn('operator_id', $ownedOperators->pluck('id'))
                        ->where('status', 'active')->count(),
                ],
                'employees' => [
                    'total' => User::whereHas('operators', function ($query) use ($ownedOperators) {
                        $query->whereIn('operators.id', $ownedOperators->pluck('id'));
                    })->where('role', Role::Employee)->distinct()->count(),
                ],
            ];
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            // إحصائيات للموظف أو الفني
            $userOperators = $user->operators;
            $stats = [
                'operators' => [
                    'total' => $userOperators->count(),
                ],
                'generators' => [
                    'total' => Generator::whereIn('operator_id', $userOperators->pluck('id'))->count(),
                    'active' => Generator::whereIn('operator_id', $userOperators->pluck('id'))
                        ->where('status', 'active')->count(),
                ],
            ];
        }

        // آخر المولدات المضافة
        $recentGenerators = Generator::with('operator')
            ->when($user->isCompanyOwner(), function ($query) use ($user) {
                $query->whereIn('operator_id', $user->ownedOperators->pluck('id'));
            })
            ->when($user->isEmployee() || $user->isTechnician(), function ($query) use ($user) {
                $query->whereIn('operator_id', $user->operators->pluck('id'));
            })
            ->latest()
            ->limit(5)
            ->get();

        // آخر المشغلين المضافة (Super Admin فقط)
        $recentOperators = $user->isSuperAdmin()
            ? Operator::with('owner')->latest()->limit(5)->get()
            : collect();

        return view('admin.dashboard', compact('stats', 'recentGenerators', 'recentOperators'));
    }
}
