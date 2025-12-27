@extends('layouts.admin')

@section('title', 'إدارة الأدوار')

@php
    $breadcrumbTitle = 'إدارة الأدوار';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/roles.css') }}">
@endpush

@section('content')
<div class="roles-page" id="rolesPage" data-index-url="{{ route('admin.roles.index') }}">
    <div class="row g-3">
        <div class="col-12">
            <div class="roles-card">
                <div class="roles-card-header">
                    <div>
                        <h5 class="roles-title">
                            <i class="bi bi-shield-check me-2"></i>
                            إدارة الأدوار
                        </h5>
                        <div class="roles-subtitle">
                            إدارة وتنظيم أدوار المستخدمين والصلاحيات
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
                    <div class="roles-stats mb-4">
                        <div class="roles-stat">
                            <div class="label">الإجمالي</div>
                            <div class="value" id="statTotal">{{ $roles->total() }}</div>
                        </div>
                        <div class="roles-stat">
                            <div class="label">نظامي</div>
                            <div class="value" id="statSystem">{{ $roles->where('is_system', true)->count() }}</div>
                        </div>
                        <div class="roles-stat">
                            <div class="label">مخصص</div>
                            <div class="value" id="statCustom">{{ $roles->where('is_system', false)->count() }}</div>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('admin.roles.index') }}" id="searchForm">
                        <div class="row g-2 align-items-end">
                            <div class="col-lg-6">
                                <label class="form-label fw-semibold">بحث</label>
                                <div class="roles-search">
                                    <i class="bi bi-search"></i>
                                    <input type="text" name="search" id="rolesSearch" class="form-control" 
                                           placeholder="اسم الدور / التسمية / الوصف..." 
                                           value="{{ request('search') }}" autocomplete="off">
                                    @if(request('search'))
                                        <button type="button" class="roles-clear" id="btnClearSearch" title="إلغاء البحث">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-grow-1" id="btnSearch">
                                        <i class="bi bi-search me-1"></i>
                                        بحث
                                    </button>
                                    @if(request('search'))
                                        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary" id="btnResetFilters">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                                            تصفير
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>

                    <hr class="my-3">

                    <div class="table-responsive" id="rolesTableContainer">
                        <table class="table table-hover align-middle mb-0 roles-table">
                            <thead>
                                <tr>
                                    <th style="min-width:150px;">اسم الدور</th>
                                    <th>التسمية</th>
                                    <th class="d-none d-md-table-cell">الوصف</th>
                                    <th class="text-center">المستخدمين</th>
                                    <th class="text-center">الصلاحيات</th>
                                    <th class="text-center">النوع</th>
                                    <th style="min-width:140px;" class="text-center">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="rolesTbody">
                                @forelse($roles as $role)
                                    <tr>
                                        <td class="text-nowrap">
                                            <code class="text-primary fw-bold">{{ $role->name }}</code>
                                        </td>
                                        <td class="text-nowrap">
                                            <span class="fw-semibold">{{ $role->label }}</span>
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            <small class="text-muted">{{ $role->description ?? '-' }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $role->users_count }} مستخدم</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success">{{ $role->permissions_count }} صلاحية</span>
                                        </td>
                                        <td class="text-center">
                                            @if($role->is_system)
                                                <span class="badge badge-system">نظامي</span>
                                            @else
                                                <span class="badge badge-custom">مخصص</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex gap-2 justify-content-center">
                                                @can('view', $role)
                                                    <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-sm btn-outline-info" title="عرض">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                @endcan
                                                @can('update', $role)
                                                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary" title="تعديل">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                @endcan
                                                @can('delete', $role)
                                                    @if(!$role->is_system && $role->users_count == 0)
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#deleteModal{{ $role->id }}" 
                                                                title="حذف">
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
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            @if(request('search'))
                                                لا توجد نتائج للبحث "{{ request('search') }}"
                                            @else
                                                لا توجد أدوار
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
<script src="{{ asset('assets/admin/libs/jquery/jquery.min.js') }}"></script>
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
