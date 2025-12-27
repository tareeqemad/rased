@extends('layouts.admin')

@section('title', 'تعديل الثابت')

@php
    $breadcrumbTitle = 'تعديل الثابت';
    $breadcrumbParent = 'إدارة الثوابت';
    $breadcrumbParentUrl = route('admin.constants.index');
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/css/custom.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/constants.css') }}">
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Card تعديل الثابت الرئيسي -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-pencil me-2"></i>
                        تعديل الثابت الرئيسي
                    </h5>
                    <a href="{{ route('admin.constants.index') }}" class="btn btn-sm">
                        <i class="bi bi-arrow-right me-1"></i>
                        رجوع
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.constants.update', $constant) }}" method="POST" id="constantForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">رقم الثابت <span class="text-danger">*</span></label>
                                <input type="number" name="constant_number" class="form-control @error('constant_number') is-invalid @enderror" 
                                       value="{{ old('constant_number', $constant->constant_number) }}" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">اسم الثابت <span class="text-danger">*</span></label>
                                <input type="text" name="constant_name" class="form-control @error('constant_name') is-invalid @enderror" 
                                       value="{{ old('constant_name', $constant->constant_name) }}" required>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">الوصف</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                          rows="3">{{ old('description', $constant->description) }}</textarea>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">ترتيب العرض</label>
                                <input type="number" name="order" class="form-control @error('order') is-invalid @enderror" 
                                       value="{{ old('order', $constant->order) }}" min="0">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">الحالة</label>
                                <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
                                    <option value="1" {{ old('is_active', $constant->is_active) ? 'selected' : '' }}>نشط</option>
                                    <option value="0" {{ !old('is_active', $constant->is_active) ? 'selected' : '' }}>غير نشط</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Card تفاصيل الثابت -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-list-ul me-2"></i>
                        تفاصيل الثابت
                    </h5>
                    <button type="button" class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#addDetailModal">
                        <i class="bi bi-plus-circle me-1"></i>
                        إضافة تفصيل
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-nowrap">البيان</th>
                                    <th class="text-nowrap d-none d-md-table-cell">الترميز</th>
                                    <th class="text-nowrap d-none d-lg-table-cell">القيمة</th>
                                    <th class="text-nowrap d-none d-lg-table-cell">الملاحظة</th>
                                    <th class="text-nowrap">الحالة</th>
                                    <th class="text-nowrap d-none d-xl-table-cell">ترتيب العرض</th>
                                    <th class="text-nowrap">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($constant->allDetails as $detail)
                                    <tr>
                                        <td class="text-nowrap">
                                            <span class="fw-semibold">{{ $detail->label }}</span>
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            <code>{{ $detail->code ?? '-' }}</code>
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            <code>{{ $detail->value ?? '-' }}</code>
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            <small class="text-muted">{{ Str::limit($detail->notes ?? '-', 30) }}</small>
                                        </td>
                                        <td class="text-nowrap">
                                            @if($detail->is_active)
                                                <span class="badge bg-success">نشط</span>
                                            @else
                                                <span class="badge bg-danger">غير نشط</span>
                                            @endif
                                        </td>
                                        <td class="d-none d-xl-table-cell">
                                            <small class="text-muted">{{ $detail->order }}</small>
                                        </td>
                                        <td class="text-nowrap">
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary edit-detail-btn" 
                                                        data-id="{{ $detail->id }}"
                                                        data-label="{{ $detail->label }}"
                                                        data-code="{{ $detail->code }}"
                                                        data-value="{{ $detail->value }}"
                                                        data-notes="{{ $detail->notes }}"
                                                        data-is-active="{{ $detail->is_active ? 1 : 0 }}"
                                                        data-order="{{ $detail->order }}"
                                                        title="تعديل">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-detail-btn" 
                                                        data-id="{{ $detail->id }}"
                                                        data-label="{{ $detail->label }}"
                                                        title="حذف">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            لا توجد تفاصيل لهذا الثابت
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.constants.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-2"></i>
                        إلغاء
                    </a>
                    <button type="submit" form="constantForm" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>
                        حفظ جميع التغييرات
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal إضافة تفصيل -->
    <div class="modal fade" id="addDetailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة تفصيل جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addDetailForm">
                    @csrf
                    <input type="hidden" name="constant_master_id" value="{{ $constant->id }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">البيان <span class="text-danger">*</span></label>
                            <input type="text" name="label" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الترميز</label>
                            <input type="text" name="code" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">القيمة</label>
                            <input type="text" name="value" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الملاحظة</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">ترتيب العرض</label>
                                <input type="number" name="order" class="form-control" value="0" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">الحالة</label>
                                <select name="is_active" class="form-select">
                                    <option value="1">نشط</option>
                                    <option value="0">غير نشط</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal تعديل تفصيل -->
    <div class="modal fade" id="editDetailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل التفصيل</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editDetailForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="detail_id" id="edit_detail_id">
                    <input type="hidden" name="constant_master_id" value="{{ $constant->id }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">البيان <span class="text-danger">*</span></label>
                            <input type="text" name="label" id="edit_label" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الترميز</label>
                            <input type="text" name="code" id="edit_code" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">القيمة</label>
                            <input type="text" name="value" id="edit_value" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الملاحظة</label>
                            <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">ترتيب العرض</label>
                                <input type="number" name="order" id="edit_order" class="form-control" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">الحالة</label>
                                <select name="is_active" id="edit_is_active" class="form-select">
                                    <option value="1">نشط</option>
                                    <option value="0">غير نشط</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/libs/jquery/jquery.min.js') }}"></script>
<script>
    $(document).ready(function() {
        // Main form AJAX submission
        const $mainForm = $('#constantForm');
        const $submitBtns = $('[form="constantForm"], #constantForm button[type="submit"]');

        $mainForm.on('submit', function(e) {
            e.preventDefault();

            if (!$mainForm[0].checkValidity()) {
                $mainForm[0].reportValidity();
                return false;
            }

            $submitBtns.prop('disabled', true);
            const originalText = $submitBtns.first().html();
            $submitBtns.html('<span class="spinner-border spinner-border-sm me-2"></span>جاري الحفظ...');

            const formData = new FormData(this);

            $.ajax({
                url: $mainForm.attr('action'),
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
                            window.showToast(response.message || 'تم تحديث الثابت بنجاح', 'success');
                        } else {
                            alert(response.message || 'تم تحديث الثابت بنجاح');
                        }
                        setTimeout(function() {
                            window.location.href = '{{ route('admin.constants.index') }}';
                        }, 500);
                    }
                },
                error: function(xhr) {
                    $submitBtns.prop('disabled', false);
                    $submitBtns.html(originalText);

                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors || {};
                        let firstError = '';

                        $mainForm.find('.is-invalid').removeClass('is-invalid');
                        $mainForm.find('.invalid-feedback').remove();

                        $.each(errors, function(field, messages) {
                            const $field = $mainForm.find('[name="' + field + '"]');
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

        // إضافة تفصيل
        $('#addDetailForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            
            $.ajax({
                url: '{{ route("admin.constant-details.store") }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        window.adminNotifications.success(response.message || 'تم إضافة التفصيل بنجاح', 'نجح');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        window.adminNotifications.error(response.message || 'حدث خطأ أثناء الإضافة', 'خطأ');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    let errorMsg = 'حدث خطأ أثناء الإضافة';
                    if (response && response.errors) {
                        errorMsg = Object.values(response.errors).flat().join('<br>');
                    } else if (response && response.message) {
                        errorMsg = response.message;
                    }
                    window.adminNotifications.error(errorMsg, 'خطأ');
                }
            });
        });
        
        // تعديل تفصيل
        $(document).on('click', '.edit-detail-btn', function() {
            const id = $(this).data('id');
            $('#edit_detail_id').val(id);
            $('#edit_label').val($(this).data('label') || '');
            $('#edit_code').val($(this).data('code') || '');
            $('#edit_value').val($(this).data('value') || '');
            $('#edit_notes').val($(this).data('notes') || '');
            $('#edit_order').val($(this).data('order') || 0);
            $('#edit_is_active').val($(this).data('is-active') || 1);
            $('#editDetailModal').modal('show');
        });
        
        $('#editDetailForm').on('submit', function(e) {
            e.preventDefault();
            const id = $('#edit_detail_id').val();
            const formData = $(this).serialize();
            
            $.ajax({
                url: '{{ route("admin.constant-details.update", ":id") }}'.replace(':id', id),
                type: 'POST',
                data: formData + '&_method=PUT',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        window.adminNotifications.success(response.message || 'تم تحديث التفصيل بنجاح', 'نجح');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        window.adminNotifications.error(response.message || 'حدث خطأ أثناء التعديل', 'خطأ');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    let errorMsg = 'حدث خطأ أثناء التعديل';
                    if (response && response.errors) {
                        errorMsg = Object.values(response.errors).flat().join('<br>');
                    } else if (response && response.message) {
                        errorMsg = response.message;
                    }
                    window.adminNotifications.error(errorMsg, 'خطأ');
                }
            });
        });
        
        // حذف تفصيل
        $(document).on('click', '.delete-detail-btn', function() {
            const id = $(this).data('id');
            const label = $(this).data('label');
            
            if (confirm('هل أنت متأكد من حذف التفصيل "' + label + '"؟')) {
                $.ajax({
                    url: '{{ route("admin.constant-details.destroy", ":id") }}'.replace(':id', id),
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            window.adminNotifications.success(response.message || 'تم حذف التفصيل بنجاح', 'نجح');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            window.adminNotifications.error(response.message || 'حدث خطأ أثناء الحذف', 'خطأ');
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        window.adminNotifications.error(response?.message || 'حدث خطأ أثناء الحذف', 'خطأ');
                    }
                });
            }
        });
    });
</script>
@endpush
