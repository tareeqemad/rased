@extends('layouts.admin')

@section('title', 'إدارة الأرقام المصرح بها')

@php
    $breadcrumbTitle = 'إدارة الأرقام المصرح بها';
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
@endpush

@section('content')
<div class="general-page" id="authorizedPhonesPage" data-index-url="{{ route('admin.authorized-phones.index') }}">
    <div class="row g-3">
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-phone me-2"></i>
                            إدارة الأرقام المصرح بها
                        </h5>
                        <div class="general-subtitle">
                            إدارة أرقام الجوالات المسموح لها بالتسجيل في المنصة
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.authorized-phones.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>
                            إضافة رقم جديد
                        </a>
                    </div>
                </div>

                <div class="card-body pb-4">
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
                                <div class="col-lg-8">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-search me-1"></i>
                                        بحث
                                    </label>
                                    <div class="general-search">
                                        <i class="bi bi-search"></i>
                                        <input type="text" id="searchInput" class="form-control" 
                                               placeholder="ابحث بالرقم أو الاسم أو الملاحظات..." 
                                               autocomplete="off">
                                        <button type="button" class="general-clear" id="btnClearSearch" title="إلغاء البحث" style="display: none;">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-lg-4 d-flex align-items-end">
                                    <div class="d-flex gap-2 w-100">
                                        <button type="button" class="btn btn-primary flex-fill" id="btnSearch">
                                            <i class="bi bi-search me-1"></i>
                                            بحث
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="table-responsive" id="phonesTableContainer">
                        <table class="table table-hover align-middle mb-0 general-table">
                            <thead>
                                <tr>
                                    <th style="min-width:80px;">#</th>
                                    <th style="min-width:150px;">رقم الجوال</th>
                                    <th>الاسم</th>
                                    <th class="d-none d-md-table-cell">الملاحظات</th>
                                    <th class="text-center" style="min-width:100px;">الحالة</th>
                                    <th class="text-center d-none d-lg-table-cell" style="min-width:120px;">تاريخ الإضافة</th>
                                    <th style="min-width:140px;" class="text-center">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="phonesTableBody">
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="data-table-loading">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">جاري التحميل...</span>
                                            </div>
                                            <p class="mt-2 text-muted">جاري تحميل البيانات...</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="paginationContainer" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/js/data-table-loading.js') }}"></script>
<script>
(function() {
    const indexUrl = '{{ route("admin.authorized-phones.index") }}';
    let currentPage = 1;
    let searchTimeout;
    const $search = document.getElementById('searchInput');
    const $clearSearch = document.getElementById('btnClearSearch');
    const $btnSearch = document.getElementById('btnSearch');
    const $container = document.getElementById('phonesTableContainer');

    function loadPhones(page = 1) {
        const search = $search.value.trim();
        const url = new URL(indexUrl);
        url.searchParams.set('ajax', '1');
        url.searchParams.set('per_page', '15');
        url.searchParams.set('page', page);
        if (search) {
            url.searchParams.set('search', search);
        }

        const tbody = document.getElementById('phonesTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="data-table-loading">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">جاري التحميل...</p>
                    </div>
                </td>
            </tr>
        `;

        if (window.DataTableLoading) {
            window.DataTableLoading.show($container);
        }

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (window.DataTableLoading) {
                window.DataTableLoading.hide($container);
            }
            if (data.ok && data.data) {
                renderPhones(data.data);
                renderPagination(data.meta);
                currentPage = page;
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-danger">حدث خطأ أثناء تحميل البيانات</td></tr>';
            }
        })
        .catch(err => {
            console.error('Error:', err);
            if (window.DataTableLoading) {
                window.DataTableLoading.hide($container);
            }
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-danger">حدث خطأ أثناء تحميل البيانات</td></tr>';
        });
    }

    function renderPhones(phones) {
        const tbody = document.getElementById('phonesTableBody');
        if (phones.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        لا توجد أرقام مسجلة
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = phones.map((phone, index) => {
            const createdDate = new Date(phone.created_at).toLocaleDateString('ar-EG', {
                year: 'numeric',
                month: 'numeric',
                day: 'numeric'
            });
            const statusBadge = phone.is_active 
                ? '<span class="badge bg-success">مفعل</span>'
                : '<span class="badge bg-danger">معطل</span>';
            
            return `
                <tr>
                    <td class="text-nowrap">${(currentPage - 1) * 15 + index + 1}</td>
                    <td class="text-nowrap"><strong>${phone.phone}</strong></td>
                    <td>${phone.name || '<span class="text-muted">—</span>'}</td>
                    <td class="d-none d-md-table-cell"><small class="text-muted">${phone.notes || '—'}</small></td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-center d-none d-lg-table-cell"><small class="text-muted">${createdDate}</small></td>
                    <td class="text-center">
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="${indexUrl.replace('/index', '')}/${phone.id}/edit" class="btn btn-sm btn-outline-primary" title="تعديل">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button onclick="deletePhone(${phone.id})" class="btn btn-sm btn-outline-danger" title="حذف">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function renderPagination(meta) {
        const container = document.getElementById('paginationContainer');
        if (meta.last_page <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '<div class="d-flex flex-wrap justify-content-between align-items-center gap-2">';
        html += `<div class="small text-muted">`;
        if (meta.total > 0) {
            html += `عرض ${meta.from} - ${meta.to} من ${meta.total}`;
        } else {
            html += '—';
        }
        html += '</div>';
        html += '<nav><ul class="pagination mb-0">';
        
        // Previous
        if (meta.current_page > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadPhones(${meta.current_page - 1}); return false;">السابق</a></li>`;
        } else {
            html += '<li class="page-item disabled"><span class="page-link">السابق</span></li>';
        }

        // Pages
        for (let i = 1; i <= meta.last_page; i++) {
            if (i === meta.current_page) {
                html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="loadPhones(${i}); return false;">${i}</a></li>`;
            }
        }

        // Next
        if (meta.current_page < meta.last_page) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadPhones(${meta.current_page + 1}); return false;">التالي</a></li>`;
        } else {
            html += '<li class="page-item disabled"><span class="page-link">التالي</span></li>';
        }

        html += '</ul></nav></div>';
        container.innerHTML = html;
    }

    // Search functionality
    if ($btnSearch) {
        $btnSearch.addEventListener('click', function() {
            loadPhones(1);
        });
    }

    if ($search) {
        $search.addEventListener('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                loadPhones(1);
            }
        });

        $search.addEventListener('input', function() {
            if ($search.value.trim()) {
                $clearSearch.style.display = 'block';
            } else {
                $clearSearch.style.display = 'none';
            }
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadPhones(1);
            }, 500);
        });
    }

    if ($clearSearch) {
        $clearSearch.addEventListener('click', function() {
            $search.value = '';
            $clearSearch.style.display = 'none';
            loadPhones(1);
        });
    }

    // Make loadPhones global
    window.loadPhones = loadPhones;

    // Delete function
    window.deletePhone = function(id) {
        if (!confirm('هل أنت متأكد من حذف هذا الرقم؟')) {
            return;
        }

        fetch(`${indexUrl.replace('/index', '')}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (window.adminNotifications && typeof window.adminNotifications.success === 'function') {
                    window.adminNotifications.success(data.message);
                } else {
                    alert(data.message);
                }
                loadPhones(currentPage);
            } else {
                alert(data.message || 'حدث خطأ أثناء الحذف');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('حدث خطأ أثناء الحذف');
        });
    };

    // Initial load
    loadPhones(1);
})();
</script>
@endpush
