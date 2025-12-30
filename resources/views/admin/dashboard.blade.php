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

    @if(auth()->user()->isEmployee() || auth()->user()->isTechnician())
        <!-- Quick Actions - للموظف والفني -->
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
                            <a href="{{ route('admin.operation-logs.create') }}" class="dashboard-quick-action">
                                <div class="dashboard-quick-action-icon bg-warning">
                                    <i class="bi bi-journal-plus"></i>
                                </div>
                                <div class="dashboard-quick-action-text">
                                    <div class="dashboard-quick-action-title">سجل تشغيل</div>
                                    <div class="dashboard-quick-action-desc">إضافة سجل تشغيل جديد</div>
                                </div>
                            </a>
                            @if(auth()->user()->isTechnician())
                                <a href="{{ route('admin.maintenance-records.create') }}" class="dashboard-quick-action">
                                    <div class="dashboard-quick-action-icon bg-danger">
                                        <i class="bi bi-tools"></i>
                                    </div>
                                    <div class="dashboard-quick-action-text">
                                        <div class="dashboard-quick-action-title">سجل صيانة</div>
                                        <div class="dashboard-quick-action-desc">تسجيل عملية صيانة</div>
                                    </div>
                                </a>
                            @endif
                            <a href="{{ route('admin.generators.index') }}" class="dashboard-quick-action">
                                <div class="dashboard-quick-action-icon bg-success">
                                    <i class="bi bi-lightning-charge-fill"></i>
                                </div>
                                <div class="dashboard-quick-action-text">
                                    <div class="dashboard-quick-action-title">المولدات</div>
                                    <div class="dashboard-quick-action-desc">عرض المولدات المرتبطة</div>
                                </div>
                            </a>
                            <a href="{{ route('admin.operation-logs.index') }}" class="dashboard-quick-action">
                                <div class="dashboard-quick-action-icon bg-info">
                                    <i class="bi bi-journal-text"></i>
                                </div>
                                <div class="dashboard-quick-action-text">
                                    <div class="dashboard-quick-action-title">سجلات التشغيل</div>
                                    <div class="dashboard-quick-action-desc">عرض جميع السجلات</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif(auth()->user()->isCompanyOwner())
        <!-- Quick Actions - للمشغل -->
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
                            @php
                                $operator = auth()->user()->ownedOperators->first();
                            @endphp
                            @if($operator)
                                <a href="{{ route('admin.operators.employees', $operator) }}" class="dashboard-quick-action">
                                    <div class="dashboard-quick-action-icon bg-info">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div class="dashboard-quick-action-text">
                                        <div class="dashboard-quick-action-title">إدارة الموظفين</div>
                                        <div class="dashboard-quick-action-desc">عرض وإدارة الموظفين</div>
                                    </div>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif(auth()->user()->isAdmin())
        <!-- Quick Actions - للأدمن (سلطة الطاقة) -->
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
                            <a href="{{ route('admin.operators.index') }}" class="dashboard-quick-action">
                                <div class="dashboard-quick-action-icon bg-info">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div class="dashboard-quick-action-text">
                                    <div class="dashboard-quick-action-title">المشغلون</div>
                                    <div class="dashboard-quick-action-desc">عرض جميع المشغلين</div>
                                </div>
                            </a>
                            <a href="{{ route('admin.generators.index') }}" class="dashboard-quick-action">
                                <div class="dashboard-quick-action-icon bg-success">
                                    <i class="bi bi-lightning-charge-fill"></i>
                                </div>
                                <div class="dashboard-quick-action-text">
                                    <div class="dashboard-quick-action-title">المولدات</div>
                                    <div class="dashboard-quick-action-desc">عرض جميع المولدات</div>
                                </div>
                            </a>
                            <a href="{{ route('admin.operation-logs.index') }}" class="dashboard-quick-action">
                                <div class="dashboard-quick-action-icon bg-warning">
                                    <i class="bi bi-journal-text"></i>
                                </div>
                                <div class="dashboard-quick-action-text">
                                    <div class="dashboard-quick-action-title">سجلات التشغيل</div>
                                    <div class="dashboard-quick-action-desc">عرض جميع السجلات</div>
                                </div>
                            </a>
                            <a href="{{ route('admin.complaints-suggestions.index') }}" class="dashboard-quick-action">
                                <div class="dashboard-quick-action-icon bg-primary">
                                    <i class="bi bi-chat-left-text"></i>
                                </div>
                                <div class="dashboard-quick-action-text">
                                    <div class="dashboard-quick-action-title">الشكاوى والمقترحات</div>
                                    <div class="dashboard-quick-action-desc">عرض الشكاوى والمقترحات</div>
                                </div>
                            </a>
                            <a href="{{ route('admin.compliance-safeties.index') }}" class="dashboard-quick-action">
                                <div class="dashboard-quick-action-icon bg-danger">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <div class="dashboard-quick-action-text">
                                    <div class="dashboard-quick-action-title">الامتثال والسلامة</div>
                                    <div class="dashboard-quick-action-desc">عرض الشهادات والامتثال</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif(auth()->user()->isSuperAdmin())
        <!-- Quick Actions - للسوبر أدمن -->
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
    @endif

    <!-- Main Statistics Cards -->
    <div class="row g-3 mb-4">
        @if(auth()->user()->isEmployee() || auth()->user()->isTechnician())
            {{-- إحصائيات الموظف والفني --}}
            <!-- Generators -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="dashboard-stat-card dashboard-stat-success">
                    <div class="dashboard-stat-icon">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-label">المولدات</div>
                        <div class="dashboard-stat-value">{{ number_format($stats['generators']['total'] ?? 0) }}</div>
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

            <!-- Operators -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="dashboard-stat-card dashboard-stat-info">
                    <div class="dashboard-stat-icon">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-label">المشغل</div>
                        <div class="dashboard-stat-value">{{ number_format($stats['operators']['total'] ?? 0) }}</div>
                    </div>
                </div>
            </div>

            <!-- Operation Logs -->
            @if(isset($operationStats) && $operationStats['total'] > 0)
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="dashboard-stat-card dashboard-stat-warning">
                    <div class="dashboard-stat-icon">
                        <i class="bi bi-journal-text"></i>
                    </div>
                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-label">سجلات التشغيل</div>
                        <div class="dashboard-stat-value">{{ number_format($operationStats['total']) }}</div>
                        <div class="dashboard-stat-badges">
                            <span class="badge badge-info">{{ $operationStats['this_month'] }} هذا الشهر</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Maintenance Records (للفني فقط) -->
            @if(auth()->user()->isTechnician() && isset($maintenanceStats) && $maintenanceStats['total'] > 0)
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="dashboard-stat-card dashboard-stat-danger">
                    <div class="dashboard-stat-icon">
                        <i class="bi bi-tools"></i>
                    </div>
                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-label">سجلات الصيانة</div>
                        <div class="dashboard-stat-value">{{ number_format($maintenanceStats['total']) }}</div>
                        <div class="dashboard-stat-badges">
                            <span class="badge badge-warning">{{ $maintenanceStats['this_month'] }} هذا الشهر</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Complaints (إذا كانت موجودة) -->
            @if(isset($complaintsStats) && $complaintsStats['total'] > 0)
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="dashboard-stat-card dashboard-stat-primary">
                    <div class="dashboard-stat-icon">
                        <i class="bi bi-chat-left-text"></i>
                    </div>
                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-label">الشكاوى والمقترحات</div>
                        <div class="dashboard-stat-value">{{ number_format($complaintsStats['total']) }}</div>
                        @if($complaintsStats['unanswered'] > 0)
                            <div class="dashboard-stat-badges">
                                <span class="badge badge-warning">{{ $complaintsStats['unanswered'] }} غير م responded عليها</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        @elseif(auth()->user()->isCompanyOwner())
            {{-- إحصائيات المشغل - مرتبة حسب الأهمية --}}
        <!-- Generators -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="dashboard-stat-card dashboard-stat-success">
                    <div class="dashboard-stat-icon">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-label">المولدات</div>
                        <div class="dashboard-stat-value">{{ number_format($stats['generators']['total'] ?? 0) }}</div>
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

            <!-- Employees -->
            @if(isset($stats['employees']))
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

            <!-- Operators -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="dashboard-stat-card dashboard-stat-info">
                    <div class="dashboard-stat-icon">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-label">المشغل</div>
                        <div class="dashboard-stat-value">{{ number_format($stats['operators']['total'] ?? 0) }}</div>
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

            <!-- Complaints -->
            @if(isset($complaintsStats) && $complaintsStats['total'] > 0)
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="dashboard-stat-card dashboard-stat-primary">
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
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @elseif(auth()->user()->isAdmin())
            {{-- إحصائيات الأدمن (سلطة الطاقة) --}}
            <!-- Operators -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="dashboard-stat-card dashboard-stat-info">
                    <div class="dashboard-stat-icon">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-label">المشغلون</div>
                        <div class="dashboard-stat-value">{{ number_format($stats['operators']['total'] ?? 0) }}</div>
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
                        <div class="dashboard-stat-value">{{ number_format($stats['generators']['total'] ?? 0) }}</div>
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

            <!-- Company Owners -->
            @if(isset($stats['company_owners']))
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="dashboard-stat-card dashboard-stat-primary">
                    <div class="dashboard-stat-icon">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-label">أصحاب المشغلين</div>
                        <div class="dashboard-stat-value">{{ number_format($stats['company_owners']['total'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Complaints -->
            @if(isset($complaintsStats) && $complaintsStats['total'] > 0)
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="dashboard-stat-card dashboard-stat-warning">
                    <div class="dashboard-stat-icon">
                        <i class="bi bi-chat-left-text"></i>
                    </div>
                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-label">الشكاوى والمقترحات</div>
                        <div class="dashboard-stat-value">{{ number_format($complaintsStats['total']) }}</div>
                        <div class="dashboard-stat-badges">
                            @if($complaintsStats['unanswered'] > 0)
                                <span class="badge badge-danger">{{ $complaintsStats['unanswered'] }} غير م responded عليها</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @elseif(auth()->user()->isSuperAdmin())
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
        @endif
    </div>

    @if(auth()->user()->isEmployee() || auth()->user()->isTechnician())
        <!-- Operations Statistics - للموظف والفني -->
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

        @if(isset($operationStats) && $operationStats['total'] > 0 && isset($chartData))
        <!-- Charts Section -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div>
                            <h5 class="dashboard-card-title">
                                <i class="bi bi-bar-chart-line me-2"></i>
                                تحليل الأداء
                            </h5>
                            <p class="dashboard-card-subtitle">مخططات تفصيلية لأداء المولدات (آخر 30 يوم)</p>
                        </div>
                    </div>
                    <div class="dashboard-card-body">
                        <!-- Tabs Navigation -->
                        <ul class="nav nav-tabs nav-tabs-custom mb-3" id="operationChartsTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="energy-tab" data-bs-toggle="tab" data-bs-target="#energy-chart" type="button" role="tab">
                                    <i class="bi bi-lightning-charge me-1"></i>
                                    الطاقة المنتجة
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="fuel-tab" data-bs-toggle="tab" data-bs-target="#fuel-chart" type="button" role="tab">
                                    <i class="bi bi-fuel-pump me-1"></i>
                                    الوقود المستهلك
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="records-tab" data-bs-toggle="tab" data-bs-target="#records-chart" type="button" role="tab">
                                    <i class="bi bi-journal-text me-1"></i>
                                    سجلات التشغيل
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="load-tab" data-bs-toggle="tab" data-bs-target="#load-chart" type="button" role="tab">
                                    <i class="bi bi-speedometer2 me-1"></i>
                                    نسبة التحميل
                                </button>
                            </li>
                        </ul>

                        <!-- Tabs Content -->
                        <div class="tab-content" id="operationChartsTabContent">
                            <!-- Energy Chart -->
                            <div class="tab-pane fade show active" id="energy-chart" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="energyChart"></canvas>
                                </div>
                            </div>

                            <!-- Fuel Chart -->
                            <div class="tab-pane fade" id="fuel-chart" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="fuelChart"></canvas>
                                </div>
                            </div>

                            <!-- Records Chart -->
                            <div class="tab-pane fade" id="records-chart" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="recordsChart"></canvas>
                                </div>
                            </div>

                            <!-- Load Chart -->
                            <div class="tab-pane fade" id="load-chart" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="loadChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(isset($pieChartData) && (count($pieChartData['generators']['data']) > 0 || count($pieChartData['operators']['data']) > 0 || count($pieChartData['governorates']['data']) > 0))
        <!-- Fuel Surplus/Deficit Charts Section -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div>
                            <h5 class="dashboard-card-title">
                                <i class="bi bi-bar-chart me-2"></i>
                                الفائض/الناقص من الوقود
                            </h5>
                            <p class="dashboard-card-subtitle">مقارنة الفائض والناقص من الوقود مقارنة بالطاقة المنتجة</p>
                        </div>
                    </div>
                    <div class="dashboard-card-body">
                        <!-- Tabs Navigation -->
                        <ul class="nav nav-tabs nav-tabs-custom mb-3" id="fuelSurplusTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="generators-surplus-tab" data-bs-toggle="tab" data-bs-target="#generators-surplus-chart" type="button" role="tab">
                                    <i class="bi bi-lightning-charge me-1"></i>
                                    حسب المولدات
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="operators-surplus-tab" data-bs-toggle="tab" data-bs-target="#operators-surplus-chart" type="button" role="tab">
                                    <i class="bi bi-building me-1"></i>
                                    حسب المشغلين
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="governorates-surplus-tab" data-bs-toggle="tab" data-bs-target="#governorates-surplus-chart" type="button" role="tab">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    حسب المحافظات
                                </button>
                            </li>
                        </ul>

                        <!-- Tabs Content -->
                        <div class="tab-content" id="fuelSurplusTabContent">
                            <!-- Generators Surplus Chart -->
                            <div class="tab-pane fade show active" id="generators-surplus-chart" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 450px;">
                                    <canvas id="generatorsSurplusChart"></canvas>
                                </div>
                            </div>

                            <!-- Operators Surplus Chart -->
                            <div class="tab-pane fade" id="operators-surplus-chart" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 450px;">
                                    <canvas id="operatorsSurplusChart"></canvas>
                                </div>
                            </div>

                            <!-- Governorates Surplus Chart -->
                            <div class="tab-pane fade" id="governorates-surplus-chart" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 450px;">
                                    <canvas id="governoratesSurplusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pie Charts Section -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div>
                            <h5 class="dashboard-card-title">
                                <i class="bi bi-pie-chart me-2"></i>
                                توزيع الطاقة المنتجة
                            </h5>
                            <p class="dashboard-card-subtitle">توزيع الطاقة المنتجة حسب المولدات والمشغلين والمحافظات</p>
                        </div>
                    </div>
                    <div class="dashboard-card-body">
                        <!-- Tabs Navigation -->
                        <ul class="nav nav-tabs nav-tabs-custom mb-3" id="pieChartsTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="generators-pie-tab" data-bs-toggle="tab" data-bs-target="#generators-pie-chart" type="button" role="tab">
                                    <i class="bi bi-lightning-charge me-1"></i>
                                    حسب المولدات
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="operators-pie-tab" data-bs-toggle="tab" data-bs-target="#operators-pie-chart" type="button" role="tab">
                                    <i class="bi bi-building me-1"></i>
                                    حسب المشغلين
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="governorates-pie-tab" data-bs-toggle="tab" data-bs-target="#governorates-pie-chart" type="button" role="tab">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    حسب المحافظات
                                </button>
                            </li>
                        </ul>

                        <!-- Tabs Content -->
                        <div class="tab-content" id="pieChartsTabContent">
                            <!-- Generators Pie Chart -->
                            <div class="tab-pane fade show active" id="generators-pie-chart" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="generatorsPieChart"></canvas>
                                </div>
                            </div>

                            <!-- Operators Pie Chart -->
                            <div class="tab-pane fade" id="operators-pie-chart" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="operatorsPieChart"></canvas>
                                </div>
                            </div>

                            <!-- Governorates Pie Chart -->
                            <div class="tab-pane fade" id="governorates-pie-chart" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="governoratesPieChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->isTechnician() && isset($maintenanceStats) && $maintenanceStats['total'] > 0)
        <!-- Maintenance Statistics - للفني -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div>
                            <h5 class="dashboard-card-title">
                                <i class="bi bi-tools me-2"></i>
                                إحصائيات الصيانة
                            </h5>
                            <p class="dashboard-card-subtitle">نظرة شاملة على سجلات الصيانة</p>
                        </div>
                        <a href="{{ route('admin.maintenance-records.index') }}" class="btn btn-outline-danger btn-sm">
                            عرض التفاصيل <i class="bi bi-arrow-left ms-1"></i>
                        </a>
                    </div>
                    <div class="dashboard-card-body">
                        <div class="row g-3">
                            <div class="col-6 col-md-4">
                                <div class="dashboard-stat-mini">
                                    <div class="dashboard-stat-mini-label">إجمالي السجلات</div>
                                    <div class="dashboard-stat-mini-value">{{ number_format($maintenanceStats['total']) }}</div>
                                    <div class="dashboard-stat-mini-badges">
                                        <span class="badge badge-warning">{{ $maintenanceStats['this_month'] }} هذا الشهر</span>
                                    </div>
                                </div>
                            </div>
                            @if($maintenanceStats['total_cost'] > 0)
                            <div class="col-6 col-md-4">
                                <div class="dashboard-stat-mini">
                                    <div class="dashboard-stat-mini-label">التكلفة الإجمالية</div>
                                    <div class="dashboard-stat-mini-value">{{ number_format($maintenanceStats['total_cost'], 0) }}</div>
                                    <div class="dashboard-stat-mini-unit">₪</div>
                                </div>
                            </div>
                            @endif
                            @if($maintenanceStats['total_downtime'] > 0)
                            <div class="col-6 col-md-4">
                                <div class="dashboard-stat-mini">
                                    <div class="dashboard-stat-mini-label">وقت التوقف</div>
                                    <div class="dashboard-stat-mini-value">{{ number_format($maintenanceStats['total_downtime'], 1) }}</div>
                                    <div class="dashboard-stat-mini-unit">ساعة</div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @elseif(auth()->user()->isAdmin())
        <!-- Operations Statistics - للأدمن -->
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
    @elseif(auth()->user()->isCompanyOwner())
        <!-- Operations Statistics - للمشغل -->
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

        @if(isset($operationStats) && $operationStats['total'] > 0 && isset($chartData))
        <!-- Charts Section -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div>
                            <h5 class="dashboard-card-title">
                                <i class="bi bi-bar-chart-line me-2"></i>
                                تحليل الأداء
                            </h5>
                            <p class="dashboard-card-subtitle">مخططات تفصيلية لأداء المولدات (آخر 30 يوم)</p>
                        </div>
                    </div>
                    <div class="dashboard-card-body">
                        <!-- Tabs Navigation -->
                        <ul class="nav nav-tabs nav-tabs-custom mb-3" id="operationChartsTabOwner" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="energy-tab-owner" data-bs-toggle="tab" data-bs-target="#energy-chart-owner" type="button" role="tab">
                                    <i class="bi bi-lightning-charge me-1"></i>
                                    الطاقة المنتجة
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="fuel-tab-owner" data-bs-toggle="tab" data-bs-target="#fuel-chart-owner" type="button" role="tab">
                                    <i class="bi bi-fuel-pump me-1"></i>
                                    الوقود المستهلك
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="records-tab-owner" data-bs-toggle="tab" data-bs-target="#records-chart-owner" type="button" role="tab">
                                    <i class="bi bi-journal-text me-1"></i>
                                    سجلات التشغيل
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="load-tab-owner" data-bs-toggle="tab" data-bs-target="#load-chart-owner" type="button" role="tab">
                                    <i class="bi bi-speedometer2 me-1"></i>
                                    نسبة التحميل
                                </button>
                            </li>
                        </ul>

                        <!-- Tabs Content -->
                        <div class="tab-content" id="operationChartsTabContentOwner">
                            <!-- Energy Chart -->
                            <div class="tab-pane fade show active" id="energy-chart-owner" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="energyChartOwner"></canvas>
                                </div>
                            </div>

                            <!-- Fuel Chart -->
                            <div class="tab-pane fade" id="fuel-chart-owner" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="fuelChartOwner"></canvas>
                                </div>
                            </div>

                            <!-- Records Chart -->
                            <div class="tab-pane fade" id="records-chart-owner" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="recordsChartOwner"></canvas>
                                </div>
                            </div>

                            <!-- Load Chart -->
                            <div class="tab-pane fade" id="load-chart-owner" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="loadChartOwner"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(isset($pieChartData) && (count($pieChartData['generators']['data']) > 0 || count($pieChartData['operators']['data']) > 0 || count($pieChartData['governorates']['data']) > 0))
        <!-- Fuel Surplus/Deficit Charts Section -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div>
                            <h5 class="dashboard-card-title">
                                <i class="bi bi-bar-chart me-2"></i>
                                الفائض/الناقص من الوقود
                            </h5>
                            <p class="dashboard-card-subtitle">مقارنة الفائض والناقص من الوقود مقارنة بالطاقة المنتجة</p>
                        </div>
                    </div>
                    <div class="dashboard-card-body">
                        <!-- Tabs Navigation -->
                        <ul class="nav nav-tabs nav-tabs-custom mb-3" id="fuelSurplusTabOwner" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="generators-surplus-tab-owner" data-bs-toggle="tab" data-bs-target="#generators-surplus-chart-owner" type="button" role="tab">
                                    <i class="bi bi-lightning-charge me-1"></i>
                                    حسب المولدات
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="operators-surplus-tab-owner" data-bs-toggle="tab" data-bs-target="#operators-surplus-chart-owner" type="button" role="tab">
                                    <i class="bi bi-building me-1"></i>
                                    حسب المشغلين
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="governorates-surplus-tab-owner" data-bs-toggle="tab" data-bs-target="#governorates-surplus-chart-owner" type="button" role="tab">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    حسب المحافظات
                                </button>
                            </li>
                        </ul>

                        <!-- Tabs Content -->
                        <div class="tab-content" id="fuelSurplusTabContentOwner">
                            <!-- Generators Surplus Chart -->
                            <div class="tab-pane fade show active" id="generators-surplus-chart-owner" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 450px;">
                                    <canvas id="generatorsSurplusChartOwner"></canvas>
                                </div>
                            </div>

                            <!-- Operators Surplus Chart -->
                            <div class="tab-pane fade" id="operators-surplus-chart-owner" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 450px;">
                                    <canvas id="operatorsSurplusChartOwner"></canvas>
                                </div>
                            </div>

                            <!-- Governorates Surplus Chart -->
                            <div class="tab-pane fade" id="governorates-surplus-chart-owner" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 450px;">
                                    <canvas id="governoratesSurplusChartOwner"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pie Charts Section -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div>
                            <h5 class="dashboard-card-title">
                                <i class="bi bi-pie-chart me-2"></i>
                                توزيع الطاقة المنتجة
                            </h5>
                            <p class="dashboard-card-subtitle">توزيع الطاقة المنتجة حسب المولدات والمشغلين والمحافظات</p>
                        </div>
                    </div>
                    <div class="dashboard-card-body">
                        <!-- Tabs Navigation -->
                        <ul class="nav nav-tabs nav-tabs-custom mb-3" id="pieChartsTabOwner" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="generators-pie-tab-owner" data-bs-toggle="tab" data-bs-target="#generators-pie-chart-owner" type="button" role="tab">
                                    <i class="bi bi-lightning-charge me-1"></i>
                                    حسب المولدات
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="operators-pie-tab-owner" data-bs-toggle="tab" data-bs-target="#operators-pie-chart-owner" type="button" role="tab">
                                    <i class="bi bi-building me-1"></i>
                                    حسب المشغلين
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="governorates-pie-tab-owner" data-bs-toggle="tab" data-bs-target="#governorates-pie-chart-owner" type="button" role="tab">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    حسب المحافظات
                                </button>
                            </li>
                        </ul>

                        <!-- Tabs Content -->
                        <div class="tab-content" id="pieChartsTabContentOwner">
                            <!-- Generators Pie Chart -->
                            <div class="tab-pane fade show active" id="generators-pie-chart-owner" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="generatorsPieChartOwner"></canvas>
                                </div>
                            </div>

                            <!-- Operators Pie Chart -->
                            <div class="tab-pane fade" id="operators-pie-chart-owner" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="operatorsPieChartOwner"></canvas>
                                </div>
                            </div>

                            <!-- Governorates Pie Chart -->
                            <div class="tab-pane fade" id="governorates-pie-chart-owner" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="governoratesPieChartOwner"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @elseif(auth()->user()->isSuperAdmin())
        <!-- Operations Statistics - للسوبر أدمن -->
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

        @if(isset($operationStats) && $operationStats['total'] > 0 && isset($chartData))
        <!-- Charts Section -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div>
                            <h5 class="dashboard-card-title">
                                <i class="bi bi-bar-chart-line me-2"></i>
                                تحليل الأداء
                            </h5>
                            <p class="dashboard-card-subtitle">مخططات تفصيلية لأداء المولدات (آخر 30 يوم)</p>
                        </div>
                    </div>
                    <div class="dashboard-card-body">
                        <!-- Tabs Navigation -->
                        <ul class="nav nav-tabs nav-tabs-custom mb-3" id="operationChartsTabAdmin" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="energy-tab-admin" data-bs-toggle="tab" data-bs-target="#energy-chart-admin" type="button" role="tab">
                                    <i class="bi bi-lightning-charge me-1"></i>
                                    الطاقة المنتجة
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="fuel-tab-admin" data-bs-toggle="tab" data-bs-target="#fuel-chart-admin" type="button" role="tab">
                                    <i class="bi bi-fuel-pump me-1"></i>
                                    الوقود المستهلك
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="records-tab-admin" data-bs-toggle="tab" data-bs-target="#records-chart-admin" type="button" role="tab">
                                    <i class="bi bi-journal-text me-1"></i>
                                    سجلات التشغيل
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="load-tab-admin" data-bs-toggle="tab" data-bs-target="#load-chart-admin" type="button" role="tab">
                                    <i class="bi bi-speedometer2 me-1"></i>
                                    نسبة التحميل
                                </button>
                            </li>
                        </ul>

                        <!-- Tabs Content -->
                        <div class="tab-content" id="operationChartsTabContentAdmin">
                            <!-- Energy Chart -->
                            <div class="tab-pane fade show active" id="energy-chart-admin" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="energyChartAdmin"></canvas>
                                </div>
                            </div>

                            <!-- Fuel Chart -->
                            <div class="tab-pane fade" id="fuel-chart-admin" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="fuelChartAdmin"></canvas>
                                </div>
                            </div>

                            <!-- Records Chart -->
                            <div class="tab-pane fade" id="records-chart-admin" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="recordsChartAdmin"></canvas>
                                </div>
                            </div>

                            <!-- Load Chart -->
                            <div class="tab-pane fade" id="load-chart-admin" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="loadChartAdmin"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(isset($pieChartData) && (count($pieChartData['generators']['data']) > 0 || count($pieChartData['operators']['data']) > 0 || count($pieChartData['governorates']['data']) > 0))
        <!-- Fuel Surplus/Deficit Charts Section -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div>
                            <h5 class="dashboard-card-title">
                                <i class="bi bi-bar-chart me-2"></i>
                                الفائض/الناقص من الوقود
                            </h5>
                            <p class="dashboard-card-subtitle">مقارنة الفائض والناقص من الوقود مقارنة بالطاقة المنتجة</p>
                        </div>
                    </div>
                    <div class="dashboard-card-body">
                        <!-- Tabs Navigation -->
                        <ul class="nav nav-tabs nav-tabs-custom mb-3" id="fuelSurplusTabAdmin" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="generators-surplus-tab-admin" data-bs-toggle="tab" data-bs-target="#generators-surplus-chart-admin" type="button" role="tab">
                                    <i class="bi bi-lightning-charge me-1"></i>
                                    حسب المولدات
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="operators-surplus-tab-admin" data-bs-toggle="tab" data-bs-target="#operators-surplus-chart-admin" type="button" role="tab">
                                    <i class="bi bi-building me-1"></i>
                                    حسب المشغلين
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="governorates-surplus-tab-admin" data-bs-toggle="tab" data-bs-target="#governorates-surplus-chart-admin" type="button" role="tab">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    حسب المحافظات
                                </button>
                            </li>
                        </ul>

                        <!-- Tabs Content -->
                        <div class="tab-content" id="fuelSurplusTabContentAdmin">
                            <!-- Generators Surplus Chart -->
                            <div class="tab-pane fade show active" id="generators-surplus-chart-admin" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 450px;">
                                    <canvas id="generatorsSurplusChartAdmin"></canvas>
                                </div>
                            </div>

                            <!-- Operators Surplus Chart -->
                            <div class="tab-pane fade" id="operators-surplus-chart-admin" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 450px;">
                                    <canvas id="operatorsSurplusChartAdmin"></canvas>
                                </div>
                            </div>

                            <!-- Governorates Surplus Chart -->
                            <div class="tab-pane fade" id="governorates-surplus-chart-admin" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 450px;">
                                    <canvas id="governoratesSurplusChartAdmin"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pie Charts Section -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div>
                            <h5 class="dashboard-card-title">
                                <i class="bi bi-pie-chart me-2"></i>
                                توزيع الطاقة المنتجة
                            </h5>
                            <p class="dashboard-card-subtitle">توزيع الطاقة المنتجة حسب المولدات والمشغلين والمحافظات</p>
                        </div>
                    </div>
                    <div class="dashboard-card-body">
                        <!-- Tabs Navigation -->
                        <ul class="nav nav-tabs nav-tabs-custom mb-3" id="pieChartsTabAdmin" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="generators-pie-tab-admin" data-bs-toggle="tab" data-bs-target="#generators-pie-chart-admin" type="button" role="tab">
                                    <i class="bi bi-lightning-charge me-1"></i>
                                    حسب المولدات
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="operators-pie-tab-admin" data-bs-toggle="tab" data-bs-target="#operators-pie-chart-admin" type="button" role="tab">
                                    <i class="bi bi-building me-1"></i>
                                    حسب المشغلين
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="governorates-pie-tab-admin" data-bs-toggle="tab" data-bs-target="#governorates-pie-chart-admin" type="button" role="tab">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    حسب المحافظات
                                </button>
                            </li>
                        </ul>

                        <!-- Tabs Content -->
                        <div class="tab-content" id="pieChartsTabContentAdmin">
                            <!-- Generators Pie Chart -->
                            <div class="tab-pane fade show active" id="generators-pie-chart-admin" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="generatorsPieChartAdmin"></canvas>
                                </div>
                            </div>

                            <!-- Operators Pie Chart -->
                            <div class="tab-pane fade" id="operators-pie-chart-admin" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="operatorsPieChartAdmin"></canvas>
                                </div>
                            </div>

                            <!-- Governorates Pie Chart -->
                            <div class="tab-pane fade" id="governorates-pie-chart-admin" role="tabpanel">
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="governoratesPieChartAdmin"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endif

    @if(auth()->user()->isEmployee() || auth()->user()->isTechnician())
        <!-- Additional Statistics Row - للموظف والفني -->
        <div class="row g-3 mb-4">
            @if(isset($fuelStats) && $fuelStats['total'] > 0)
                <div class="col-12 col-sm-6 col-lg-4">
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
                <div class="col-12 col-sm-6 col-lg-4">
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
        </div>
    @elseif(auth()->user()->isAdmin())
        <!-- Additional Statistics Row - للأدمن -->
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
        </div>
    @elseif(auth()->user()->isCompanyOwner())
        <!-- Additional Statistics Row - للمشغل -->
        <div class="row g-3 mb-4">
            @if(isset($maintenanceStats) && $maintenanceStats['total'] > 0)
                <div class="col-12 col-sm-6 col-lg-4">
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
                <div class="col-12 col-sm-6 col-lg-4">
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
                <div class="col-12 col-sm-6 col-lg-4">
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
        </div>
    @elseif(auth()->user()->isSuperAdmin())
        <!-- Additional Statistics Row - للسوبر أدمن -->
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
    @endif

    <!-- Recent Items & Alerts -->
    <div class="row g-3">
        @if(auth()->user()->isEmployee() || auth()->user()->isTechnician())
            <!-- Generators Needing Maintenance - للموظف والفني -->
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
                                                @if($generator->operator)
                                                    <span class="dashboard-list-item-text">
                                                        <i class="bi bi-building me-1"></i>
                                                        {{ $generator->operator->name }}
                                                    </span>
                                                @endif
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

            <!-- Recent Operation Logs - للموظف والفني -->
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
                                                @if($log->energy_produced)
                                                    <span class="badge badge-success">{{ number_format($log->energy_produced, 2) }} kWh</span>
                                                @endif
                                                @if($log->operator)
                                                    <span class="dashboard-list-item-text">
                                                        <i class="bi bi-building me-1"></i>
                                                        {{ $log->operator->name }}
                                                    </span>
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
        @elseif(auth()->user()->isCompanyOwner())
            <!-- Generators Needing Maintenance - للمشغل -->
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
                                                @if($generator->operator)
                                                    <span class="dashboard-list-item-text">
                                                        <i class="bi bi-building me-1"></i>
                                                        {{ $generator->operator->name }}
                                                    </span>
                                                @endif
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
        @elseif(auth()->user()->isAdmin())
            <!-- Generators Needing Maintenance - للأدمن -->
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

            <!-- Recent Operation Logs - للأدمن -->
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
        @elseif(auth()->user()->isSuperAdmin())
            <!-- Generators Needing Maintenance - للسوبر أدمن -->
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
        @endif

        <!-- Recent Operation Logs -->
        @if(isset($recentOperationLogs) && $recentOperationLogs->count() > 0)
            @if(auth()->user()->isCompanyOwner())
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
            @elseif(auth()->user()->isSuperAdmin())
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

        @if((auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) && isset($recentOperators) && $recentOperators->count() > 0)
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

@push('scripts')
@if(isset($chartData))
<script>
    // Pass chart data to JavaScript
    window.dashboardChartData = @json($chartData);
</script>
<!-- Chart.js Local -->
<script src="{{ asset('assets/admin/libs/chart.js/chart.umd.min.js') }}"></script>
<script>
(function() {
    function initCharts() {
        // Check if Chart.js is loaded
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded. File path: {{ asset("assets/admin/libs/chart.js/chart.umd.min.js") }}');
            return;
        }
        
        // Check if chart data is available
        if (typeof window.dashboardChartData === 'undefined') {
            console.error('Chart data is not available');
            return;
        }
        
        const chartData = window.dashboardChartData;
    
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: true, position: 'top', rtl: true },
            tooltip: {
                rtl: true,
                titleFont: { family: 'Cairo, Arial, sans-serif' },
                bodyFont: { family: 'Cairo, Arial, sans-serif' }
            }
        },
        scales: {
            x: { ticks: { font: { family: 'Cairo, Arial, sans-serif' } } },
            y: { ticks: { font: { family: 'Cairo, Arial, sans-serif' } } }
        }
    };

    function createChart(canvasId, type, label, data, borderColor, backgroundColor, isBar = false) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;
        new Chart(ctx, {
            type: type,
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: label,
                    data: data,
                    borderColor: borderColor,
                    backgroundColor: backgroundColor,
                    ...(isBar ? { borderWidth: 1 } : { tension: 0.4, fill: true })
                }]
            },
            options: chartOptions
        });
    }

    // Energy Charts
    createChart('energyChart', 'line', 'الطاقة المنتجة (kWh)', chartData.energy, 'rgb(75, 192, 192)', 'rgba(75, 192, 192, 0.1)');
    createChart('energyChartOwner', 'line', 'الطاقة المنتجة (kWh)', chartData.energy, 'rgb(75, 192, 192)', 'rgba(75, 192, 192, 0.1)');
    createChart('energyChartAdmin', 'line', 'الطاقة المنتجة (kWh)', chartData.energy, 'rgb(75, 192, 192)', 'rgba(75, 192, 192, 0.1)');
    
    // Fuel Charts
    createChart('fuelChart', 'line', 'الوقود المستهلك (لتر)', chartData.fuel, 'rgb(255, 99, 132)', 'rgba(255, 99, 132, 0.1)');
    createChart('fuelChartOwner', 'line', 'الوقود المستهلك (لتر)', chartData.fuel, 'rgb(255, 99, 132)', 'rgba(255, 99, 132, 0.1)');
    createChart('fuelChartAdmin', 'line', 'الوقود المستهلك (لتر)', chartData.fuel, 'rgb(255, 99, 132)', 'rgba(255, 99, 132, 0.1)');
    
    // Records Charts
    createChart('recordsChart', 'bar', 'عدد السجلات', chartData.records, 'rgb(54, 162, 235)', 'rgba(54, 162, 235, 0.6)', true);
    createChart('recordsChartOwner', 'bar', 'عدد السجلات', chartData.records, 'rgb(54, 162, 235)', 'rgba(54, 162, 235, 0.6)', true);
    createChart('recordsChartAdmin', 'bar', 'عدد السجلات', chartData.records, 'rgb(54, 162, 235)', 'rgba(54, 162, 235, 0.6)', true);
    
    // Load Charts
    createChart('loadChart', 'line', 'نسبة التحميل (%)', chartData.load, 'rgb(255, 206, 86)', 'rgba(255, 206, 86, 0.1)');
    createChart('loadChartOwner', 'line', 'نسبة التحميل (%)', chartData.load, 'rgb(255, 206, 86)', 'rgba(255, 206, 86, 0.1)');
        createChart('loadChartAdmin', 'line', 'نسبة التحميل (%)', chartData.load, 'rgb(255, 206, 86)', 'rgba(255, 206, 86, 0.1)');
    }

    // Wait for both DOM and Chart.js to be ready
    function waitForChart() {
        if (typeof Chart !== 'undefined') {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initCharts);
            } else {
                initCharts();
            }
        } else {
            // Chart.js not loaded yet, wait a bit and try again
            setTimeout(waitForChart, 100);
        }
    }

    // Start waiting for Chart.js
    waitForChart();
})();
</script>
@endif

@if(isset($pieChartData))
<script>
(function() {
    function initPieCharts() {
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded for pie charts');
            return;
        }

        if (typeof window.dashboardChartData === 'undefined' || typeof window.pieChartData === 'undefined') {
            console.error('Pie chart data is not available');
            return;
        }

        const pieData = window.pieChartData;

        // Generate colors for pie charts
        function generateColors(count) {
            const colors = [
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)',
                'rgba(199, 199, 199, 0.8)',
                'rgba(83, 102, 255, 0.8)',
                'rgba(255, 99, 255, 0.8)',
                'rgba(99, 255, 132, 0.8)',
            ];
            return colors.slice(0, count);
        }

        const pieChartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'right',
                    rtl: true,
                    labels: {
                        font: {
                            family: 'Cairo, Arial, sans-serif',
                            size: 12
                        },
                        padding: 20,
                        usePointStyle: true,
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map((label, i) => {
                                    const value = data.datasets[0].data[i];
                                    const detail = data.details && data.details[i] ? data.details[i] : null;
                                    return {
                                        text: label + ' (' + value.toLocaleString('ar') + ' kWh)',
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        strokeStyle: data.datasets[0].borderColor[i],
                                        lineWidth: data.datasets[0].borderWidth,
                                        hidden: false,
                                        index: i,
                                        datasetIndex: 0
                                    };
                                });
                            }
                            return [];
                        }
                    }
                },
                tooltip: {
                    rtl: true,
                    titleFont: {
                        family: 'Cairo, Arial, sans-serif',
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        family: 'Cairo, Arial, sans-serif',
                        size: 12
                    },
                    padding: 12,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(255, 255, 255, 0.2)',
                    borderWidth: 1,
                    callbacks: {
                        title: function(context) {
                            return context[0].label || '';
                        },
                        label: function(context) {
                            const detail = context.chart.data.details && context.chart.data.details[context.dataIndex] 
                                ? context.chart.data.details[context.dataIndex] 
                                : null;
                            
                            let lines = [];
                            lines.push('الطاقة المنتجة: ' + context.parsed.toLocaleString('ar') + ' kWh');
                            
                            if (detail) {
                                if (detail.fuel_consumed > 0) {
                                    lines.push('الوقود المستهلك: ' + detail.fuel_consumed.toLocaleString('ar') + ' لتر');
                                }
                                if (detail.fuel_capacity > 0) {
                                    lines.push('سعة الخزانات: ' + detail.fuel_capacity.toLocaleString('ar') + ' لتر');
                                }
                                if (detail.fuel_surplus > 0) {
                                    lines.push('الفائض من الوقود: ' + detail.fuel_surplus.toLocaleString('ar') + ' لتر');
                                } else if (detail.fuel_capacity > 0) {
                                    lines.push('الفائض من الوقود: 0 لتر');
                                }
                            }
                            
                            return lines;
                        }
                    }
                }
            }
        };

        // Create Horizontal Bar Chart for Energy Distribution (Better than Pie Chart)
        function createEnergyDistributionChart(canvasId, labels, data, details, title) {
            const ctx = document.getElementById(canvasId);
            if (!ctx || labels.length === 0 || data.length === 0) return;

            const colors = generateColors(labels.length);
            
            // Calculate percentages for display
            const total = data.reduce((sum, val) => sum + val, 0);
            const percentages = data.map(val => total > 0 ? ((val / total) * 100).toFixed(1) : 0);
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels.map((label, i) => {
                        const value = data[i];
                        const percent = percentages[i];
                        return `${label} (${value.toLocaleString('ar')} kWh - ${percent}%)`;
                    }),
                    datasets: [{
                        label: title,
                        data: data,
                        backgroundColor: colors,
                        borderColor: colors.map(c => c.replace('0.8', '1')),
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false,
                    }],
                    details: details || []
                },
                options: {
                    indexAxis: 'y', // Horizontal bar chart
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            left: 10,
                            right: 10,
                            top: 10,
                            bottom: 10
                        }
                    },
                    plugins: {
                        legend: {
                            display: false // Hide legend as labels contain the info
                        },
                        tooltip: {
                            rtl: true,
                            titleFont: {
                                family: 'Cairo, Arial, sans-serif',
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                family: 'Cairo, Arial, sans-serif',
                                size: 12
                            },
                            padding: 12,
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.2)',
                            borderWidth: 1,
                            callbacks: {
                                title: function(context) {
                                    const index = context[0].dataIndex;
                                    return labels[index] || '';
                                },
                                label: function(context) {
                                    const index = context.dataIndex;
                                    const detail = context.chart.data.details && context.chart.data.details[index] 
                                        ? context.chart.data.details[index] 
                                        : null;
                                    
                                    let lines = [];
                                    const value = context.parsed.x;
                                    const percent = percentages[index];
                                    lines.push(`الطاقة المنتجة: ${value.toLocaleString('ar')} kWh (${percent}%)`);
                                    
                                    if (detail) {
                                        if (detail.fuel_consumed > 0) {
                                            lines.push(`الوقود المستهلك: ${detail.fuel_consumed.toLocaleString('ar')} لتر`);
                                        }
                                        if (detail.fuel_capacity > 0) {
                                            lines.push(`سعة الخزانات: ${detail.fuel_capacity.toLocaleString('ar')} لتر`);
                                        }
                                        if (detail.fuel_surplus > 0) {
                                            lines.push(`الفائض من الوقود: ${detail.fuel_surplus.toLocaleString('ar')} لتر`);
                                        } else if (detail.fuel_capacity > 0) {
                                            lines.push(`الفائض من الوقود: 0 لتر`);
                                        }
                                    }
                                    
                                    return lines;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                font: {
                                    family: 'Cairo, Arial, sans-serif',
                                    size: 11
                                },
                                callback: function(value) {
                                    return value.toLocaleString('ar') + ' kWh';
                                }
                            },
                            title: {
                                display: true,
                                text: 'الطاقة المنتجة (kWh)',
                                font: {
                                    family: 'Cairo, Arial, sans-serif',
                                    size: 13,
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        y: {
                            ticks: {
                                font: {
                                    family: 'Cairo, Arial, sans-serif',
                                    size: 11
                                }
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        }

        // Generators Energy Distribution Charts (Horizontal Bar)
        if (pieData.generators && pieData.generators.labels.length > 0) {
            const genDetails = pieData.generators.details || [];
            createEnergyDistributionChart('generatorsPieChart', pieData.generators.labels, pieData.generators.data, genDetails, 'الطاقة المنتجة (kWh)');
            createEnergyDistributionChart('generatorsPieChartOwner', pieData.generators.labels, pieData.generators.data, genDetails, 'الطاقة المنتجة (kWh)');
            createEnergyDistributionChart('generatorsPieChartAdmin', pieData.generators.labels, pieData.generators.data, genDetails, 'الطاقة المنتجة (kWh)');
        }

        // Operators Energy Distribution Charts (Horizontal Bar)
        if (pieData.operators && pieData.operators.labels.length > 0) {
            const opDetails = pieData.operators.details || [];
            createEnergyDistributionChart('operatorsPieChart', pieData.operators.labels, pieData.operators.data, opDetails, 'الطاقة المنتجة (kWh)');
            createEnergyDistributionChart('operatorsPieChartOwner', pieData.operators.labels, pieData.operators.data, opDetails, 'الطاقة المنتجة (kWh)');
            createEnergyDistributionChart('operatorsPieChartAdmin', pieData.operators.labels, pieData.operators.data, opDetails, 'الطاقة المنتجة (kWh)');
        }

        // Governorates Energy Distribution Charts (Horizontal Bar)
        if (pieData.governorates && pieData.governorates.labels.length > 0) {
            const govDetails = pieData.governorates.details || [];
            createEnergyDistributionChart('governoratesPieChart', pieData.governorates.labels, pieData.governorates.data, govDetails, 'الطاقة المنتجة (kWh)');
            createEnergyDistributionChart('governoratesPieChartOwner', pieData.governorates.labels, pieData.governorates.data, govDetails, 'الطاقة المنتجة (kWh)');
            createEnergyDistributionChart('governoratesPieChartAdmin', pieData.governorates.labels, pieData.governorates.data, govDetails, 'الطاقة المنتجة (kWh)');
        }

        // Create Surplus/Deficit Combo Charts (Bar + Line)
        function createSurplusChart(canvasId, labels, details, title) {
            const ctx = document.getElementById(canvasId);
            if (!ctx || !labels.length || !details.length) return;

            const energyData = details.map(d => d.energy || 0);
            const fuelConsumedData = details.map(d => d.fuel_consumed || 0);
            const fuelCapacityData = details.map(d => d.fuel_capacity || 0);
            const fuelSurplusData = details.map(d => {
                const surplus = d.fuel_surplus || 0;
                return surplus > 0 ? surplus : 0;
            });
            const fuelDeficitData = details.map(d => {
                const capacity = d.fuel_capacity || 0;
                const consumed = d.fuel_consumed || 0;
                // الناقص = المستهلك - السعة (إذا كان المستهلك أكبر من السعة)
                return consumed > capacity ? consumed - capacity : 0;
            });

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'الطاقة المنتجة (kWh)',
                            data: energyData,
                            type: 'line',
                            borderColor: 'rgb(75, 192, 192)',
                            backgroundColor: 'rgba(75, 192, 192, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            pointBackgroundColor: 'rgb(75, 192, 192)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            yAxisID: 'y',
                            order: 1
                        },
                        {
                            label: 'سعة الخزانات (لتر)',
                            data: fuelCapacityData,
                            backgroundColor: 'rgba(200, 200, 200, 0.5)',
                            borderColor: 'rgb(150, 150, 150)',
                            borderWidth: 1,
                            yAxisID: 'y1',
                            order: 2
                        },
                        {
                            label: 'الوقود المستهلك (لتر)',
                            data: fuelConsumedData,
                            backgroundColor: 'rgba(255, 99, 132, 0.7)',
                            borderColor: 'rgb(255, 99, 132)',
                            borderWidth: 1,
                            yAxisID: 'y1',
                            order: 3
                        },
                        {
                            label: 'الفائض من الوقود (لتر)',
                            data: fuelSurplusData,
                            backgroundColor: 'rgba(54, 162, 235, 0.8)',
                            borderColor: 'rgb(54, 162, 235)',
                            borderWidth: 1,
                            yAxisID: 'y1',
                            order: 4
                        },
                        {
                            label: 'الناقص من الوقود (لتر)',
                            data: fuelDeficitData,
                            backgroundColor: 'rgba(255, 159, 64, 0.8)',
                            borderColor: 'rgb(255, 159, 64)',
                            borderWidth: 1,
                            yAxisID: 'y1',
                            order: 5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            rtl: true,
                            labels: {
                                font: {
                                    family: 'Cairo, Arial, sans-serif',
                                    size: 12
                                },
                                padding: 15,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            rtl: true,
                            titleFont: {
                                family: 'Cairo, Arial, sans-serif',
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                family: 'Cairo, Arial, sans-serif',
                                size: 12
                            },
                            padding: 12,
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.2)',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += context.parsed.y.toLocaleString('ar');
                                    if (context.datasetIndex === 0) {
                                        label += ' kWh';
                                    } else {
                                        label += ' لتر';
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: false,
                            ticks: {
                                font: {
                                    family: 'Cairo, Arial, sans-serif',
                                    size: 11
                                },
                                maxRotation: 45,
                                minRotation: 45
                            },
                            grid: {
                                display: true
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            stacked: false,
                            title: {
                                display: true,
                                text: 'الطاقة المنتجة (kWh)',
                                font: {
                                    family: 'Cairo, Arial, sans-serif',
                                    size: 13,
                                    weight: 'bold'
                                },
                                color: 'rgb(75, 192, 192)'
                            },
                            ticks: {
                                font: {
                                    family: 'Cairo, Arial, sans-serif',
                                    size: 11
                                },
                                color: 'rgb(75, 192, 192)'
                            },
                            grid: {
                                color: 'rgba(75, 192, 192, 0.1)'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            stacked: false,
                            title: {
                                display: true,
                                text: 'الوقود (لتر)',
                                font: {
                                    family: 'Cairo, Arial, sans-serif',
                                    size: 13,
                                    weight: 'bold'
                                },
                                color: 'rgb(255, 99, 132)'
                            },
                            ticks: {
                                font: {
                                    family: 'Cairo, Arial, sans-serif',
                                    size: 11
                                },
                                color: 'rgb(255, 99, 132)'
                            },
                            grid: {
                                drawOnChartArea: false,
                                color: 'rgba(255, 99, 132, 0.1)'
                            },
                        }
                    }
                }
            });
        }

        // Create Surplus Charts for all roles
        if (pieData.generators && pieData.generators.details && pieData.generators.details.length > 0) {
            createSurplusChart('generatorsSurplusChart', pieData.generators.labels, pieData.generators.details, 'المولدات');
            createSurplusChart('generatorsSurplusChartOwner', pieData.generators.labels, pieData.generators.details, 'المولدات');
            createSurplusChart('generatorsSurplusChartAdmin', pieData.generators.labels, pieData.generators.details, 'المولدات');
        }

        if (pieData.operators && pieData.operators.details && pieData.operators.details.length > 0) {
            createSurplusChart('operatorsSurplusChart', pieData.operators.labels, pieData.operators.details, 'المشغلين');
            createSurplusChart('operatorsSurplusChartOwner', pieData.operators.labels, pieData.operators.details, 'المشغلين');
            createSurplusChart('operatorsSurplusChartAdmin', pieData.operators.labels, pieData.operators.details, 'المشغلين');
        }

        if (pieData.governorates && pieData.governorates.details && pieData.governorates.details.length > 0) {
            createSurplusChart('governoratesSurplusChart', pieData.governorates.labels, pieData.governorates.details, 'المحافظات');
            createSurplusChart('governoratesSurplusChartOwner', pieData.governorates.labels, pieData.governorates.details, 'المحافظات');
            createSurplusChart('governoratesSurplusChartAdmin', pieData.governorates.labels, pieData.governorates.details, 'المحافظات');
        }
    }

    // Pass pie chart data to JavaScript
    window.pieChartData = @json($pieChartData);

    // Wait for Chart.js to be ready
    function waitForPieChart() {
        if (typeof Chart !== 'undefined') {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initPieCharts);
            } else {
                initPieCharts();
            }
        } else {
            setTimeout(waitForPieChart, 100);
        }
    }

    waitForPieChart();
})();
</script>
@endif
@endpush
