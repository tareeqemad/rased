@extends('layouts.admin')

@section('title', 'تفاصيل الثابت')

@php
    $breadcrumbTitle = 'تفاصيل الثابت';
    $breadcrumbParent = 'إدارة الثوابت';
    $breadcrumbParentUrl = route('admin.constants.index');
@endphp

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="{{ asset('assets/admin/css/custom.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/constants.css') }}">
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-database me-2"></i>
                        تفاصيل الثابت
                    </h5>
                    <div class="d-flex gap-2">
                        @can('update', $constant)
                            <a href="{{ route('admin.constants.edit', $constant) }}" class="btn btn-sm">
                                <i class="bi bi-pencil me-1"></i>
                                تعديل
                            </a>
                        @endcan
                        <a href="{{ route('admin.constants.index') }}" class="btn btn-sm">
                            <i class="bi bi-arrow-right me-1"></i>
                            رجوع
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="bi bi-info-circle me-2"></i>
                                        معلومات الثابت
                                    </h6>
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <td class="fw-semibold" style="width: 40%;">اسم الثابت:</td>
                                            <td>{{ $constant->name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">الوصف:</td>
                                            <td>{{ $constant->description ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">الحالة:</td>
                                            <td>
                                                @if($constant->status === 'active')
                                                    <span class="badge bg-success">نشط</span>
                                                @else
                                                    <span class="badge bg-danger">غير نشط</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">ترتيب العرض:</td>
                                            <td>{{ $constant->order }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="bi bi-list-ul me-2"></i>
                                        الإحصائيات
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="text-center p-3 bg-white rounded">
                                                <div class="fs-2 fw-bold text-info">{{ $constant->details()->count() }}</div>
                                                <div class="text-muted small">تفصيل</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center p-3 bg-white rounded">
                                                <div class="fs-2 fw-bold text-success">{{ $constant->details()->count() }}</div>
                                                <div class="text-muted small">نشط</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-list-ul"></i>
                        تفاصيل الثابت
                    </h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#addDetailModal">
                            <i class="bi bi-plus-circle me-1"></i>
                            إضافة تفصيل
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="detailsTable" class="table table-hover w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>البيان</th>
                                    <th>الترميز</th>
                                    <th>القيمة</th>
                                    <th>الملاحظة</th>
                                    <th>الحالة</th>
                                    <th>ترتيب العرض</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($constant->details as $detail)
                                    <tr>
                                        <td>{{ $detail->label }}</td>
                                        <td><code>{{ $detail->code ?? '-' }}</code></td>
                                        <td><code>{{ $detail->value ?? '-' }}</code></td>
                                        <td>{{ $detail->notes ?? '-' }}</td>
                                        <td>
                                            @if($detail->status === 'active')
                                                <span class="badge bg-success">نشط</span>
                                            @else
                                                <span class="badge bg-danger">غير نشط</span>
                                            @endif
                                        </td>
                                        <td>{{ $detail->order }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary edit-detail-btn" 
                                                        data-id="{{ $detail->id }}"
                                                        data-label="{{ $detail->label }}"
                                                        data-code="{{ $detail->code }}"
                                                        data-value="{{ $detail->value }}"
                                                        data-notes="{{ $detail->notes }}"
                                                        data-status="{{ $detail->status }}"
                                                        data-order="{{ $detail->order }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-detail-btn" 
                                                        data-id="{{ $detail->id }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
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
                                <select name="status" class="form-select">
                                    <option value="active">نشط</option>
                                    <option value="inactive">غير نشط</option>
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
                                <select name="status" id="edit_status" class="form-select">
                                    <option value="active">نشط</option>
                                    <option value="inactive">غير نشط</option>
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
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        // تهيئة DataTable
        const table = $('#detailsTable').DataTable({
            responsive: true,
            scrollX: true,
            autoWidth: false,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
            },
            order: [[5, 'asc']],
            pageLength: 10
        });
        
        // إضافة تفصيل
        $('#addDetailForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            
            $.ajax({
                url: '{{ route("admin.constant-details.store") }}',
                type: 'POST',
                data: formData,
                success: function(response) {
                    window.adminNotifications.success('تم إضافة التفصيل بنجاح', 'نجح');
                    setTimeout(() => location.reload(), 1000);
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    let errorMsg = 'حدث خطأ أثناء الإضافة';
                    if (response && response.message) {
                        errorMsg = response.message;
                    } else if (response && response.errors) {
                        errorMsg = Object.values(response.errors).flat().join('<br>');
                    }
                    window.adminNotifications.error(errorMsg, 'خطأ');
                }
            });
        });
        
        // تعديل تفصيل
        $('.edit-detail-btn').on('click', function() {
            const id = $(this).data('id');
            $('#edit_detail_id').val(id);
            $('#edit_label').val($(this).data('label'));
            $('#edit_code').val($(this).data('code'));
            $('#edit_value').val($(this).data('value'));
            $('#edit_notes').val($(this).data('notes'));
            $('#edit_order').val($(this).data('order'));
            $('#edit_status').val($(this).data('status'));
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
                success: function(response) {
                    window.adminNotifications.success('تم تحديث التفصيل بنجاح', 'نجح');
                    setTimeout(() => location.reload(), 1000);
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    let errorMsg = 'حدث خطأ أثناء التعديل';
                    if (response && response.message) {
                        errorMsg = response.message;
                    } else if (response && response.errors) {
                        errorMsg = Object.values(response.errors).flat().join('<br>');
                    }
                    window.adminNotifications.error(errorMsg, 'خطأ');
                }
            });
        });
        
        // حذف تفصيل
        $('.delete-detail-btn').on('click', function() {
            const id = $(this).data('id');
            const row = $(this).closest('tr');
            
            if (confirm('هل أنت متأكد من حذف هذا التفصيل؟')) {
                $.ajax({
                    url: '{{ route("admin.constant-details.destroy", ":id") }}'.replace(':id', id),
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        table.row(row).remove().draw();
                        window.adminNotifications.success('تم حذف التفصيل بنجاح', 'نجح');
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
