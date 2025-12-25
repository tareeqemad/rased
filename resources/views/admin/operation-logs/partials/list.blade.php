@if($operationLogs->count() > 0)
    <div class="log-list">
        @foreach($operationLogs as $log)
            <div class="log-row" data-operation-log-id="{{ $log->id }}">
                <div class="log-row-main">
                    <div class="log-row-content">
                        <div class="log-row-header">
                            <div class="log-row-title">
                                <i class="bi bi-journal-text me-2 text-primary"></i>
                                <span class="fw-bold">سجل تشغيل #{{ $log->id }}</span>
                                @if($log->generator)
                                    <span class="badge bg-secondary ms-2">{{ $log->generator->generator_number }}</span>
                                @endif
                            </div>
                            <div class="log-row-meta">
                                <span class="badge bg-info">{{ $log->operation_date->format('Y-m-d') }}</span>
                            </div>
                        </div>

                        <div class="log-row-details">
                            <div class="row g-2">
                                @if($log->operator)
                                    <div class="col-md-3 col-sm-6">
                                        <div class="log-detail-item">
                                            <i class="bi bi-building me-2 text-muted"></i>
                                            <span class="text-muted">المشغل:</span>
                                            <strong>{{ $log->operator->name }}</strong>
                                        </div>
                                    </div>
                                @endif
                                
                                @if($log->generator)
                                    <div class="col-md-3 col-sm-6">
                                        <div class="log-detail-item">
                                            <i class="bi bi-lightning-charge me-2 text-muted"></i>
                                            <span class="text-muted">المولد:</span>
                                            <strong>{{ $log->generator->name }}</strong>
                                        </div>
                                    </div>
                                @endif

                                @if($log->start_time && $log->end_time)
                                    <div class="col-md-3 col-sm-6">
                                        <div class="log-detail-item">
                                            <i class="bi bi-clock me-2 text-muted"></i>
                                            <span class="text-muted">الوقت:</span>
                                            <strong>{{ date('H:i', strtotime($log->start_time)) }} - {{ date('H:i', strtotime($log->end_time)) }}</strong>
                                        </div>
                                    </div>
                                @endif

                                @if($log->load_percentage)
                                    <div class="col-md-3 col-sm-6">
                                        <div class="log-detail-item">
                                            <i class="bi bi-speedometer2 me-2 text-muted"></i>
                                            <span class="text-muted">نسبة التحميل:</span>
                                            <strong>
                                                <span class="badge bg-{{ $log->load_percentage >= 80 ? 'success' : ($log->load_percentage >= 50 ? 'warning' : 'danger') }}">
                                                    {{ number_format($log->load_percentage, 2) }}%
                                                </span>
                                            </strong>
                                        </div>
                                    </div>
                                @endif

                                @if($log->fuel_consumed)
                                    <div class="col-md-3 col-sm-6">
                                        <div class="log-detail-item">
                                            <i class="bi bi-fuel-pump me-2 text-muted"></i>
                                            <span class="text-muted">الوقود المستهلك:</span>
                                            <strong>{{ number_format($log->fuel_consumed, 2) }} لتر</strong>
                                        </div>
                                    </div>
                                @endif

                                @if($log->energy_produced)
                                    <div class="col-md-3 col-sm-6">
                                        <div class="log-detail-item">
                                            <i class="bi bi-lightning me-2 text-muted"></i>
                                            <span class="text-muted">الطاقة المنتجة:</span>
                                            <strong>{{ number_format($log->energy_produced, 2) }} kWh</strong>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="log-row-actions">
                        @can('view', $log)
                            <a href="{{ route('admin.operation-logs.show', $log) }}" class="btn btn-sm btn-outline-info" title="عرض">
                                <i class="bi bi-eye"></i>
                            </a>
                        @endcan
                        @can('update', $log)
                            <a href="{{ route('admin.operation-logs.edit', $log) }}" class="btn btn-sm btn-outline-primary" title="تعديل">
                                <i class="bi bi-pencil"></i>
                            </a>
                        @endcan
                        @can('delete', $log)
                            <button type="button" class="btn btn-sm btn-outline-danger log-delete-btn" 
                                    data-operation-log-id="{{ $log->id }}"
                                    data-operation-log-name="سجل #{{ $log->id }}"
                                    title="حذف">
                                <i class="bi bi-trash"></i>
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($operationLogs->hasPages())
        <div class="log-pagination mt-4">
            {{ $operationLogs->links() }}
        </div>
    @endif
@else
    <div class="log-empty-state text-center py-5">
        <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
        <h5 class="text-muted">لا توجد سجلات تشغيل</h5>
        <p class="text-muted">لم يتم العثور على سجلات تشغيل تطابق البحث</p>
        @can('create', App\Models\OperationLog::class)
            <a href="{{ route('admin.operation-logs.create') }}" class="btn btn-primary mt-3">
                <i class="bi bi-plus-circle me-2"></i>
                إضافة سجل تشغيل جديد
            </a>
        @endcan
    </div>
@endif

