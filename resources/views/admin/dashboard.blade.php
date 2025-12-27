@extends('layouts.admin')

@section('title', 'لوحة التحكم')

@php
    $breadcrumbTitle = 'لوحة التحكم';
    use Carbon\Carbon;
@endphp

@section('content')
<div class="dashboard-page">
    <!-- Welcome Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="dashboard-welcome-card">
                <div class="dashboard-welcome-content">
                    <div class="dashboard-welcome-text">
                        <h2 class="dashboard-welcome-title">
                            <i class="bi bi-hand-thumbs-up me-2"></i>
                            مرحباً بك {{ auth()->user()->name }} 👋
                        </h2>
                        <p class="dashboard-welcome-subtitle">
                            {{ now('Asia/Gaza')->locale('ar')->translatedFormat('l، d F Y') }}
                        </p>
                    </div>
                    <div class="dashboard-welcome-time">
                        <div class="dashboard-time-value">{{ now('Asia/Gaza')->format('H:i') }}</div>
                        <div class="dashboard-time-label">{{ now('Asia/Gaza')->locale('ar')->translatedFormat('A') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Important Alerts -->
    @if((isset($generatorsNeedingMaintenance) && $generatorsNeedingMaintenance->count() > 0) || 
        (isset($unansweredComplaints) && $unansweredComplaints->count() > 0) || 
        (isset($expiringCompliance) && $expiringCompliance->count() > 0))
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="dashboard-alerts">
                <div class="dashboard-alerts-header">
                    <h5 class="dashboard-alerts-title">
                        <i class="bi bi-bell-fill me-2"></i>
                        تنبيهات مهمة
                    </h5>
                </div>
                <div class="dashboard-alerts-body">
                    @if(isset($generatorsNeedingMaintenance) && $generatorsNeedingMaintenance->count() > 0)
                        <div class="dashboard-alert-item dashboard-alert-warning">
                            <div class="dashboard-alert-icon">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </div>
                            <div class="dashboard-alert-content">
                                <div class="dashboard-alert-title">مولدات تحتاج صيانة</div>
                                <div class="dashboard-alert-desc">{{ $generatorsNeedingMaintenance->count() }} مولد يحتاج إلى صيانة فورية</div>
                            </div>
                            <a href="{{ route('admin.maintenance-records.index') }}" class="dashboard-alert-action">
                                عرض <i class="bi bi-arrow-left ms-1"></i>
                            </a>
                        </div>
                    @endif

                    @if(isset($unansweredComplaints) && $unansweredComplaints->count() > 0)
                        <div class="dashboard-alert-item dashboard-alert-info">
                            <div class="dashboard-alert-icon">
                                <i class="bi bi-chat-left-text"></i>
                            </div>
                            <div class="dashboard-alert-content">
                                <div class="dashboard-alert-title">شكاوى ومقترحات غير م responded عليها</div>
                                <div class="dashboard-alert-desc">{{ $unansweredComplaints->count() }} طلب يحتاج إلى رد</div>
                            </div>
                            <a href="{{ route('admin.complaints-suggestions.index') }}" class="dashboard-alert-action">
                                عرض <i class="bi bi-arrow-left ms-1"></i>
                            </a>
                        </div>
                    @endif

                    @if(isset($expiringCompliance) && $expiringCompliance->count() > 0)
                        <div class="dashboard-alert-item dashboard-alert-danger">
                            <div class="dashboard-alert-icon">
                                <i class="bi bi-shield-exclamation"></i>
                            </div>
                            <div class="dashboard-alert-content">
                                <div class="dashboard-alert-title">شهادات منتهية أو قريبة من الانتهاء</div>
                                <div class="dashboard-alert-desc">{{ $expiringCompliance->count() }} شهادة تحتاج إلى متابعة</div>
                            </div>
                            <a href="{{ route('admin.compliance-safeties.index') }}" class="dashboard-alert-action">
                                عرض <i class="bi bi-arrow-left ms-1"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <div>
                        <h5 class="dashboard-card-title">
                            <i class="bi bi-lightning-charge me-2"></i>
                            إجراءات سريعة
                        </h5>
                        <p class="dashboard-card-subtitle">وصول سريع للصفحات المهمة</p>
                    </div>
                </div>
                <div class="dashboard-card-body">
                    <div class="dashboard-quick-actions">
                        @if(auth()->user()->isSuperAdmin())
                            <a href="{{ route('admin.operators.create') }}" class="dashboard-quick-action">
                                <div class="dashboard-quick-action-icon bg-info">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div class="dashboard-quick-action-text">
                                    <div class="dashboard-quick-action-title">إضافة مشغل</div>
                                    <div class="dashboard-quick-action-desc">تسجيل مشغل جديد</div>
                                </div>
                            </a>
                            <a href="{{ route('admin.users.create') }}" class="dashboard-quick-action">
                                <div class="dashboard-quick-action-icon bg-primary">
                                    <i class="bi bi-person-plus"></i>
                                </div>
                                <div class="dashboard-quick-action-text">
                                    <div class="dashboard-quick-action-title">إضافة مستخدم</div>
                                    <div class="dashboard-quick-action-desc">إنشاء حساب جديد</div>
                                </div>
                            </a>
                        @endif
                        <a href="{{ route('admin.generators.create') }}" class="dashboard-quick-action">
                            <div class="dashboard-quick-action-icon bg-success">
                                <i class="bi bi-lightning-charge-fill"></i>
                            </div>
                            <div class="dashboard-quick-action-text">
                                <div class="dashboard-quick-action-title">إضافة مولد</div>
                                <div class="dashboard-quick-action-desc">تسجيل مولد جديد</div>
                            </div>
                        </a>
                        <a href="{{ route('admin.operation-logs.create') }}" class="dashboard-quick-action">
                            <div class="dashboard-quick-action-icon bg-warning">
                                <i class="bi bi-journal-plus"></i>
                            </div>
                            <div class="dashboard-quick-action-text">
                                <div class="dashboard-quick-action-title">سجل تشغيل</div>
                                <div class="dashboard-quick-action-desc">إضافة سجل جديد</div>
                            </div>
                        </a>
                        <a href="{{ route('admin.maintenance-records.create') }}" class="dashboard-quick-action">
                            <div class="dashboard-quick-action-icon bg-danger">
                                <i class="bi bi-tools"></i>
                            </div>
                            <div class="dashboard-quick-action-text">
                                <div class="dashboard-quick-action-title">سجل صيانة</div>
                                <div class="dashboard-quick-action-desc">تسجيل عملية صيانة</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Statistics Cards -->
    <div class="row g-3 mb-4">
        @if(auth()->user()->isSuperAdmin())
            <!-- Users -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="dashboard-stat-card dashboard-stat-primary">
                    <div class="dashboard-stat-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-label">المستخدمون</div>
                        <div class="dashboard-stat-value">{{ number_format($stats['users']['total']) }}</div>
                        <div class="dashboard-stat-badges">
                            <span class="badge badge-primary">{{ $stats['users']['super_admins'] }} مدير</span>
                            <span class="badge badge-info">{{ $stats['users']['company_owners'] }} صاحب</span>
                            <span class="badge badge-success">{{ $stats['users']['employees'] }} موظف</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Operators -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="dashboard-stat-card dashboard-stat-info">
                <div class="dashboard-stat-icon">
                    <i class="bi bi-building"></i>
                </div>
                <div class="dashboard-stat-content">
                    <div class="dashboard-stat-label">المشغلون</div>
                    <div class="dashboard-stat-value">{{ number_format($stats['operators']['total']) }}</div>
                    @if(isset($stats['operators']['active']))
                        <div class="dashboard-stat-badges">
                            <span class="badge badge-success">
                                <i class="bi bi-check-circle me-1"></i>
                                {{ $stats['operators']['active'] }} نشط
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Generators -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="dashboard-stat-card dashboard-stat-success">
                <div class="dashboard-stat-icon">
                    <i class="bi bi-lightning-charge-fill"></i>
                </div>
                <div class="dashboard-stat-content">
                    <div class="dashboard-stat-label">المولدات</div>
                    <div class="dashboard-stat-value">{{ number_format($stats['generators']['total']) }}</div>
                    @if(isset($stats['generators']['active']))
                        <div class="dashboard-stat-badges">
                            <span class="badge badge-success">
                                <i class="bi bi-check-circle me-1"></i>
                                {{ $stats['generators']['active'] }} نشطة
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if(auth()->user()->isCompanyOwner() && isset($stats['employees']))
            <!-- Employees -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="dashboard-stat-card dashboard-stat-warning">
                    <div class="dashboard-stat-icon">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-label">الموظفون</div>
                        <div class="dashboard-stat-value">{{ number_format($stats['employees']['total']) }}</div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Operations Statistics -->
    @if(isset($operationStats) && $operationStats['total'] > 0)
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <div>
                        <h5 class="dashboard-card-title">
                            <i class="bi bi-graph-up me-2"></i>
                            إحصائيات التشغيل
                        </h5>
                        <p class="dashboard-card-subtitle">نظرة شاملة على أداء المولدات</p>
                    </div>
                    <a href="{{ route('admin.operation-logs.index') }}" class="btn btn-outline-primary btn-sm">
                        عرض التفاصيل <i class="bi bi-arrow-left ms-1"></i>
                    </a>
                </div>
                <div class="dashboard-card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="dashboard-stat-mini">
                                <div class="dashboard-stat-mini-label">إجمالي السجلات</div>
                                <div class="dashboard-stat-mini-value">{{ number_format($operationStats['total']) }}</div>
                                <div class="dashboard-stat-mini-badges">
                                    <span class="badge badge-info">{{ $operationStats['this_month'] }} هذا الشهر</span>
                                    <span class="badge badge-primary">{{ $operationStats['this_week'] }} هذا الأسبوع</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="dashboard-stat-mini">
                                <div class="dashboard-stat-mini-label">الطاقة المنتجة</div>
                                <div class="dashboard-stat-mini-value">{{ number_format($operationStats['total_energy'], 2) }}</div>
                                <div class="dashboard-stat-mini-unit">kWh</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="dashboard-stat-mini">
                                <div class="dashboard-stat-mini-label">الوقود المستهلك</div>
                                <div class="dashboard-stat-mini-value">{{ number_format($operationStats['total_fuel'], 2) }}</div>
                                <div class="dashboard-stat-mini-unit">لتر</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="dashboard-stat-mini">
                                <div class="dashboard-stat-mini-label">متوسط نسبة التحميل</div>
                                <div class="dashboard-stat-mini-value">{{ number_format($operationStats['avg_load'], 1) }}%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Additional Statistics Row -->
    <div class="row g-3 mb-4">
        @if(isset($maintenanceStats) && $maintenanceStats['total'] > 0)
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="dashboard-stat-card dashboard-stat-warning">
                    <div class="dashboard-stat-icon">
                        <i class="bi bi-tools"></i>
                    </div>
                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-label">سجلات الصيانة</div>
                        <div class="dashboard-stat-value">{{ number_format($maintenanceStats['total']) }}</div>
                        <div class="dashboard-stat-badges">
                            <span class="badge badge-warning">{{ $maintenanceStats['this_month'] }} هذا الشهر</span>
                            @if($maintenanceStats['total_cost'] > 0)
                                <span class="badge badge-danger">{{ number_format($maintenanceStats['total_cost']) }} ₪</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($fuelStats) && $fuelStats['total'] > 0)
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="dashboard-stat-card dashboard-stat-success">
                    <div class="dashboard-stat-icon">
                        <i class="bi bi-fuel-pump"></i>
                    </div>
                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-label">كفاءة الوقود</div>
                        <div class="dashboard-stat-value">{{ number_format($fuelStats['avg_fuel_efficiency'], 1) }}%</div>
                        @if($fuelStats['total_cost'] > 0)
                            <div class="dashboard-stat-badges">
                                <span class="badge badge-success">{{ number_format($fuelStats['total_cost']) }} ₪</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if(isset($complianceStats) && $complianceStats['total'] > 0)
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="dashboard-stat-card dashboard-stat-primary">
                    <div class="dashboard-stat-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-label">الامتثال والسلامة</div>
                        <div class="dashboard-stat-value">{{ number_format($complianceStats['valid']) }}</div>
                        <div class="dashboard-stat-badges">
                            <span class="badge badge-success">{{ $complianceStats['valid'] }} ساري</span>
                            @if($complianceStats['expired'] > 0)
                                <span class="badge badge-danger">{{ $complianceStats['expired'] }} منتهي</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($complaintsStats) && $complaintsStats['total'] > 0)
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="dashboard-stat-card dashboard-stat-info">
                    <div class="dashboard-stat-icon">
                        <i class="bi bi-chat-left-text"></i>
                    </div>
                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-label">الشكاوى والمقترحات</div>
                        <div class="dashboard-stat-value">{{ number_format($complaintsStats['total']) }}</div>
                        <div class="dashboard-stat-badges">
                            @if($complaintsStats['unanswered'] > 0)
                                <span class="badge badge-warning">{{ $complaintsStats['unanswered'] }} غير م responded عليها</span>
                            @endif
                            <span class="badge badge-primary">{{ $complaintsStats['pending'] }} قيد الانتظار</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Recent Items & Alerts -->
    <div class="row g-3">
        <!-- Generators Needing Maintenance -->
        @if(isset($generatorsNeedingMaintenance) && $generatorsNeedingMaintenance->count() > 0)
            <div class="col-12 col-lg-6">
                <div class="dashboard-card dashboard-card-warning">
                    <div class="dashboard-card-header">
                        <div>
                            <h5 class="dashboard-card-title text-warning">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                مولدات تحتاج صيانة
                            </h5>
                            <p class="dashboard-card-subtitle">مولدات تحتاج إلى صيانة فورية</p>
                        </div>
                        <a href="{{ route('admin.maintenance-records.index') }}" class="btn btn-outline-warning btn-sm">
                            عرض الكل <i class="bi bi-arrow-left ms-1"></i>
                        </a>
                    </div>
                    <div class="dashboard-card-body p-0">
                        <div class="dashboard-list-container">
                            @foreach($generatorsNeedingMaintenance as $generator)
                                <div class="dashboard-list-item">
                                    <div class="dashboard-list-item-icon">
                                        <i class="bi bi-exclamation-circle text-danger"></i>
                                    </div>
                                    <div class="dashboard-list-item-content">
                                        <h6 class="dashboard-list-item-title">{{ $generator->name }}</h6>
                                        <div class="dashboard-list-item-meta">
                                            <span class="dashboard-list-item-text">
                                                <i class="bi bi-building me-1"></i>
                                                {{ $generator->operator->name }}
                                            </span>
                                        </div>
                                        <small class="dashboard-list-item-time">
                                            @if($generator->last_major_maintenance_date)
                                                <i class="bi bi-calendar-x me-1"></i>
                                                آخر صيانة: {{ $generator->last_major_maintenance_date->diffForHumans() }}
                                            @else
                                                <span class="text-danger">
                                                    <i class="bi bi-exclamation-circle me-1"></i>
                                                    لم يتم تسجيل صيانة
                                                </span>
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Recent Operation Logs -->
        @if(isset($recentOperationLogs) && $recentOperationLogs->count() > 0)
            <div class="col-12 col-lg-6">
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div>
                            <h5 class="dashboard-card-title">
                                <i class="bi bi-journal-text me-2 text-info"></i>
                                آخر سجلات التشغيل
                            </h5>
                            <p class="dashboard-card-subtitle">آخر السجلات المسجلة</p>
                        </div>
                        <a href="{{ route('admin.operation-logs.index') }}" class="btn btn-outline-primary btn-sm">
                            عرض الكل <i class="bi bi-arrow-left ms-1"></i>
                        </a>
                    </div>
                    <div class="dashboard-card-body p-0">
                        <div class="dashboard-list-container">
                            @foreach($recentOperationLogs as $log)
                                <div class="dashboard-list-item">
                                    <div class="dashboard-list-item-icon">
                                        <i class="bi bi-lightning-charge text-success"></i>
                                    </div>
                                    <div class="dashboard-list-item-content">
                                        <h6 class="dashboard-list-item-title">{{ $log->generator->name }}</h6>
                                        <div class="dashboard-list-item-meta">
                                            <span class="dashboard-list-item-text">
                                                <i class="bi bi-building me-1"></i>
                                                {{ $log->operator->name }}
                                            </span>
                                            @if($log->energy_produced)
                                                <span class="badge badge-success">{{ number_format($log->energy_produced, 2) }} kWh</span>
                                            @endif
                                        </div>
                                        <small class="dashboard-list-item-time">
                                            <i class="bi bi-calendar me-1"></i>
                                            {{ $log->operation_date->format('Y-m-d') }}
                                            <span class="mx-2">|</span>
                                            <i class="bi bi-clock me-1"></i>
                                            {{ $log->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Additional Recent Items -->
    <div class="row g-3 mt-3">
        @if(isset($unansweredComplaints) && $unansweredComplaints->count() > 0)
            <!-- Unanswered Complaints -->
            <div class="col-12 col-lg-6">
                <div class="dashboard-card dashboard-card-info">
                    <div class="dashboard-card-header">
                        <div>
                            <h5 class="dashboard-card-title text-info">
                                <i class="bi bi-chat-left-text me-2"></i>
                                شكاوى ومقترحات غير م responded عليها
                            </h5>
                            <p class="dashboard-card-subtitle">طلبات تحتاج إلى متابعة</p>
                        </div>
                        <a href="{{ route('admin.complaints-suggestions.index') }}" class="btn btn-outline-info btn-sm">
                            عرض الكل <i class="bi bi-arrow-left ms-1"></i>
                        </a>
                    </div>
                    <div class="dashboard-card-body p-0">
                        <div class="dashboard-list-container">
                            @foreach($unansweredComplaints as $complaint)
                                <div class="dashboard-list-item">
                                    <div class="dashboard-list-item-icon">
                                        <i class="bi bi-chat-left-text text-info"></i>
                                    </div>
                                    <div class="dashboard-list-item-content">
                                        <h6 class="dashboard-list-item-title">
                                            {{ $complaint->type === 'complaint' ? 'شكوى' : 'مقترح' }}: {{ $complaint->name }}
                                        </h6>
                                        <div class="dashboard-list-item-meta">
                                            @if($complaint->generator)
                                                <span class="dashboard-list-item-text">
                                                    <i class="bi bi-lightning-charge me-1"></i>
                                                    {{ $complaint->generator->name }}
                                                </span>
                                            @endif
                                            <span class="badge badge-{{ $complaint->status === 'pending' ? 'warning' : 'info' }}">
                                                {{ $complaint->status_label }}
                                            </span>
                                        </div>
                                        <small class="dashboard-list-item-time">
                                            <i class="bi bi-clock me-1"></i>
                                            {{ $complaint->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($expiringCompliance) && $expiringCompliance->count() > 0)
            <!-- Expiring Compliance -->
            <div class="col-12 col-lg-6">
                <div class="dashboard-card dashboard-card-danger">
                    <div class="dashboard-card-header">
                        <div>
                            <h5 class="dashboard-card-title text-danger">
                                <i class="bi bi-shield-exclamation me-2"></i>
                                شهادات منتهية أو قريبة من الانتهاء
                            </h5>
                            <p class="dashboard-card-subtitle">شهادات تحتاج إلى متابعة</p>
                        </div>
                        <a href="{{ route('admin.compliance-safeties.index') }}" class="btn btn-outline-danger btn-sm">
                            عرض الكل <i class="bi bi-arrow-left ms-1"></i>
                        </a>
                    </div>
                    <div class="dashboard-card-body p-0">
                        <div class="dashboard-list-container">
                            @foreach($expiringCompliance as $compliance)
                                <div class="dashboard-list-item">
                                    <div class="dashboard-list-item-icon">
                                        <i class="bi bi-shield-exclamation text-danger"></i>
                                    </div>
                                    <div class="dashboard-list-item-content">
                                        <h6 class="dashboard-list-item-title">{{ $compliance->operator->name }}</h6>
                                        <div class="dashboard-list-item-meta">
                                            <span class="badge badge-{{ $compliance->safety_certificate_status === 'expired' ? 'danger' : 'warning' }}">
                                                {{ $compliance->safety_certificate_status === 'expired' ? 'منتهية' : 'قريبة من الانتهاء' }}
                                            </span>
                                        </div>
                                        <small class="dashboard-list-item-time">
                                            @if($compliance->last_inspection_date)
                                                <i class="bi bi-calendar-x me-1"></i>
                                                آخر فحص: {{ $compliance->last_inspection_date->format('Y-m-d') }}
                                                @if($compliance->last_inspection_date->lt(Carbon::now()->subMonths(6)))
                                                    <span class="text-danger ms-2">(منذ {{ $compliance->last_inspection_date->diffForHumans() }})</span>
                                                @endif
                                            @else
                                                <span class="text-danger">
                                                    <i class="bi bi-exclamation-circle me-1"></i>
                                                    لم يتم تسجيل فحص
                                                </span>
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Additional Recent Items -->
    <div class="row g-3 mt-3">
        <!-- Recent Generators -->
        <div class="col-12 col-lg-6">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <div>
                        <h5 class="dashboard-card-title">
                            <i class="bi bi-lightning-charge-fill me-2 text-success"></i>
                            آخر المولدات
                        </h5>
                        <p class="dashboard-card-subtitle">آخر المولدات المضافة للنظام</p>
                    </div>
                    <a href="{{ route('admin.generators.index') }}" class="btn btn-outline-primary btn-sm">
                        عرض الكل <i class="bi bi-arrow-left ms-1"></i>
                    </a>
                </div>
                <div class="dashboard-card-body p-0">
                    <div class="dashboard-list-container">
                        @forelse($recentGenerators as $generator)
                            <div class="dashboard-list-item">
                                <div class="dashboard-list-item-icon">
                                    <i class="bi bi-lightning-charge-fill text-success"></i>
                                </div>
                                <div class="dashboard-list-item-content">
                                    <h6 class="dashboard-list-item-title">{{ $generator->name }}</h6>
                                    <div class="dashboard-list-item-meta">
                                        <span class="badge badge-{{ $generator->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ $generator->status === 'active' ? 'نشط' : 'غير نشط' }}
                                        </span>
                                        <span class="dashboard-list-item-text">
                                            <i class="bi bi-building me-1"></i>
                                            {{ $generator->operator->name }}
                                        </span>
                                    </div>
                                    <small class="dashboard-list-item-time">
                                        <i class="bi bi-clock me-1"></i>
                                        {{ $generator->created_at->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        @empty
                            <div class="dashboard-empty-state">
                                <i class="bi bi-inbox fs-1"></i>
                                <p>لا توجد مولدات</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->isSuperAdmin() && $recentOperators->count() > 0)
            <!-- Recent Operators -->
            <div class="col-12 col-lg-6">
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div>
                            <h5 class="dashboard-card-title">
                                <i class="bi bi-building me-2 text-info"></i>
                                آخر المشغلين
                            </h5>
                            <p class="dashboard-card-subtitle">آخر المشغلين المسجلين</p>
                        </div>
                        <a href="{{ route('admin.operators.index') }}" class="btn btn-outline-primary btn-sm">
                            عرض الكل <i class="bi bi-arrow-left ms-1"></i>
                        </a>
                    </div>
                    <div class="dashboard-card-body p-0">
                        <div class="dashboard-list-container">
                            @forelse($recentOperators as $operator)
                                <div class="dashboard-list-item">
                                    <div class="dashboard-list-item-icon">
                                        <i class="bi bi-building text-info"></i>
                                    </div>
                                    <div class="dashboard-list-item-content">
                                        <h6 class="dashboard-list-item-title">{{ $operator->name }}</h6>
                                        <div class="dashboard-list-item-meta">
                                            <span class="dashboard-list-item-text">
                                                <i class="bi bi-person me-1"></i>
                                                {{ $operator->owner->name }}
                                            </span>
                                        </div>
                                        <small class="dashboard-list-item-time">
                                            <i class="bi bi-clock me-1"></i>
                                            {{ $operator->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            @empty
                                <div class="dashboard-empty-state">
                                    <i class="bi bi-inbox fs-1"></i>
                                    <p>لا توجد مشغلين</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/css/dashboard.css') }}">
@endpush
