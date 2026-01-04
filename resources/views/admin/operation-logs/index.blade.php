@extends('layouts.admin')

@section('title', 'سجلات التشغيل')

@php
    $breadcrumbTitle = 'سجلات التشغيل';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/operation-logs.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
@endpush

@section('content')
    <input type="hidden" id="csrfToken" value="{{ csrf_token() }}">

    <div class="operation-logs-page">
        <div class="row g-3">
            {{-- Main: قائمة سجلات التشغيل --}}
            <div class="col-12">
                <div class="log-card">
                    <div class="log-card-header log-toolbar-header">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <div>
                                <div class="log-title">
                                    <i class="bi bi-journal-text me-2"></i>
                                    سجلات التشغيل
                                </div>
                                <div class="log-subtitle">
                                    البحث والفلترة وإدارة سجلات التشغيل. العدد: <span id="operationLogsCount">{{ $operationLogs->total() }}</span>
                                </div>
                            </div>

                            @can('create', App\Models\OperationLog::class)
                                <a href="{{ route('admin.operation-logs.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>
                                    إضافة سجل جديد
                                </a>
                            @endcan
                        </div>

                        {{-- كارد واحد للفلاتر --}}
                        <div class="card border mb-3">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="bi bi-funnel me-2"></i>
                                    فلاتر البحث
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    {{-- البحث --}}
                                    <div class="{{ (auth()->user()->isSuperAdmin() && isset($operators) && $operators->count() > 0) || (isset($generators) && $generators->count() > 0) ? 'col-md-3' : 'col-md-4' }}">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-search me-1"></i>
                                            البحث
                                        </label>
                                        <input
                                            type="text"
                                            id="searchInput"
                                            class="form-control"
                                            placeholder="ابحث عن سجل بالاسم/الرقم/المشغل..."
                                            value="{{ request('q', '') }}"
                                        >
                                    </div>

                                    {{-- المشغل (SuperAdmin فقط) --}}
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

                                    {{-- المولد --}}
                                    @if(isset($generators) && $generators->count() > 0)
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-lightning-charge me-1"></i>
                                                المولد
                                            </label>
                                            <select id="generatorFilter" class="form-select">
                                                <option value="">كل المولدات</option>
                                                @foreach($generators as $gen)
                                                    <option value="{{ $gen->id }}" {{ request('generator_id') == $gen->id ? 'selected' : '' }}>
                                                        {{ $gen->generator_number }} - {{ $gen->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    {{-- تاريخ من --}}
                                    <div class="{{ ((auth()->user()->isSuperAdmin() && isset($operators) && $operators->count() > 0) || (isset($generators) && $generators->count() > 0)) ? 'col-md-3' : 'col-md-4' }}">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            من تاريخ
                                        </label>
                                        <input type="date" id="dateFromFilter" class="form-control" 
                                               value="{{ request('date_from', '') }}">
                                    </div>

                                    {{-- تاريخ إلى --}}
                                    <div class="{{ ((auth()->user()->isSuperAdmin() && isset($operators) && $operators->count() > 0) || (isset($generators) && $generators->count() > 0)) ? 'col-md-3' : 'col-md-4' }}">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-calendar-check me-1"></i>
                                            إلى تاريخ
                                        </label>
                                        <input type="date" id="dateToFilter" class="form-control" 
                                               value="{{ request('date_to', '') }}">
                                    </div>

                                    {{-- أزرار البحث والتجميع --}}
                                    <div class="col-md-12">
                                        <div class="d-flex gap-2 align-items-center flex-wrap">
                                            <button class="btn btn-primary" type="button" id="searchBtn">
                                                <i class="bi bi-search me-2"></i>
                                                بحث
                                            </button>
                                            <button
                                                class="btn btn-outline-secondary {{ request('q') || request('operator_id') || request('generator_id') || request('date_from') || request('date_to') ? '' : 'd-none' }}"
                                                type="button"
                                                id="clearSearchBtn"
                                            >
                                                <i class="bi bi-x me-2"></i>
                                                إلغاء الفلاتر
                                            </button>
                                            <div class="form-check form-switch ms-auto">
                                                <input class="form-check-input" type="checkbox" id="groupByGeneratorToggle" {{ request('group_by_generator') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="groupByGeneratorToggle">
                                                    <i class="bi bi-grid-3x3-gap me-1"></i>
                                                    تجميع حسب المولد
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body log-list-body data-table-container">
                        <div id="operationLogsListContainer">
                            @if(isset($groupedLogs) && $groupedLogs->isNotEmpty())
                                @include('admin.operation-logs.partials.grouped-list', ['groupedLogs' => $groupedLogs, 'totalCount' => $groupedLogs->flatten()->count()])
                            @else
                                @include('admin.operation-logs.partials.list', ['operationLogs' => $operationLogs])
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/admin/js/data-table-loading.js') }}"></script>
    <script>
        window.OPLOG = {
            routes: {
                index: @json(route('admin.operation-logs.index')),
                search: @json(route('admin.operation-logs.index')),
                delete: @json(route('admin.operation-logs.destroy', ['operation_log' => '__ID__'])),
            }
        };
    </script>
    <script src="{{ asset('assets/admin/js/operation-logs.js') }}"></script>
@endpush
