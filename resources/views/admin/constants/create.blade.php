@extends('layouts.admin')

@section('title', 'إضافة ثابت جديد')

@php
    $breadcrumbTitle = 'إضافة ثابت جديد';
    $breadcrumbParent = 'إدارة الثوابت';
    $breadcrumbParentUrl = route('admin.constants.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/constants.css') }}">
@endpush

@section('content')
<div class="constants-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="constants-card">
                <div class="constants-card-header">
                    <div>
                        <h5 class="constants-title">
                            <i class="bi bi-plus-circle me-2"></i>
                            إضافة ثابت جديد
                        </h5>
                        <div class="constants-subtitle">
                            قم بإدخال بيانات الثابت الرئيسي وتفاصيله
                        </div>
                    </div>
                    <a href="{{ route('admin.constants.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-right me-2"></i>
                        رجوع
                    </a>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('admin.constants.store') }}" method="POST" id="constantForm">
                        @csrf
                        
                        <!-- قسم المعلومات الأساسية -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3 text-primary">
                                <i class="bi bi-info-circle me-2"></i>
                                المعلومات الأساسية
                            </h6>
                            
                            <div class="row g-3">
                                {{-- Select للثوابت الموجودة (للاستنساخ) --}}
                                @if(isset($existingConstants) && $existingConstants->count() > 0)
                                    <div class="col-12">
                                        <div class="alert alert-info d-flex align-items-center mb-0">
                                            <i class="bi bi-info-circle me-2"></i>
                                            <div>
                                                <strong>استنساخ ثابت موجود (اختياري):</strong>
                                                <small class="d-block text-muted">يمكنك اختيار ثابت موجود لجلب بياناته واستنساخها</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">استنساخ من ثابت موجود</label>
                                        <select id="existingConstantSelect" class="form-select">
                                            <option value="">-- اختر ثابت موجود للاستنساخ (اختياري) --</option>
                                            @foreach($existingConstants as $existing)
                                                <option value="{{ $existing->constant_number }}">
                                                    {{ $existing->constant_number }} - {{ $existing->constant_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">رقم الثابت <span class="text-danger">*</span></label>
                                    <input type="number" name="constant_number" id="constant_number" 
                                           class="form-control @error('constant_number') is-invalid @enderror" 
                                           value="{{ old('constant_number') }}" required min="1">
                                    @error('constant_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">رقم فريد للثابت (مثال: 1, 2, 3...)</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">اسم الثابت <span class="text-danger">*</span></label>
                                    <input type="text" name="constant_name" id="constant_name" 
                                           class="form-control @error('constant_name') is-invalid @enderror" 
                                           value="{{ old('constant_name') }}" required>
                                    @error('constant_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">اسم الثابت (مثال: المحافظة، المدينة)</small>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label fw-semibold">الوصف</label>
                                    <textarea name="description" id="description" 
                                              class="form-control @error('description') is-invalid @enderror" 
                                              rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">ترتيب العرض</label>
                                    <input type="number" name="order" id="order" 
                                           class="form-control @error('order') is-invalid @enderror" 
                                           value="{{ old('order', 0) }}" min="0">
                                    @error('order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">الحالة</label>
                                    <select name="is_active" id="is_active" 
                                            class="form-select @error('is_active') is-invalid @enderror">
                                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>نشط</option>
                                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>غير نشط</option>
                                    </select>
                                    @error('is_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- قسم التفاصيل -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0 text-primary">
                                    <i class="bi bi-list-ul me-2"></i>
                                    تفاصيل الثابت
                                </h6>
                                <button type="button" class="btn btn-sm btn-primary" id="btnAddDetail">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    إضافة تفصيل
                                </button>
                            </div>

                            <div id="detailsContainer">
                                <!-- التفاصيل ستُضاف هنا ديناميكياً -->
                            </div>
                        </div>
                        
                        <hr class="my-4">

                        <!-- أزرار الإجراءات -->
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.constants.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-check-lg me-2"></i>
                                حفظ الثابت والتفاصيل
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
            let detailCounter = 0;
            const $form = $('#constantForm');
            const $submitBtn = $('#submitBtn');
            const $existingConstantSelect = $('#existingConstantSelect');
            const $constantNumber = $('#constant_number');
            const $constantName = $('#constant_name');
            const $description = $('#description');
            const $order = $('#order');
            const $isActive = $('#is_active');
            const $detailsContainer = $('#detailsContainer');

            // Template للتفصيل الجديد
            function getDetailRowHtml(index) {
                return `
                    <div class="detail-row" data-index="${index}">
                        <div class="detail-row-header">
                            <span class="detail-row-number">تفصيل #${index + 1}</span>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-detail" data-index="${index}">
                                <i class="bi bi-trash me-1"></i>
                                حذف
                            </button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">البيان <span class="text-danger">*</span></label>
                                <input type="text" name="details[${index}][label]" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">الترميز</label>
                                <input type="text" name="details[${index}][code]" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">القيمة</label>
                                <input type="text" name="details[${index}][value]" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">ترتيب العرض</label>
                                <input type="number" name="details[${index}][order]" class="form-control" value="0" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">الحالة</label>
                                <select name="details[${index}][is_active]" class="form-select">
                                    <option value="1">نشط</option>
                                    <option value="0">غير نشط</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">الملاحظة</label>
                                <textarea name="details[${index}][notes]" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                `;
            }

            // إضافة تفصيل جديد
            $('#btnAddDetail').on('click', function() {
                const html = getDetailRowHtml(detailCounter);
                $detailsContainer.append(html);
                detailCounter++;
            });

            // حذف تفصيل
            $(document).on('click', '.btn-remove-detail', function() {
                const $row = $(this).closest('.detail-row');
                $row.fadeOut(300, function() {
                    $(this).remove();
                    updateDetailNumbers();
                });
            });

            // تحديث أرقام التفاصيل
            function updateDetailNumbers() {
                $detailsContainer.find('.detail-row').each(function(index) {
                    $(this).find('.detail-row-number').text('تفصيل #' + (index + 1));
                    $(this).attr('data-index', index);
                    $(this).find('.btn-remove-detail').attr('data-index', index);
                    // تحديث أسماء الحقول
                    $(this).find('input, select, textarea').each(function() {
                        const $field = $(this);
                        const name = $field.attr('name');
                        if (name && name.includes('details[')) {
                            const fieldName = name.replace(/details\[\d+\]/, `details[${index}]`);
                            $field.attr('name', fieldName);
                        }
                    });
                });
            }

            // Load constant data when selecting from existing constants
            if ($existingConstantSelect.length) {
                $existingConstantSelect.on('change', function() {
                    const constantNumber = $(this).val();
                    if (!constantNumber) {
                        // Clear fields if no constant selected
                        $constantName.val('');
                        $description.val('');
                        $order.val('0');
                        $isActive.val('1');
                        $detailsContainer.empty();
                        detailCounter = 0;
                        return;
                    }

                    // Show loading
                    const $option = $(this).find('option:selected');
                    const originalText = $option.text();
                    $option.text('جاري التحميل...');

                    $.ajax({
                        url: `{{ route('admin.constants.get-by-number', ['number' => '__NUMBER__']) }}`.replace('__NUMBER__', constantNumber),
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        success: function(response) {
                            if (response.success && response.data) {
                                // Fill form fields with constant data (but keep constant_number empty for new constant)
                                $constantName.val(response.data.constant_name || '');
                                $description.val(response.data.description || '');
                                $order.val(response.data.order || '0');
                                $isActive.val(response.data.is_active ? '1' : '0');
                                
                                // Load details
                                $detailsContainer.empty();
                                detailCounter = 0;
                                if (response.data.details && response.data.details.length > 0) {
                                    response.data.details.forEach(function(detail) {
                                        const html = getDetailRowHtml(detailCounter);
                                        const $row = $(html);
                                        $row.find('input[name*="[label]"]').val(detail.label || '');
                                        $row.find('input[name*="[code]"]').val(detail.code || '');
                                        $row.find('input[name*="[value]"]').val(detail.value || '');
                                        $row.find('input[name*="[order]"]').val(detail.order || '0');
                                        $row.find('select[name*="[is_active]"]').val(detail.is_active ? '1' : '0');
                                        $row.find('textarea[name*="[notes]"]').val(detail.notes || '');
                                        $detailsContainer.append($row);
                                        detailCounter++;
                                    });
                                }
                            }
                            $option.text(originalText);
                        },
                        error: function(xhr) {
                            $option.text(originalText);
                            if (xhr.status === 404) {
                                alert('الثابت المحدد غير موجود');
                            } else {
                                alert('حدث خطأ أثناء جلب بيانات الثابت');
                            }
                        }
                    });
                });
            }

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
                                window.showToast(response.message || 'تم إنشاء الثابت بنجاح', 'success');
                            } else {
                                alert(response.message || 'تم إنشاء الثابت بنجاح');
                            }
                            setTimeout(function() {
                                window.location.href = '{{ route('admin.constants.index') }}';
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
                                // Handle nested field errors (e.g., details.0.label)
                                const fieldParts = field.split('.');
                                if (fieldParts[0] === 'details' && fieldParts.length >= 2) {
                                    const detailIndex = fieldParts[1];
                                    const detailField = fieldParts[2];
                                    const $field = $form.find(`[name="details[${detailIndex}][${detailField}]"]`);
                                    if ($field.length) {
                                        $field.addClass('is-invalid');
                                        const errorMsg = Array.isArray(messages) ? messages[0] : messages;
                                        if (!firstError) firstError = errorMsg;
                                        $field.after('<div class="invalid-feedback d-block">' + errorMsg + '</div>');
                                    }
                                } else {
                                    const $field = $form.find('[name="' + field + '"]');
                                    if ($field.length) {
                                        $field.addClass('is-invalid');
                                        const errorMsg = Array.isArray(messages) ? messages[0] : messages;
                                        if (!firstError) firstError = errorMsg;
                                        $field.after('<div class="invalid-feedback d-block">' + errorMsg + '</div>');
                                    }
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
