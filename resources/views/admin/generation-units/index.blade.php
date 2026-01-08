@extends('layouts.admin')

@section('title', 'إدارة وحدات التوليد')

@php
    $breadcrumbTitle = 'إدارة وحدات التوليد';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/generators.css') }}">
@endpush

@section('content')
    <input type="hidden" id="csrfToken" value="{{ csrf_token() }}">

    <div class="generators-page">
        <div class="row g-3">
            {{-- Main: قائمة وحدات التوليد --}}
            <div class="col-12">
                <div class="card gen-card">
                    <div class="gen-card-header gen-toolbar-header">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <div class="gen-title">
                                    <i class="bi bi-lightning-charge me-2"></i>
                                    إدارة وحدات التوليد
                                </div>
                                <div class="gen-subtitle">
                                    البحث والفلترة وإدارة وحدات التوليد. العدد: <span id="generationUnitsCount">{{ $generationUnits->total() }}</span>
                                </div>
                            </div>

                            @can('create', App\Models\GenerationUnit::class)
                                <a href="{{ route('admin.generation-units.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>
                                    إضافة وحدة توليد جديدة
                                </a>
                            @endcan
                        </div>

                        {{-- كارد واحد للفلاتر --}}
                        <div class="filter-card">
                            <div class="card-header">
                                <h6 class="card-title">
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
                                            placeholder="ابحث عن وحدة توليد بالاسم/الكود/المشغل..."
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
                                                        {{ $op->name }}
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
                                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>فعال</option>
                                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير فعال</option>
                                        </select>
                                    </div>

                                    <div class="col-md-1 d-flex align-items-end">
                                        <div class="d-flex gap-2 w-100">
                                            <button class="btn btn-primary flex-fill" type="button" id="searchBtn" title="بحث">
                                                <i class="bi bi-search"></i>
                                            </button>
                                            <button
                                                class="btn btn-outline-secondary {{ request('q') || request('operator_id') || request('status') ? '' : 'd-none' }}"
                                                type="button"
                                                id="clearBtn"
                                                title="تفريغ الحقول"
                                            >
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="generationUnitsListWrap" class="data-table-container">
                            @include('admin.generation-units.partials.list', ['generationUnits' => $generationUnits])
                        </div>

                        <div id="genLoading" class="gen-loading d-none">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status"></div>
                                <div class="mt-2 text-muted">جاري التحميل...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function() {
    const listUrl = @json(route('admin.generation-units.index'));
    const $wrap = $('#generationUnitsListWrap');
    const $loading = $('#genLoading');
    const $searchInput = $('#searchInput');
    const $operatorFilter = $('#operatorFilter');
    const $statusFilter = $('#statusFilter');
    const $searchBtn = $('#searchBtn');
    const $clearBtn = $('#clearBtn');

    function setLoading(on) {
        $loading.toggleClass('d-none', !on);
    }

    function currentParams(extra = {}) {
        return Object.assign({
            q: $searchInput.val() || '',
            operator_id: $operatorFilter.val() || '',
            status: $statusFilter.val() || '',
        }, extra);
    }

    function loadList(extra = {}) {
        setLoading(true);
        $.ajax({
            url: listUrl,
            method: 'GET',
            data: currentParams(Object.assign({ ajax: 1 }, extra)),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (res) {
                if (res && res.success) {
                    $wrap.html(res.html);
                    $('#generationUnitsCount').text(res.count || 0);
                    wireListEvents();
                }
            },
            error: function () {
                alert('حدث خطأ أثناء تحميل البيانات');
            },
            complete: function () {
                setLoading(false);
            }
        });
    }

    function wireListEvents() {
        $wrap.find('.pagination a').off('click').on('click', function (e) {
            e.preventDefault();
            const url = $(this).attr('href');
            if (!url) return;
            const u = new URL(url, window.location.origin);
            const page = u.searchParams.get('page') || 1;
            loadList({ page: page });
        });
    }

    function toggleClearBtn() {
        const hasValue = $searchInput.val().trim() !== '' || 
                        $operatorFilter.val() !== '' || 
                        $statusFilter.val() !== '';
        $clearBtn.toggleClass('d-none', !hasValue);
    }

    $searchBtn.on('click', function () {
        loadList({ page: 1 });
    });

    $clearBtn.on('click', function () {
        $searchInput.val('');
        $operatorFilter.val('').trigger('change');
        $statusFilter.val('').trigger('change');
        loadList({ page: 1 });
    });

    $searchInput.on('input', toggleClearBtn);
    $operatorFilter.on('change', toggleClearBtn);
    $statusFilter.on('change', toggleClearBtn);

    $searchInput.on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            loadList({ page: 1 });
        }
    });

    wireListEvents();
    toggleClearBtn();
})();
</script>
@endpush

