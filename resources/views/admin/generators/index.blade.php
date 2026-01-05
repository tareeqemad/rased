@extends('layouts.admin')

@section('title', 'إدارة المولدات')

@php
    $breadcrumbTitle = 'إدارة المولدات';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/generators.css') }}">
@endpush

@section('content')
    <input type="hidden" id="csrfToken" value="{{ csrf_token() }}">

    <div class="generators-page">
        <div class="row g-3">
            {{-- Main: قائمة المولدات --}}
            <div class="col-12">
                <div class="card gen-card">
                    <div class="gen-card-header gen-toolbar-header">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <div class="gen-title">
                                    <i class="bi bi-lightning-charge me-2"></i>
                                    إدارة المولدات
                                </div>
                                <div class="gen-subtitle">
                                    البحث والفلترة وإدارة المولدات. العدد: <span id="generatorsCount">{{ $generators->total() }}</span>
                                </div>
                            </div>

                            @can('create', App\Models\Generator::class)
                                <a href="{{ route('admin.generators.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>
                                    إضافة مولد جديد
                                </a>
                            @endcan
                        </div>

                        {{-- كارد واحد للفلاتر --}}
                        <div class="card border mt-3 mb-3">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="bi bi-funnel me-2"></i>
                                    فلاتر البحث
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-search me-1"></i>
                                            البحث
                                        </label>
                                        <input
                                            type="text"
                                            id="searchInput"
                                            class="form-control"
                                            placeholder="ابحث عن مولد بالاسم/الرقم/المشغل..."
                                            value="{{ request('q', '') }}"
                                        >
                                    </div>

                                    @if(auth()->user()->isSuperAdmin() && isset($operators) && $operators->count() > 0)
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-building me-1"></i>
                                                المشغل
                                            </label>
                                            <select id="operatorFilter" class="form-select">
                                                <option value="">كل المشغلين</option>
                                                @foreach($operators as $op)
                                                    <option value="{{ $op->id }}" {{ request('operator_id') == $op->id ? 'selected' : '' }}>
                                                        {{ $op->unit_number ? $op->unit_number . ' - ' : '' }}{{ $op->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-funnel me-1"></i>
                                            الحالة
                                        </label>
                                        <select id="statusFilter" class="form-select">
                                            <option value="">الكل</option>
                                            @php
                                                $activeStatus = ($statusConstants ?? collect())->firstWhere('code', 'ACTIVE');
                                                $inactiveStatus = ($statusConstants ?? collect())->firstWhere('code', 'INACTIVE');
                                            @endphp
                                            @if($activeStatus)
                                                <option value="{{ $activeStatus->id }}" {{ request('status_id') == $activeStatus->id ? 'selected' : '' }}>{{ $activeStatus->label }}</option>
                                            @endif
                                            @if($inactiveStatus)
                                                <option value="{{ $inactiveStatus->id }}" {{ request('status_id') == $inactiveStatus->id ? 'selected' : '' }}>{{ $inactiveStatus->label }}</option>
                                            @endif
                                        </select>
                                    </div>

                                    <div class="col-md-1 d-flex align-items-end">
                                        <div class="d-flex gap-2 w-100">
                                            <button class="btn btn-primary flex-fill" type="button" id="searchBtn" title="بحث">
                                                <i class="bi bi-search"></i>
                                            </button>
                                            <button
                                                class="btn btn-outline-secondary {{ request('q') || request('operator_id') || request('status_id') ? '' : 'd-none' }}"
                                                type="button"
                                                id="clearSearchBtn"
                                                title="تفريغ"
                                            >
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body position-relative gen-list-body">
                        <div id="generatorsLoadingOverlay" class="gen-loading" style="display:none;">
                            <div class="text-center">
                                <div class="spinner-border" role="status"></div>
                                <div class="mt-2 text-muted fw-semibold">جاري التحميل...</div>
                            </div>
                        </div>

                        <div id="generatorsListContainer">
                            @include('admin.generators.partials.list', ['generators' => $generators])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.GEN = {
            routes: {
                index: @json(route('admin.generators.index')),
                search: @json(route('admin.generators.index')),
                delete: @json(route('admin.generators.destroy', ['generator' => '__ID__'])),
            }
        };
    </script>
    <script src="{{ asset('assets/admin/js/generators.js') }}"></script>
@endpush
