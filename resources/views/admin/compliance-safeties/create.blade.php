@extends('layouts.admin')

@section('title', 'إضافة سجل امتثال وسلامة')

@php
    $breadcrumbTitle = 'إضافة سجل امتثال وسلامة';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/compliance-safeties.css') }}">
@endpush

@section('content')
    <div class="compliance-safeties-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="card log-card">
                    <div class="log-card-header">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-0">
                            <div>
                                <div class="log-title">
                                    <i class="bi bi-shield-check me-2"></i>
                                    إضافة سجل امتثال وسلامة جديد
                                </div>
                                <div class="log-subtitle">
                                    قم بإدخال بيانات الامتثال والسلامة بشكل كامل
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('admin.compliance-safeties.store') }}" method="POST" id="complianceSafetyForm">
                            @csrf

                            <!-- Basic Information Section -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-muted">
                                    <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                    المعلومات الأساسية
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-building text-info me-1"></i>
                                            المشغل <span class="text-danger">*</span>
                                        </label>
                                        <select name="operator_id" class="form-select @error('operator_id') is-invalid @enderror"
                                            @if(auth()->user()->isCompanyOwner()) disabled @endif>
                                            <option value="">اختر المشغل</option>
                                            @foreach($operators as $operator)
                                                <option value="{{ $operator->id }}" 
                                                        {{ old('operator_id', auth()->user()->isCompanyOwner() ? auth()->user()->ownedOperators()->first()->id : '') == $operator->id ? 'selected' : '' }}>
                                                    {{ $operator->name }}
                                                    @if($operator->unit_number)
                                                        - {{ $operator->unit_number }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @if(auth()->user()->isCompanyOwner())
                                            <input type="hidden" name="operator_id" value="{{ auth()->user()->ownedOperators()->first()->id }}">
                                        @endif
                                        @error('operator_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-shield-check text-success me-1"></i>
                                            حالة شهادة السلامة <span class="text-danger">*</span>
                                        </label>
                                        <select name="safety_certificate_status" class="form-select @error('safety_certificate_status') is-invalid @enderror">
                                            <option value="">اختر الحالة</option>
                                            <option value="available" {{ old('safety_certificate_status') === 'available' ? 'selected' : '' }}>متوفرة</option>
                                            <option value="expired" {{ old('safety_certificate_status') === 'expired' ? 'selected' : '' }}>منتهية</option>
                                            <option value="not_available" {{ old('safety_certificate_status') === 'not_available' ? 'selected' : '' }}>غير متوفرة</option>
                                        </select>
                                        @error('safety_certificate_status')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Inspection Details Section -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-muted">
                                    <i class="bi bi-clipboard-check text-warning me-2"></i>
                                    تفاصيل الزيارة التفقدية
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-calendar3 text-primary me-1"></i>
                                            تاريخ آخر زيارة تفقدية
                                        </label>
                                        <input type="date" name="last_inspection_date" 
                                               class="form-control @error('last_inspection_date') is-invalid @enderror" 
                                               value="{{ old('last_inspection_date') }}">
                                        @error('last_inspection_date')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-building-check text-info me-1"></i>
                                            الجهة المنفذة
                                        </label>
                                        <input type="text" name="inspection_authority" 
                                               class="form-control @error('inspection_authority') is-invalid @enderror" 
                                               value="{{ old('inspection_authority') }}" 
                                               placeholder="اسم الجهة المنفذة للزيارة">
                                        @error('inspection_authority')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-file-text text-secondary me-1"></i>
                                            نتيجة الزيارة
                                        </label>
                                        <textarea name="inspection_result" 
                                                  class="form-control @error('inspection_result') is-invalid @enderror" 
                                                  rows="4"
                                                  placeholder="تفاصيل نتيجة الزيارة التفقدية">{{ old('inspection_result') }}</textarea>
                                        @error('inspection_result')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-exclamation-triangle text-danger me-1"></i>
                                            المخالفات المسجلة
                                        </label>
                                        <textarea name="violations" 
                                                  class="form-control @error('violations') is-invalid @enderror" 
                                                  rows="4"
                                                  placeholder="تفاصيل المخالفات المسجلة إن وجدت">{{ old('violations') }}</textarea>
                                        @error('violations')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.compliance-safeties.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-right me-2"></i>
                                    إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-check-lg me-2"></i>
                                    حفظ البيانات
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/libs/jquery/jquery.min.js') }}"></script>
<script>
    (function($) {
        $(document).ready(function() {
            const $form = $('#complianceSafetyForm');
            const $submitBtn = $form.find('button[type="submit"]');

            $form.on('submit', function(e) {
                e.preventDefault();
                
                if (!$form[0].checkValidity()) {
                    $form[0].reportValidity();
                    return false;
                }

                $submitBtn.prop('disabled', true);
                const originalText = $submitBtn.html();
                $submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>جاري الحفظ...');

                const formData = new FormData(this);

                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        if (response.success) {
                            if (typeof window.showToast === 'function') {
                                window.showToast(response.message || 'تم إنشاء سجل الامتثال والسلامة بنجاح', 'success');
                            } else {
                                alert(response.message || 'تم إنشاء سجل الامتثال والسلامة بنجاح');
                            }
                            setTimeout(function() {
                                window.location.href = '{{ route('admin.compliance-safeties.index') }}';
                            }, 500);
                        }
                    },
                    error: function(xhr) {
                        $submitBtn.prop('disabled', false);
                        $submitBtn.html(originalText);

                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON?.errors || {};
                            let firstError = '';
                            
                            $form.find('.is-invalid').removeClass('is-invalid');
                            $form.find('.invalid-feedback').remove();

                            $.each(errors, function(field, messages) {
                                const $field = $form.find('[name="' + field + '"]');
                                if ($field.length) {
                                    $field.addClass('is-invalid');
                                    const errorMsg = Array.isArray(messages) ? messages[0] : messages;
                                    if (!firstError) firstError = errorMsg;
                                    $field.after('<div class="invalid-feedback d-block">' + errorMsg + '</div>');
                                }
                            });

                            if (typeof window.showToast === 'function') {
                                window.showToast(firstError || 'يرجى التحقق من الحقول المطلوبة', 'error');
                            } else {
                                alert(firstError || 'يرجى التحقق من الحقول المطلوبة');
                            }
                        } else {
                            const errorMsg = xhr.responseJSON?.message || 'حدث خطأ أثناء حفظ البيانات';
                            if (typeof window.showToast === 'function') {
                                window.showToast(errorMsg, 'error');
                            } else {
                                alert(errorMsg);
                            }
                        }
                    }
                });
            });
        });
    })(jQuery);
</script>
@endpush
