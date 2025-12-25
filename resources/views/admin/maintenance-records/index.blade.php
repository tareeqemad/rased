@extends('layouts.admin')

@section('title', 'سجلات الصيانة')

@php
    $breadcrumbTitle = 'سجلات الصيانة';
@endphp

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-tools me-2"></i>
                        سجلات الصيانة
                    </h5>
                    @can('create', App\Models\MaintenanceRecord::class)
                        <a href="{{ route('admin.maintenance-records.create') }}" class="btn btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>
                            إضافة سجل جديد
                        </a>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>رقم المولد</th>
                                    <th>نوع الصيانة</th>
                                    <th>تاريخ الصيانة</th>
                                    <th>اسم الفني</th>
                                    <th>زمن التوقف</th>
                                    <th>تكلفة الصيانة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($maintenanceRecords as $record)
                                    <tr>
                                        <td>{{ $record->id }}</td>
                                        <td>
                                            @if($record->generator)
                                                <span class="badge bg-secondary">{{ $record->generator->generator_number }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $record->maintenance_type === 'periodic' ? 'info' : 'warning' }}">
                                                {{ $record->maintenance_type === 'periodic' ? 'دورية' : 'طارئة' }}
                                            </span>
                                        </td>
                                        <td>{{ $record->maintenance_date->format('Y-m-d') }}</td>
                                        <td>{{ $record->technician_name ?? '-' }}</td>
                                        <td>
                                            @if($record->downtime_hours)
                                                {{ number_format($record->downtime_hours, 2) }} ساعة
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($record->maintenance_cost)
                                                {{ number_format($record->maintenance_cost, 2) }} ₪
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                @can('view', $record)
                                                    <a href="{{ route('admin.maintenance-records.show', $record) }}" class="btn btn-sm btn-outline-info">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                @endcan
                                                @can('update', $record)
                                                    <a href="{{ route('admin.maintenance-records.edit', $record) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                @endcan
                                                @can('delete', $record)
                                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $record->id }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>

                                    @can('delete', $record)
                                        <div class="modal fade" id="deleteModal{{ $record->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">تأكيد الحذف</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>هل أنت متأكد من حذف سجل الصيانة #{{ $record->id }}؟</p>
                                                        <p class="text-danger"><small>هذا الإجراء لا يمكن التراجع عنه</small></p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                        <form action="{{ route('admin.maintenance-records.destroy', $record) }}" method="POST" class="d-inline">
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
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            لا توجد سجلات صيانة
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($maintenanceRecords->hasPages())
                    <div class="card-footer">
                        {{ $maintenanceRecords->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
