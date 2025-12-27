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

                        {{-- Row 1: الفلاتر --}}
                        <div class="row g-3 mb-3">
                            {{-- البحث --}}
                            <div class="{{ (auth()->user()->isSuperAdmin() && isset($operators) && $operators->count() > 0) || (isset($generators) && $generators->count() > 0) ? 'col-md-3' : 'col-md-4' }}">
                                <label class="form-label small text-muted">البحث</label>
                                <div class="log-searchfield">
                                    <i class="bi bi-search log-search-icon"></i>
                                    <input
                                        type="text"
                                        id="searchInput"
                                        class="form-control log-search-input"
                                        placeholder="ابحث عن سجل بالمولد..."
                                        value="{{ request('q', '') }}"
                                    >
                                </div>
                            </div>

                            {{-- المشغل (SuperAdmin فقط) --}}
                            @if(auth()->user()->isSuperAdmin() && isset($operators) && $operators->count() > 0)
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">المشغل</label>
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
                                    <label class="form-label small text-muted">
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
                                <label class="form-label small text-muted">من تاريخ</label>
                                <input type="date" id="dateFromFilter" class="form-control" 
                                       value="{{ request('date_from', '') }}">
                            </div>

                            {{-- تاريخ إلى --}}
                            <div class="{{ ((auth()->user()->isSuperAdmin() && isset($operators) && $operators->count() > 0) || (isset($generators) && $generators->count() > 0)) ? 'col-md-3' : 'col-md-4' }}">
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

                    <div class="card-body log-list-body data-table-container">
                        <div id="fuelEfficienciesListContainer">
                            @if(isset($groupedLogs) && $groupedLogs->isNotEmpty())
                                @include('admin.fuel-efficiencies.partials.grouped-list', ['groupedLogs' => $groupedLogs, 'fuelEfficiencies' => $fuelEfficiencies])
                            @else
                                @include('admin.fuel-efficiencies.partials.list', ['fuelEfficiencies' => $fuelEfficiencies])
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
        window.FUEL_EFF = {
            routes: {
                index: @json(route('admin.fuel-efficiencies.index')),
                search: @json(route('admin.fuel-efficiencies.index')),
                delete: @json(route('admin.fuel-efficiencies.destroy', ['fuel_efficiency' => '__ID__'])),
            }
        };
    </script>
    <script src="{{ asset('assets/admin/js/fuel-efficiencies.js') }}"></script>
@endpush
