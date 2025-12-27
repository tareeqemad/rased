@if($groupedLogs->isNotEmpty())
    @foreach($groupedLogs as $generatorId => $logs)
        @php
            $generator = $logs->first()->generator;
        @endphp
        
        @if($generator)
            {{-- Generator Header --}}
            <div class="log-generator-group mb-4">
                <div class="log-generator-header bg-light p-3 rounded-top border">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="log-generator-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-lightning-charge fs-5"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">
                                    {{ $generator->name }}
                                    <span class="badge bg-secondary ms-2">{{ $generator->generator_number }}</span>
                                </h5>
                                <div class="text-muted small">
                                    {{ $generator->capacity_kva ?? '-' }} KVA
                                    @if($generator->operator)
                                        | {{ $generator->operator->name }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="text-muted small">
                            <i class="bi bi-list-ul me-1"></i>
                            عدد السجلات: <strong>{{ $logs->count() }}</strong>
                        </div>
                    </div>
                </div>

                {{-- Logs for this generator --}}
                <div class="log-list bg-white border border-top-0 rounded-bottom p-3">
                    @foreach($logs as $log)
                        <div class="log-row mb-2" data-operation-log-id="{{ $log->id }}">
                            <div class="log-row-main">
                                <div class="log-row-content">
                                    <div class="log-row-header">
                                        <div class="log-row-title">
                                            <i class="bi bi-journal-text me-2 text-primary"></i>
                                            @if($log->sequence)
                                                <span class="fw-bold">سجل تشغيل #{{ $log->sequence }}</span>
                                                <span class="badge bg-primary ms-2" title="رقم التسلسل لهذا المولد">
                                                    <i class="bi bi-list-ol me-1"></i>
                                                    تسلسل {{ $log->sequence }}
                                                </span>
                                            @else
                                                <span class="fw-bold">سجل تشغيل #{{ $log->id }}</span>
                                            @endif
                                        </div>
                                        <div class="log-row-meta">
                                            <span class="badge bg-info">{{ $log->operation_date->format('Y-m-d') }}</span>
                                            @if($log->start_time && $log->end_time)
                                                <span class="badge bg-secondary ms-2">
                                                    {{ \Carbon\Carbon::parse($log->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($log->end_time)->format('H:i') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="log-row-details mt-2">
                                        <div class="row g-2">
                                            @if($log->start_time && $log->end_time)
                                                @php
                                                    $startTime = \Carbon\Carbon::parse($log->operation_date->format('Y-m-d') . ' ' . $log->start_time->format('H:i:s'));
                                                    $endTime = \Carbon\Carbon::parse($log->operation_date->format('Y-m-d') . ' ' . $log->end_time->format('H:i:s'));
                                                    if ($endTime->lt($startTime)) {
                                                        $endTime->addDay();
                                                    }
                                                    $totalMinutes = $startTime->diffInMinutes($endTime);
                                                    $hours = floor($totalMinutes / 60);
                                                    $minutes = $totalMinutes % 60;
                                                @endphp
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="log-detail-item">
                                                        <i class="bi bi-hourglass-split me-2 text-muted"></i>
                                                        <span class="text-muted">المدة:</span>
                                                        <strong>
                                                            @if($hours > 0){{ $hours }}س @endif
                                                            @if($minutes > 0){{ $minutes }}د @endif
                                                        </strong>
                                                    </div>
                                                </div>
                                            @endif

                                            @if($log->load_percentage)
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="log-detail-item">
                                                        <i class="bi bi-speedometer2 me-2 text-muted"></i>
                                                        <span class="text-muted">التحميل:</span>
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
                                                        <span class="text-muted">الوقود:</span>
                                                        <strong>{{ number_format($log->fuel_consumed, 2) }} لتر</strong>
                                                    </div>
                                                </div>
                                            @endif

                                            @if($log->energy_produced)
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="log-detail-item">
                                                        <i class="bi bi-lightning me-2 text-muted"></i>
                                                        <span class="text-muted">الطاقة:</span>
                                                        <strong>{{ number_format($log->energy_produced, 2) }} kWh</strong>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="log-row-actions">
                                    @can('view', $log)
                                        <a href="{{ route('admin.operation-logs.show', $log) }}" class="btn btn-xs btn-outline-info" title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endcan
                                    @can('update', $log)
                                        <a href="{{ route('admin.operation-logs.edit', $log) }}" class="btn btn-xs btn-outline-primary" title="تعديل">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $log)
                                        <button type="button" class="btn btn-xs btn-outline-danger log-delete-btn" 
                                                data-operation-log-id="{{ $log->id }}"
                                                data-operation-log-name="سجل #{{ $log->id }}"
                                                title="حذف">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        @if(!$loop->last)
                            <hr class="my-2">
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach

    {{-- Pagination for grouped view --}}
    @if(isset($operationLogs) && $operationLogs->hasPages())
        <div class="log-pagination mt-4">
            {{ $operationLogs->links() }}
        </div>
    @endif
@else
    <div class="log-empty-state text-center py-5">
        <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
        <h5 class="text-muted">لا توجد سجلات تشغيل</h5>
        <p class="text-muted">لم يتم العثور على سجلات تشغيل تطابق البحث</p>
    </div>
@endif

