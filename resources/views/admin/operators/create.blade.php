@extends('layouts.admin')

@section('title', 'إضافة مشغل جديد')

@php
    $breadcrumbTitle = 'إضافة مشغل جديد';
    $breadcrumbParent = 'إدارة المشغلين';
    $breadcrumbParentUrl = route('admin.operators.index');
@endphp

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-building me-2"></i>
                        إضافة مشغل جديد
                    </h5>
                    <a href="{{ route('admin.operators.index') }}" class="btn btn-sm">
                        <i class="bi bi-arrow-right me-1"></i>
                        رجوع
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.operators.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="bi bi-person-badge me-2"></i>
                                    بيانات تسجيل الدخول للمشغل
                                </h6>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    سيتم إنشاء حساب مشغل ببيانات بسيطة. يمكن للمشغل تسجيل الدخول وإكمال بياناته لاحقاً.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">اسم المستخدم <span class="text-danger">*</span></label>
                                <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" required>
                                
                                <small class="form-text">سيستخدمه المشغل لتسجيل الدخول</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">كلمة المرور <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required minlength="8">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                    </button>
                                </div>
                                
                                <small class="form-text">يجب أن تكون 8 أحرف على الأقل</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">البريد الإلكتروني (اختياري)</label>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                                
                                <small class="form-text">لإرسال بيانات تسجيل الدخول للمشغل</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">إرسال الإيميل</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="send_email" id="send_email" value="1" {{ old('send_email') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="send_email">
                                        إرسال بيانات تسجيل الدخول إلى البريد الإلكتروني
                                    </label>
                                </div>
                                <small class="form-text">سيتم إرسال اسم المستخدم وكلمة المرور إلى البريد الإلكتروني</small>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.operators.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>
                                حفظ البيانات
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
<link rel="stylesheet" href="{{ asset('assets/admin/css/operators.css') }}">
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Toggle password visibility
        $('#togglePassword').on('click', function() {
            const passwordInput = $('#password');
            const icon = $('#togglePasswordIcon');
            
            if (passwordInput.attr('type') === 'password') {
                passwordInput.attr('type', 'text');
                icon.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                passwordInput.attr('type', 'password');
                icon.removeClass('bi-eye-slash').addClass('bi-eye');
            }
        });
        
        // Auto-generate email from username if email is empty
        $('#username').on('blur', function() {
            if (!$('#email').val() && $(this).val()) {
                $('#email').val($(this).val() + '@rased.ps');
            }
        });
        
        // Enable/disable send_email checkbox based on email field
        $('#email').on('input', function() {
            if ($(this).val()) {
                $('#send_email').prop('disabled', false);
            } else {
                $('#send_email').prop('checked', false).prop('disabled', true);
            }
        });
        
        // Check on page load
        if (!$('#email').val()) {
            $('#send_email').prop('disabled', true);
        }
    });
</script>
@endpush
