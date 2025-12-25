@extends('layouts.admin')

@section('title', 'إضافة دور جديد')

@php
    $breadcrumbTitle = 'إضافة دور جديد';
    $breadcrumbParent = 'إدارة الأدوار';
    $breadcrumbParentUrl = route('admin.roles.index');
@endphp

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-shield-plus me-2"></i>
                        إضافة دور جديد
                    </h5>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-sm">
                        <i class="bi bi-arrow-right me-1"></i>
                        رجوع
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.roles.store') }}" method="POST" id="roleForm">
                        @csrf

                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    بيانات الدور الأساسية
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">اسم الدور (مفتاح) <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="roleName" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name') }}" required pattern="[a-z_]+" 
                                       placeholder="مثال: custom_role">
                                <small class="form-text">يجب أن يكون باللغة الإنجليزية، أحرف صغيرة وشرطات سفلية فقط</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">التسمية (العربية) <span class="text-danger">*</span></label>
                                <input type="text" name="label" class="form-control @error('label') is-invalid @enderror" 
                                       value="{{ old('label') }}" required>
                                <small class="form-text">اسم الدور بالعربية كما سيظهر في النظام</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">الوصف</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                          rows="3">{{ old('description') }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">ترتيب العرض</label>
                                <input type="number" name="order" class="form-control @error('order') is-invalid @enderror" 
                                       value="{{ old('order', 0) }}" min="0">
                                <small class="form-text">رقم الترتيب (الأصغر يظهر أولاً)</small>
                            </div>

                            <div class="col-12 mt-4">
                                <hr>
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="bi bi-shield-check me-2"></i>
                                    الصلاحيات
                                </h6>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    اختر الصلاحيات التي سيحصل عليها المستخدمون بهذا الدور
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="permissions-container" style="max-height: 500px; overflow-y: auto; border: 1px solid #e9ecef; border-radius: 0.5rem; padding: 1rem;">
                                    @foreach($permissions as $group => $groupPermissions)
                                        <div class="permission-group mb-4">
                                            <div class="d-flex align-items-center mb-3 p-2 bg-light rounded">
                                                <input type="checkbox" class="form-check-input me-2 group-checkbox" 
                                                       data-group="{{ $group }}" id="group_{{ $group }}">
                                                <label class="form-check-label fw-bold mb-0" for="group_{{ $group }}">
                                                    {{ $groupPermissions->first()->group_label }}
                                                    <span class="badge bg-primary ms-2">{{ $groupPermissions->count() }}</span>
                                                </label>
                                            </div>
                                            <div class="ms-4">
                                                @foreach($groupPermissions as $permission)
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input permission-checkbox" 
                                                               type="checkbox" 
                                                               name="permissions[]" 
                                                               value="{{ $permission->id }}" 
                                                               id="permission_{{ $permission->id }}"
                                                               data-group="{{ $group }}">
                                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                            {{ $permission->label }}
                                                            <small class="text-muted d-block">{{ $permission->description }}</small>
                                                            <code class="text-muted" style="font-size: 0.7rem;">{{ $permission->name }}</code>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>
                                حفظ الدور
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/css/custom.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/roles-forms.css') }}">
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Group checkbox functionality
        $('.group-checkbox').on('change', function() {
            const group = $(this).data('group');
            const isChecked = $(this).is(':checked');
            
            $('.permission-checkbox[data-group="' + group + '"]').prop('checked', isChecked);
        });
        
        // Individual checkbox - update group checkbox
        $('.permission-checkbox').on('change', function() {
            const group = $(this).data('group');
            const groupCheckbox = $('#group_' + group);
            const groupPermissions = $('.permission-checkbox[data-group="' + group + '"]');
            const checkedCount = groupPermissions.filter(':checked').length;
            
            if (checkedCount === 0) {
                groupCheckbox.prop('checked', false).prop('indeterminate', false);
            } else if (checkedCount === groupPermissions.length) {
                groupCheckbox.prop('checked', true).prop('indeterminate', false);
            } else {
                groupCheckbox.prop('checked', false).prop('indeterminate', true);
            }
        });
        
        // Initialize group checkboxes
        $('.group-checkbox').each(function() {
            const group = $(this).data('group');
            const groupPermissions = $('.permission-checkbox[data-group="' + group + '"]');
            const checkedCount = groupPermissions.filter(':checked').length;
            
            if (checkedCount === groupPermissions.length) {
                $(this).prop('checked', true);
            } else if (checkedCount > 0) {
                $(this).prop('indeterminate', true);
            }
        });
    });
</script>
@endpush
