@extends('layouts.admin')

@section('title', 'إدارة الأدوار')

@php
    $breadcrumbTitle = 'إدارة الأدوار';
    $isCompanyOwner = auth()->user()->isCompanyOwner();
    $isSuperAdmin = auth()->user()->isSuperAdmin();
    $operator = $isCompanyOwner ? auth()->user()->ownedOperators()->first() : null;
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/roles.css') }}">
@endpush

@section('content')
<div class="general-page" id="rolesPage" data-index-url="{{ route('admin.roles.index') }}">
    <div class="row g-3">
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-shield-check me-2"></i>
                            {{ $isCompanyOwner ? 'أدوار المستخدمين' : 'إدارة الأدوار' }}
                        </h5>
                        <div class="general-subtitle">
                            @if($isCompanyOwner)
                                إدارة الأدوار والصلاحيات لمستخدمي مشغلك
                            @else
                                إدارة وتنظيم أدوار المستخدمين والصلاحيات
                            @endif
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        @can('create', App\Models\Role::class)
                            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i>
                                إضافة دور جديد
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="card-body pb-4">
                    @if($isCompanyOwner && $operator)
                        <div class="alert alert-info mb-4">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-info-circle me-2 fs-5 mt-1"></i>
                                <div class="flex-grow-1">
                                    <strong>ملاحظة:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>الأدوار <strong>النظامية</strong> (موظف، فني) هي أدوار أساسية في النظام ويمكنك استخدامها فقط.</li>
                                        <li>يمكنك إنشاء أدوار <strong>مخصصة</strong> لمستخدمي مشغلك وتحديد الصلاحيات المناسبة لهم.</li>
                                        <li>الأدوار المخصصة الخاصة بك تكون مرتبطة بمشغلك فقط.</li>
                                    </ul>
                                </div>
                            </div>
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
                            <form method="GET" action="{{ route('admin.roles.index') }}" id="searchForm">
                                <div class="row g-3">
                                    <div class="col-lg-8">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-search me-1"></i>
                                            بحث
                                        </label>
                                        <div class="general-search">
                                            <i class="bi bi-search"></i>
                                            <input type="text" name="search" id="rolesSearch" class="form-control" 
                                                   placeholder="اسم الدور / التسمية / الوصف..." 
                                                   value="{{ request('search') }}" autocomplete="off">
                                            @if(request('search'))
                                                <button type="button" class="general-clear" id="btnClearSearch" title="إلغاء البحث">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-lg-4 d-flex align-items-end">
                                        <div class="d-flex gap-2 w-100">
                                            <button type="submit" class="btn btn-primary flex-fill" id="btnSearch">
                                                <i class="bi bi-search me-1"></i>
                                                بحث
                                            </button>
                                            @if(request('search'))
                                                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary" id="btnResetFilters">
                                                    <i class="bi bi-x"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="table-responsive" id="rolesTableContainer">
                        <table class="table table-hover align-middle mb-0 general-table">
                            <thead>
                                <tr>
                                    <th style="min-width:150px;">اسم الدور</th>
                                    <th>التسمية</th>
                                    <th class="d-none d-md-table-cell">الوصف</th>
                                    @if(auth()->user()->isSuperAdmin())
                                        <th class="text-center">المشغل</th>
                                    @endif
                                    <th class="text-center">المستخدمين</th>
                                    <th class="text-center">الصلاحيات</th>
                                    <th class="text-center">النوع</th>
                                    <th style="min-width:140px;" class="text-center">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="rolesTbody">
                                @forelse($roles as $role)
                                    <tr class="{{ $role->is_system ? 'table-light' : '' }}">
                                        <td class="text-nowrap">
                                            @if($isCompanyOwner)
                                                <code class="text-primary fw-bold">{{ $role->name }}</code>
                                            @else
                                                <code class="text-primary fw-bold">{{ $role->name }}</code>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-semibold">{{ $role->label }}</span>
                                                @if($isCompanyOwner && $role->is_system)
                                                    <span class="badge bg-light text-dark border" title="دور نظامي - للقراءة فقط">
                                                        <i class="bi bi-lock-fill me-1"></i>
                                                        نظامي
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            <small class="text-muted">{{ $role->description ?? '-' }}</small>
                                        </td>
                                        @if($isSuperAdmin)
                                            <td class="text-center">
                                                @if($role->operator)
                                                    <span class="badge bg-secondary">{{ $role->operator->name }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        @endif
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $role->users_count }} مستخدم</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success">{{ $role->permissions_count }} صلاحية</span>
                                        </td>
                                        <td class="text-center">
                                            @if($role->is_system)
                                                <span class="badge badge-system">
                                                    <i class="bi bi-shield-check me-1"></i>
                                                    نظامي
                                                </span>
                                            @else
                                                <span class="badge badge-custom">
                                                    <i class="bi bi-gear me-1"></i>
                                                    مخصص
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex gap-2 justify-content-center">
                                                @can('view', $role)
                                                    <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-sm btn-outline-info" title="عرض التفاصيل">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                @endcan
                                                @can('update', $role)
                                                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary" title="{{ $role->is_system && $isCompanyOwner ? 'تعديل محدود (النظامي)' : 'تعديل' }}">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                @endcan
                                                @can('delete', $role)
                                                    @if(!$role->is_system && $role->users_count == 0)
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#deleteModal{{ $role->id }}" 
                                                                title="حذف الدور">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @endif
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>

                                    @can('delete', $role)
                                        @if(!$role->is_system)
                                            <div class="modal fade" id="deleteModal{{ $role->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">تأكيد الحذف</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>هل أنت متأكد من حذف الدور <strong>{{ $role->label }}</strong>؟</p>
                                                            @if($role->users_count > 0)
                                                                <div class="alert alert-warning mb-0">
                                                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                                                    <small>هذا الدور مرتبط بـ {{ $role->users_count }} مستخدم. لا يمكن حذفه.</small>
                                                                </div>
                                                            @else
                                                                <p class="text-danger mb-0"><small>هذا الإجراء لا يمكن التراجع عنه</small></p>
                                                            @endif
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                            @if($role->users_count == 0)
                                                                <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger">حذف</button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endcan
                                @empty
                                    <tr>
                                        <td colspan="{{ $isSuperAdmin ? '8' : '7' }}" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            @if(request('search'))
                                                لا توجد نتائج للبحث "{{ request('search') }}"
                                            @else
                                                @if($isCompanyOwner)
                                                    لا توجد أدوار متاحة. يمكنك إنشاء أدوار مخصصة لمستخدمي مشغلك.
                                                @else
                                                    لا توجد أدوار
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($roles->hasPages())
                        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
                            <div class="small text-muted">
                                @if($roles->total() > 0)
                                    عرض {{ $roles->firstItem() }} - {{ $roles->lastItem() }} من {{ $roles->total() }}
                                @else
                                    —
                                @endif
                            </div>
                            <div>
                                {{ $roles->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/js/data-table-loading.js') }}"></script>
<script>
    (function($) {
        $(document).ready(function() {
            const $search = $('#rolesSearch');
            const $clearSearch = $('#btnClearSearch');
            const $form = $('#searchForm');
            const $container = $('#rolesTableContainer');

            if ($clearSearch.length) {
                $clearSearch.on('click', function() {
                    $search.val('');
                    $form.submit();
                });
            }

            $search.on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $form.submit();
                }
            });

            $form.on('submit', function() {
                if (window.DataTableLoading) {
                    window.DataTableLoading.show($container);
                }
            });
        });
    })(jQuery);
</script>
@endpush
