@extends('layouts.admin')

@section('title', 'لوحة التحكم')

@php
    $breadcrumbTitle = 'لوحة التحكم';
@endphp

@section('content')
    <!-- Welcome Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div>
                            <h2 class="mb-2 fw-bold text-white">مرحباً بك {{ auth()->user()->name }} 👋</h2>
                            <p class="mb-0 opacity-90 text-white">لوحة التحكم الرئيسية - {{ now('Asia/Gaza')->locale('ar')->translatedFormat('l، d F Y') }}</p>
                        </div>
                        <div class="text-end">
                            <div class="fs-4 fw-bold text-white">{{ now('Asia/Gaza')->format('H:i') }}</div>
                            <small class="opacity-75 text-white">{{ now('Asia/Gaza')->locale('ar')->translatedFormat('A') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        @if(auth()->user()->isSuperAdmin())
            <!-- Users -->
            <div class="col-12 col-sm-6 col-lg-3 mb-3">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="stat-label text-muted">المستخدمون</div>
                                <div class="stat-value text-primary">{{ number_format($stats['users']['total']) }}</div>
                                <div class="stat-change mt-2">
                                    <small class="text-muted">
                                        <span class="badge bg-primary">{{ $stats['users']['super_admins'] }} مدير</span>
                                        <span class="badge bg-info">{{ $stats['users']['company_owners'] }} صاحب شركة</span>
                                        <span class="badge bg-success">{{ $stats['users']['employees'] }} موظف</span>
                                    </small>
                                </div>
                            </div>
                            <div class="stat-icon text-primary">
                                <i class="bi bi-people-fill fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Operators -->
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="stat-label text-muted">المشغلون</div>
                            <div class="stat-value text-info">{{ number_format($stats['operators']['total']) }}</div>
                            @if(isset($stats['operators']['active']))
                                <div class="stat-change mt-2">
                                    <small class="text-info">
                                        <i class="bi bi-check-circle"></i> {{ $stats['operators']['active'] }} نشط
                                    </small>
                                </div>
                            @endif
                        </div>
                        <div class="stat-icon text-info">
                            <i class="bi bi-building fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Generators -->
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="stat-label text-muted">المولدات</div>
                            <div class="stat-value text-success">{{ number_format($stats['generators']['total']) }}</div>
                            @if(isset($stats['generators']['active']))
                                <div class="stat-change mt-2">
                                    <small class="text-success">
                                        <i class="bi bi-check-circle"></i> {{ $stats['generators']['active'] }} نشطة
                                    </small>
                                </div>
                            @endif
                        </div>
                        <div class="stat-icon text-success">
                            <i class="bi bi-lightning-charge-fill fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->isCompanyOwner() && isset($stats['employees']))
            <!-- Employees -->
            <div class="col-12 col-sm-6 col-lg-3 mb-3">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="stat-label text-muted">الموظفون</div>
                                <div class="stat-value text-warning">{{ number_format($stats['employees']['total']) }}</div>
                            </div>
                            <div class="stat-icon text-warning">
                                <i class="bi bi-person-badge fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Recent Items -->
    <div class="row">
        <!-- Recent Generators -->
        <div class="col-12 col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-lightning-charge-fill me-2 text-success"></i>
                        آخر المولدات
                    </h5>
                </div>
                <div class="card-body p-0">
                    @forelse($recentGenerators as $generator)
                        <div class="p-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold">{{ $generator->name }}</h6>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <span class="badge bg-{{ $generator->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ $generator->status === 'active' ? 'نشط' : 'غير نشط' }}
                                        </span>
                                        <small class="text-muted">
                                            <i class="bi bi-building"></i> {{ $generator->operator->name }}
                                        </small>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        <i class="bi bi-clock"></i> {{ $generator->created_at->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            لا توجد مولدات
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        @if(auth()->user()->isSuperAdmin() && $recentOperators->count() > 0)
            <!-- Recent Operators -->
            <div class="col-12 col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-building me-2 text-info"></i>
                            آخر المشغلين
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @forelse($recentOperators as $operator)
                            <div class="p-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-semibold">{{ $operator->name }}</h6>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <small class="text-muted">
                                                <i class="bi bi-person"></i> {{ $operator->owner->name }}
                                            </small>
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            <i class="bi bi-clock"></i> {{ $operator->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                لا توجد مشغلين
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('styles')
<style>
    .stat-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    .stat-label {
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1.2;
        margin-top: 0.5rem;
    }
    .stat-icon {
        opacity: 0.2;
    }
    .bg-gradient-primary {
        background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
    }
    .bg-gradient-primary .text-white,
    .bg-gradient-primary h2,
    .bg-gradient-primary p,
    .bg-gradient-primary small {
        color: #ffffff !important;
    }
</style>
@endpush

