@extends('layouts.admin')

@section('title', 'إدارة الثوابت')

@php
    $breadcrumbTitle = 'إدارة الثوابت';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/constants.css') }}">
@endpush

@section('content')
<div class="constants-page"
     id="constantsPage"
     data-index-url="{{ route('admin.constants.index') }}">

    <div class="row g-3">
        <div class="col-12">
            <div class="constants-card">

                <div class="constants-card-header">
                    <div>
                        <h5 class="constants-title">
                        <i class="bi bi-database me-2"></i>
                        إدارة الثوابت
                    </h5>
                        <div class="constants-subtitle">
                            إدارة وتنظيم ثوابت النظام
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        @can('create', App\Models\ConstantMaster::class)
                            <a href="{{ route('admin.constants.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i>
                                إضافة ثابت
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    <div class="constants-stats mb-4">
                        <div class="constants-stat">
                            <div class="label">الإجمالي</div>
                            <div class="value" id="statTotal">—</div>
                        </div>
                        <div class="constants-stat">
                            <div class="label">نشط</div>
                            <div class="value" id="statActive">—</div>
                        </div>
                        <div class="constants-stat">
                            <div class="label">غير نشط</div>
                            <div class="value" id="statInactive">—</div>
                        </div>
                        <div class="constants-stat">
                            <div class="label">إجمالي التفاصيل</div>
                            <div class="value" id="statDetails">—</div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-end">
                        <div class="col-lg-6">
                            <label class="form-label fw-semibold">بحث</label>
                            <div class="constants-search">
                                <i class="bi bi-search"></i>
                                <input type="text" class="form-control" id="constantsSearch" placeholder="رقم الثابت / الاسم / الوصف..." autocomplete="off">
                                <button type="button" class="constants-clear d-none" id="btnClearSearch" title="إلغاء البحث">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label fw-semibold">الحالة</label>
                            <select class="form-select" id="statusFilter">
                                <option value="">الكل</option>
                                <option value="active">نشط فقط</option>
                                <option value="inactive">غير نشط فقط</option>
                            </select>
                        </div>

                        <div class="col-lg-3">
                            <div class="d-flex gap-2">
                                <button class="btn btn-primary flex-grow-1" id="btnSearch">
                                    <i class="bi bi-search me-1"></i>
                                    بحث
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="btnResetFilters">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>
                                    تصفير
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="table-responsive" id="constantsTableContainer">
                        <table class="table table-hover align-middle mb-0 constants-table">
                            <thead>
                            <tr>
                                <th style="min-width:120px;">رقم الثابت</th>
                                <th>اسم الثابت</th>
                                <th class="d-none d-md-table-cell">الوصف</th>
                                <th class="text-center">التفاصيل</th>
                                <th class="text-center">الحالة</th>
                                <th class="text-center d-none d-lg-table-cell">الترتيب</th>
                                <th style="min-width:140px;">الإجراءات</th>
                            </tr>
                            </thead>
                            <tbody id="constantsTbody">
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <div class="spinner-border" role="status"></div>
                                            <div class="mt-2">جاري تحميل البيانات...</div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
                        <div class="small text-muted" id="constantsMeta">
                            @if($constants->total() > 0)
                                عرض {{ $constants->firstItem() }} - {{ $constants->lastItem() }} من {{ $constants->total() }}
                            @else
                                —
                            @endif
                        </div>
                        <nav>
                            <ul class="pagination mb-0" id="constantsPagination">
                                @if($constants->hasPages())
                                    @php
                                        $current = $constants->currentPage();
                                        $last = $constants->lastPage();
                                    @endphp
                                    
                                    {{-- Previous --}}
                                    @if($constants->onFirstPage())
                                        <li class="page-item disabled"><span class="page-link">‹</span></li>
                                    @else
                                        <li class="page-item"><a class="page-link" href="#" data-page="{{ $current - 1 }}">‹</a></li>
                                    @endif

                                    {{-- Pages --}}
                                    @if($last <= 7)
                                        @for($i = 1; $i <= $last; $i++)
                                            @if($i == $current)
                                                <li class="page-item active"><span class="page-link">{{ $i }}</span></li>
                                            @else
                                                <li class="page-item"><a class="page-link" href="#" data-page="{{ $i }}">{{ $i }}</a></li>
                                            @endif
                                        @endfor
                                    @else
                                        {{-- First page --}}
                                        @if($current > 3)
                                            <li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>
                                            @if($current > 4)
                                                <li class="page-item disabled"><span class="page-link">…</span></li>
                                            @endif
                                        @endif

                                        {{-- Pages around current --}}
                                        @for($i = max(1, $current - 1); $i <= min($last, $current + 1); $i++)
                                            @if($i == $current)
                                                <li class="page-item active"><span class="page-link">{{ $i }}</span></li>
                                            @else
                                                <li class="page-item"><a class="page-link" href="#" data-page="{{ $i }}">{{ $i }}</a></li>
                                            @endif
                                        @endfor

                                        {{-- Last page --}}
                                        @if($current < $last - 2)
                                            @if($current < $last - 3)
                                                <li class="page-item disabled"><span class="page-link">…</span></li>
                                            @endif
                                            <li class="page-item"><a class="page-link" href="#" data-page="{{ $last }}">{{ $last }}</a></li>
                                        @endif
                                    @endif

                                    {{-- Next --}}
                                    @if($constants->hasMorePages())
                                        <li class="page-item"><a class="page-link" href="#" data-page="{{ $current + 1 }}">›</a></li>
                                    @else
                                        <li class="page-item disabled"><span class="page-link">›</span></li>
                                    @endif
                                @endif
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals Container -->
        <div id="modalsContainer">
            @include('admin.constants.partials.modals', ['constants' => $constants])
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/data-table-loading.js') }}"></script>
<script>
(function () {
    const $page = $('#constantsPage');
    const INDEX_URL = $page.data('index-url');

    // CSRF for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    });

    const state = {
        search: '',
        status: '',
        page: 1,
    };

    const $container = $('#constantsTableContainer');
    const $tbody = $('#constantsTbody');
    const $meta = $('#constantsMeta');
    const $pagination = $('#constantsPagination');

    const $statTotal = $('#statTotal');
    const $statActive = $('#statActive');
    const $statInactive = $('#statInactive');
    const $statDetails = $('#statDetails');

    function setLoading(on){
        if(on) {
            if(window.DataTableLoading) {
                window.DataTableLoading.show($container[0]);
            }
        } else {
            if(window.DataTableLoading) {
                window.DataTableLoading.hide($container[0]);
            }
        }
    }

    function escapeHtml(str){
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function debounce(fn, wait){
        let t;
        return function(...args){
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), wait);
        }
    }

    function notify(type, message){
        if (window.adminNotifications && typeof window.adminNotifications[type] === 'function') {
            window.adminNotifications[type](message);
            return;
        }
        if(type === 'error') console.error(message);
        else console.log(message);
    }

    function renderEmpty(text){
        $tbody.html(`
            <tr>
                <td colspan="7">
                    <div class="empty-state">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                        ${escapeHtml(text || 'لا يوجد نتائج')}
                    </div>
                </td>
            </tr>
        `);
    }

    function renderRows(rows){
        if(!rows || !rows.length){
            renderEmpty('لا يوجد نتائج مطابقة');
            return;
        }

        const html = rows.map(c => {
            const id = c.id || 0;
            const number = escapeHtml(c.constant_number || '-');
            const name = escapeHtml(c.constant_name || '-');
            const description = c.description ? escapeHtml(c.description.length > 50 ? c.description.substring(0, 50) + '...' : c.description) : '-';
            const detailsCount = c.all_details_count || 0;
            const isActive = c.is_active === true || c.is_active === 1;
            const order = c.order || 0;

            const statusBadge = isActive 
                ? '<span class="badge-soft badge-active"><i class="bi bi-check-circle me-1"></i>نشط</span>'
                : '<span class="badge-soft badge-inactive"><i class="bi bi-x-circle me-1"></i>غير نشط</span>';

            const showUrl = `{{ url('admin/constants') }}/${id}`;
            const editUrl = `{{ url('admin/constants') }}/${id}/edit`;

            return `
                <tr>
                    <td><code>${number}</code></td>
                    <td class="fw-semibold">${name}</td>
                    <td class="d-none d-md-table-cell"><small class="text-muted">${description}</small></td>
                    <td class="text-center">
                        <span class="badge-soft">${detailsCount}</span>
                    </td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-center d-none d-lg-table-cell">
                        <span class="badge-soft">${order}</span>
                    </td>
                    <td>
                        <div class="d-flex gap-2 justify-content-center">
                            <a class="btn btn-light btn-icon" href="${showUrl}" title="عرض">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a class="btn btn-light btn-icon" href="${editUrl}" title="تعديل">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-light btn-icon text-danger" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deleteModal${id}" 
                                    title="حذف">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        $tbody.html(html);
    }

    function renderMeta(meta){
        if(!meta || typeof meta.total === 'undefined'){
            $meta.text('—');
            return;
        }
        $meta.text(`عرض ${meta.from || 0} - ${meta.to || 0} من ${meta.total || 0}`);
    }

    function renderPagination(meta){
        if(!meta || !meta.last_page || meta.last_page <= 1){
            $pagination.html('');
            return;
        }

        const current = parseInt(meta.current_page) || 1;
        const last = parseInt(meta.last_page) || 1;

        const makeItem = (page, label, disabled=false, active=false) => {
            if(disabled || page < 1 || page > last){
                return `<li class="page-item disabled"><span class="page-link">${label}</span></li>`;
            }
            return `
                <li class="page-item ${active?'active':''}">
                    <a class="page-link" href="#" data-page="${page}">${label}</a>
                </li>
            `;
        };

        let html = '';
        
        // Previous button
        html += makeItem(current-1, '‹', current <= 1);

        // Show pages intelligently
        if(last <= 7){
            // Show all pages if 7 or less
            for(let p=1; p<=last; p++){
                html += makeItem(p, String(p), false, p === current);
            }
        } else {
            // Show first page
            if(current > 3){
                html += makeItem(1, '1', false, current === 1);
                if(current > 4){
                    html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
                }
            }

            // Show pages around current
            const start = Math.max(1, current - 1);
            const end = Math.min(last, current + 1);

            for(let p=start; p<=end; p++){
                html += makeItem(p, String(p), false, p === current);
            }

            // Show last page
            if(current < last - 2){
                if(current < last - 3){
                    html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
                }
                html += makeItem(last, String(last), false, current === last);
            }
        }

        // Next button
        html += makeItem(current+1, '›', current >= last);

        $pagination.html(html);
    }

    function renderStats(stats){
        if(!stats) return;

        if($statTotal.length) $statTotal.text(stats.total ?? '—');
        if($statActive.length) $statActive.text(stats.active ?? '—');
        if($statInactive.length) $statInactive.text(stats.inactive ?? '—');
        if($statDetails.length) $statDetails.text(stats.details ?? '—');
    }

    function loadConstants(page=1){
        state.page = page;

        setLoading(true);
            $.ajax({
            url: INDEX_URL,
            method: 'GET',
                dataType: 'json',
            data: {
                ajax: 1,
                search: state.search,
                status: state.status,
                page: state.page,
            },
                headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            success: function(resp){
                if(resp && resp.success === false){
                    notify('error', resp.message || 'حدث خطأ أثناء جلب البيانات');
                    renderEmpty('تعذر تحميل البيانات');
                    return;
                }

                if(resp && resp.success){
                    renderRows(resp.data || []);
                    renderMeta(resp.meta || {});
                    
                    // Render pagination
                    if(resp.meta && resp.meta.last_page > 1){
                        renderPagination(resp.meta);
                    } else {
                        $pagination.html('');
                    }
                    
                    renderStats(resp.stats || {});
                    
                    // Update modals
                    if(resp.modals){
                        $('#modalsContainer').html(resp.modals);
                        // Reinitialize Bootstrap modals
                        $('[data-bs-toggle="modal"]').off('click').on('click', function(){
                            const target = $(this).data('bs-target');
                            $(target).modal('show');
                        });
                    }
                } else {
                    notify('error', 'استجابة غير صحيحة من الخادم');
                    renderEmpty('تعذر تحميل البيانات');
                }
            },
            error: function(xhr){
                const msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'تعذر تحميل البيانات';
                notify('error', msg);
                renderEmpty('تعذر تحميل البيانات');
            },
            complete: function(){
                setLoading(false);
            }
        });
    }

    // ===== Filters
    const $search = $('#constantsSearch');
    const $clearSearch = $('#btnClearSearch');
    const $statusFilter = $('#statusFilter');

    const doSearch = function(){
        state.search = $search.val().trim();
        $clearSearch.toggleClass('d-none', state.search.length === 0);
        loadConstants(1);
    };

    // Search on button click
    $('#btnSearch').on('click', doSearch);
    
    // Search on Enter key
    $search.on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            doSearch();
        }
    });
    
    // Show/hide clear button when typing (without auto search)
    $search.on('input', function() {
        $clearSearch.toggleClass('d-none', $(this).val().trim().length === 0);
    });

    $clearSearch.on('click', function(){
        $search.val('');
        state.search = '';
        $clearSearch.addClass('d-none');
        loadConstants(1);
    });

    $statusFilter.on('change', function(){
        state.status = $(this).val() || '';
        loadConstants(1);
    });

    $('#btnResetFilters').on('click', function(){
        $search.val('');
        $clearSearch.addClass('d-none');
        $statusFilter.val('').trigger('change');

        state.search = '';
        state.status = '';

        loadConstants(1);
    });


    // Pagination click
    $pagination.on('click', 'a.page-link', function(e){
        e.preventDefault();
        const p = parseInt($(this).data('page'), 10);
        if(!p || p < 1) return;
        loadConstants(p);
    });

    // ===== Init
    loadConstants(1);

})();
</script>
@endpush
