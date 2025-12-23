@extends('layouts.admin')

@section('title', 'تفاصيل المستخدم')

@php
    $breadcrumbTitle = 'تفاصيل المستخدم';
    $breadcrumbParent = 'إدارة المستخدمين';
    $breadcrumbParentUrl = route('admin.users.index');
@endphp

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="avatar-circle-lg mx-auto mb-3">{{ substr($user->name, 0, 1) }}</div>
                        <h4 class="fw-bold">{{ $user->name }}</h4>
                        <p class="text-muted mb-3">{{ $user->email }}</p>
                        <div class="mb-3">
                            @if($user->isSuperAdmin())
                                <span class="badge bg-danger fs-6">مدير النظام</span>
                            @elseif($user->isCompanyOwner())
                                <span class="badge bg-primary fs-6">صاحب شركة</span>
                            @elseif($user->isTechnician())
                                <span class="badge bg-warning fs-6">فني</span>
                            @else
                                <span class="badge bg-success fs-6">موظف</span>
                            @endif
                        </div>
                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                            @can('update', $user)
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                                    <i class="bi bi-pencil me-2"></i>
                                    تعديل
                                </a>
                            @endcan
                            @if((auth()->user()->isSuperAdmin() || (auth()->user()->isCompanyOwner() && ($user->isEmployee() || $user->isTechnician()))) && !$user->isSuperAdmin())
                                <a href="{{ route('admin.users.permissions', $user) }}" class="btn btn-warning">
                                    <i class="bi bi-shield-check me-2"></i>
                                    إدارة الصلاحيات
                                </a>
                            @endif
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>
                                رجوع
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold">معلومات المستخدم</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="200">اسم المستخدم:</th>
                                <td>{{ $user->username }}</td>
                            </tr>
                            <tr>
                                <th>البريد الإلكتروني:</th>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <th>الصلاحية:</th>
                                <td>
                                    @if($user->isSuperAdmin())
                                        <span class="badge bg-danger">مدير النظام</span>
                                    @elseif($user->isCompanyOwner())
                                        <span class="badge bg-primary">صاحب شركة</span>
                                    @elseif($user->isTechnician())
                                        <span class="badge bg-warning">فني</span>
                                    @else
                                        <span class="badge bg-success">موظف</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>تاريخ الإنشاء:</th>
                                <td>{{ $user->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                            <tr>
                                <th>آخر تحديث:</th>
                                <td>{{ $user->updated_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($user->isCompanyOwner() && $user->ownedOperators->count() > 0)
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold">المشغلون المملوكون</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                @foreach($user->ownedOperators as $operator)
                                    <div class="list-group-item">
                                        <h6 class="mb-1">{{ $operator->name }}</h6>
                                        <small class="text-muted">{{ $operator->email }}</small>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @if(($user->isEmployee() || $user->isTechnician()) && $user->operators->count() > 0)
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold">المشغلون المنتمي إليهم</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                @foreach($user->operators as $operator)
                                    <div class="list-group-item">
                                        <h6 class="mb-1">{{ $operator->name }}</h6>
                                        <small class="text-muted">{{ $operator->email }}</small>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- الصلاحيات -->
                @if($user->permissions->count() > 0 || auth()->user()->isSuperAdmin() || (auth()->user()->isCompanyOwner() && ($user->isEmployee() || $user->isTechnician())))
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-shield-check me-2"></i>
                                الصلاحيات
                            </h5>
                            @if(auth()->user()->isSuperAdmin() || (auth()->user()->isCompanyOwner() && ($user->isEmployee() || $user->isTechnician())))
                                <a href="{{ route('admin.users.permissions', $user) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil me-1"></i>
                                    إدارة الصلاحيات
                                </a>
                            @endif
                        </div>
                        <div class="card-body">
                            @if($user->isSuperAdmin())
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>مدير النظام</strong> لديه جميع الصلاحيات تلقائياً.
                                </div>
                            @elseif($user->permissions->count() > 0)
                                <div class="row g-2">
                                    @foreach($user->permissions->groupBy('group') as $group => $groupPermissions)
                                        <div class="col-12">
                                            <h6 class="fw-bold text-primary mb-2">
                                                <i class="bi bi-folder-fill me-2"></i>
                                                {{ $groupPermissions->first()->group_label }}
                                            </h6>
                                            <div class="d-flex flex-wrap gap-2 mb-3">
                                                @foreach($groupPermissions as $permission)
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle me-1"></i>
                                                        {{ $permission->label }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-warning mb-0">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    لا توجد صلاحيات محددة لهذا المستخدم. سيتم استخدام صلاحيات الدور الافتراضية.
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

