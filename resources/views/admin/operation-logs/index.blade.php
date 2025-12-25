@extends('layouts.admin')

@section('title', 'سجلات التشغيل')

@php
    $breadcrumbTitle = 'سجلات التشغيل';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/operation-logs.css') }}">
@endpush

@section('content')
    <input type="hidden" id="csrfToken" value="{{ csrf_token() }}">

    <div class="operation-logs-page">
        <div class="row g-3">
            {{-- Main: قائمة سجلات التشغيل --}}
            <div class="col-12">
                <div class="card log-card">
                    <div class="log-card-header log-toolbar-header">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
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

                        <div class="log-toolbar mt-3">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-6">
                                    {{-- Search Bar --}}
                                    <div class="log-searchbar">
                                        <div class="log-searchfield">
                                            <i class="bi bi-search log-search-icon"></i>
                                            <input
                                                type="text"
                                                id="searchInput"
                                                class="form-control log-search-input"
                                                placeholder="ابحث عن سجل بالاسم/الرقم/المشغل..."
                                                value="{{ request('q', '') }}"
                                            >
                                        </div>
                                        <button class="btn btn-primary log-search-action" type="button" id="searchBtn">
                                            <i class="bi bi-search me-1"></i>
                                            بحث
                                        </button>
                                        <button
                                            class="btn log-clear-btn log-search-action {{ request('q') ? '' : 'd-none' }}"
                                            type="button"
                                            id="clearSearchBtn"
                                        >
                                            <i class="bi bi-x me-1"></i>
                                            إلغاء
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="log-filters">
                                        @if(auth()->user()->isSuperAdmin() && isset($operators) && $operators->count() > 0)
                                            <select id="operatorFilter" class="form-select form-select-sm log-filter-select">
                                                <option value="">كل المشغلين</option>
                                                @foreach($operators as $op)
                                                    <option value="{{ $op->id }}" {{ request('operator_id') == $op->id ? 'selected' : '' }}>
                                                        {{ $op->unit_number ? $op->unit_number . ' - ' : '' }}{{ $op->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif
                                        <input type="date" id="dateFromFilter" class="form-control form-control-sm log-filter-select" 
                                               placeholder="من تاريخ" value="{{ request('date_from', '') }}">
                                        <input type="date" id="dateToFilter" class="form-control form-control-sm log-filter-select" 
                                               placeholder="إلى تاريخ" value="{{ request('date_to', '') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body position-relative log-list-body">
                        <div id="operationLogsLoadingOverlay" class="log-loading" style="display:none;">
                            <div class="text-center">
                                <div class="spinner-border" role="status"></div>
                                <div class="mt-2 text-muted fw-semibold">جاري التحميل...</div>
                            </div>
                        </div>

                        <div id="operationLogsListContainer">
                            @include('admin.operation-logs.partials.list', ['operationLogs' => $operationLogs])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/admin/libs/jquery/jquery.min.js') }}"></script>
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
