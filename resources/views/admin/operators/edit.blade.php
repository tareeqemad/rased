@extends('layouts.admin')

@section('title', 'تعديل المشغل')

@php
    $breadcrumbTitle = 'تعديل المشغل';
    $breadcrumbParent = 'إدارة المشغلين';
    $breadcrumbParentUrl = route('admin.operators.index');
@endphp

@section('content')
    <div class="container-fluid">
        <div class="card border-0 shadow-lg">
            <!-- كارد هيدر بتصميم جميل -->
            <div class="card-header border-0" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); padding: 1.25rem 1.5rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 45px; height: 45px; background: rgba(255,255,255,0.2);">
                            <i class="bi bi-building-gear text-white fs-5"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-white">تعديل المشغل</h5>
                            <p class="mb-0 text-white-50 small">{{ $operator->name }}</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.operators.index') }}" class="btn btn-light btn-sm rounded-pill">
                        <i class="bi bi-arrow-left me-2"></i>رجوع
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.operators.update', $operator) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-12">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                بيانات المشغل الأساسية
                            </h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">اسم المشغل <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $operator->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">البريد الإلكتروني للمشغل</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $operator->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">الهاتف</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $operator->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">العنوان</label>
                            <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $operator->address) }}">
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($operator->owner)
                            <div class="col-12 mt-4">
                                <hr>
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="bi bi-person-badge me-2"></i>
                                    تحديث بيانات تسجيل الدخول (اختياري)
                                </h6>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    اترك الحقول فارغة إذا لم ترد تغييرها
                                </div>
                            </div>

                            @if(auth()->user()->isSuperAdmin())
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">اسم المستخدم</label>
                                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $operator->owner->username) }}">
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @else
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">اسم المستخدم</label>
                                    <input type="text" class="form-control" value="{{ $operator->owner->username }}" readonly disabled>
                                    <small class="text-muted">لا يمكن تغيير اسم المستخدم</small>
                                </div>
                            @endif

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">البريد الإلكتروني للمستخدم</label>
                                <input type="email" name="user_email" class="form-control @error('user_email') is-invalid @enderror" value="{{ old('user_email', $operator->owner->email) }}">
                                @error('user_email')
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
                        @endif
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.operators.index') }}" class="btn btn-outline-secondary btn-lg px-4 rounded-pill">
                            <i class="bi bi-x-circle me-2"></i>إلغاء
                        </a>
                        <button type="submit" class="btn btn-success btn-lg px-5 rounded-pill shadow">
                            <i class="bi bi-check-lg me-2"></i>حفظ التغييرات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    /* تحسين الفورم */
    .form-label {
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .form-control, .form-select {
        border: 2px solid #e9ecef;
        border-radius: 0.5rem;
        padding: 0.625rem 0.875rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.15);
    }
    
    /* تحسين الأزرار */
    .btn {
        transition: all 0.3s ease;
    }
    
    .btn-success {
        background: linear-gradient(135deg, #15803d 0%, #22c55e 100%);
        border: none;
    }
    
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4);
        background: linear-gradient(135deg, #166534 0%, #16a34a 100%);
    }
    
    .btn-outline-secondary:hover {
        transform: translateY(-2px);
    }
    
    .btn-light:hover {
        background-color: rgba(255,255,255,0.9);
        transform: scale(1.05);
    }
    
    /* تحسين الكارد */
    .card {
        border-radius: 1rem;
        overflow: hidden;
    }
    
    .alert {
        border-radius: 0.75rem;
        border-left: 4px solid #3b82f6;
    }
</style>
@endpush

