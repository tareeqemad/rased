{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'إدارة المستخدمين')

@php
    $breadcrumbTitle = 'إدارة المستخدمين';

    $isSuperAdmin   = auth()->user()->isSuperAdmin();
    $isCompanyOwner = auth()->user()->isCompanyOwner();

    // Meta for roles (JS uses it to render badges + labels)
    $roleMeta = [
        'company_owner' => ['label' => 'مشغل',        'badge' => 'badge-role-owner'],
        'employee'      => ['label' => 'موظف',        'badge' => 'badge-role-employee'],
        'technician'    => ['label' => 'فني',         'badge' => 'badge-role-tech'],
        'admin'         => ['label' => 'سلطة الطاقة',  'badge' => 'badge-role-admin'],
        'super_admin'   => ['label' => 'مدير النظام',  'badge' => 'badge-role-sa'],
    ];

    // Roles visible in filters
    $filterRoleKeys = $isSuperAdmin ? array_keys($roleMeta) : ['employee','technician'];

    // Roles allowed in create modal
    $createRoleKeys = $isSuperAdmin ? ['company_owner','admin','employee','technician'] : ['employee','technician'];
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/users.css') }}">
@endpush

@section('content')
<div class="general-page"
     id="usersPage"
     data-index-url="{{ route('admin.users.index') }}"
     data-users-base-url="{{ route('admin.users.index') }}"
     data-operators-search-url="{{ route('admin.users.ajaxOperators') }}"
     data-is-super-admin="{{ $isSuperAdmin ? 1 : 0 }}"
     data-is-company-owner="{{ $isCompanyOwner ? 1 : 0 }}">

    <div class="row g-3">
        <div class="col-12">
            <div class="general-card">

                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-people me-2"></i>
                            إدارة المستخدمين
                        </h5>
                        <div class="general-subtitle">
                            إدارة الحسابات حسب الصلاحيات والارتباط بالمشغل.
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        @can('create', App\Models\User::class)
                            <button type="button" class="btn btn-primary" id="btnOpenCreate">
                                <i class="bi bi-plus-lg me-1"></i>
                                إضافة مستخدم
                            </button>
                        @endcan
                    </div>
                </div>

                <div class="card-body pb-4">
                    @if($isCompanyOwner)
                        <div class="users-note mb-3">
                            <i class="bi bi-shield-lock me-1"></i>
                            أنت ترى وتدير فقط <strong>الموظفين والفنيين</strong> التابعين لمشغّلك.
                        </div>
                    @endif


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
                                <div class="col-lg-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-person me-1"></i>
                                        الاسم
                                    </label>
                                    <input type="text" class="form-control" id="nameFilter" placeholder="ابحث بالاسم..." autocomplete="off">
                                </div>

                                <div class="col-lg-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-person-badge me-1"></i>
                                        اسم المستخدم
                                    </label>
                                    <input type="text" class="form-control" id="usernameFilter" placeholder="ابحث باسم المستخدم..." autocomplete="off">
                                </div>

                                <div class="col-lg-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-envelope me-1"></i>
                                        البريد الإلكتروني
                                    </label>
                                    <input type="text" class="form-control" id="emailFilter" placeholder="ابحث بالبريد الإلكتروني..." autocomplete="off">
                                </div>

                                <div class="col-lg-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-shield-check me-1"></i>
                                        الدور
                                    </label>
                                    <select class="form-select" id="roleFilter">
                                        <option value="">الكل</option>
                                        @foreach($filterRoleKeys as $rk)
                                            <option value="{{ $rk }}">{{ $roleMeta[$rk]['label'] ?? $rk }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                @if($isSuperAdmin)
                                    <div class="col-lg-3" id="operatorFilterWrap">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-building me-1"></i>
                                            المشغل
                                        </label>
                                        <select class="form-select" id="operatorFilter"></select>
                                        <div class="form-text small">فلترة الموظفين/الفنيين حسب مشغل معيّن.</div>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="row g-3 mt-2">
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-primary" id="btnSearch">
                                            <i class="bi bi-search me-1"></i>
                                            بحث
                                        </button>
                                        <button class="btn btn-outline-secondary" id="btnResetFilters" title="تفريغ الحقول">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                                            تفريغ الحقول
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="table-responsive" id="usersTableContainer">
                        <table class="table table-hover align-middle mb-0 general-table">
                            <thead>
                            <tr>
                                <th style="min-width:220px;">الاسم</th>
                                <th>اسم المستخدم</th>
                                <th>البريد الإلكتروني</th>
                                <th class="text-center">الدور</th>
                                <th class="text-center">المشغل</th>
                                <th class="text-center">عدد الموظفين</th>
                                <th style="min-width:140px;" class="text-center">الإجراءات</th>
                            </tr>
                            </thead>
                            <tbody id="usersTbody">
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
                        <div class="small text-muted" id="usersMeta">—</div>
                        <nav>
                            <ul class="pagination mb-0" id="usersPagination"></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Modal (AJAX) --}}
    @can('create', App\Models\User::class)
    <div class="modal fade" id="userCreateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-plus me-1"></i>
                        إضافة مستخدم
                    </h5>
                    <button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body pt-2">
                    <form id="userCreateForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">الاسم <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">اسم المستخدم <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">البريد الإلكتروني <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">الدور <span class="text-danger">*</span></label>
                                <select name="role" class="form-select" id="createRole">
                                    <option value="">اختر الدور</option>
                                    @foreach($createRoleKeys as $rk)
                                        <option value="{{ $rk }}">{{ $roleMeta[$rk]['label'] ?? $rk }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            @if($isSuperAdmin)
                                <div class="col-md-12 d-none" id="createOperatorWrap">
                                    <label class="form-label fw-semibold">المشغل</label>
                                    <select name="operator_id" class="form-select" id="createOperatorSelect"></select>
                                    <div class="form-text">مطلوب عند إنشاء موظف/فني (حتى نربطه بالمشغل).</div>
                                    <div class="invalid-feedback"></div>
                                </div>
                            @endif

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">كلمة المرور <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" minlength="8">
                                <div class="form-text">8 أحرف على الأقل.</div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control" minlength="8">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" id="btnSubmitCreate">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="createSpinner"></span>
                        حفظ
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endcan

    {{-- Delete confirm --}}
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="bi bi-trash3 me-1"></i>
                        تأكيد الحذف
                    </h5>
                    <button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <div class="text-muted">هل أنت متأكد من حذف المستخدم:</div>
                    <div class="fw-bold mt-2" id="deleteUserName">—</div>
                    <div class="text-danger small mt-2">هذا الإجراء لا يمكن التراجع عنه.</div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmDelete">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="deleteSpinner"></span>
                        حذف
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    {{-- Select2 --}}
    <script src="{{ asset('assets/admin/js/data-table-loading.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/select2/select2.min.js') }}"></script>
    @if(file_exists(public_path('assets/admin/libs/select2/i18n/ar.js')))
        <script src="{{ asset('assets/admin/libs/select2/i18n/ar.js') }}"></script>
    @endif

    <script>
        (function () {
            const ROLE_META = @json($roleMeta);

            const $page = $('#usersPage');
            const INDEX_URL = $page.data('index-url');
            const USERS_BASE_URL = $page.data('users-base-url'); // /admin/users
            const OPERATORS_SEARCH_URL = $page.data('operators-search-url'); // /admin/operators
            const IS_SUPER_ADMIN = parseInt($page.data('is-super-admin'), 10) === 1;

            // Toast helper (uses your notifications.js)
            function notify(type, message){
                if (window.adminNotifications && typeof window.adminNotifications[type] === 'function') {
                    window.adminNotifications[type](message);
                    return;
                }
                // fallback
                if(type === 'error') console.error(message);
                else console.log(message);
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

            // CSRF for AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });

            const state = {
                name: '',
                username: '',
                email: '',
                role: '',
                operator_id: 0,
                page: 1,
            };

            const $container = $('#usersTableContainer');
            const $tbody = $('#usersTbody');
            const $meta = $('#usersMeta');
            const $pagination = $('#usersPagination');

            const $statTotal = $('#statTotal');
            const $statOwners = $('#statOwners');
            const $statAdmins = $('#statAdmins');
            const $statEmployees = $('#statEmployees');
            const $statTechnicians = $('#statTechnicians');

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

            function roleBadge(roleKey, roleLabelFromServer){
                const meta = ROLE_META[roleKey] || {label: roleLabelFromServer || roleKey, badge: ''};
                const label = meta.label || roleLabelFromServer || roleKey;
                const cls = meta.badge ? meta.badge : 'badge-soft';
                return `<span class="badge-soft ${escapeHtml(cls)}">${escapeHtml(label)}</span>`;
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

                const html = rows.map(u => {
                    const name = u.name || '-';
                    const username = u.username || '-';
                    const email = u.email || '-';
                    const roleKey = u.role || u.role_key || '';
                    const roleLabel = u.role_label || '';
                    const operatorName = u.operator || u.operator_name || (u.operator && u.operator.name) || '';
                    const employeesCount = (u.employees_count !== undefined && u.employees_count !== null) ? u.employees_count : '-';

                    const initials = (name && name !== '-') ? name.trim().charAt(0) : '?';

                    const showUrl = (u.urls && u.urls.show) ? u.urls.show : `${USERS_BASE_URL}/${u.id}`;
                    const editUrl = (u.urls && u.urls.edit) ? u.urls.edit : `${USERS_BASE_URL}/${u.id}/edit`;
                    const permsUrl = (u.urls && u.urls.permissions) ? u.urls.permissions : `${USERS_BASE_URL}/${u.id}/permissions`;

                    // can flags (optional) - if not provided, show buttons and let server protect
                    const canView = (u.can && u.can.view !== undefined) ? !!u.can.view : true;
                    const canEdit = (u.can && u.can.update !== undefined) ? !!u.can.update : true;
                    const canDelete = (u.can && u.can.delete !== undefined) ? !!u.can.delete : true;
                    const isSuperAdmin = {{ $isSuperAdmin ? 'true' : 'false' }};
                    const currentUserId = parseInt({{ auth()->id() }}, 10);
                    const targetUserId = parseInt(u.id, 10);

                    const operatorCell = operatorName
                        ? `<span class="badge-soft">${escapeHtml(operatorName)}</span>`
                        : `<span class="text-muted">-</span>`;

                    const employeesCell = (roleKey === 'company_owner')
                        ? `<span class="fw-bold">${escapeHtml(employeesCount)}</span>`
                        : `<span class="text-muted">-</span>`;

                    // زر الدخول بحساب (للسوبر أدمن فقط وليس لنفسه)
                    const impersonateBtn = (isSuperAdmin && targetUserId !== currentUserId) ? `
                        <form action="${escapeHtml(USERS_BASE_URL)}/${escapeHtml(u.id)}/impersonate" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الدخول بحساب ${escapeHtml(name)}؟');">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="submit" class="btn btn-light btn-icon text-info" title="الدخول بحساب هذا المستخدم">
                                <i class="bi bi-person-check"></i>
                            </button>
                        </form>
                    ` : '';

                    // زر toggle status (للسوبر أدمن والمشغل - وليس لنفسه)
                    const userStatus = (u.status || 'active');
                    const isCompanyOwner = {{ $isCompanyOwner ? 'true' : 'false' }};
                    const canToggleStatus = (isSuperAdmin || isCompanyOwner) && targetUserId !== currentUserId;
                    const toggleStatusBtn = canToggleStatus ? `
                        <button type="button" class="btn btn-light btn-icon text-${userStatus === 'active' ? 'warning' : 'success'} btn-toggle-status"
                                data-id="${escapeHtml(u.id)}"
                                data-status="${escapeHtml(userStatus)}"
                                data-name="${escapeHtml(name)}"
                                title="${userStatus === 'active' ? 'إيقاف' : 'تفعيل'}">
                            <i class="bi bi-${userStatus === 'active' ? 'pause' : 'play'}-fill"></i>
                        </button>
                    ` : '';

                    return `
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-pill flex-shrink-0">${escapeHtml(initials)}</div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">${escapeHtml(name)}</div>
                                        <div class="small text-muted">#${escapeHtml(u.id)}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="fw-semibold">${escapeHtml(username)}</td>
                            <td>${escapeHtml(email)}</td>
                            <td>${roleBadge(roleKey, roleLabel)}</td>
                            <td>${operatorCell}</td>
                            <td>${employeesCell}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    ${canView ? `<a class="btn btn-light btn-icon" href="${escapeHtml(showUrl)}" title="عرض"><i class="bi bi-eye"></i></a>` : ``}
                                    ${canEdit ? `<a class="btn btn-light btn-icon" href="${escapeHtml(editUrl)}" title="تعديل"><i class="bi bi-pencil"></i></a>` : ``}
                                    <a class="btn btn-light btn-icon" href="${escapeHtml(permsUrl)}" title="الصلاحيات"><i class="bi bi-shield-check"></i></a>
                                    ${impersonateBtn}
                                    ${toggleStatusBtn}
                                    ${canDelete && (targetUserId !== currentUserId) ? `
                                        <button type="button" class="btn btn-light btn-icon text-danger btn-delete-user"
                                                data-id="${escapeHtml(u.id)}"
                                                data-name="${escapeHtml(name)}"
                                                title="حذف">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    ` : ``}
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

                const current = meta.current_page || 1;
                const last = meta.last_page || 1;

                const makeItem = (page, label, disabled=false, active=false) => `
                    <li class="page-item ${disabled?'disabled':''} ${active?'active':''}">
                        <a class="page-link" href="#" data-page="${page}">${label}</a>
                    </li>
                `;

                let html = '';
                html += makeItem(current-1, '‹', current <= 1);

                // window pages
                const start = Math.max(1, current - 2);
                const end = Math.min(last, current + 2);

                if(start > 1) html += makeItem(1, '1', false, current === 1);
                if(start > 2) html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;

                for(let p=start; p<=end; p++){
                    html += makeItem(p, String(p), false, p === current);
                }

                if(end < last - 1) html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
                if(end < last) html += makeItem(last, String(last), false, current === last);

                html += makeItem(current+1, '›', current >= last);

                $pagination.html(html);
            }

            function renderStats(stats){
                if(!stats) return;

                if($statTotal.length) $statTotal.text(stats.total ?? '—');
                if($statEmployees.length) $statEmployees.text(stats.employees ?? '—');
                if($statTechnicians.length) $statTechnicians.text(stats.technicians ?? '—');

                if($statOwners.length) $statOwners.text(stats.company_owners ?? '—');
                if($statAdmins.length) $statAdmins.text(stats.admins ?? '—');
            }

            function loadUsers(page=1){
                state.page = page;

                setLoading(true);
                $.ajax({
                    url: INDEX_URL,
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        ajax: 1,
                        name: state.name,
                        username: state.username,
                        email: state.email,
                        role: state.role,
                        operator_id: state.operator_id,
                        page: state.page,
                    },
                    success: function(resp){
                        // Expected: { ok:true, data:[], meta:{}, stats:{} }
                        if(resp && resp.ok === false){
                            notify('error', resp.message || 'حدث خطأ أثناء جلب البيانات');
                            renderEmpty('تعذر تحميل البيانات');
                            return;
                        }

                        renderRows(resp.data || []);
                        renderMeta(resp.meta || {});
                        renderPagination(resp.meta || {});
                        renderStats(resp.stats || {});
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
            const $nameFilter = $('#nameFilter');
            const $usernameFilter = $('#usernameFilter');
            const $emailFilter = $('#emailFilter');
            const $roleFilter = $('#roleFilter');

            const doSearch = function(){
                state.name = $nameFilter.val().trim();
                state.username = $usernameFilter.val().trim();
                state.email = $emailFilter.val().trim();
                state.role = $roleFilter.val() || '';
                
                if(IS_SUPER_ADMIN){
                    const val = $('#operatorFilter').val();
                    // Select2 قد يعيد '' أو null
                    state.operator_id = (val && val !== '' && val !== null) ? parseInt(val, 10) : 0;
                }
                
                loadUsers(1);
            };

            // Search on button click
            $('#btnSearch').on('click', doSearch);
            
            // Search on Enter key in any filter field
            $nameFilter.add($usernameFilter).add($emailFilter).on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    doSearch();
                }
            });

            $roleFilter.on('change', function(){
                const role = $(this).val() || '';

                // SuperAdmin: operator filter is meaningful mostly for employee/technician/all
                if(IS_SUPER_ADMIN){
                    const shouldShowOperator = (role === '' || role === 'employee' || role === 'technician');
                    $('#operatorFilterWrap').toggleClass('d-none', !shouldShowOperator);

                    if(!shouldShowOperator){
                        try { $('#operatorFilter').val(null).trigger('change'); } catch(e){}
                    }
                }
                // لا نقوم بتحميل البيانات تلقائياً - فقط عند الضغط على زر البحث
            });

            $('#btnResetFilters').on('click', function(){
                $nameFilter.val('');
                $usernameFilter.val('');
                $emailFilter.val('');
                $roleFilter.val('').trigger('change');

                state.name = '';
                state.username = '';
                state.email = '';
                state.role = '';
                state.operator_id = 0;

                if(IS_SUPER_ADMIN){
                    try { $('#operatorFilter').val(null).trigger('change'); } catch(e){}
                    $('#operatorFilterWrap').removeClass('d-none');
                }

                // تفريغ الحقول فقط - لا تحميل تلقائي
                // يمكن للمستخدم الضغط على "بحث" بعد تفريغ الحقول
            });

            // Pagination click
            $pagination.on('click', 'a.page-link', function(e){
                e.preventDefault();
                const p = parseInt($(this).data('page'), 10);
                if(!p || p < 1) return;
                loadUsers(p);
            });

            // ===== Select2: operator filter (super admin)
            if(IS_SUPER_ADMIN){
                // init empty select2
                $('#operatorFilter').select2({
                    dir: 'rtl',
                    width: '100%',
                    placeholder: 'ابحث عن مشغل...',
                    allowClear: true,
                    language: 'ar',
                    ajax: {
                        url: OPERATORS_SEARCH_URL,
                        dataType: 'json',
                        delay: 250,
                        data: function(params){
                            return { ajax: 1, term: params.term || '', page: params.page || 1 };
                        },
                        processResults: function(resp, params){
                            // Accept either {results:[{id,text}]} OR {data:[{id,name}], meta:{...}}
                            if(resp && resp.results){
                                return resp;
                            }

                            const items = (resp && resp.data) ? resp.data : [];
                            const results = items.map(o => ({
                                id: o.id,
                                text: o.text || o.name || ('مشغل #' + o.id),
                            }));

                            const more = resp && resp.meta ? (resp.meta.current_page < resp.meta.last_page) : false;
                            return { results: results, pagination: { more: more } };
                        }
                    }
                });

                $('#operatorFilter').on('change', function(){
                    // لا نقوم بتحميل البيانات تلقائياً - فقط عند الضغط على زر البحث
                    // القيمة ستُقرأ في doSearch()
                });

                // hide initially only if role is not eligible
                $('#operatorFilterWrap').removeClass('d-none');
            }

            // ===== Create Modal (AJAX)
            @can('create', App\Models\User::class)
                const createModalEl = document.getElementById('userCreateModal');
                const createModal = createModalEl ? new bootstrap.Modal(createModalEl) : null;

                const $createForm = $('#userCreateForm');
                const $createRole = $('#createRole');
                const $createSpinner = $('#createSpinner');

                function clearCreateErrors(){
                    $createForm.find('.is-invalid').removeClass('is-invalid');
                    $createForm.find('.invalid-feedback').text('');
                }

                function setCreateLoading(on){
                    $('#btnSubmitCreate').prop('disabled', on);
                    $createSpinner.toggleClass('d-none', !on);
                }

                function showCreateError(field, msg){
                    const $input = $createForm.find(`[name="${field}"]`);
                    if(!$input.length) return;
                    $input.addClass('is-invalid');
                    $input.closest('.col-md-6, .col-md-12, .col-12').find('.invalid-feedback').first().text(msg);
                }

                function toggleOperatorInCreate(){
                    if(!IS_SUPER_ADMIN) return;

                    const role = $createRole.val();
                    const shouldShow = (role === 'employee' || role === 'technician');

                    $('#createOperatorWrap').toggleClass('d-none', !shouldShow);

                    if(!shouldShow){
                        try { $('#createOperatorSelect').val(null).trigger('change'); } catch(e){}
                    }
                }

                $('#btnOpenCreate').on('click', function(){
                    clearCreateErrors();
                    $createForm[0].reset();

                    if(IS_SUPER_ADMIN){
                        toggleOperatorInCreate();
                    }

                    if(createModal) createModal.show();
                });

                $createRole.on('change', function(){
                    toggleOperatorInCreate();
                });

                // Select2 for operator inside create modal (super admin)
                if(IS_SUPER_ADMIN){
                    $('#createOperatorSelect').select2({
                        dropdownParent: $('#userCreateModal'),
                        dir: 'rtl',
                        width: '100%',
                        placeholder: 'ابحث عن مشغل...',
                        allowClear: true,
                        language: 'ar',
                        ajax: {
                            url: OPERATORS_SEARCH_URL,
                            dataType: 'json',
                            delay: 250,
                            data: function(params){
                                return { ajax: 1, term: params.term || '', page: params.page || 1 };
                            },
                            processResults: function(resp){
                                if(resp && resp.results){
                                    return resp;
                                }
                                const items = (resp && resp.data) ? resp.data : [];
                                const results = items.map(o => ({ id: o.id, text: o.text || o.name || ('مشغل #' + o.id) }));
                                return { results };
                            }
                        }
                    });
                }

                $('#btnSubmitCreate').on('click', function(){
                    clearCreateErrors();
                    setCreateLoading(true);

                    $.ajax({
                        url: USERS_BASE_URL,
                        method: 'POST',
                        dataType: 'json',
                        data: $createForm.serialize(),
                        success: function(resp){
                            notify('success', resp.message || 'تم إنشاء المستخدم بنجاح');
                            if(createModal) createModal.hide();
                            loadUsers(1);
                        },
                        error: function(xhr){
                            if(xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors){
                                const errs = xhr.responseJSON.errors;
                                Object.keys(errs).forEach(k => showCreateError(k, (errs[k] && errs[k][0]) ? errs[k][0] : 'خطأ'));
                                notify('error', 'تحقق من البيانات المدخلة');
                                return;
                            }

                            const msg = (xhr.responseJSON && xhr.responseJSON.message)
                                ? xhr.responseJSON.message
                                : 'تعذر حفظ المستخدم';
                            notify('error', msg);
                        },
                        complete: function(){
                            setCreateLoading(false);
                        }
                    });
                });
            @endcan

            // ===== Delete (AJAX)
            const deleteModalEl = document.getElementById('deleteUserModal');
            const deleteModal = deleteModalEl ? new bootstrap.Modal(deleteModalEl) : null;
            let pendingDeleteId = null;

            const $deleteName = $('#deleteUserName');
            const $deleteSpinner = $('#deleteSpinner');

            function setDeleteLoading(on){
                $('#btnConfirmDelete').prop('disabled', on);
                $deleteSpinner.toggleClass('d-none', !on);
            }

            // Toggle status handler
            $tbody.on('click', '.btn-toggle-status', function(){
                const $btn = $(this);
                const id = $btn.data('id');
                const currentStatus = $btn.data('status');
                const name = $btn.data('name');
                const action = currentStatus === 'active' ? 'إيقاف' : 'تفعيل';
                
                if (!confirm(`هل أنت متأكد من ${action} المستخدم "${name}"؟`)) {
                    return;
                }

                $.ajax({
                    url: `${USERS_BASE_URL}/${id}/toggle-status`,
                    method: 'POST',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(resp){
                        if(resp.ok){
                            notify('success', resp.message || `تم ${action} المستخدم بنجاح`);
                            loadUsers(state.page || 1);
                        } else {
                            notify('error', resp.message || 'حدث خطأ');
                        }
                    },
                    error: function(xhr){
                        const msg = (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : 'حدث خطأ أثناء تغيير الحالة';
                        notify('error', msg);
                    }
                });
            });

            $tbody.on('click', '.btn-delete-user', function(){
                const id = $(this).data('id');
                const name = $(this).data('name');

                pendingDeleteId = id;
                $deleteName.text(name || '—');

                if(deleteModal) deleteModal.show();
            });

            $('#btnConfirmDelete').on('click', function(){
                if(!pendingDeleteId) return;

                setDeleteLoading(true);

                $.ajax({
                    url: `${USERS_BASE_URL}/${pendingDeleteId}`,
                    method: 'POST',
                    dataType: 'json',
                    data: { _method: 'DELETE' },
                    success: function(resp){
                        notify('success', resp.message || 'تم حذف المستخدم');
                        if(deleteModal) deleteModal.hide();
                        pendingDeleteId = null;
                        loadUsers(1);
                    },
                    error: function(xhr){
                        const msg = (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : 'تعذر حذف المستخدم';
                        notify('error', msg);
                    },
                    complete: function(){
                        setDeleteLoading(false);
                    }
                });
            });

            // ===== Init
            loadUsers(1);

        })();
    </script>
@endpush
