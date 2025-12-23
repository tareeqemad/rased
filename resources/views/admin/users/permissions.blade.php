@extends('layouts.admin')

@section('title', 'إدارة صلاحيات المستخدم')

@php
    $breadcrumbTitle = 'إدارة صلاحيات المستخدم';
    $breadcrumbParent = 'إدارة المستخدمين';
    $breadcrumbParentUrl = route('admin.users.index');
@endphp

@section('content')
    <div class="container-fluid">
        <div class="card border-0 shadow-lg">
            <div class="card-header border-0" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); padding: 1.25rem 1.5rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 45px; height: 45px; background: rgba(255,255,255,0.2);">
                            <i class="bi bi-shield-check text-white fs-5"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-white">إدارة صلاحيات المستخدم</h5>
                            <p class="mb-0 text-white-50 small">{{ $user->name }}</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-left me-2"></i>
                        رجوع
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('admin.users.permissions.update', $user) }}" method="POST" id="permissionsForm">
                    @csrf
                    @method('PUT')

                    <!-- معلومات المستخدم -->
                    <div class="alert alert-info border-0 mb-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person-circle fs-4 me-3"></i>
                            <div>
                                <strong>المستخدم:</strong> {{ $user->name }}<br>
                                <strong>الدور:</strong> 
                                @if($user->isSuperAdmin())
                                    <span class="badge bg-danger">مدير النظام</span>
                                @elseif($user->isCompanyOwner())
                                    <span class="badge bg-primary">صاحب المشغل</span>
                                @elseif($user->isTechnician())
                                    <span class="badge bg-warning">فني</span>
                                @else
                                    <span class="badge bg-success">موظف</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- شجرة الصلاحيات -->
                    <div class="permissions-tree">
                        @foreach($permissions as $group => $groupPermissions)
                            <div class="permission-group mb-4">
                                <div class="d-flex align-items-center justify-content-between mb-3 p-3 bg-light rounded">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-folder-fill me-2 text-primary fs-5"></i>
                                        <h6 class="mb-0 fw-bold">{{ $groupPermissions->first()->group_label }}</h6>
                                        <span class="badge bg-secondary ms-2">{{ $groupPermissions->count() }} صلاحية</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary select-all-group" data-group="{{ $group }}">
                                        <i class="bi bi-check-all me-1"></i>
                                        تحديد الكل
                                    </button>
                                </div>
                                <div class="ps-4">
                                    <div class="row g-2">
                                        @foreach($groupPermissions as $permission)
                                            <div class="col-md-6 col-lg-4">
                                                <div class="card border mb-2 permission-item">
                                                    <div class="card-body p-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input permission-checkbox" 
                                                                   type="checkbox" 
                                                                   name="permissions[]" 
                                                                   value="{{ $permission->id }}" 
                                                                   id="permission_{{ $permission->id }}"
                                                                   data-group="{{ $group }}"
                                                                   {{ in_array($permission->id, $userPermissions) ? 'checked' : '' }}>
                                                            <label class="form-check-label w-100" for="permission_{{ $permission->id }}">
                                                                <div class="d-flex align-items-start">
                                                                    <div class="flex-grow-1">
                                                                        <h6 class="mb-1 fw-semibold small">{{ $permission->label }}</h6>
                                                                        <p class="text-muted small mb-0">{{ $permission->description }}</p>
                                                                        <code class="small text-muted">{{ $permission->name }}</code>
                                                                    </div>
                                                                </div>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- أزرار الحفظ -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <button type="button" class="btn btn-outline-primary" id="selectAllBtn">
                                        <i class="bi bi-check-all me-2"></i>
                                        تحديد جميع الصلاحيات
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="deselectAllBtn">
                                        <i class="bi bi-x-circle me-2"></i>
                                        إلغاء تحديد الكل
                                    </button>
                                </div>
                                <div>
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary me-2">
                                        <i class="bi bi-x-circle me-2"></i>
                                        إلغاء
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-2"></i>
                                        حفظ الصلاحيات
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .permissions-tree {
        max-height: 600px;
        overflow-y: auto;
    }
    .permission-group {
        border-left: 3px solid #3b82f6;
        padding-left: 1rem;
    }
    .permission-item .card {
        transition: all 0.3s ease;
    }
    .permission-item:hover .card {
        transform: translateX(5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        border-color: #3b82f6 !important;
    }
    .permission-item input[type="checkbox"]:checked ~ label .card {
        background-color: #e7f3ff;
        border-color: #3b82f6 !important;
    }
    .form-check-input:checked {
        background-color: #3b82f6;
        border-color: #3b82f6;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // تحديد/إلغاء تحديد جميع الصلاحيات
        document.getElementById('selectAllBtn').addEventListener('click', function() {
            document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = true);
        });

        document.getElementById('deselectAllBtn').addEventListener('click', function() {
            document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
        });

        // تحديد/إلغاء تحديد مجموعة معينة
        document.querySelectorAll('.select-all-group').forEach(btn => {
            btn.addEventListener('click', function() {
                const group = this.dataset.group;
                const checkboxes = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`);
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                
                checkboxes.forEach(cb => {
                    cb.checked = !allChecked;
                });
            });
        });
    });
</script>
@endpush

