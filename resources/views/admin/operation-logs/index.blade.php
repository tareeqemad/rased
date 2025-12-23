@extends('layouts.admin')

@section('title', 'سجلات التشغيل')

@php
    $breadcrumbTitle = 'سجلات التشغيل';
@endphp

@section('content')
    <div class="container-fluid">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-journal-text me-2"></i>
                    سجلات التشغيل
                </h5>
                @can('create', App\Models\OperationLog::class)
                    <a href="{{ route('admin.operation-logs.create') }}" class="btn btn-primary">
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
                                <th>المشغل</th>
                                <th>رقم المولد</th>
                                <th>تاريخ التشغيل</th>
                                <th>وقت البدء</th>
                                <th>وقت الإيقاف</th>
                                <th>نسبة التحميل</th>
                                <th>الوقود المستهلك</th>
                                <th>الطاقة المنتجة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($operationLogs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>
                                        @if($log->operator)
                                            <span class="badge bg-info">{{ $log->operator->name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->generator)
                                            <span class="badge bg-secondary">{{ $log->generator->generator_number }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->operation_date->format('Y-m-d') }}</td>
                                    <td>{{ $log->start_time }}</td>
                                    <td>{{ $log->end_time }}</td>
                                    <td>
                                        @if($log->load_percentage)
                                            <span class="badge bg-{{ $log->load_percentage >= 80 ? 'success' : ($log->load_percentage >= 50 ? 'warning' : 'danger') }}">
                                                {{ $log->load_percentage }}%
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->fuel_consumed)
                                            {{ number_format($log->fuel_consumed, 2) }} لتر
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->energy_produced)
                                            {{ number_format($log->energy_produced, 2) }} kWh
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            @can('view', $log)
                                                <a href="{{ route('admin.operation-logs.show', $log) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @endcan
                                            @can('update', $log)
                                                <a href="{{ route('admin.operation-logs.edit', $log) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endcan
                                            @can('delete', $log)
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $log->id }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>

                                @can('delete', $log)
                                    <div class="modal fade" id="deleteModal{{ $log->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">تأكيد الحذف</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>هل أنت متأكد من حذف سجل التشغيل #{{ $log->id }}؟</p>
                                                    <p class="text-danger"><small>هذا الإجراء لا يمكن التراجع عنه</small></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                    <form action="{{ route('admin.operation-logs.destroy', $log) }}" method="POST" class="d-inline">
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
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        لا توجد سجلات تشغيل
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($operationLogs->hasPages())
                <div class="card-footer bg-white border-top">
                    {{ $operationLogs->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

