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

                        <div class="gen-toolbar mt-3">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-6">
                                    {{-- Search Bar --}}
                                    <div class="gen-searchbar">
                                        <div class="gen-searchfield">
                                            <i class="bi bi-search gen-search-icon"></i>
                                            <input
                                                type="text"
                                                id="searchInput"
                                                class="form-control gen-search-input"
                                                placeholder="ابحث عن مولد بالاسم/الرقم/المشغل..."
                                                value="{{ request('q', '') }}"
                                            >
                                        </div>
                                        <button class="btn btn-primary gen-search-action" type="button" id="searchBtn">
                                            <i class="bi bi-search me-1"></i>
                                            بحث
                                        </button>
                                        <button
                                            class="btn gen-clear-btn gen-search-action {{ request('q') ? '' : 'd-none' }}"
                                            type="button"
                                            id="clearSearchBtn"
                                        >
                                            <i class="bi bi-x me-1"></i>
                                            إلغاء
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="gen-filters">
                                        <button type="button" class="gen-filter active" data-filter="all">الكل</button>
                                        <button type="button" class="gen-filter" data-filter="active">فعال</button>
                                        <button type="button" class="gen-filter" data-filter="inactive">غير فعال</button>
                                    @if(auth()->user()->isSuperAdmin() && isset($operators) && $operators->count() > 0)
                                        <select id="operatorFilter" class="form-select form-select-sm gen-filter-select">
                                            <option value="">كل المشغلين</option>
                                            @foreach($operators as $op)
                                                <option value="{{ $op->id }}" {{ request('operator_id') == $op->id ? 'selected' : '' }}>
                                                    {{ $op->unit_number ? $op->unit_number . ' - ' : '' }}{{ $op->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
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
    <script src="{{ asset('assets/admin/libs/jquery/jquery.min.js') }}"></script>
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
