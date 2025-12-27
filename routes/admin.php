<?php

use App\Http\Controllers\Admin\ComplaintSuggestionController;
use App\Http\Controllers\Admin\ComplianceSafetyController;
use App\Http\Controllers\Admin\ConstantDetailController;
use App\Http\Controllers\Admin\ConstantMasterController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FuelEfficiencyController;
use App\Http\Controllers\Admin\GeneratorController;
use App\Http\Controllers\Admin\MaintenanceRecordController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OperationLogController;
use App\Http\Controllers\Admin\OperatorController;
use App\Http\Controllers\Admin\OperatorProfileController;
use App\Http\Controllers\Admin\OperatorUnitNumberController;
use App\Http\Controllers\Admin\PermissionAuditLogController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    /**
     * Permissions Tree
     */
    Route::prefix('permissions')->as('permissions.')->group(function () {
        Route::get('/', [PermissionsController::class, 'index'])->name('index');
        Route::post('/assign', [PermissionsController::class, 'assignPermissions'])->name('assign');
        Route::match(['GET', 'POST'], '/search', [PermissionsController::class, 'search'])->name('search');

        Route::get('/user/{user}', [PermissionsController::class, 'getUserPermissions'])->name('user');
        Route::get('/user/{user}/permissions', [PermissionsController::class, 'getUserPermissions'])->name('user.permissions');

        Route::get('/select2/operators', [PermissionsController::class, 'select2Operators'])->name('select2.operators');
        Route::get('/select2/users', [PermissionsController::class, 'select2Users'])->name('select2.users');
    });

    /**
     * Permission Audit Logs
     */
    Route::get('permission-audit-logs', [PermissionAuditLogController::class, 'index'])->name('permission-audit-logs.index');
    Route::get('permission-audit-logs/{permissionAuditLog}', [PermissionAuditLogController::class, 'show'])->name('permission-audit-logs.show');

    /**
     * Roles (SuperAdmin only via Policy)
     */
    Route::resource('roles', RoleController::class);

    /**
     * Settings (SuperAdmin only)
     */
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('settings', [SettingsController::class, 'store'])->name('settings.store');
    Route::delete('settings/{setting}', [SettingsController::class, 'destroy'])->name('settings.destroy');

    /**
     * Constants (SuperAdmin only via Policy)
     */
    Route::resource('constants', ConstantMasterController::class);
    Route::get('constants/{constant}/details', [ConstantMasterController::class, 'show'])->name('constants.show');
    Route::get('constants/by-number/{number}', [ConstantMasterController::class, 'getByNumber'])->name('constants.get-by-number');
    Route::post('constant-details', [ConstantDetailController::class, 'store'])->name('constant-details.store');
    Route::put('constant-details/{constantDetail}', [ConstantDetailController::class, 'update'])->name('constant-details.update');
    Route::delete('constant-details/{constantDetail}', [ConstantDetailController::class, 'destroy'])->name('constant-details.destroy');
    Route::get('constant-details/by-master/{constantMaster}', [ConstantDetailController::class, 'getByMaster'])->name('constant-details.by-master');

    /**
     * Users
     */
    Route::get('users/ajax/operators', [UserController::class, 'ajaxOperators'])
    ->name('users.ajaxOperators');
    Route::post('users/{user}/impersonate', [UserController::class, 'impersonate'])->name('users.impersonate');
    Route::post('users/stop-impersonating', [UserController::class, 'stopImpersonating'])->name('users.stop-impersonating');
    Route::resource('users', UserController::class);

    // Operator employees (lock it via policy at route-level too)
    Route::get('operators/{operator}/employees', [UserController::class, 'operatorEmployees'])
        ->middleware('can:view,operator')
        ->name('operators.employees');

    /**
     * Operator profile must be before resource
     */
    Route::get('operators/profile', [OperatorProfileController::class, 'show'])->name('operators.profile');
    Route::put('operators/profile', [OperatorProfileController::class, 'update'])->name('operators.profile.update');

    Route::get('operators/next-unit-number/{governorate}', [OperatorUnitNumberController::class, 'getNextUnitNumber'])->name('operators.next-unit-number');
    
    // API للحصول على المشغلين حسب المحافظة
    Route::get('operators/by-governorate/{governorate}', [OperatorController::class, 'getByGovernorate'])->name('operators.by-governorate');

    /**
     * Operators
     */
    Route::resource('operators', OperatorController::class);

    /**
     * Generators & related modules
     */
    Route::resource('generators', GeneratorController::class);
    Route::resource('operation-logs', OperationLogController::class);
    Route::resource('fuel-efficiencies', FuelEfficiencyController::class);
    Route::resource('maintenance-records', MaintenanceRecordController::class);
    Route::resource('compliance-safeties', ComplianceSafetyController::class);

    /**
     * Complaints & Suggestions
     */
    Route::get('complaints-suggestions', [ComplaintSuggestionController::class, 'index'])->name('complaints-suggestions.index');
    Route::get('complaints-suggestions/{complaintSuggestion}', [ComplaintSuggestionController::class, 'show'])->name('complaints-suggestions.show');
    Route::get('complaints-suggestions/{complaintSuggestion}/edit', [ComplaintSuggestionController::class, 'edit'])->name('complaints-suggestions.edit');
    Route::put('complaints-suggestions/{complaintSuggestion}', [ComplaintSuggestionController::class, 'update'])->name('complaints-suggestions.update');
    Route::post('complaints-suggestions/{complaintSuggestion}/respond', [ComplaintSuggestionController::class, 'respond'])->name('complaints-suggestions.respond');
    Route::delete('complaints-suggestions/{complaintSuggestion}', [ComplaintSuggestionController::class, 'destroy'])->name('complaints-suggestions.destroy');

    /**
     * Notifications
     */
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});
