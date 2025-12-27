@extends('layouts.admin')

@section('title', 'تعديل سجل صيانة')

@php
    $breadcrumbTitle = 'تعديل سجل صيانة';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/maintenance-records.css') }}">
@endpush

@section('content')
    <div class="maintenance-records-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="card log-card">
                    <div class="log-card-header">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-0">
                            <div>
                                <div class="log-title">
                                    <i class="bi bi-pencil-square me-2"></i>
                                    تعديل سجل الصيانة
                                </div>
                                <div class="log-subtitle">
                                    @if($maintenanceRecord->generator)
                                        المولد: {{ $maintenanceRecord->generator->name }} | 
                                    @endif
                                    التاريخ: {{ $maintenanceRecord->maintenance_date->format('Y-m-d') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('admin.maintenance-records.update', $maintenanceRecord) }}" method="POST" id="maintenanceRecordForm">
                            @csrf
                            @method('PUT')

                            <!-- Basic Information Section -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-muted">
                                    <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                    المعلومات الأساسية
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-lightning-charge-fill text-warning me-1"></i>
                                            المولد <span class="text-danger">*</span>
                                        </label>
                                        <select name="generator_id" id="generator_id" 
                                                class="form-select @error('generator_id') is-invalid @enderror">
                                            <option value="">اختر المولد</option>
                                            @foreach($generators as $generator)
                                                <option value="{{ $generator->id }}" 
                                                        {{ old('generator_id', $maintenanceRecord->generator_id) == $generator->id ? 'selected' : '' }}>
                                                    {{ $generator->generator_number }} - {{ $generator->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('generator_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-tag text-info me-1"></i>
                                            نوع الصيانة <span class="text-danger">*</span>
                                        </label>
                                        <select name="maintenance_type" id="maintenance_type" 
                                                class="form-select @error('maintenance_type') is-invalid @enderror">
                                            <option value="">اختر النوع</option>
                                            <option value="periodic" {{ old('maintenance_type', $maintenanceRecord->maintenance_type) === 'periodic' ? 'selected' : '' }}>دورية</option>
                                            <option value="emergency" {{ old('maintenance_type', $maintenanceRecord->maintenance_type) === 'emergency' ? 'selected' : '' }}>طارئة</option>
                                        </select>
                                        @error('maintenance_type')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-calendar3 text-primary me-1"></i>
                                            تاريخ الصيانة <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" name="maintenance_date" 
                                               class="form-control @error('maintenance_date') is-invalid @enderror" 
                                               value="{{ old('maintenance_date', $maintenanceRecord->maintenance_date->format('Y-m-d')) }}">
                                        @error('maintenance_date')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-person-badge text-success me-1"></i>
                                            اسم الفني المسؤول
                                        </label>
                                        <input type="text" name="technician_name" 
                                               class="form-control @error('technician_name') is-invalid @enderror" 
                                               value="{{ old('technician_name', $maintenanceRecord->technician_name) }}" 
                                               placeholder="اسم الفني">
                                        @error('technician_name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Work Details Section -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-muted">
                                    <i class="bi bi-clipboard-check text-warning me-2"></i>
                                    تفاصيل الأعمال
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-journal-text text-info me-1"></i>
                                            الأعمال المنفذة
                                        </label>
                                        <textarea name="work_performed" 
                                                  class="form-control @error('work_performed') is-invalid @enderror" 
                                                  rows="4" 
                                                  placeholder="أدخل تفاصيل الأعمال المنفذة...">{{ old('work_performed', $maintenanceRecord->work_performed) }}</textarea>
                                        @error('work_performed')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Time & Cost Section -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-muted">
                                    <i class="bi bi-clock-history text-danger me-2"></i>
                                    الوقت والتكلفة
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-hourglass-split text-primary me-1"></i>
                                            زمن التوقف (ساعات)
                                        </label>
                                        <input type="number" step="0.01" name="downtime_hours" 
                                               class="form-control @error('downtime_hours') is-invalid @enderror" 
                                               value="{{ old('downtime_hours', $maintenanceRecord->downtime_hours) }}" 
                                               min="0" 
                                               placeholder="0.00">
                                        <small class="text-muted">عدد ساعات التوقف بسبب الصيانة</small>
                                        @error('downtime_hours')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-cash-stack text-success me-1"></i>
                                            تكلفة الصيانة
                                        </label>
                                        <input type="number" step="0.01" name="maintenance_cost" 
                                               class="form-control @error('maintenance_cost') is-invalid @enderror" 
                                               value="{{ old('maintenance_cost', $maintenanceRecord->maintenance_cost) }}" 
                                               min="0" 
                                               placeholder="0.00">
                                        <small class="text-muted">التكلفة الإجمالية بالشيكل</small>
                                        @error('maintenance_cost')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.maintenance-records.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-right me-2"></i>
                                    إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-check-lg me-2"></i>
                                    حفظ التعديلات
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
            const $form = $('#maintenanceRecordForm');
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
                                window.showToast(response.message || 'تم تحديث سجل الصيانة بنجاح', 'success');
                            } else {
                                alert(response.message || 'تم تحديث سجل الصيانة بنجاح');
                            }
                            setTimeout(function() {
                                window.location.href = '{{ route('admin.maintenance-records.index') }}';
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
