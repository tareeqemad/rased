@extends('layouts.admin')

@section('title', 'إدارة الأدوار')

@php
    $breadcrumbTitle = 'إدارة الأدوار';
@endphp

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-shield-check me-2"></i>
                        إدارة الأدوار
                    </h5>
                    @can('create', App\Models\Role::class)
                        <a href="{{ route('admin.roles.create') }}" class="btn btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>
                            إضافة دور جديد
                        </a>
                    @endcan
                </div>
                <div class="card-body">
                    <!-- Search Form -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form method="GET" action="{{ route('admin.roles.index') }}" class="d-flex gap-2">
                                <input type="text" name="search" class="form-control" placeholder="بحث في الأدوار..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-1"></i>
                                    بحث
                                </button>
                                @if(request('search'))
                                    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-x"></i>
                                    </a>
                                @endif
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-nowrap">اسم الدور</th>
                                    <th class="text-nowrap">التسمية</th>
                                    <th class="text-nowrap d-none d-md-table-cell">الوصف</th>
                                    <th class="text-nowrap">عدد المستخدمين</th>
                                    <th class="text-nowrap">عدد الصلاحيات</th>
                                    <th class="text-nowrap">نوع الدور</th>
                                    <th class="text-nowrap">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                    <tr>
                                        <td class="text-nowrap">
                                            <code class="text-primary">{{ $role->name }}</code>
                                        </td>
                                        <td class="text-nowrap">
                                            <span class="fw-semibold">{{ $role->label }}</span>
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            <small class="text-muted">{{ $role->description ?? '-' }}</small>
                                        </td>
                                        <td class="text-nowrap">
                                            <span class="badge bg-info">{{ $role->users_count }} مستخدم</span>
                                        </td>
                                        <td class="text-nowrap">
                                            <span class="badge bg-success">{{ $role->permissions_count }} صلاحية</span>
                                        </td>
                                        <td class="text-nowrap">
                                            @if($role->is_system)
                                                <span class="badge badge-system text-white">نظامي</span>
                                            @else
                                                <span class="badge badge-custom text-white">مخصص</span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">
                                            <div class="d-flex gap-2">
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
                                                    @if(!$role->is_system)
                                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $role->id }}" title="حذف">
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
                                                                <p class="text-warning">
                                                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                                                    <small>هذا الدور مرتبط بـ {{ $role->users_count }} مستخدم. لا يمكن حذفه.</small>
                                                                </p>
                                                            @else
                                                                <p class="text-danger"><small>هذا الإجراء لا يمكن التراجع عنه</small></p>
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
                </div>
                @if($roles->hasPages())
                    <div class="card-footer">
                        {{ $roles->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
