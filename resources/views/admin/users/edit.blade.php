@extends('layouts.admin')

@section('title', 'تعديل المستخدم')

@php
    $breadcrumbTitle = 'تعديل المستخدم';
    $breadcrumbParent = 'إدارة المستخدمين';
    $breadcrumbParentUrl = route('admin.users.index');
@endphp

@section('content')
    <div class="container-fluid">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-person-gear me-2"></i>
                    تعديل المستخدم: {{ $user->name }}
                </h5>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>
                    رجوع
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">الاسم <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">اسم المستخدم <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}" required>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">البريد الإلكتروني <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">الصلاحية <span class="text-danger">*</span></label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                @foreach($roles as $role)
                                    <option value="{{ $role->value }}" {{ old('role', $user->role->value) === $role->value ? 'selected' : '' }}>
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
                            <label class="form-label fw-semibold">كلمة المرور</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="8">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">اتركه فارغاً إذا لم ترد تغيير كلمة المرور</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">تأكيد كلمة المرور</label>
                            <input type="password" name="password_confirmation" class="form-control" minlength="8">
                        </div>

                        <div class="col-md-6" id="operatorField" style="display: none;">
                            <label class="form-label fw-semibold">المشغلون</label>
                            <select name="operator_id[]" class="form-select @error('operator_id') is-invalid @enderror" multiple>
                                @foreach($operators as $operator)
                                    <option value="{{ $operator->id }}" {{ in_array($operator->id, $userOperators) ? 'selected' : '' }}>
                                        {{ $operator->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('operator_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">اختر المشغلين الذين ينتمي إليهم الموظف (يمكن اختيار أكثر من مشغل)</small>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>
                            حفظ التغييرات
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
            }
        });

        // Check on page load
        if (roleSelect.value === '{{ App\Role::Employee->value }}' || roleSelect.value === '{{ App\Role::Technician->value }}') {
            operatorField.style.display = 'block';
        }
    });
</script>
@endpush

