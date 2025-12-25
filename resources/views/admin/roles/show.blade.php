@extends('layouts.admin')

@section('title', 'تفاصيل الدور')

@php
    $breadcrumbTitle = 'تفاصيل الدور';
    $breadcrumbParent = 'إدارة الأدوار';
    $breadcrumbParentUrl = route('admin.roles.index');
@endphp

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-shield-check me-2"></i>
                        تفاصيل الدور
                    </h5>
                    <div class="d-flex gap-2">
                        @can('update', $role)
                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm">
                                <i class="bi bi-pencil me-1"></i>
                                تعديل
                            </a>
                        @endcan
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-sm">
                            <i class="bi bi-arrow-right me-1"></i>
                            رجوع
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="bi bi-info-circle me-2"></i>
                                        معلومات الدور
                                    </h6>
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <td class="fw-semibold" style="width: 40%;">اسم الدور:</td>
                                            <td><code class="text-primary">{{ $role->name }}</code></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">التسمية:</td>
                                            <td>{{ $role->label }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">الوصف:</td>
                                            <td>{{ $role->description ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">نوع الدور:</td>
                                            <td>
                                                @if($role->is_system)
                                                    <span class="badge bg-danger">نظامي</span>
                                                @else
                                                    <span class="badge bg-success">مخصص</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">ترتيب العرض:</td>
                                            <td>{{ $role->order }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">تاريخ الإنشاء:</td>
                                            <td>{{ $role->created_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="bi bi-people me-2"></i>
                                        الإحصائيات
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="text-center p-3 bg-white rounded">
                                                <div class="fs-2 fw-bold text-info">{{ $role->users_count }}</div>
                                                <div class="text-muted small">مستخدم</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center p-3 bg-white rounded">
                                                <div class="fs-2 fw-bold text-success">{{ $role->permissions->count() }}</div>
                                                <div class="text-muted small">صلاحية</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card border-0">
                                <div class="card-header bg-white border-bottom">
                                    <h6 class="fw-bold text-primary mb-0">
                                        <i class="bi bi-shield-check me-2"></i>
                                        الصلاحيات المرتبطة
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if($role->permissions->count() > 0)
                                        <div class="row g-3">
                                            @foreach($role->permissions->groupBy('group') as $group => $groupPermissions)
                                                <div class="col-md-6 col-lg-4">
                                                    <div class="card border mb-3">
                                                        <div class="card-header bg-light">
                                                            <h6 class="mb-0 fw-bold">
                                                                {{ $groupPermissions->first()->group_label }}
                                                                <span class="badge bg-primary ms-2">{{ $groupPermissions->count() }}</span>
                                                            </h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <ul class="list-unstyled mb-0">
                                                                @foreach($groupPermissions as $permission)
                                                                    <li class="mb-2">
                                                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                                        <strong>{{ $permission->label }}</strong>
                                                                        <br>
                                                                        <small class="text-muted">{{ $permission->description }}</small>
                                                                        <br>
                                                                        <code class="text-muted" style="font-size: 0.7rem;">{{ $permission->name }}</code>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-warning text-center mb-0">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            لا توجد صلاحيات مرتبطة بهذا الدور
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
