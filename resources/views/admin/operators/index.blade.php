@extends('layouts.admin')

@section('title', auth()->user()->isSuperAdmin() ? 'إدارة المشغلين' : 'المشغل')
@php
    $breadcrumbTitle = auth()->user()->isSuperAdmin() ? 'إدارة المشغلين' : 'المشغل';
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/css/operators.css') }}">
@endpush

@section('content')
<div class="operators-page">

    @if(auth()->user()->isSuperAdmin())
        <div class="row">
            <div class="col-12">
                <div class="card op-card">
                    <div class="op-card-header">
                        <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between">
                            <div>
                                <div class="op-title">
                                    <i class="bi bi-buildings me-2"></i>
                                    إدارة المشغلين
                                </div>
                                <div class="op-subtitle">
                                    بحث + إضافة/تعديل/حذف بدون Reload (Server‑Side AJAX).
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                @can('create', App\Models\Operator::class)
                                    <button class="btn btn-primary" id="openCreateOperator"
                                            data-url="{{ route('admin.operators.create') }}">
                                        <i class="bi bi-plus-circle me-1"></i>
                                        إضافة مشغل
                                    </button>
                                @endcan
                            </div>
                        </div>

                        <div class="op-toolbar mt-3">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-6">
                                    <div class="op-search">
                                        <i class="bi bi-search"></i>
                                        <input type="text" id="opSearch" class="form-control"
                                               placeholder="ابحث بالاسم / رقم الوحدة / اسم المستخدم..."
                                               value="{{ $q ?? '' }}">
                                        <button type="button" class="btn op-clear" id="opClearBtn" title="مسح البحث">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <select id="opStatus" class="form-select">
                                        <option value="">كل الحالات</option>
                                        <option value="active" {{ ($status ?? '') === 'active' ? 'selected' : '' }}>فعّال</option>
                                        <option value="inactive" {{ ($status ?? '') === 'inactive' ? 'selected' : '' }}>غير فعّال</option>
                                    </select>
                                </div>

                                <div class="col-lg-3 d-flex justify-content-lg-end">
                                    <button class="btn btn-outline-secondary w-100 w-lg-auto" id="opRefreshBtn">
                                        <i class="bi bi-arrow-clockwise me-1"></i>
                                        تحديث
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- list (body + footer pagination) --}}
                    <div id="operatorsListWrap">
                        @include('admin.operators.partials.list', ['operators' => $operators])
                    </div>

                    {{-- Loading overlay --}}
                    <div class="op-loading d-none" id="opLoading">
                        <div class="text-center">
                            <div class="spinner-border" role="status"></div>
                            <div class="mt-2 text-muted fw-semibold">جاري التحميل...</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Modal: Create/Edit --}}
        <div class="modal fade" id="operatorModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content op-modal">
                    <div class="modal-header">
                        <h5 class="modal-title" id="operatorModalTitle">...</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="operatorModalBody">
                        <div class="py-5 text-center text-muted">
                            <div class="spinner-border" role="status"></div>
                            <div class="mt-2">جاري التحميل...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Confirm Delete --}}
        <div class="modal fade" id="operatorDeleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content op-modal">
                    <div class="modal-header">
                        <h5 class="modal-title">تأكيد الحذف</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-muted">هل أنت متأكد من حذف المشغل:</div>
                        <div class="fw-bold mt-1" id="deleteOpName">—</div>
                        <div class="alert alert-warning mt-3 mb-0">
                            سيتم حذف حساب المستخدم المرتبط أيضًا.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <button class="btn btn-danger" id="confirmDeleteOperatorBtn">
                            <i class="bi bi-trash me-1"></i>
                            حذف
                        </button>
                    </div>
                </div>
            </div>
        </div>

    @else
        {{-- CompanyOwner / Employee / Technician --}}
        <div class="row g-3">
            <div class="col-12 col-lg-4">
                <div class="card op-card">
                    <div class="op-card-header">
                        <div class="op-title">
                            <i class="bi bi-building me-2"></i>
                            مشغلي
                        </div>
                        <div class="op-subtitle">عرض سريع + روابط للإدارة حسب صلاحياتك.</div>
                    </div>

                    <div class="card-body">
                        @if($myOperator)
                            <div class="op-kv">
                                <div class="k">اسم المشغل</div>
                                <div class="v">{{ $myOperator->unit_name ?? $myOperator->name }}</div>
                            </div>
                            <div class="op-kv">
                                <div class="k">رقم الوحدة</div>
                                <div class="v">{{ $myOperator->unit_number ?? '—' }}</div>
                            </div>
                            <div class="op-kv">
                                <div class="k">المحافظة</div>
                                <div class="v">{{ $myOperator->getGovernorateLabel() ?? '—' }}</div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <span class="badge bg-info">
                                    <i class="bi bi-lightning-charge me-1"></i>
                                    {{ $myOperator->generators_count ?? $myOperator->generators()->count() }} مولد
                                </span>
                                <span class="badge bg-success">
                                    <i class="bi bi-people me-1"></i>
                                    {{ $myOperator->employees_count ?? $myOperator->users()->count() }} موظف/فني
                                </span>

                                @if($myOperator->profile_completed)
                                    <span class="badge bg-primary">الملف مكتمل</span>
                                @else
                                    <span class="badge bg-warning text-dark">الملف غير مكتمل</span>
                                @endif
                            </div>

                            <div class="d-grid gap-2 mt-3">
                                @if(auth()->user()->isCompanyOwner())
                                    <a class="btn btn-primary" href="{{ route('admin.operators.profile') }}">
                                        <i class="bi bi-ui-checks-grid me-1"></i>
                                        إكمال/تعديل بيانات المشغل
                                    </a>
                                @endif

                                @can('viewAny', App\Models\User::class)
                                    <a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">
                                        <i class="bi bi-people me-1"></i>
                                        إدارة الموظفين
                                    </a>
                                @endcan

                                @can('viewAny', App\Models\Generator::class)
                                    <a class="btn btn-outline-secondary" href="{{ route('admin.generators.index') }}">
                                        <i class="bi bi-lightning me-1"></i>
                                        المولدات
                                    </a>
                                @endcan
                            </div>
                        @else
                            <div class="text-muted text-center py-4">
                                لا يوجد مشغل مرتبط بحسابك.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-8">
                <div class="card op-card">
                    <div class="op-card-header">
                        <div class="op-title">
                            <i class="bi bi-info-circle me-2"></i>
                            معلومات المشغل
                        </div>
                    </div>
                    <div class="card-body">
                        @if($myOperator)
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="op-kv">
                                        <div class="k">الاسم</div>
                                        <div class="v">{{ $myOperator->unit_name ?? $myOperator->name }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="op-kv">
                                        <div class="k">البريد</div>
                                        <div class="v">{{ $myOperator->email ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="op-kv">
                                        <div class="k">الهاتف</div>
                                        <div class="v">{{ $myOperator->phone ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="op-kv">
                                        <div class="k">الحالة</div>
                                        <div class="v">
                                            @if($myOperator->status === 'active')
                                                <span class="badge bg-success">فعّال</span>
                                            @elseif($myOperator->status === 'inactive')
                                                <span class="badge bg-secondary">غير فعّال</span>
                                            @else
                                                <span class="badge bg-light text-dark">غير محدد</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-muted text-center py-5">
                                —
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/libs/jquery/jquery.min.js') }}"></script>

<script>
(function () {
    // ====== Toast helper (Bootstrap Toast عبر adminNotifications) ======
    function notify(type, msg, title) {
        if (window.adminNotifications && typeof window.adminNotifications[type] === 'function') {
            window.adminNotifications[type](msg, title);
            return;
        }
        // fallback بسيط
        alert(msg);
    }

    // ====== SuperAdmin AJAX only ======
    const isSuperAdmin = @json(auth()->user()->isSuperAdmin());
    if (!isSuperAdmin) return;

    const listUrl = @json(route('admin.operators.index'));
    const modalEl = document.getElementById('operatorModal');
    const modal = new bootstrap.Modal(modalEl);
    const deleteModalEl = document.getElementById('operatorDeleteModal');
    const deleteModal = new bootstrap.Modal(deleteModalEl);

    let deleteUrl = null;

    const $wrap = $('#operatorsListWrap');
    const $loading = $('#opLoading');

    function setLoading(on) {
        $loading.toggleClass('d-none', !on);
    }

    function debounce(fn, ms) {
        let t;
        return function () {
            clearTimeout(t);
            const args = arguments;
            t = setTimeout(() => fn.apply(this, args), ms);
        }
    }

    function currentParams(extra = {}) {
        return Object.assign({
            q: $('#opSearch').val() || '',
            status: $('#opStatus').val() || '',
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
                    wireListEvents(); // re-bind
                } else {
                    notify('error', 'فشل تحميل البيانات');
                }
            },
            error: function () {
                notify('error', 'حدث خطأ أثناء تحميل البيانات');
            },
            complete: function () {
                setLoading(false);
                toggleClearBtn();
            }
        });
    }

    function toggleClearBtn() {
        const has = ($('#opSearch').val() || '').trim().length > 0;
        $('#opClearBtn').toggleClass('d-none', !has);
    }

    // ========== Modal load (create/edit) ==========
    function openOperatorModal(url) {
        $('#operatorModalTitle').text('...');
        $('#operatorModalBody').html(`
            <div class="py-5 text-center text-muted">
                <div class="spinner-border" role="status"></div>
                <div class="mt-2">جاري التحميل...</div>
            </div>
        `);
        modal.show();

        $.ajax({
            url: url,
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (html) {
                $('#operatorModalBody').html(html);
                const title = $('#operatorModalBody').find('[data-modal-title]').attr('data-modal-title') || 'المشغل';
                $('#operatorModalTitle').text(title);
                initOperatorFormAjax();
            },
            error: function () {
                $('#operatorModalBody').html(`<div class="alert alert-danger mb-0">تعذر تحميل النموذج</div>`);
            }
        });
    }

    function clearFormErrors($form) {
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').remove();
    }

    function applyFormErrors($form, errors) {
        // errors: {field: [msg]}
        Object.keys(errors || {}).forEach(function (field) {
            const msg = errors[field][0];
            const $input = $form.find(`[name="${field}"]`);
            if ($input.length) {
                $input.addClass('is-invalid');
                $input.after(`<div class="invalid-feedback">${msg}</div>`);
            }
        });
    }

    function initOperatorFormAjax() {
        const $form = $('#operatorModalBody').find('form[data-ajax-form="operator"]');
        if (!$form.length) return;

        // toggle password
        $form.on('click', '[data-toggle-pass]', function () {
            const $inp = $form.find('#op_password');
            const $icn = $(this).find('i');
            if ($inp.attr('type') === 'password') {
                $inp.attr('type', 'text');
                $icn.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                $inp.attr('type', 'password');
                $icn.removeClass('bi-eye-slash').addClass('bi-eye');
            }
        });

        // send_email enable/disable
        $form.on('input', '#op_email', function () {
            const has = ($(this).val() || '').trim().length > 0;
            const $send = $form.find('#op_send_email');
            if (!has) $send.prop('checked', false);
            $send.prop('disabled', !has);
        }).trigger('input');

        // submit ajax
        $form.on('submit', function (e) {
            e.preventDefault();
            clearFormErrors($form);

            const $btn = $form.find('[data-submit-btn]');
            $btn.prop('disabled', true).addClass('disabled');

            const fd = new FormData(this);

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function (res) {
                    if (res && res.success) {
                        notify('success', res.message || 'تم الحفظ');
                        modal.hide();
                        loadList({ page: 1 });
                    } else {
                        notify('error', res.message || 'فشل الحفظ');
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        const json = xhr.responseJSON || {};
                        applyFormErrors($form, json.errors || {});
                        notify('error', 'تحقق من الحقول المطلوبة');
                        return;
                    }
                    notify('error', 'حدث خطأ غير متوقع');
                },
                complete: function () {
                    $btn.prop('disabled', false).removeClass('disabled');
                }
            });
        });
    }

    // ========== Delete ==========
    function askDelete(name, url) {
        deleteUrl = url;
        $('#deleteOpName').text(name || '—');
        deleteModal.show();
    }

    $('#confirmDeleteOperatorBtn').on('click', function () {
        if (!deleteUrl) return;

        const $btn = $(this);
        $btn.prop('disabled', true);

        $.ajax({
            url: deleteUrl,
            method: 'POST',
            data: { _method: 'DELETE', _token: document.querySelector('meta[name="csrf-token"]').content },
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            success: function (res) {
                if (res && res.success) {
                    notify('success', res.message || 'تم الحذف');
                    deleteModal.hide();
                    loadList();
                } else {
                    notify('error', res.message || 'فشل الحذف');
                }
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'تعذر الحذف';
                notify('error', msg);
            },
            complete: function () {
                $btn.prop('disabled', false);
                deleteUrl = null;
            }
        });
    });

    // ========== Bind list events ==========
    function wireListEvents() {
        // pagination via ajax
        $wrap.find('.pagination a').off('click').on('click', function (e) {
            e.preventDefault();
            const url = $(this).attr('href');
            if (!url) return;
            const u = new URL(url, window.location.origin);
            const page = u.searchParams.get('page') || 1;
            loadList({ page: page });
        });

        // edit modal
        $wrap.find('[data-action="edit-operator"]').off('click').on('click', function (e) {
            e.preventDefault();
            openOperatorModal($(this).data('url'));
        });

        // create modal
        $('#openCreateOperator').off('click').on('click', function () {
            openOperatorModal($(this).data('url'));
        });

        // delete
        $wrap.find('[data-action="delete-operator"]').off('click').on('click', function () {
            askDelete($(this).data('name'), $(this).data('url'));
        });
    }

    // ========== UI events ==========
    $('#opRefreshBtn').on('click', function () { loadList({ page: 1 }); });

    $('#opClearBtn').on('click', function () {
        $('#opSearch').val('');
        toggleClearBtn();
        loadList({ page: 1 });
    });

    $('#opStatus').on('change', function () { loadList({ page: 1 }); });

    $('#opSearch').on('input', debounce(function () {
        loadList({ page: 1 });
    }, 350));

    // initial
    wireListEvents();
    toggleClearBtn();

})();
</script>
@endpush
