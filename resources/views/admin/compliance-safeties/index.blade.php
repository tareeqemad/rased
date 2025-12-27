@extends('layouts.admin')

@section('title', 'الامتثال والسلامة')

@php
    $breadcrumbTitle = 'الامتثال والسلامة';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/compliance-safeties.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
@endpush

@section('content')
    <div class="compliance-safeties-page">
        <div class="row g-3">
            {{-- Main: قائمة الامتثال والسلامة --}}
            <div class="col-12">
                <div class="card log-card">
                    <div class="log-card-header log-toolbar-header">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <div>
                                <div class="log-title">
                                    <i class="bi bi-shield-check me-2"></i>
                                    الامتثال والسلامة
                                </div>
                                <div class="log-subtitle">
                                    إدارة سجلات الامتثال والسلامة. العدد: <span id="complianceSafetiesCount">{{ isset($groupedLogs) ? $groupedLogs->flatten()->count() : $complianceSafeties->total() }}</span>
                                </div>
                            </div>

                            @can('create', App\Models\ComplianceSafety::class)
                                <a href="{{ route('admin.compliance-safeties.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>
                                    إضافة سجل جديد
                                </a>
                            @endcan
                        </div>

                        {{-- Row 1: الفلاتر --}}
                        <div class="row g-3 mb-3">
                            {{-- البحث --}}
                            <div class="{{ isset($operators) && $operators->count() > 0 ? 'col-md-3' : 'col-md-4' }}">
                                <label class="form-label small text-muted">البحث</label>
                                <div class="log-searchfield">
                                    <i class="bi bi-search log-search-icon"></i>
                                    <input
                                        type="text"
                                        id="searchInput"
                                        class="form-control log-search-input"
                                        placeholder="ابحث عن سجل بالمشغل أو الجهة..."
                                        value="{{ request('q', '') }}"
                                    >
                                </div>
                            </div>

                            {{-- المشغل --}}
                            @if(isset($operators) && $operators->count() > 0)
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">
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

                            {{-- تاريخ من --}}
                            <div class="{{ isset($operators) && $operators->count() > 0 ? 'col-md-3' : 'col-md-4' }}">
                                <label class="form-label small text-muted">من تاريخ</label>
                                <input type="date" id="dateFromFilter" class="form-control" 
                                       value="{{ request('date_from', '') }}">
                            </div>

                            {{-- تاريخ إلى --}}
                            <div class="{{ isset($operators) && $operators->count() > 0 ? 'col-md-3' : 'col-md-4' }}">
                                <label class="form-label small text-muted">إلى تاريخ</label>
                                <input type="date" id="dateToFilter" class="form-control" 
                                       value="{{ request('date_to', '') }}">
                            </div>
                        </div>

                        {{-- Row 2: زر البحث وخيارات العرض --}}
                        <div class="row mb-3">
                            <div class="col-12 d-flex gap-2 align-items-center flex-wrap">
                                <button class="btn btn-primary" type="button" id="searchBtn">
                                    <i class="bi bi-search me-2"></i>
                                    بحث
                                </button>
                                <button
                                    class="btn btn-outline-secondary {{ request('q') || request('operator_id') || request('date_from') || request('date_to') ? '' : 'd-none' }}"
                                    type="button"
                                    id="clearSearchBtn"
                                >
                                    <i class="bi bi-x me-2"></i>
                                    إلغاء الفلاتر
                                </button>
                                <div class="form-check form-switch ms-auto">
                                    <input class="form-check-input" type="checkbox" id="groupByOperatorToggle" {{ request('group_by_operator') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="groupByOperatorToggle">
                                        <i class="bi bi-grid-3x3-gap me-1"></i>
                                        تجميع حسب المشغل
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body log-list-body data-table-container">
                        <div id="complianceSafetiesListContainer">
                            @if(request('group_by_operator') && isset($groupedLogs) && $groupedLogs->isNotEmpty())
                                @include('admin.compliance-safeties.partials.grouped-list', ['groupedLogs' => $groupedLogs, 'complianceSafeties' => $complianceSafeties])
                            @else
                                @include('admin.compliance-safeties.partials.list', ['complianceSafeties' => $complianceSafeties])
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/admin/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/data-table-loading.js') }}"></script>
    <script>
        window.COMPLIANCE_SAFETY = {
            routes: {
                index: @json(route('admin.compliance-safeties.index')),
                search: @json(route('admin.compliance-safeties.index')),
                delete: @json(route('admin.compliance-safeties.destroy', ['compliance_safety' => '__ID__'])),
            }
        };
    </script>
    <script src="{{ asset('assets/admin/js/compliance-safeties.js') }}"></script>
@endpush
