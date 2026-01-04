@extends('layouts.admin')

@section('title', 'كفاءة الوقود')

@php
    $breadcrumbTitle = 'كفاءة الوقود';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/fuel-efficiencies.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
@endpush

@section('content')
    <div class="fuel-efficiencies-page">
        <div class="row g-3">
            {{-- Main: قائمة كفاءة الوقود --}}
            <div class="col-12">
                <div class="card log-card">
                    <div class="log-card-header log-toolbar-header">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <div>
                                <div class="log-title">
                                    <i class="bi bi-speedometer2 me-2"></i>
                                    كفاءة الوقود
                                </div>
                                <div class="log-subtitle">
                                    إدارة سجلات كفاءة الوقود. العدد: <span id="fuelEfficienciesCount">{{ $fuelEfficiencies->total() }}</span>
                                </div>
                            </div>

                            @can('create', App\Models\FuelEfficiency::class)
                                <a href="{{ route('admin.fuel-efficiencies.create') }}" class="btn btn-primary">
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
                                            placeholder="ابحث عن سجل بالمولد..."
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
                                        <div class="d-flex justify-content-center gap-2 align-items-center flex-wrap">
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
                                                تفريغ
                                            </button>
                                            <div class="form-check form-switch ms-3">
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

                    {{-- Row 3: Card للجدول --}}
                    <div class="card border mt-3">
                        <div class="card-body">
                            @if(request('group_by_generator') && isset($groupedLogs) && $groupedLogs->isNotEmpty())
                                {{-- Grouped view (non-AJAX only) --}}
                                @include('admin.fuel-efficiencies.partials.grouped-list', ['groupedLogs' => $groupedLogs, 'fuelEfficiencies' => $fuelEfficiencies])
                            @else
                                {{-- Normal table view with AJAX --}}
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>رقم المولد</th>
                                                <th>تاريخ الاستهلاك</th>
                                                <th>ساعات التشغيل</th>
                                                <th>كفاءة الوقود</th>
                                                <th>كفاءة الطاقة</th>
                                                <th>التكلفة الإجمالية</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody id="fuelEfficienciesTbody">
                                            @include('admin.fuel-efficiencies.partials.tbody-rows', ['fuelEfficiencies' => $fuelEfficiencies])
                                        </tbody>
                                    </table>
                                </div>

                                @if(isset($fuelEfficiencies) && $fuelEfficiencies->hasPages())
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div class="small text-muted">
                                            عرض {{ $fuelEfficiencies->firstItem() }} - {{ $fuelEfficiencies->lastItem() }} من {{ $fuelEfficiencies->total() }}
                                        </div>
                                        <nav>
                                            <ul class="pagination mb-0" id="fuelEfficienciesPagination">
                                                @include('admin.fuel-efficiencies.partials.pagination', ['fuelEfficiencies' => $fuelEfficiencies])
                                            </ul>
                                        </nav>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    @if(!request('group_by_generator'))
    AdminCRUD.initList({
        url: '{{ route('admin.fuel-efficiencies.index') }}',
        container: '#fuelEfficienciesTbody',
        filters: {
            q: '#searchInput',
            operator_id: '#operatorFilter',
            generator_id: '#generatorFilter',
            date_from: '#dateFromFilter',
            date_to: '#dateToFilter'
        },
        searchButton: '#searchBtn',
        clearButton: '#clearSearchBtn',
        paginationContainer: '#fuelEfficienciesPagination',
        countElement: '#fuelEfficienciesCount',
        perPage: 100,
        listId: 'fuelEfficienciesList'
    });

    $(document).on('click', '.fuel-efficiency-delete-btn', function(e) {
        e.preventDefault();
        const id = $(this).data('fuel-efficiency-id');
        const name = $(this).data('fuel-efficiency-name') || 'هذا السجل';
        
        AdminCRUD.delete({
            url: '{{ route('admin.fuel-efficiencies.destroy', ['fuel_efficiency' => '__ID__']) }}',
            id: id,
            confirmMessage: `هل أنت متأكد من حذف ${name}؟`,
            onSuccess: function() {
                const listController = AdminCRUD.activeLists.get('fuelEfficienciesList');
                if (listController) {
                    listController.refresh();
                }
            }
        });
    });
    @endif

    $('#groupByGeneratorToggle').on('change', function() {
        const url = new URL(window.location.href);
        if ($(this).is(':checked')) {
            url.searchParams.set('group_by_generator', '1');
        } else {
            url.searchParams.delete('group_by_generator');
        }
        window.location.href = url.toString();
    });
});
</script>
@endpush
