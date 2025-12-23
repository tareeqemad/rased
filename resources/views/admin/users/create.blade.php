@extends('layouts.admin')

@section('title', 'إضافة مستخدم جديد')

@php
    $breadcrumbTitle = 'إضافة مستخدم جديد';
    $breadcrumbParent = 'إدارة المستخدمين';
    $breadcrumbParentUrl = route('admin.users.index');
@endphp

@section('content')
    <div class="container-fluid">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-person-plus me-2"></i>
                    إضافة مستخدم جديد
                </h5>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>
                    رجوع
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">الاسم <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">اسم المستخدم <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" required>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">البريد الإلكتروني <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">الصلاحية <span class="text-danger">*</span></label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                <option value="">اختر الصلاحية</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->value }}" {{ old('role') === $role->value ? 'selected' : '' }}>
                                        @if($role === App\Role::SuperAdmin)
                                            مدير النظام
                                        @elseif($role === App\Role::CompanyOwner)
                                            صاحب شركة
                                        @else
                                            موظف
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">كلمة المرور <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="8">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">يجب أن تكون 8 أحرف على الأقل</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required minlength="8">
                        </div>

                        <div class="col-md-6" id="operatorField" style="display: none;">
                            <label class="form-label fw-semibold">المشغل</label>
                            <select name="operator_id" class="form-select @error('operator_id') is-invalid @enderror">
                                <option value="">اختر المشغل</option>
                                @foreach($operators as $operator)
                                    <option value="{{ $operator->id }}" {{ old('operator_id') == $operator->id ? 'selected' : '' }}>
                                        {{ $operator->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('operator_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">اختر المشغل إذا كان المستخدم موظف</small>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>
                            حفظ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.querySelector('select[name="role"]');
        const operatorField = document.getElementById('operatorField');

        roleSelect.addEventListener('change', function() {
            if (this.value === '{{ App\Role::Employee->value }}' || this.value === '{{ App\Role::Technician->value }}') {
                operatorField.style.display = 'block';
            } else {
                operatorField.style.display = 'none';
                document.querySelector('select[name="operator_id"]').value = '';
            }
        });

        // Check on page load
        if (roleSelect.value === '{{ App\Role::Employee->value }}' || roleSelect.value === '{{ App\Role::Technician->value }}') {
            operatorField.style.display = 'block';
        }
    });
</script>
@endpush

