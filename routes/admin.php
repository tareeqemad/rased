<?php

use App\Http\Controllers\Admin\ComplianceSafetyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FuelEfficiencyController;
use App\Http\Controllers\Admin\GeneratorController;
use App\Http\Controllers\Admin\MaintenanceRecordController;
use App\Http\Controllers\Admin\OperationLogController;
use App\Http\Controllers\Admin\OperatorController;
use App\Http\Controllers\Admin\OperatorProfileController;
use App\Http\Controllers\Admin\OperatorUnitNumberController;
use App\Http\Controllers\Admin\PermissionAuditLogController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserPermissionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('permissions', [PermissionsController::class, 'index'])->name('permissions.index');
    Route::post('permissions/assign', [PermissionsController::class, 'assignPermissions'])->name('permissions.assign');
    Route::post('permissions/search', [PermissionsController::class, 'search'])->name('permissions.search');

    // Permission Audit Logs
    Route::get('permission-audit-logs', [PermissionAuditLogController::class, 'index'])->name('permission-audit-logs.index');
    Route::get('permission-audit-logs/{permissionAuditLog}', [PermissionAuditLogController::class, 'show'])->name('permission-audit-logs.show');

    Route::resource('users', UserController::class);
    Route::get('users/{user}/permissions', [UserPermissionController::class, 'show'])->name('users.permissions');
    Route::put('users/{user}/permissions', [UserPermissionController::class, 'update'])->name('users.permissions.update');

    // صفحة إكمال بيانات المشغل (يجب أن تكون قبل resource route)
    Route::get('operators/profile', [OperatorProfileController::class, 'show'])->name('operators.profile');
    Route::put('operators/profile', [OperatorProfileController::class, 'update'])->name('operators.profile.update');

    // الحصول على رقم الوحدة التالي
    Route::get('operators/next-unit-number/{governorate}', [OperatorUnitNumberController::class, 'getNextUnitNumber'])->name('operators.next-unit-number');

    Route::resource('operators', OperatorController::class);

    Route::resource('generators', GeneratorController::class);

    // سجلات التشغيل
    Route::resource('operation-logs', OperationLogController::class);

    // كفاءة الوقود
    Route::resource('fuel-efficiencies', FuelEfficiencyController::class);

    // سجلات الصيانة
    Route::resource('maintenance-records', MaintenanceRecordController::class);

    // الامتثال والسلامة
    Route::resource('compliance-safeties', ComplianceSafetyController::class);
});
