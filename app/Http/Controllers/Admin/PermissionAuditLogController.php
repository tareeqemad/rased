<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PermissionAuditLog;
use Illuminate\View\View;

class PermissionAuditLogController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $query = PermissionAuditLog::with(['user', 'performedBy', 'permission']);

        // SuperAdmin يرى جميع السجلات
        // Admin يرى جميع السجلات (view only)
        // CompanyOwner يرى فقط سجلات موظفيه وفنييه
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $operatorUserIds = $operator->users->pluck('id')->toArray();
                $query->whereIn('user_id', $operatorUserIds);
            } else {
                $query->whereRaw('1 = 0'); // لا يوجد سجلات
            }
        } elseif (! $user->isSuperAdmin() && ! $user->isAdmin()) {
            abort(403);
        }

        $auditLogs = $query->latest()->paginate(20);

        return view('admin.permission-audit-logs.index', compact('auditLogs'));
    }

    public function show(PermissionAuditLog $permissionAuditLog): View
    {
        $user = auth()->user();

        // التحقق من الصلاحيات
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if (! $operator || ! $operator->users->contains($permissionAuditLog->user)) {
                abort(403);
            }
        } elseif (! $user->isSuperAdmin() && ! $user->isAdmin()) {
            abort(403);
        }

        $permissionAuditLog->load(['user', 'performedBy', 'permission']);

        return view('admin.permission-audit-logs.show', compact('permissionAuditLog'));
    }
}
