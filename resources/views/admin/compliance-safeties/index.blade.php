@extends('layouts.admin')

@section('title', 'الامتثال والسلامة')

@php
    $breadcrumbTitle = 'الامتثال والسلامة';
@endphp

@section('content')
    <div class="container-fluid">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-shield-check me-2"></i>
                    الامتثال والسلامة
                </h5>
                @can('create', App\Models\ComplianceSafety::class)
                    <a href="{{ route('admin.compliance-safeties.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        إضافة سجل جديد
                    </a>
                @endcan
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>اسم المشغل</th>
                                <th>حالة شهادة السلامة</th>
                                <th>تاريخ آخر زيارة</th>
                                <th>الجهة المنفذة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($complianceSafeties as $compliance)
                                <tr>
                                    <td>{{ $compliance->id }}</td>
                                    <td>
                                        @if($compliance->operator)
                                            <span class="badge bg-info">{{ $compliance->operator->name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'available' => 'success',
                                                'expired' => 'warning',
                                                'not_available' => 'danger'
                                            ];
                                            $statusLabels = [
                                                'available' => 'متوفرة',
                                                'expired' => 'منتهية',
                                                'not_available' => 'غير متوفرة'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$compliance->safety_certificate_status] ?? 'secondary' }}">
                                            {{ $statusLabels[$compliance->safety_certificate_status] ?? $compliance->safety_certificate_status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($compliance->last_inspection_date)
                                            {{ $compliance->last_inspection_date->format('Y-m-d') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $compliance->inspection_authority ?? '-' }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            @can('view', $compliance)
                                                <a href="{{ route('admin.compliance-safeties.show', $compliance) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @endcan
                                            @can('update', $compliance)
                                                <a href="{{ route('admin.compliance-safeties.edit', $compliance) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endcan
                                            @can('delete', $compliance)
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $compliance->id }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>

                                @can('delete', $compliance)
                                    <div class="modal fade" id="deleteModal{{ $compliance->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">تأكيد الحذف</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>هل أنت متأكد من حذف سجل الامتثال والسلامة #{{ $compliance->id }}؟</p>
                                                    <p class="text-danger"><small>هذا الإجراء لا يمكن التراجع عنه</small></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                    <form action="{{ route('admin.compliance-safeties.destroy', $compliance) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">حذف</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endcan
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        لا توجد سجلات امتثال وسلامة
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($complianceSafeties->hasPages())
                <div class="card-footer bg-white border-top">
                    {{ $complianceSafeties->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

