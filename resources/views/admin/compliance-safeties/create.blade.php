@extends('layouts.admin')

@section('title', 'إضافة سجل امتثال وسلامة')

@php
    $breadcrumbTitle = 'إضافة سجل امتثال وسلامة';
@endphp

@section('content')
    <div class="container-fluid">
        <div class="card border-0 shadow-lg">
            <div class="card-header border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 2rem 1.5rem;">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                         style="width: 50px; height: 50px; background: rgba(255,255,255,0.2);">
                        <i class="bi bi-shield-check text-white fs-4"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-white">إضافة سجل امتثال وسلامة جديد</h4>
                        <p class="mb-0 text-white-50 small">قم بإدخال بيانات الامتثال والسلامة</p>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('admin.compliance-safeties.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">المشغل <span class="text-danger">*</span></label>
                            <select name="operator_id" class="form-select @error('operator_id') is-invalid @enderror" required
                                @if(auth()->user()->isCompanyOwner()) disabled @endif>
                                <option value="">اختر المشغل</option>
                                @foreach($operators as $operator)
                                    <option value="{{ $operator->id }}" 
                                            {{ old('operator_id', auth()->user()->isCompanyOwner() ? auth()->user()->ownedOperators()->first()->id : '') == $operator->id ? 'selected' : '' }}>
                                        {{ $operator->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if(auth()->user()->isCompanyOwner())
                                <input type="hidden" name="operator_id" value="{{ auth()->user()->ownedOperators()->first()->id }}">
                            @endif
                            @error('operator_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">شهادة السلامة <span class="text-danger">*</span></label>
                            <select name="safety_certificate_status" class="form-select @error('safety_certificate_status') is-invalid @enderror" required>
                                <option value="">اختر الحالة</option>
                                <option value="available" {{ old('safety_certificate_status') === 'available' ? 'selected' : '' }}>متوفرة</option>
                                <option value="expired" {{ old('safety_certificate_status') === 'expired' ? 'selected' : '' }}>منتهية</option>
                                <option value="not_available" {{ old('safety_certificate_status') === 'not_available' ? 'selected' : '' }}>غير متوفرة</option>
                            </select>
                            @error('safety_certificate_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">تاريخ آخر زيارة تفقدية</label>
                            <input type="date" name="last_inspection_date" class="form-control @error('last_inspection_date') is-invalid @enderror" 
                                   value="{{ old('last_inspection_date') }}">
                            @error('last_inspection_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">الجهة المنفذة</label>
                            <input type="text" name="inspection_authority" class="form-control @error('inspection_authority') is-invalid @enderror" 
                                   value="{{ old('inspection_authority') }}">
                            @error('inspection_authority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">نتيجة الزيارة</label>
                            <textarea name="inspection_result" class="form-control @error('inspection_result') is-invalid @enderror" rows="4">{{ old('inspection_result') }}</textarea>
                            @error('inspection_result')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">المخالفات المسجلة</label>
                            <textarea name="violations" class="form-control @error('violations') is-invalid @enderror" rows="4">{{ old('violations') }}</textarea>
                            @error('violations')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('admin.compliance-safeties.index') }}" class="btn btn-secondary">إلغاء</a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg me-2"></i>
                            حفظ البيانات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

